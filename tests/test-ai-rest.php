<?php
/**
 * Test case for the AI REST routes.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\AI\Connection;
use Tasty_Recipes\AI\Recipe_Image;
use Tasty_Recipes\AI\Rest;
use Tasty_Recipes\Options;

/**
 * Test case for the AI REST routes.
 */
class Test_AI_Rest extends Tasty_Recipes_AI_Testcase {

	/**
	 * Settings routes, with the method and parameters each needs.
	 *
	 * @return array
	 */
	public function data_settings_routes() {
		return array(
			'read connection' => array( 'GET', '/ai/connection', array() ),
			'connect'         => array(
				'POST',
				'/ai/connection',
				array(
					'provider' => 'fake',
					'api_key'  => self::FAKE_KEY,
				),
			),
			'disconnect'      => array( 'DELETE', '/ai/connection', array() ),
			'models'          => array( 'GET', '/ai/models', array() ),
			'set model'       => array( 'POST', '/ai/model', array( 'model' => 'fake-vision-1' ) ),
			'test'            => array( 'POST', '/ai/test', array() ),
		);
	}

	/**
	 * Only administrators can see or change the connection.
	 *
	 * @dataProvider data_settings_routes
	 *
	 * @param string $method Request method.
	 * @param string $route  Route.
	 * @param array  $params Parameters.
	 *
	 * @return void
	 */
	public function test_settings_routes_require_manage_options( $method, $route, $params ) {
		foreach ( array( 'editor', 'author', 'contributor', 'subscriber' ) as $role ) {
			$this->log_in_as( $role );

			$response = $this->dispatch( $method, $route, $params );

			$this->assertSame( 403, $response->get_status(), "$role on $method $route" );
		}

		wp_set_current_user( 0 );
		$this->assertSame( 401, $this->dispatch( $method, $route, $params )->get_status() );
	}

	/**
	 * Recipe assist and photo reading are open to anyone who can write posts,
	 * so contributors can use the site's key; subscribers can't.
	 *
	 * @return void
	 */
	public function test_editor_routes_require_edit_posts() {
		$this->assertTrue( user_can( $this->log_in_as( 'contributor' ), 'edit_posts' ) );
		$this->assertNotSame( 403, $this->dispatch( 'POST', '/ai/recipe-assist', array( 'request' => 'Make it vegan' ) )->get_status() );
		$this->assertNotSame( 403, $this->dispatch( 'POST', '/ai/recipe-from-image' )->get_status() );

		$this->log_in_as( 'subscriber' );
		$this->assertSame( 403, $this->dispatch( 'POST', '/ai/recipe-assist', array( 'request' => 'Make it vegan' ) )->get_status() );
		$this->assertSame( 403, $this->dispatch( 'POST', '/ai/recipe-from-image' )->get_status() );
	}

	/**
	 * No route, and no data printed for the admin screens, returns the key.
	 *
	 * @return void
	 */
	public function test_key_is_never_returned() {
		$this->log_in_as( 'administrator' );

		$responses = array(
			$this->dispatch(
				'POST',
				'/ai/connection',
				array(
					'provider' => 'fake',
					'api_key'  => self::FAKE_KEY,
				)
			),
			$this->dispatch( 'GET', '/ai/connection' ),
			$this->dispatch( 'GET', '/ai/models' ),
			$this->dispatch( 'GET', '/ai/models', array( 'refresh' => true ) ),
			$this->dispatch( 'POST', '/ai/model', array( 'model' => 'fake-vision-1' ) ),
			$this->dispatch( 'POST', '/ai/test' ),
		);

		$stored = get_option( Options::AI_CONNECTION );
		$this->assertNotEmpty( $stored['key'] );

		foreach ( $responses as $response ) {
			$this->assertLessThan( 300, $response->get_status(), wp_json_encode( $response->get_data() ) );

			$json = wp_json_encode( $response->get_data() );
			$this->assertStringNotContainsString( self::FAKE_KEY, $json );
			$this->assertStringNotContainsString( $stored['key'], $json );
		}

		foreach ( array( Connection::get_public_status(), Recipe_Image::get_editor_status() ) as $printed ) {
			$json = wp_json_encode( $printed );
			$this->assertStringNotContainsString( self::FAKE_KEY, $json );
			$this->assertStringNotContainsString( $stored['key'], $json );
		}
	}

