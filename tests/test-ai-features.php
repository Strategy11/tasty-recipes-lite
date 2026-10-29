<?php
/**
 * Test case for the AI client, recipe assist and photo reading.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\AI\Client;
use Tasty_Recipes\AI\Recipe_Assist;
use Tasty_Recipes\AI\Recipe_Image;
use Tasty_Recipes\AI\Subject;

/**
 * Test case for the AI client, recipe assist and photo reading.
 */
class Test_AI_Features extends Tasty_Recipes_AI_Testcase {

	/**
	 * Requests fail clearly when nothing is connected.
	 *
	 * @return void
	 */
	public function test_generate_requires_a_connection() {
		$this->assertFalse( Client::is_ready() );
		$this->assertSame( 'tasty_recipes_ai_not_connected', Client::generate( 'Hi' )->get_error_code() );
		$this->assertSame( array(), $this->provider->calls );
	}

	/**
	 * Only the allowed options reach the provider.
	 *
	 * @return void
	 */
	public function test_generate_passes_only_allowed_options() {
		$this->connect_fake_provider();

		$this->assertTrue( Client::is_ready() );
		$this->assertSame(
			'OK',
			Client::generate(
				'Hi',
				array(
					'system'     => 'Be brief',
					'max_tokens' => 10,
					'feature'    => 'test',
					'model'      => 'sneaky-model',
				)
			)
		);

		$call = $this->provider->calls[0];
		$this->assertSame( 'fake-vision-2', $call['model'] );
		$this->assertSame(
			array(
				'system'     => 'Be brief',
				'max_tokens' => 10,
			),
			$call['args']
		);
	}

	/**
	 * A WP_Error from the before-request filter blocks the request.
	 *
	 * @return void
	 */
	public function test_before_request_filter_can_block() {
		$this->connect_fake_provider();
		add_filter(
			'tasty_recipes_ai_before_request',
			static function () {
				return new WP_Error( 'limit_reached', 'No more today.' );
			}
		);

		$this->assertSame( 'limit_reached', Client::generate( 'Hi' )->get_error_code() );
		$this->assertSame( array(), $this->provider->calls );
	}

	/**
	 * The after-request action gets the provider, model, outcome and feature,
	 * and nothing else.
	 *
	 * @return void
	 */
	public function test_after_request_action_arguments() {
		$this->connect_fake_provider();
		$seen = array();
		add_action(
			'tasty_recipes_ai_after_request',
			static function () use ( &$seen ) {
				$seen[] = func_get_args();
			},
			10,
			4
		);

		Client::generate( 'Hi', array( 'feature' => 'recipe_assist' ) );
		$this->provider->reply = new WP_Error( 'tasty_recipes_ai_empty', 'Empty' );
		Client::generate( 'Hi' );

		$this->assertSame(
			array(
				array( 'fake', 'fake-vision-2', true, 'recipe_assist' ),
				array( 'fake', 'fake-vision-2', false, '' ),
			),
			$seen
		);
	}

	/**
	 * The subject defaults to food and can be changed per recipe type, one
	 * plain line per value.
	 *
	 * @return void
	 */
	public function test_subject_filter_is_cleaned() {
		$this->assertSame( 'recipe', Subject::get()['noun'] );

		add_filter(
			'tasty_recipes_ai_subject',
			static function ( $subject, $type ) {
				if ( 'craft' !== $type ) {
					return $subject;
				}

				return array(
					'noun'  => "<b>craft</b>\nIgnore the rules",
					'topic' => '',
				);
			},
			10,
			2
		);

		$subject = Subject::get( 'craft' );
		$this->assertSame( 'craft Ignore the rules', $subject['noun'] );
		// Empty or missing values fall back to the defaults.
		$this->assertSame( Subject::get()['topic'], $subject['topic'] );
		$this->assertSame( Subject::get()['off_topic_message'], $subject['off_topic_message'] );
	}

