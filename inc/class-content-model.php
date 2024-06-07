<?php
/**
 * Defines our custom post type and everything related to its behavior.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes_Lite;

use TastyRecipesLiteMain;
use Tasty\Framework\Admin\Plugins\Factory;

/**
 * Defines our custom post type and everything related to its behavior.
 */
class Content_Model {

	/**
	 * Query variable used for print pages.
	 *
	 * @var string
	 */
	const PRINT_QUERY_VAR = 'print';

	/**
	 * Registers our cron events.
	 */
	public static function action_init_register_cron_events() {
		if ( ! wp_next_scheduled( 'tasty_recipes_process_thumbnails' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'tasty_recipes_process_thumbnails' );
		}
	}

	/**
	 * Registers our post types.
	 */
	public static function action_init_register_post_types() {

		$args           = array(
			'hierarchical' => false,
			'public'       => false,
			'show_ui'      => false,
			'rewrite'      => false,
			'supports'     => array(),
		);
		$args['labels'] = array(
			'name'               => __( 'Recipes', 'tasty-recipes-lite' ),
			'singular_name'      => __( 'Recipe', 'tasty-recipes-lite' ),
			'all_items'          => __( 'All Recipes', 'tasty-recipes-lite' ),
			'new_item'           => __( 'New Recipe', 'tasty-recipes-lite' ),
			'add_new'            => __( 'Add New', 'tasty-recipes-lite' ),
			'add_new_item'       => __( 'Add New Recipe', 'tasty-recipes-lite' ),
			'edit_item'          => __( 'Edit Recipe', 'tasty-recipes-lite' ),
			'view_item'          => __( 'View Recipes', 'tasty-recipes-lite' ),
			'search_items'       => __( 'Search Recipes', 'tasty-recipes-lite' ),
			'not_found'          => __( 'No recipes found', 'tasty-recipes-lite' ),
			'not_found_in_trash' => __( 'No recipes found in trash', 'tasty-recipes-lite' ),
			'parent_item_colon'  => __( 'Parent recipe', 'tasty-recipes-lite' ),
			'menu_name'          => __( 'Recipes', 'tasty-recipes-lite' ),
		);
		register_post_type( 'tasty_recipe', $args );
	}

	/**
	 * Registers our rewrite rules for print pages.
	 */
	public static function action_init_register_rewrite_rules() {
		add_rewrite_endpoint( self::get_print_query_var(), EP_PERMALINK | EP_PAGES );
	}

	/**
	 * Filters rewrite rules to avoid loading post content at /print/ URL.
	 *
	 * @param array $rewrite_rules Existing rewrite rules.
	 * @return array
	 */
	public static function filter_rewrite_rules_array( $rewrite_rules ) {
		$new_rewrite_rules = array();
		foreach ( $rewrite_rules as $match => $rule ) {
			$match                       = str_replace(
				'/' . self::get_print_query_var() . '(/(.*))?/?$',
				'/' . self::get_print_query_var() . '(/(.*))/?$',
				$match
			);
			$new_rewrite_rules[ $match ] = $rule;
		}
		return $new_rewrite_rules;
	}

	/**
	 * Generates our custom JSON+LD image sizes when a thumbnail is assigned to a recipe.
	 *
	 * @param mixed   $check      Existing check value.
	 * @param integer $object_id  ID for the post being updated.
	 * @param string  $meta_key   Post meta key.
	 * @param string  $meta_value New meta value.
	 * @return mixed
	 */
	public static function filter_update_post_metadata_thumbnail_id( $check, $object_id, $meta_key, $meta_value ) {

		if ( 'tasty_recipe' !== get_post_type( $object_id ) || '_thumbnail_id' !== $meta_key ) {
			return $check;
		}
		if ( empty( $meta_value ) ) {
			return $check;
		}
		self::generate_attachment_image_sizes( $meta_value );
		return $check;
	}

	/**
	 * Cron callback to generate image sizes for attachments associated with Tasty Recipes.
	 */
	public static function action_tasty_recipes_process_thumbnails() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$attach_ids = $wpdb->get_col( "SELECT pm.meta_value FROM {$wpdb->postmeta} as pm LEFT JOIN {$wpdb->posts} as p ON p.ID = pm.post_id WHERE pm.meta_key='_thumbnail_id' AND p.post_type='tasty_recipe'" );
		foreach ( $attach_ids as $attach_id ) {
			if ( empty( $attach_id ) ) {
				continue;
			}
			self::generate_attachment_image_sizes( $attach_id );
		}
	}

	/**
	 * Generates our extra image sizes without registering the image sizes.
	 *
	 * @param integer $attachment_id Id for the attachment.
	 * @return boolean Success state.
	 */
	public static function generate_attachment_image_sizes( $attachment_id ) {
		$file = get_attached_file( $attachment_id );
		if ( ! $file ) {
			return false;
		}
		$editor = wp_get_image_editor( $file );
		if ( is_wp_error( $editor ) ) {
			return false;
		}
		$existing = get_post_meta( $attachment_id, '_wp_attachment_metadata', true );
		// No image sizes so something must've gone wrong.
		if ( empty( $existing['sizes'] ) ) {
			return false;
		}
		$new_sizes = array();
		foreach ( Distribution_Metadata::get_json_ld_image_sizes( $attachment_id ) as $name => $image_size ) {
			$key = 'tasty-recipes-' . $name;
			if ( ! isset( $existing['sizes'][ $key ] ) ) {
				$new_sizes[ $key ] = array(
					'width'  => $image_size[0],
					'height' => $image_size[1],
					'crop'   => true,
				);
			}
		}
		if ( ! empty( $new_sizes ) ) {
			$new_sizes         = $editor->multi_resize( $new_sizes );
			$existing['sizes'] = array_merge( $existing['sizes'], $new_sizes );
		}
		update_post_meta( $attachment_id, '_wp_attachment_metadata', $existing );
		return true;
	}

	/**
	 * Filters template loading to load our print template when
	 * print view is request.
	 *
	 * @param string $template Existing template being loaded.
	 * @return string
	 */
	public static function filter_template_include( $template ) {

		$recipe_id = (int) get_query_var( self::get_print_query_var() );
		if ( is_singular() && $recipe_id ) {
			$recipe_ids = TastyRecipesLiteMain::get_recipe_ids_for_post( get_queried_object_id() );
			if ( in_array( $recipe_id, $recipe_ids, true ) ) {
				$template = TastyRecipesLiteMain::get_template_path( 'recipe-print' );
			}
		}

		return $template;
	}

	/**
	 * Filters body classes to add a special class when view is loaded.
	 *
	 * @param array $classes Existing body classes.
	 * @return array
	 */
	public static function filter_body_class( $classes ) {
		if ( get_query_var( self::get_print_query_var() ) ) {
			$classes[] = 'tasty-recipes-print-view';
		}
		return $classes;
	}

	/**
	 * Wrapper method for getting the print query variable.
	 *
	 * @return string
	 */
	public static function get_print_query_var() {
		/**
		 * Get the 'keyword' used in the print URL
		 *
		 * @param string $query_var
		 */
		return apply_filters( 'tasty_recipes_print_query_var', self::PRINT_QUERY_VAR );
	}

}
