<?php
/**
 * Defines our custom post type and everything related to its behavior.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes;

use Tasty_Recipes;
use Tasty_Recipes\Utils;
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
	 * Run functions on 'init' hook.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function load_init() {
		self::action_init_register_cron_events();
		self::action_init_register_post_types();
		self::action_init_register_rewrite_rules();
		self::action_init_register_oembed_providers();
	}

	/**
	 * Registers our cron events.
	 *
	 * @return void
	 */
	public static function action_init_register_cron_events() {
		if ( ! wp_next_scheduled( 'tasty_recipes_process_thumbnails' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'tasty_recipes_process_thumbnails' );
		}
	}

	/**
	 * Registers our post types.
	 *
	 * @return void
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
			'name'               => __( 'Recipes', 'tasty-recipes' ),
			'singular_name'      => __( 'Recipe', 'tasty-recipes' ),
			'all_items'          => __( 'All Recipes', 'tasty-recipes' ),
			'new_item'           => __( 'New Recipe', 'tasty-recipes' ),
			'add_new'            => __( 'Add New', 'tasty-recipes' ),
			'add_new_item'       => __( 'Add New Recipe', 'tasty-recipes' ),
			'edit_item'          => __( 'Edit Recipe', 'tasty-recipes' ),
			'view_item'          => __( 'View Recipes', 'tasty-recipes' ),
			'search_items'       => __( 'Search Recipes', 'tasty-recipes' ),
			'not_found'          => __( 'No recipes found', 'tasty-recipes' ),
			'not_found_in_trash' => __( 'No recipes found in trash', 'tasty-recipes' ),
			'parent_item_colon'  => __( 'Parent recipe', 'tasty-recipes' ),
			'menu_name'          => __( 'Recipes', 'tasty-recipes' ),
		);
		register_post_type( 'tasty_recipe', $args );
	}

	/**
	 * Registers our rewrite rules for print pages.
	 *
	 * @return void
	 */
	public static function action_init_register_rewrite_rules() {
		add_rewrite_endpoint( self::get_print_query_var(), EP_PERMALINK | EP_PAGES );
	}

	/**
	 * Registers our custom oEmbed providers.
	 *
	 * @return void
	 */
	public static function action_init_register_oembed_providers() {
		wp_oembed_add_provider( '#video\.mediavine\.com/videos/.*\.js#', 'https://embed.mediavine.com/oembed/', true );
		wp_oembed_add_provider( '#https://dashboard\.mediavine\.com/videos/.*/edit#', 'https://embed.mediavine.com/oembed/', true );
		wp_oembed_add_provider( '#https://reporting\.mediavine\.com/sites/[\d]+/videos/edit/.+#', 'https://embed.mediavine.com/oembed/', true );
		wp_oembed_add_provider( '#https?://((m|www)\.)?youtube\.com/shorts/*#i', 'https://www.youtube.com/oembed', true );
	}

	/**
	 * Filters rewrite rules to avoid loading post content at /print/ URL.
	 *
	 * @param array $rewrite_rules Existing rewrite rules.
	 *
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
	 * Fetches Nutrifox API data when the Nutrifox ID is updated.
	 *
	 * @param mixed  $check      Existing check value.
	 * @param int    $object_id  ID for the post being updated.
	 * @param string $meta_key   Post meta key.
	 * @param string $meta_value New meta value.
	 *
	 * @return mixed
	 */
	public static function filter_update_post_metadata_nutrifox_id( $check, $object_id, $meta_key, $meta_value ) {
		if ( 'tasty_recipe' !== get_post_type( $object_id ) || 'nutrifox_id' !== $meta_key ) {
			return $check;
		}
		if ( empty( $meta_value ) ) {
			delete_post_meta( $object_id, 'nutrifox_response' );
			delete_post_meta( $object_id, 'nutrifox_error' );
			return $check;
		}
		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get
		$response      = wp_remote_get( sprintf( '%s/api/recipes/%d', Tasty_Recipes::nutrifox_url(), $meta_value ) );
		$response_code = wp_remote_retrieve_response_code( $response );
		if ( ! is_wp_error( $response ) && 200 === $response_code ) {
			$body          = wp_remote_retrieve_body( $response );
			$response_data = json_decode( $body, true );
			update_post_meta( $object_id, 'nutrifox_response', $response_data );
			delete_post_meta( $object_id, 'nutrifox_error' );
		} else {
			if ( ! is_wp_error( $response ) ) {
				// translators: Nutrifox HTTP error code.
				$response = new \WP_Error( 'nutrifox-api', sprintf( __( 'Nutrifox API request failed (HTTP code %d)', 'tasty-recipes' ), $response_code ) );
			}
			update_post_meta( $object_id, 'nutrifox_error', $response );
			delete_post_meta( $object_id, 'nutrifox_response' );
		}
		return $check;
	}

	/**
	 * Fetches and stores oEmbed data when the recipe video URL is updated.
	 *
	 * @param mixed  $check      Existing check value.
	 * @param int    $object_id  ID for the post being updated.
	 * @param string $meta_key   Post meta key.
	 * @param string $meta_value New meta value.
	 *
	 * @return mixed
	 */
	public static function filter_update_post_metadata_video_url( $check, $object_id, $meta_key, $meta_value ) {
		if ( 'tasty_recipe' !== get_post_type( $object_id ) || 'video_url' !== $meta_key ) {
			return $check;
		}
		if ( empty( $meta_value ) ) {
			delete_post_meta( $object_id, 'video_url_response' );
			delete_post_meta( $object_id, 'video_url_error' );
			return $check;
		}

		// Looks like a shortcode.
		if ( 0 === strpos( trim( $meta_value ), '[' ) ) {
			$shortcode = trim( $meta_value, '[] ' ); // Deliberate empty space.
			// Only AdThrive shortcodes are supported.
			$adthrive_beginning = 'adthrive-in-post-video-player ';
			if ( 0 !== stripos( $shortcode, $adthrive_beginning ) ) {
				delete_post_meta( $object_id, 'video_url_response' );
				update_post_meta( $object_id, 'video_url_error', new \WP_Error( 'video-url', __( 'Unknown shortcode in video URL.', 'tasty-recipes' ) ) );
				return $check;
			}
			$shortcode_inner = substr( $shortcode, strlen( $adthrive_beginning ) );
			// WP 4.0 doesn't correctly handle dashes in shortcode attributes.
			$shortcode_inner = str_replace(
				array(
					'video-id',
					'upload-date',
				),
				array(
					'video_id',
					'upload_date',
				),
				$shortcode_inner
			);
			$atts            = shortcode_parse_atts( $shortcode_inner );
			if ( empty( $atts['video_id'] ) ) {
				delete_post_meta( $object_id, 'video_url_response' );
				update_post_meta( $object_id, 'video_url_error', new \WP_Error( 'video-url', __( 'Shortcode is missing video id.', 'tasty-recipes' ) ) );
				return $check;
			}
			$response_data = Tasty_Recipes::get_template_part(
				'video/adthrive-oembed-response',
				array(
					'video_id'    => $atts['video_id'],
					'title'       => isset( $atts['name'] ) ? $atts['name'] : '',
					'description' => isset( $atts['description'] ) ? $atts['description'] : '',
					'upload_date' => isset( $atts['upload_date'] ) ? $atts['upload_date'] : '',
				)
			);
			update_post_meta( $object_id, 'video_url_response', json_decode( $response_data ) );
			delete_post_meta( $object_id, 'video_url_error' );
			return $check;
		}

		$existing_value = get_post_meta( $object_id, $meta_key, true );
		if ( $existing_value !== $meta_value || ! get_post_meta( $object_id, 'video_url_response', true ) ) {
			if ( ! function_exists( '_wp_oembed_get_object' ) ) {
				require_once ABSPATH . WPINC . '/class-oembed.php';
			}
			$wp_oembed = _wp_oembed_get_object();
			$provider  = $wp_oembed->get_provider( $meta_value );
			if ( ! $provider ) {
				delete_post_meta( $object_id, 'video_url_response' );
				update_post_meta( $object_id, 'video_url_error', new \WP_Error( 'video-url', __( 'Unknown provider for URL.', 'tasty-recipes' ) ) );
			}

			$response_data = $wp_oembed->fetch( $provider, $meta_value );
			if ( false !== $response_data ) {
				update_post_meta( $object_id, 'video_url_response', $response_data );
				delete_post_meta( $object_id, 'video_url_error' );

				/**
				 * Enrich the video data with additional metadata.
				 *
				 * @since x.x
				 *
				 * @param int    $object_id  ID for the post being updated.
				 * @param object $meta_value Existing oEmbed response data.
				 */
				do_action( 'tasty_recipes_enrich_video', $object_id, $meta_value );
			} else {
				delete_post_meta( $object_id, 'video_url_response' );
				update_post_meta( $object_id, 'video_url_error', new \WP_Error( 'video-url', __( 'Invalid response from provider.', 'tasty-recipes' ) ) );
			}
		}
		return $check;
	}

	/**
	 * Generates our custom JSON+LD image sizes when a thumbnail is assigned to a recipe.
	 *
	 * @param mixed  $check      Existing check value.
	 * @param int    $object_id  ID for the post being updated.
	 * @param string $meta_key   Post meta key.
	 * @param string $meta_value New meta value.
	 *
	 * @return mixed
	 */
	public static function filter_update_post_metadata_thumbnail_id( $check, $object_id, $meta_key, $meta_value ) {

		if ( 'tasty_recipe' !== get_post_type( $object_id ) || '_thumbnail_id' !== $meta_key ) {
			return $check;
		}
		if ( empty( $meta_value ) ) {
			return $check;
		}
		self::generate_attachment_image_sizes( (int) $meta_value );
		return $check;
	}

	/**
	 * Cron callback to generate image sizes for attachments associated with Tasty Recipes.
	 *
	 * @return void
	 */
	public static function action_tasty_recipes_process_thumbnails() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$attach_ids = $wpdb->get_col(
			"SELECT pm.meta_value FROM {$wpdb->postmeta} as pm
			LEFT JOIN {$wpdb->posts} as p ON p.ID = pm.post_id
			WHERE pm.meta_key='_thumbnail_id' AND p.post_type='tasty_recipe'"
		);
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
	 * @param int $attachment_id Id for the attachment.
	 *
	 * @return bool Success state.
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
	 * Emulates the WordPress auto-embed behavior for content immediately after
	 * line breaks and list items.
	 *
	 * @param string $content Existing post content.
	 *
	 * @return string
	 */
	public static function autoembed_advanced( $content ) {
		// Find URLs immediately after list items '<li>' and line breaks '<br />'.
		$content = preg_replace_callback(
			'#(^|<li(?: [^>]*)?>\s*|<p(?: [^>]*)?>\s*|\n\s*|<br(?: [^>]*)?>\s*)(https?://[^\s<>"]+)(\s*<\/li>|\s*<\/p>|\s*<br(?: [^>]*)?>|\s*\n|$)#Ui',
			function ( $matches ) {
				global $wp_embed;
				if ( ! $wp_embed ) {
					return $matches[0];
				}
				return $matches[1] . $wp_embed->shortcode( array(), $matches[2] ) . $matches[3];
			},
			$content
		);
		return $content;
	}

	/**
	 * Filters template loading to load our print template when
	 * print view is request.
	 *
	 * @param string $template Existing template being loaded.
	 *
	 * @return string
	 */
	public static function filter_template_include( $template ) {

		$recipe_id = (int) get_query_var( self::get_print_query_var() );
		if ( is_singular() && $recipe_id ) {
			$recipe_ids = Tasty_Recipes::get_recipe_ids_for_post( get_queried_object_id() );
			if ( in_array( $recipe_id, $recipe_ids, true ) ) {
				$template = Tasty_Recipes::get_template_path( 'recipe-print' );
			}
		}

		return $template;
	}

	/**
	 * Filters body classes to add a special class when view is loaded.
	 *
	 * @param array $classes Existing body classes.
	 *
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
		 * Get the 'keyword' used in the print URL.
		 *
		 * @param string $query_var
		 */
		return apply_filters( 'tasty_recipes_print_query_var', self::PRINT_QUERY_VAR );
	}

	/**
	 * Fetches Nutrifox conversion data when the ingredients are updated.
	 *
	 * @deprecated x.x
	 *
	 * @param mixed $check Existing check value.
	 *
	 * @return mixed
	 */
	public static function filter_update_post_metadata_ingredients( $check ) {
		_deprecated_function( __METHOD__, 'x.x', 'Tasty_Recipes_Pro\Content_Model::filter_update_post_metadata_ingredients' );
		return $check;
	}

	/**
	 * Generates the Nutrifox conversion data.
	 *
	 * @deprecated x.x
	 *
	 * @return bool
	 */
	public static function generate_nutrifox_conversion() {
		_deprecated_function( __METHOD__, 'x.x', 'Tasty_Recipes_Pro\Content_Model::generate_nutrifox_conversion' );
		return false;
	}

	/**
	 * Cron callback to apply unit conversion to recipes without it.
	 *
	 * @deprecated x.x
	 *
	 * @return void
	 */
	public static function action_tasty_recipes_apply_unit_conversion() {
		_deprecated_function( __METHOD__, 'x.x' );
	}

	/**
	 * Cron callback to enrich YouTube responses with additional metadata.
	 *
	 * @deprecated x.x
	 *
	 * @return void
	 */
	public static function action_tasty_recipes_enrich_youtube_embeds() {
		_deprecated_function( __METHOD__, 'x.x', 'Tasty_Recipes_Pro\Content_Model::action_tasty_recipes_enrich_youtube_embeds' );
	}

	/**
	 * Enriches the YouTube oEmbed response with additional metadata.
	 *
	 * @deprecated x.x
	 *
	 * @return bool
	 */
	public static function enrich_youtube_oembed() {
		_deprecated_function( __METHOD__, 'x.x', 'Tasty_Recipes_Pro\Content_Model::enrich_youtube_oembed' );
		return false;
	}

	/**
	 * Applies the enrichment to the response data.
	 *
	 * @deprecated x.x
	 *
	 * @param string $video_url     URL assigned to the recipe.
	 * @param object $response_data Existing oEmbed response data.
	 *
	 * @return mixed
	 */
	public static function apply_youtube_enrichment_to_response_data( $video_url, $response_data ) {
		_deprecated_function( __METHOD__, 'x.x', 'Tasty_Recipes_Pro\Content_Model::apply_youtube_enrichment_to_response_data' );
		return $response_data;
	}
}