	/**
	 * Every AI response tells browsers and proxies not to cache it.
	 *
	 * @return void
	 */
	public function test_responses_are_not_cacheable() {
		$this->log_in_as( 'administrator' );
		$this->connect_fake_provider();

		foreach ( array( array( 'GET', '/ai/connection' ), array( 'GET', '/ai/models' ), array( 'POST', '/ai/test' ) ) as $call ) {
			$headers = $this->dispatch( $call[0], $call[1] )->get_headers();

			$this->assertSame( 'no-store, private', $headers['Cache-Control'] ?? '', $call[1] );
		}
	}

	/**
	 * Values that can't be API keys are refused before the provider is called.
	 *
	 * @dataProvider data_bad_keys
	 *
	 * @param string $key Submitted key.
	 *
	 * @return void
	 */
	public function test_connect_rejects_malformed_keys( $key ) {
		$this->log_in_as( 'administrator' );

		$response = $this->dispatch(
			'POST',
			'/ai/connection',
			array(
				'provider' => 'fake',
				'api_key'  => $key,
			)
		);

		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( array(), $this->provider->listed_keys );
	}

	/**
	 * Keys the route must refuse.
	 *
	 * @return array
	 */
	public function data_bad_keys() {
		return array(
			'too short'   => array( 'abc' ),
			'too long'    => array( str_repeat( 'a', 513 ) ),
			'has a space' => array( 'sk-abc def-0123456789' ),
			'has newline' => array( "sk-abcdef\n0123456789" ),
			'non ascii'   => array( 'sk-abcdéf-0123456789' ),
		);
	}

	/**
	 * Only known providers can be connected.
	 *
	 * @return void
	 */
	public function test_connect_rejects_unknown_provider() {
		$this->log_in_as( 'administrator' );

		$response = $this->dispatch(
			'POST',
			'/ai/connection',
			array(
				'provider' => 'nope',
				'api_key'  => self::FAKE_KEY,
			)
		);

		$this->assertSame( 400, $response->get_status() );
	}

	/**
	 * Provider errors keep a 4xx status so the settings screen can show them.
	 *
	 * @return void
	 */
	public function test_connect_returns_provider_errors() {
		$this->log_in_as( 'administrator' );
		$this->provider->models = new WP_Error( 'tasty_recipes_ai_invalid_key', 'Bad key' );

		$response = $this->dispatch(
			'POST',
			'/ai/connection',
			array(
				'provider' => 'fake',
				'api_key'  => self::FAKE_KEY,
			)
		);

		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( 'tasty_recipes_ai_invalid_key', $response->get_data()['code'] );
	}

	/**
	 * Requests that reach the provider are rate limited per user.
	 *
	 * @return void
	 */
	public function test_settings_routes_are_rate_limited() {
		$this->log_in_as( 'administrator' );
		$this->connect_fake_provider();

		for ( $i = 0; $i < Rest::RATE_LIMIT; $i++ ) {
			$this->assertSame( 200, $this->dispatch( 'POST', '/ai/test' )->get_status() );
		}

		$response = $this->dispatch( 'POST', '/ai/test' );
		$this->assertSame( 429, $response->get_status() );
		$this->assertSame( 'tasty_recipes_ai_too_many_requests', $response->get_data()['code'] );

		// Cached reads don't reach the provider, so they aren't limited.
		$this->assertSame( 200, $this->dispatch( 'GET', '/ai/models' )->get_status() );
		$this->assertSame( 429, $this->dispatch( 'GET', '/ai/models', array( 'refresh' => true ) )->get_status() );

		// The limit is per user.
		$this->log_in_as( 'administrator' );
		$this->assertSame( 200, $this->dispatch( 'POST', '/ai/test' )->get_status() );
	}

	/**
	 * The connection test sends a request tagged with its feature.
	 *
	 * @return void
	 */
	public function test_connection_test_reports_duration() {
		$this->log_in_as( 'administrator' );
		$this->connect_fake_provider();

		$response = $this->dispatch( 'POST', '/ai/test' );

		$this->assertTrue( $response->get_data()['success'] );
		$this->assertIsInt( $response->get_data()['durationMs'] );
		$this->assertSame( 'fake-vision-2', $this->provider->calls[0]['model'] );
	}

	/**
	 * Disconnecting over REST clears the connection.
	 *
	 * @return void
	 */
	public function test_disconnect_route() {
		$this->log_in_as( 'administrator' );
		$this->connect_fake_provider();

		$response = $this->dispatch( 'DELETE', '/ai/connection' );

		$this->assertSame( 200, $response->get_status() );
		$this->assertFalse( $response->get_data()['connected'] );
		$this->assertFalse( get_option( Options::AI_CONNECTION ) );
	}
}
