<?php
/**
 * Manages block editor registration and configuration.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite;

use Tasty\Recipes\Lite\Objects\Recipe;
use WP_Post;

/**
 * Manages block editor registration and configuration.
 */
class BlockEditor {

	/**
	 * Block type name.
	 *
	 * @var string
	 */
	const RECIPE_BLOCK_TYPE = 'wp-tasty/tasty-recipe';

	/**
	 * Register the scripts and block type.
	 */
	public static function action_init_register() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}
		Assets::register_modal_editor_script();
		$time = 0;
		if ( file_exists( dirname( TASTY_RECIPES_LITE_PLUGIN_FILE ) . '/assets/dist/block-editor.build.js' ) ) {
			$time = filemtime( dirname( TASTY_RECIPES_LITE_PLUGIN_FILE ) . '/assets/dist/block-editor.build.js' );
		}
		wp_register_script(
			'tasty-recipes-block-editor',
			plugins_url( 'assets/dist/block-editor.build.js', TASTY_RECIPES_LITE_PLUGIN_FILE ),
			array(
				'tasty-recipes-editor-modal',
				'wp-blocks',
				'wp-block-editor',
				'wp-element',
				'wp-components',
				'wp-server-side-render',
				'wp-data',
				'wp-i18n',
				'wp-plugins',
			),
			$time,
			true
		);
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'tasty-recipes-block-editor', 'tasty-recipes' );
		}
		register_block_type(
			self::RECIPE_BLOCK_TYPE,
			array(
				'attributes'      => array(
					'className'               => array(
						'type' => 'string',
					),
					'id'                      => array(
						'type' => 'number',
					),
					'lastUpdated'             => array(
						'type' => 'number',
					),
					'author_link'             => array(
						'type' => 'string',
					),
					'disableJSON'             => array(
						'type' => 'boolean',
					),
					'disable_unit_conversion' => array(
						'type' => 'boolean',
					),
					'disable_scaling'         => array(
						'type' => 'boolean',
					),
				),
				'editor_script'   => 'tasty-recipes-block-editor',
				'render_callback' => array( Shortcodes::class, 'render_tasty_recipe_shortcode' ),
			)
		);
	}

	/**
	 * Get the block for a given recipe.
	 *
	 * @param Recipe $recipe Recipe instance.
	 * @return string
	 */
	public static function get_block_for_recipe( Recipe $recipe ) {
		return '<!-- wp:wp-tasty/tasty-recipe {"id":' . $recipe->get_id() . ',"lastUpdated":' . time() . '} /-->';
	}

	/**
	 * Check if post has recipes.
	 *
	 * @param WP_Post $post Post object to check.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function post_has_recipes( $post ) {
		return function_exists( 'has_block' ) && has_block( self::RECIPE_BLOCK_TYPE, $post );
	}

}
