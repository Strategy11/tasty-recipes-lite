<?php
/**
 * Manages Frontend events.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes;

use Tasty_Recipes;
use Tasty_Recipes\Objects\Recipe;
use WP_Post;

/**
 * Frontend class.
 */
class Frontend {
	/**
	 * Singleton instance.
	 *
	 * @since 3.8
	 *
	 * @var Frontend
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 3.8
	 *
	 * @return Frontend
	 */
	public static function get_instance() {
		if ( null !== self::$instance ) {
			return self::$instance;
		}

		self::$instance = new self();
		self::$instance->add_hooks();
		return self::$instance;
	}

	/**
	 * Subscribe hooks.
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	private function add_hooks() {
	}
}
