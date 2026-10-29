<?php
/**
 * Base test class for the AI features.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\AI\Connection;
use Tasty_Recipes\AI\Providers\Provider;
use Tasty_Recipes\Options;

/**
 * Provider that answers from canned values instead of calling an API.
 */
class Tasty_Recipes_Fake_AI_Provider extends Provider {

	/**
	 * Provider ID.
	 *
	 * @var string
	 */
	public $id = 'fake';

	/**
	 * Models list_models() returns, or a WP_Error.
	 *
	 * @var array<int, array{id: string, label: string}>|WP_Error
	 */
	public $models = array(
		array(
			'id'    => 'fake-vision-2',
			'label' => 'Fake Vision 2',
		),
		array(
			'id'    => 'fake-vision-1',
			'label' => 'Fake Vision 1',
		),
	);

	/**
	 * Text generate() returns, or a WP_Error.
	 *
	 * @var string|WP_Error
	 */
	public $reply = 'OK';

	/**
	 * Calls to generate(), without the key.
	 *
	 * @var array
	 */
	public $calls = array();

	/**
	 * Keys passed to list_models().
	 *
	 * @var array
	 */
	public $listed_keys = array();

	/**
	 * Provider ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Provider name.
	 *
	 * @return string
	 */
	public function get_label() {
		return 'Fake AI';
	}

	/**
	 * Where keys are made.
	 *
	 * @return string
	 */
	public function get_credentials_url() {
		return 'https://example.com/keys';
	}

	/**
	 * Recommended model family.
	 *
	 * @return string
	 */
	public function get_model_family_label() {
		return 'Fake Vision';
	}

	/**
	 * Newest fake-vision model.
	 *
	 * @param array $models Models.
	 *
	 * @return string
	 */
	public function get_recommended_model( $models ) {
		return self::newest_matching( wp_list_pluck( $models, 'id' ), '/^fake-vision-(\d+)$/' );
	}

	/**
	 * Returns the canned models.
	 *
	 * @param string $api_key API key.
	 *
	 * @return array<int, array{id: string, label: string}>|WP_Error
	 */
	public function list_models(
		#[\SensitiveParameter]
		$api_key
	) {
		$this->listed_keys[] = $api_key;

		return $this->models;
	}

	/**
	 * Returns the canned reply.
	 *
	 * @param string $api_key API key.
	 * @param string $model   Model ID.
	 * @param string $prompt  Prompt.
	 * @param array  $args    Request options.
	 *
	 * @return string|WP_Error
	 */
	public function generate(
		#[\SensitiveParameter]
		$api_key,
		$model,
		$prompt,
		$args = array()
	) {
		$this->calls[] = array(
			'model'  => $model,
			'prompt' => $prompt,
			'args'   => $args,
		);

		return $this->reply;
	}
}

/**
 * Base test class for the AI features.
 */
class Tasty_Recipes_AI_Testcase extends Tasty_Recipes_Testcase {

	/**
	 * Key used to connect the fake provider.
	 *
	 * @var string
	 */
	const FAKE_KEY = 'fake-key-0123456789-SECRET-abcd';

	/**
	 * The fake provider every test talks to.
	 *
	 * @var Tasty_Recipes_Fake_AI_Provider
	 */
	protected $provider;

	/**
	 * Adds the fake provider and starts from no connection.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->provider = new Tasty_Recipes_Fake_AI_Provider();
		add_filter( 'tasty_recipes_ai_providers', array( $this, 'add_fake_provider' ) );

		$this->reset_ai_state();
		$this->reset_rest_server();
	}

	/**
	 * Removes the fake provider and any saved connection.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		remove_filter( 'tasty_recipes_ai_providers', array( $this, 'add_fake_provider' ) );
		$this->reset_ai_state();

		global $wp_rest_server;
		$wp_rest_server = null;

		parent::tearDown();
	}

	/**
	 * Adds the fake provider to the provider list.
	 *
	 * @param array $providers Providers.
	 *
	 * @return array
	 */
	public function add_fake_provider( $providers ) {
		$providers[] = $this->provider;

		return $providers;
	}

	/**
	 * Connects the fake provider with FAKE_KEY.
	 *
	 * @return void
	 */
	protected function connect_fake_provider() {
		$models = Connection::connect( 'fake', self::FAKE_KEY );

		$this->assertIsArray( $models );
	}

	/**
	 * Logs in a new user with the given role.
	 *
	 * @param string $role Role name.
	 *
	 * @return int User ID.
	 */
	protected function log_in_as( $role ) {
		$user_id = self::factory()->user->create( array( 'role' => $role ) );
		wp_set_current_user( $user_id );

		return $user_id;
	}

	/**
	 * Dispatches a REST request.
	 *
	 * @param string $method Request method.
	 * @param string $route  Route under tasty-recipes/v1.
	 * @param array  $params Request parameters.
	 *
	 * @return WP_REST_Response
	 */
	protected function dispatch( $method, $route, $params = array() ) {
		$request = new WP_REST_Request( $method, '/tasty-recipes/v1' . $route );

		foreach ( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}

		return rest_do_request( $request );
	}

	/**
	 * Builds a fresh REST server so routes see the fake provider.
	 *
	 * @return void
	 */
	protected function reset_rest_server() {
		global $wp_rest_server;

		$wp_rest_server = new WP_REST_Server();
		do_action( 'rest_api_init', $wp_rest_server );
	}

	/**
	 * Clears the connection, model, caches and rate limits.
	 *
	 * @return void
	 */
	protected function reset_ai_state() {
		delete_option( Options::AI_CONNECTION );
		delete_option( Options::AI_MODEL );
		delete_option( Options::TELEMETRY_AI_REQUESTS );
		delete_transient( Connection::MODELS_TRANSIENT );

		$user_id = get_current_user_id();
		foreach ( array( 'tasty_recipes_ai_rate_', 'tasty_recipes_ai_recipe_rate_', 'tasty_recipes_ai_photo_rate_' ) as $prefix ) {
			delete_transient( $prefix . $user_id );
		}
	}
}