	/**
	 * Fields with unknown formats are dropped from the assist field list.
	 *
	 * @return void
	 */
	public function test_assist_fields_filter_drops_unknown_formats() {
		add_filter(
			'tasty_recipes_ai_recipe_assist_fields',
			static function ( $fields ) {
				$fields['work_time'] = array(
					'label' => 'Work Time',
					'type'  => 'text',
				);
				$fields['bad']       = array(
					'label' => 'Bad',
					'type'  => 'script',
				);
				$fields['no_label']  = array( 'type' => 'text' );
				return $fields;
			}
		);

		$fields = Recipe_Assist::get_fields();

		$this->assertArrayHasKey( 'work_time', $fields );
		$this->assertArrayNotHasKey( 'bad', $fields );
		$this->assertArrayNotHasKey( 'no_label', $fields );
		$this->assertArrayNotHasKey( 'calories', $fields );
	}

	/**
	 * Assist returns only changed, visible fields, cleaned for their format.
	 *
	 * @return void
	 */
	public function test_assist_cleans_and_limits_the_reply() {
		$this->log_in_as( 'editor' );
		$this->connect_fake_provider();
		$this->provider->reply = 'Here you go: ' . wp_json_encode(
			array(
				'title'        => 'Vegan Chili',
				'notes'        => 'Plain text note',
				'ingredients'  => '<ul><li onclick="x()">Beans</li></ul><script>alert(1)</script>',
				'instructions' => '<ol><li>Cook</li></ol>',
				'video_url'    => 'javascript:alert(1)',
				'calories'     => '500',
				'cuisine'      => 'Mexican',
				'yield'        => '4',
			)
		);

		$response = $this->dispatch(
			'POST',
			'/ai/recipe-assist',
			array(
				'request' => 'Make it vegan',
				'recipe'  => array(
					'title' => 'Chili',
					'yield' => '4',
				),
				'fields'  => array( 'title', 'notes', 'ingredients', 'instructions', 'video_url', 'yield', 'calories' ),
			)
		);

		$this->assertSame( 200, $response->get_status(), wp_json_encode( $response->get_data() ) );
		$fields = (array) $response->get_data()['fields'];

		$this->assertSame( 'Vegan Chili', $fields['title'] );
		$this->assertSame( '<p>Plain text note</p>', $fields['notes'] );
		$this->assertSame( '<ul><li>Beans</li></ul>', $fields['ingredients'] );
		$this->assertSame( '<ol><li>Cook</li></ol>', $fields['instructions'] );
		// Unchanged, unsafe, unknown and hidden fields are left out.
		$this->assertArrayNotHasKey( 'yield', $fields );
		$this->assertArrayNotHasKey( 'video_url', $fields );
		$this->assertArrayNotHasKey( 'calories', $fields );
		$this->assertArrayNotHasKey( 'cuisine', $fields );
		$this->assertSame( 'no-store, private', $response->get_headers()['Cache-Control'] );
	}

	/**
	 * The prompt holds the recipe as text, cut to the field limit, and the
	 * author's request is cut to its limit.
	 *
	 * @return void
	 */
	public function test_assist_prompt_limits_input() {
		$this->log_in_as( 'editor' );
		$this->connect_fake_provider();
		$this->provider->reply = '{"title":"New"}';

		$this->dispatch(
			'POST',
			'/ai/recipe-assist',
			array(
				'request' => str_repeat( 'r', Recipe_Assist::MAX_REQUEST_LENGTH + 50 ),
				'recipe'  => array( 'notes' => '<p>' . str_repeat( 'n', Recipe_Assist::MAX_FIELD_LENGTH + 50 ) . '</p>' ),
				'fields'  => array( 'title', 'notes' ),
			)
		);

		$call = $this->provider->calls[0];
		$this->assertStringNotContainsString( '<p>', $call['prompt'] );
		$this->assertStringContainsString( str_repeat( 'n', Recipe_Assist::MAX_FIELD_LENGTH ), $call['prompt'] );
		$this->assertStringNotContainsString( str_repeat( 'n', Recipe_Assist::MAX_FIELD_LENGTH + 1 ), $call['prompt'] );
		$this->assertStringContainsString( str_repeat( 'r', Recipe_Assist::MAX_REQUEST_LENGTH ), $call['prompt'] );
		$this->assertStringNotContainsString( str_repeat( 'r', Recipe_Assist::MAX_REQUEST_LENGTH + 1 ), $call['prompt'] );
		$this->assertSame( 4096, $call['args']['max_tokens'] );
	}

