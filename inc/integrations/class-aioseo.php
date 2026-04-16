<?php
/**
 * Integrates Tasty Recipes with All in One SEO.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\Integrations;

use Tasty_Recipes;

/**
 * Integrates Tasty Recipes with All in One SEO.
 *
 * @since x.x
 */
class AIOSEO {

	/**
	 * Load AI scraper prevention hooks for AIOSEO.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function load_ai_scraper_hooks() {
		add_filter( 'aioseo_robots_meta', array( __CLASS__, 'filter_robots_meta' ) );
	}

	/**
	 * Add noai and noimageai to AIOSEO's robots meta attributes for recipe posts.
	 *
	 * @since x.x
	 *
	 * @param array $attributes The robots meta attributes array.
	 *
	 * @return array
	 */
	public static function filter_robots_meta( $attributes ) {
		if ( ! is_singular() ) {
			return $attributes;
		}

		$post_id = get_queried_object_id();

		if ( ! $post_id || ! Tasty_Recipes::has_recipe( $post_id ) ) {
			return $attributes;
		}

		if ( ! isset( $attributes['noai'] ) ) {
			$attributes['noai'] = 'noai';
		}

		if ( ! isset( $attributes['noimageai'] ) ) {
			$attributes['noimageai'] = 'noimageai';
		}

		return $attributes;
	}
}
