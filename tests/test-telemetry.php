<?php
/**
 * Test case for the Tasty_Recipes\Telemetry class.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Options;
use Tasty_Recipes\Telemetry;

/**
 * Test case for the Tasty_Recipes\Telemetry class.
 */
class Test_Telemetry extends Tasty_Recipes_Testcase {

	/**
	 * Reset consent, screen, and script state after each test.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		delete_option( Options::ONBOARDING_USAGE_DATA );
		$GLOBALS['current_screen'] = null;
		$this->reset_posthog_scripts();

		parent::tearDown();
	}

	/**
	 * PostHog stays off when usage consent is missing.
	 *
	 * @return void
	 */
	public function test_should_load_posthog_requires_consent() {
		$this->assertFalse( Telemetry::should_load_posthog() );

		update_option( Options::ONBOARDING_USAGE_DATA, 'no' );
		$this->assertFalse( Telemetry::should_load_posthog() );

		update_option( Options::ONBOARDING_USAGE_DATA, 'yes' );
		$this->assertTrue( Telemetry::should_load_posthog() );
	}

	/**
	 * PostHog is not enqueued on unrelated admin screens.
	 *
	 * @return void
	 */
	public function test_maybe_enqueue_posthog_skips_unrelated_admin_screens() {
		update_option( Options::ONBOARDING_USAGE_DATA, 'yes' );
		set_current_screen( 'dashboard' );

		Telemetry::maybe_enqueue_posthog();

		$this->assertFalse( wp_script_is( Telemetry::POSTHOG_SCRIPT_HANDLE, 'enqueued' ) );
		$this->assertFalse( wp_script_is( Telemetry::POSTHOG_INIT_SCRIPT_HANDLE, 'enqueued' ) );
		$this->assertFalse( wp_script_is( Telemetry::POSTHOG_BLOCK_EDITOR_SCRIPT_HANDLE, 'enqueued' ) );
	}

	/**
	 * PostHog is not enqueued without usage consent.
	 *
	 * @return void
	 */
	public function test_maybe_enqueue_posthog_skips_without_consent() {
		set_current_screen( 'wp-tasty_page_tasty-recipes' );

		Telemetry::maybe_enqueue_posthog();

		$this->assertFalse( wp_script_is( Telemetry::POSTHOG_SCRIPT_HANDLE, 'enqueued' ) );
		$this->assertFalse( wp_script_is( Telemetry::POSTHOG_INIT_SCRIPT_HANDLE, 'enqueued' ) );
	}

	/**
	 * PostHog initializes immediately on recipe admin screens.
	 *
	 * @return void
	 */
	public function test_maybe_enqueue_posthog_on_recipe_admin_screen() {
		update_option( Options::ONBOARDING_USAGE_DATA, 'yes' );
		set_current_screen( 'wp-tasty_page_tasty-recipes' );

		Telemetry::maybe_enqueue_posthog();

		$this->assertTrue( wp_script_is( Telemetry::POSTHOG_SCRIPT_HANDLE, 'enqueued' ) );
		$this->assertTrue( wp_script_is( Telemetry::POSTHOG_INIT_SCRIPT_HANDLE, 'enqueued' ) );
		$this->assertFalse( wp_script_is( Telemetry::POSTHOG_BLOCK_EDITOR_SCRIPT_HANDLE, 'enqueued' ) );
		$this->assertTrue( $this->get_posthog_config()['autoInit'] );
	}

	/**
	 * Block editor without a Tasty block gets the watcher instead of an immediate init.
	 *
	 * @return void
	 */
	public function test_maybe_enqueue_posthog_block_editor_without_tasty_block() {
		update_option( Options::ONBOARDING_USAGE_DATA, 'yes' );
		$this->set_block_editor_screen();

		Telemetry::maybe_enqueue_posthog();

		$this->assertTrue( wp_script_is( Telemetry::POSTHOG_SCRIPT_HANDLE, 'enqueued' ) );
		$this->assertTrue( wp_script_is( Telemetry::POSTHOG_INIT_SCRIPT_HANDLE, 'enqueued' ) );
		$this->assertTrue( wp_script_is( Telemetry::POSTHOG_BLOCK_EDITOR_SCRIPT_HANDLE, 'enqueued' ) );
		$this->assertFalse( $this->get_posthog_config()['autoInit'] );
	}

	/**
	 * Block editor with an existing Tasty block initializes PostHog immediately.
	 *
	 * @return void
	 */
	public function test_maybe_enqueue_posthog_block_editor_with_tasty_block() {
		update_option( Options::ONBOARDING_USAGE_DATA, 'yes' );
		$post_id = $this->factory->post->create(
			array(
				'post_content' => '<!-- wp:wp-tasty/tasty-recipe {"id":123} /-->',
			)
		);
		$this->set_block_editor_screen( $post_id );

		Telemetry::maybe_enqueue_posthog();

		$this->assertTrue( wp_script_is( Telemetry::POSTHOG_SCRIPT_HANDLE, 'enqueued' ) );
		$this->assertTrue( wp_script_is( Telemetry::POSTHOG_INIT_SCRIPT_HANDLE, 'enqueued' ) );
		$this->assertFalse( wp_script_is( Telemetry::POSTHOG_BLOCK_EDITOR_SCRIPT_HANDLE, 'enqueued' ) );
		$this->assertTrue( $this->get_posthog_config()['autoInit'] );
	}

