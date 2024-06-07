<?php
/**
 * Integrates Tasty Recipes with Rank Math
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes_Lite\Integrations;

/**
 * Integrates Tasty Recipes with Rank Math.
 */
class Rank_Math {

	/**
	 * Enqueue Rank Math JavaScript when Tasty Recipes is enqueued.
	 */
	public static function action_admin_enqueue_scripts() {
		$time = filemtime( dirname( TASTY_RECIPES_LITE_PLUGIN_FILE ) . '/assets/js/rank-math.js' );
		wp_enqueue_script(
			'tasty-recipes-rank-math',
			plugins_url( 'assets/js/rank-math.js', TASTY_RECIPES_LITE_PLUGIN_FILE ),
			array(
				'jquery',
				'wp-hooks',
				'tasty-recipes-block-editor',
				'rank-math-analyzer',
			),
			$time,
			true
		);
	}
}