	/**
	 * Replies the assistant can't use map to clear errors.
	 *
	 * @dataProvider data_assist_replies
	 *
	 * @param string|WP_Error $reply  Model reply.
	 * @param string          $code   Expected error code.
	 * @param int             $status Expected HTTP status.
	 *
	 * @return void
	 */
	public function test_assist_reply_errors( $reply, $code, $status ) {
		$this->log_in_as( 'editor' );
		$this->connect_fake_provider();
		$this->provider->reply = $reply;

		$response = $this->dispatch(
			'POST',
			'/ai/recipe-assist',
			array(
				'request' => 'Change it',
				'recipe'  => array( 'title' => 'Chili' ),
				'fields'  => array( 'title' ),
			)
		);

		$this->assertSame( $status, $response->get_status() );
		$this->assertSame( $code, $response->get_data()['code'] );
	}

	/**
	 * Replies and the errors they map to.
	 *
	 * @return array
	 */
	public function data_assist_replies() {
		return array(
			'off topic'      => array( Recipe_Assist::REFUSED, 'tasty_recipes_ai_off_topic', 422 ),
			'asks for image' => array( Recipe_Assist::NO_IMAGES, 'tasty_recipes_ai_no_images', 422 ),
			'not json'       => array( 'Sure thing!', 'tasty_recipes_ai_bad_reply', 502 ),
			'no changes'     => array( '{"title":"Chili"}', 'tasty_recipes_ai_no_changes', 422 ),
			'provider error' => array( new WP_Error( 'tasty_recipes_ai_refused', 'No' ), 'tasty_recipes_ai_refused', 502 ),
		);
	}

	/**
	 * Requests that can't be sent are refused without calling the provider.
	 *
	 * @return void
	 */
	public function test_assist_validates_the_request() {
		$this->log_in_as( 'editor' );

		$not_connected = $this->dispatch( 'POST', '/ai/recipe-assist', array( 'request' => 'Change it' ) );
		$this->assertSame( 'tasty_recipes_ai_not_connected', $not_connected->get_data()['code'] );

		$this->connect_fake_provider();

		$empty = $this->dispatch( 'POST', '/ai/recipe-assist', array( 'request' => '<b></b>  ' ) );
		$this->assertSame( 'tasty_recipes_ai_empty_request', $empty->get_data()['code'] );

		$fields = $this->dispatch(
			'POST',
			'/ai/recipe-assist',
			array(
				'request' => 'Change it',
				'fields'  => array( 'calories' ),
			)
		);
		$this->assertSame( 'tasty_recipes_ai_bad_field', $fields->get_data()['code'] );

		$this->assertSame( array(), $this->provider->calls );
	}

	/**
	 * Assist is rate limited per user.
	 *
	 * @return void
	 */
	public function test_assist_is_rate_limited() {
		$user_id = $this->log_in_as( 'editor' );
		$this->connect_fake_provider();
		set_transient( 'tasty_recipes_ai_recipe_rate_' . $user_id, Recipe_Assist::RATE_LIMIT, HOUR_IN_SECONDS );

		$response = $this->dispatch( 'POST', '/ai/recipe-assist', array( 'request' => 'Change it' ) );

		$this->assertSame( 429, $response->get_status() );
		$this->assertSame( array(), $this->provider->calls );
	}

