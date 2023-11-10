<?php
/**
 * Functions you can use in your theme
 *
 * @package Tasty_Recipes
 */

use Tasty\Recipes\Lite\ContentModel;

/**
 * Whether or not the recipe is being displayed for print.
 *
 * @return boolean
 */
function tasty_recipes_is_print() {
	return (bool) get_query_var( ContentModel::get_print_query_var() );
}

/**
 * Get the print link for a recipe embedded within a given post.
 *
 * @param integer $post_id   ID for the post with the recipe.
 * @param integer $recipe_id ID for the recipe to display.
 * @return string
 */
function tasty_recipes_get_print_url( $post_id, $recipe_id ) {
	global $wp_rewrite;
	$permalink = get_permalink( $post_id );
	if ( ! empty( $wp_rewrite->permalink_structure ) && ! is_preview() ) {
		$bits = explode( '?', $permalink );
		$args = '';
		if ( 2 === count( $bits ) ) {
			$permalink = $bits[0];
			$args      = $bits[1];
		}
		$permalink = trailingslashit( $permalink ) . ContentModel::get_print_query_var() . '/' . $recipe_id;
		if ( $args ) {
			$permalink .= '?' . $args;
		}
		if ( '/' === substr( $wp_rewrite->permalink_structure, -1 ) ) {
			$permalink .= '/';
		}
	} else {
		$permalink = add_query_arg( ContentModel::get_print_query_var(), $recipe_id, $permalink );
	}
	/**
	 * Allows modification of the print URL.
	 *
	 * @param string  $permalink Current print URL.
	 * @param integer $post_id   ID for the displayed post.
	 * @param integer $recipe_id ID for the displayed recipe.
	 */
	return apply_filters( 'tasty_recipes_print_url', $permalink, $post_id, $recipe_id );
}

/**
 * Provide the data used to display print option buttons and their associated
 * sections in the print view.
 *
 * @return array A list of modified print options.
 */
function tasty_recipes_get_print_view_options() {
	$options = array(
		'images'      => esc_html__( 'Images', 'tasty-recipes-lite' ),
		'description' => esc_html__( 'Description', 'tasty-recipes-lite' ),
		'notes'       => esc_html__( 'Notes', 'tasty-recipes-lite' ),
		'nutrition'   => esc_html__( 'Nutrition', 'tasty-recipes-lite' ),
	);

	if ( tasty_recipes_is_print() ) {
		/**
		 * Allows print option buttons and their associated sections to be modified.
		 *
		 * @var array $options Existing print view options.
		 */
		return apply_filters( 'tasty_recipes_print_view_buttons', $options );
	}

	return $options;
}

function tasty_recipes_is_pro_active() {
	return defined( 'TASTY_RECIPES_PLUGIN_VERSION' );
}

function tasty_recipes_base_file() {
	return tasty_recipes_is_pro_active() ? TASTY_RECIPES_PLUGIN_FILE : TASTY_RECIPES_LITE_PLUGIN_FILE;
}
