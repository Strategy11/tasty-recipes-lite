<?php
/**
 * Integrates Tasty Recipes with Rank Math.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\Integrations;

use Tasty_Recipes;

/**
 * Integrates Tasty Recipes with Rank Math.
 */
class Rank_Math {

	/**
	 * Enqueue Rank Math JavaScript when Tasty Recipes is enqueued.
	 *
	 * @return void
	 */
	public static function action_admin_enqueue_scripts() {
		$time = filemtime( dirname( TASTY_RECIPES_LITE_FILE ) . '/assets/js/rank-math.js' );
		wp_enqueue_script(
			'tasty-recipes-rank-math',
			plugins_url( 'assets/js/rank-math.js', TASTY_RECIPES_LITE_FILE ),
			array(
				'jquery',
				'wp-hooks',
				'tasty-recipes-block-editor',
				'rank-math-analyzer',
			),
			(string) $time,
			true
		);
	}

	/**
	 * Load AI scraper prevention hooks for Rank Math.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function load_ai_scraper_hooks() {
		add_filter( 'rank_math/frontend/robots', array( __CLASS__, 'filter_robots' ) );
	}

	/**
	 * Add noai and noimageai to Rank Math's robots meta array for recipe posts.
	 *
	 * @since x.x
	 *
	 * @param array $robots The meta robots directives array.
	 *
	 * @return array
	 */
	public static function filter_robots( $robots ) {
		if ( ! is_singular() ) {
			return $robots;
		}

		$post_id = get_queried_object_id();

		if ( ! $post_id || ! Tasty_Recipes::has_recipe( $post_id ) ) {
			return $robots;
		}

		if ( ! in_array( 'noai', $robots, true ) ) {
			$robots['noai'] = 'noai';
		}

		if ( ! in_array( 'noimageai', $robots, true ) ) {
			$robots['noimageai'] = 'noimageai';
		}

		return $robots;
	}
}
