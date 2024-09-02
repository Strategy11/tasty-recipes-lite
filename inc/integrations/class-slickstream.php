<?php
/**
 * Handles the integration with Slickstream.
 *
 * @package Tasty_Reipes
 */

namespace Tasty_Recipes\Integrations;

use Tasty\Framework\Traits\Singleton;

/**
 * Integration with Slickstream.
 *
 * @since 3.13
 */
class Slickstream {
	use Singleton;

	/**
	 * Initialize the integration code based on the plugin status if it's active or not.
	 *
	 * @since 3.13
	 *
	 * @return void
	 */
	public static function init() {
		$instance = self::instance();
		if ( ! $instance || ! $instance->is_active() ) {
			return;
		}

		$instance->add_hooks();
	}

	/**
	 * Check if plugin is active or not.
	 *
	 * @since 3.13
	 *
	 * @return bool
	 */
	private function is_active() {
		return function_exists( 'SlickEngagement_init' );
	}

	/**
	 * Register WP hooks.
	 *
	 * @return void
	 */
	private function add_hooks() {
		if ( is_admin() ) {
			add_filter( 'tasty_recipes_button_options', array( __CLASS__, 'enable_card_button' ) );
		}
	}

	/**
	 * Enable Slickstream button since the plugin is active.
	 *
	 * @since 3.13
	 *
	 * @param array $options Array of options.
	 *
	 * @return array
	 */
	public static function enable_card_button( $options ) {
		unset( $options['slickstream']['disabled'] );
		return $options;
	}
}
