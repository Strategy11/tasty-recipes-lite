<?php
/**
 * Integrates Tasty Recipes with Akismet
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Integrations;

use Tasty_Recipes\Ratings;

/**
 * Integrates Tasty Recipes with Akismet.
 */
class Akismet {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Temp rating comment hash.
	 *
	 * @var string
	 */
	private $temp_rating_comment_hash;

	/**
	 * Get singleton instance.
	 *
	 * @return self|null
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->add_hooks();
		}
		return self::$instance;
	}

	/**
	 * Check if the Akismet plugin is active or not.
	 *
	 * @return bool
	 */
	protected function is_active() {
		return defined( 'AKISMET_VERSION' );
	}

	/**
	 * Register integration hooks.
	 *
	 * @return void
	 */
	public function add_hooks() {
		if ( ! $this->is_active() ) {
			return;
		}

	}

}