	/**
	 * Usage snapshots POST to PostHog capture with the project API key.
	 *
	 * @return void
	 */
	public function test_usage_snapshot_posts_to_posthog_capture() {
		update_option( Options::ONBOARDING_USAGE_DATA, 'yes' );
		add_filter( 'pre_http_request', array( $this, 'mock_posthog_capture' ), 5, 3 );

		$sent = Telemetry::send_usage_snapshot();

		remove_filter( 'pre_http_request', array( $this, 'mock_posthog_capture' ), 5 );

		$this->assertTrue( $sent );
		$this->assertAPIRequest(
			'us.i.posthog.com',
			array(
				'method' => 'POST',
				'path'   => '/i/v0/e/',
				'body'   => array(
					'api_key' => Telemetry::POSTHOG_API_KEY,
					'event'   => 'tasty_recipes_usage_snapshot',
				),
			)
		);
	}

	/**
	 * Snapshot payload includes the PostHog project API key.
	 *
	 * @return void
	 */
	public function test_build_payload_includes_posthog_api_key() {
		$payload = Telemetry::build_payload();

		$this->assertSame( Telemetry::POSTHOG_API_KEY, $payload['api_key'] );
		$this->assertSame( 'tasty_recipes_usage_snapshot', $payload['event'] );
		$this->assertNotEmpty( $payload['distinct_id'] );
	}

	/**
	 * Mock a successful PostHog capture response.
	 *
	 * @param mixed  $pre Existing pre-request value.
	 * @param array  $args Request arguments.
	 * @param string $url  Request URL.
	 *
	 * @return mixed
	 */
	public function mock_posthog_capture( $pre, $args, $url ) {
		unset( $args );

		if ( 0 !== strpos( $url, Telemetry::get_capture_url() ) ) {
			return $pre;
		}

		return array(
			'headers'  => array(),
			'body'     => '{}',
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
		);
	}

	/**
	 * Mark the current screen as the block editor, optionally for a post.
	 *
	 * @param int $post_id Optional post ID to edit.
	 *
	 * @return void
	 */
	private function set_block_editor_screen( $post_id = 0 ) {
		set_current_screen( 'post' );
		$screen = get_current_screen();
		$screen->is_block_editor( true );

		if ( ! $post_id ) {
			$GLOBALS['post'] = null;
			return;
		}

		$GLOBALS['post'] = get_post( $post_id );
		setup_postdata( $GLOBALS['post'] );
	}

	/**
	 * Parse the localized PostHog config from the init script.
	 *
	 * @return array
	 */
	private function get_posthog_config() {
		$inline = $this->get_script_inline( Telemetry::POSTHOG_INIT_SCRIPT_HANDLE, 'before' );
		$prefix = 'window.tastyRecipesPosthog = ';

		if ( 0 !== strpos( $inline, $prefix ) ) {
			return array();
		}

		$json = trim( substr( $inline, strlen( $prefix ) ), " \n\r\t;" );
		$data = json_decode( $json, true );

		return is_array( $data ) ? $data : array();
	}

	/**
	 * Get concatenated extra inline script for a handle.
	 *
	 * @param string $handle   Script handle.
	 * @param string $position Inline position (before or after).
	 *
	 * @return string
	 */
	private function get_script_inline( $handle, $position = 'after' ) {
		$scripts = wp_scripts();
		if ( empty( $scripts->registered[ $handle ] ) ) {
			return '';
		}

		$data = $scripts->get_data( $handle, $position );
		if ( is_array( $data ) ) {
			return implode( "\n", $data );
		}

		return is_string( $data ) ? $data : '';
	}

	/**
	 * Drop PostHog scripts from the queue so tests stay isolated.
	 *
	 * @return void
	 */
	private function reset_posthog_scripts() {
		wp_dequeue_script( Telemetry::POSTHOG_SCRIPT_HANDLE );
		wp_deregister_script( Telemetry::POSTHOG_SCRIPT_HANDLE );
		wp_dequeue_script( Telemetry::POSTHOG_INIT_SCRIPT_HANDLE );
		wp_deregister_script( Telemetry::POSTHOG_INIT_SCRIPT_HANDLE );
		wp_dequeue_script( Telemetry::POSTHOG_BLOCK_EDITOR_SCRIPT_HANDLE );
		wp_deregister_script( Telemetry::POSTHOG_BLOCK_EDITOR_SCRIPT_HANDLE );
	}
}
