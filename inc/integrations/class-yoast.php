<?php
/**
 * Integrates Tasty Recipes with Yoast SEO.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\Integrations;

use Tasty_Recipes;

/**
 * Integrates Tasty Recipes with Yoast SEO.
 *
 * @since x.x
 */
class Yoast {

	/**
	 * Load AI scraper prevention hooks for Yoast SEO.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function load_ai_scraper_hooks() {
		add_filter( 'wpseo_robots_array', array( __CLASS__, 'filter_robots_array' ), 10, 2 );
	}

	/**
	 * Add noai and noimageai to Yoast's robots meta array for recipe posts.
	 *
	 * @since x.x
	 *
	 * @param array $robots The meta robots directives array.
	 *
	 * @return array
	 */
	public static function filter_robots_array( $robots ) {
		if ( ! is_singular() ) {
			return $robots;
		}

		$post_id = get_queried_object_id();

		if ( ! $post_id || ! Tasty_Recipes::has_recipe( $post_id ) ) {
			return $robots;
		}

		if ( ! in_array( 'noai', $robots, true ) ) {
			$robots[] = 'noai';
		}

		if ( ! in_array( 'noimageai', $robots, true ) ) {
			$robots[] = 'noimageai';
		}

		return $robots;
	}
}