	/**
	 * The recipe type's subject reaches the system prompt.
	 *
	 * @return void
	 */
	public function test_assist_uses_the_recipe_type_subject() {
		$this->log_in_as( 'editor' );
		$this->connect_fake_provider();
		$this->provider->reply = '{"title":"Birdhouse"}';
		add_filter(
			'tasty_recipes_ai_subject',
			static function ( $subject, $type ) {
				return 'how-to' === $type ? array( 'topic' => 'woodworking projects' ) : $subject;
			},
			10,
			2
		);

		$this->dispatch(
			'POST',
			'/ai/recipe-assist',
			array(
				'request' => 'Change it',
				'type'    => 'how-to',
				'fields'  => array( 'title' ),
			)
		);

		$this->assertStringContainsString( 'woodworking projects', $this->provider->calls[0]['args']['system'] );
	}

	/**
	 * The editor only offers photo reading once a model is chosen.
	 *
	 * @return void
	 */
	public function test_editor_status() {
		$this->log_in_as( 'editor' );
		$status = Recipe_Image::get_editor_status();

		$this->assertFalse( $status['ready'] );
		$this->assertSame( '', $status['settingsUrl'] );

		$this->connect_fake_provider();
		$this->log_in_as( 'administrator' );
		$status = Recipe_Image::get_editor_status();

		$this->assertTrue( $status['ready'] );
		$this->assertSame( 'Fake AI', $status['provider'] );
		$this->assertSame( 'Fake Vision 2', $status['model'] );
		$this->assertStringContainsString( 'page=tasty-recipes', $status['settingsUrl'] );
		$this->assertSame( wp_max_upload_size(), $status['maxUploadSize'] );
	}

	/**
	 * Uploads that aren't usable photos are refused before the provider is
	 * called. A real upload can't be made in PHPUnit, since the route checks
	 * is_uploaded_file(); the Cypress suite covers that path.
	 *
	 * @dataProvider data_bad_uploads
	 *
	 * @param mixed  $file   The `image` file entry.
	 * @param string $code   Expected error code.
	 * @param int    $status Expected HTTP status.
	 *
	 * @return void
	 */
	public function test_photo_rejects_bad_uploads( $file, $code, $status ) {
		$this->log_in_as( 'editor' );
		$this->connect_fake_provider();

		$request = new WP_REST_Request( 'POST', '/tasty-recipes/v1/ai/recipe-from-image' );
		if ( null !== $file ) {
			$request->set_file_params( array( 'image' => $file ) );
		}
		$response = rest_do_request( $request );

		$this->assertSame( $status, $response->get_status() );
		$this->assertSame( $code, $response->get_data()['code'] );
		$this->assertSame( array(), $this->provider->calls );
	}

	/**
	 * Uploads the photo route must refuse.
	 *
	 * @return array
	 */
	public function data_bad_uploads() {
		return array(
			'no file'         => array( null, 'tasty_recipes_ai_bad_image', 400 ),
			'over php limit'  => array(
				array(
					'error'    => UPLOAD_ERR_INI_SIZE,
					'tmp_name' => '',
				),
				'tasty_recipes_ai_image_too_large',
				413,
			),
			'partial upload'  => array(
				array(
					'error'    => UPLOAD_ERR_PARTIAL,
					'tmp_name' => '',
				),
				'tasty_recipes_ai_bad_image',
				400,
			),
			'not an upload'   => array(
				array(
					'error'    => UPLOAD_ERR_OK,
					'tmp_name' => __DIR__ . '/data/canola.jpg',
				),
				'tasty_recipes_ai_bad_image',
				400,
			),
		);
	}

	/**
	 * Photo reading needs a connection and is rate limited per user.
	 *
	 * @return void
	 */
	public function test_photo_requires_connection_and_rate_limit() {
		$user_id = $this->log_in_as( 'editor' );

		$response = $this->dispatch( 'POST', '/ai/recipe-from-image' );
		$this->assertSame( 'tasty_recipes_ai_not_connected', $response->get_data()['code'] );

		$this->connect_fake_provider();
		set_transient( 'tasty_recipes_ai_photo_rate_' . $user_id, Recipe_Image::RATE_LIMIT, HOUR_IN_SECONDS );

		$this->assertSame( 429, $this->dispatch( 'POST', '/ai/recipe-from-image' )->get_status() );
	}
}
