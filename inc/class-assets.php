<?php
/**
 * Registers all scripts and styles.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes;

use Tasty_Recipes;
use Tasty_Recipes\Objects\Recipe;

/**
 * Registers all scripts and styles.
 */
class Assets {

	/**
	 * ID used for TinyMCE instance in modal.
	 *
	 * @var string
	 */
	private static $editor_id = 'tasty-recipes-editor';

	/**
	 * Whether the CSS has been loaded.
	 *
	 * @var bool
	 */
	private static $css_loaded = false;

	/**
	 * Load hooks.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public static function load_hooks() {
		add_action( 'init', array( __CLASS__, 'register_assets' ) );
		add_action( 'wp_print_styles', array( __CLASS__, 'action_wp_print_styles' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'action_admin_enqueue_scripts' ) );
		add_action( 'wp_enqueue_editor', array( __CLASS__, 'action_wp_enqueue_editor' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'action_enqueue_block_editor_assets' ) );
		add_action( 'tcb_editor_enqueue_scripts', array( __CLASS__, 'action_enqueue_tcb_frame_styles' ) );
	}

	/**
	 * Registers all scripts and styles.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public static function register_assets() {
		add_action( 'wp_head', array( __CLASS__, 'maybe_load_head' ), 7 );

		wp_register_style(
			'tasty-recipes-main',
			plugins_url( '/assets/dist/recipe.css', __DIR__ ),
			array(),
			TASTY_RECIPES_LITE_VERSION
		);

		wp_register_script(
			'tasty-recipes',
			plugins_url( '/assets/dist/recipe-js.js', __DIR__ ),
			array(),
			TASTY_RECIPES_LITE_VERSION,
			true
		);
	}

	/**
	 * Enqueues relevant scripts in the admin.
	 *
	 * @return void
	 */
	public static function action_admin_enqueue_scripts() {
		global $wpdb;

		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		if ( 'wp-tasty_page_tasty-recipes' === $screen->id ) {
			$time = filemtime( dirname( __DIR__ ) . '/assets/js/settings.js' );
			wp_enqueue_script(
				'tasty-recipes-settings',
				plugins_url( 'assets/js/settings.js', __DIR__ ),
				array( 'jquery', 'wp-util' ),
				(string) $time,
				true
			);
			wp_localize_script(
				'tasty-recipes-settings',
				'tastyRecipesSettings',
				array(
					'nonce'          => wp_create_nonce( Admin::NONCE_KEY ),
					'pluginUrl'      => plugins_url( '', __DIR__ ),
					'isNutrifoxUser' => $wpdb->get_var( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key='nutrifox_id';" ), // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				)
			);
			$time = filemtime( dirname( __DIR__ ) . '/assets/dist/settings.css' );
			wp_enqueue_style(
				'tasty-recipes-settings',
				plugins_url( 'assets/dist/settings.css', __DIR__ ),
				array( 'editor-buttons' ),
				(string) $time
			);
			$time = filemtime( dirname( __DIR__ ) . '/assets/dist/settings.build.js' );
			wp_enqueue_script(
				'tasty-recipes-settings-v2',
				plugins_url( 'assets/dist/settings.build.js', __DIR__ ),
				array( 'wp-i18n', 'wp-util', 'wplink' ),
				(string) $time,
				true
			);
		}

		self::setup_editor_screen( $screen );
	}

	/**
	 * Enqueues relevant scripts in the editor.
	 *
	 * @since 3.12.3
	 *
	 * @param \WP_Screen $screen The current screen object.
	 *
	 * @return void
	 */
	private static function setup_editor_screen( $screen ) {
		if ( 'edit' !== $screen->base ) {
			return;
		}

		self::register_modal_editor_script();
		wp_enqueue_media();
		$time = filemtime( dirname( __DIR__ ) . '/assets/js/manage-posts.js' );
		wp_enqueue_script(
			'tasty-recipes-manage-posts',
			plugins_url( 'assets/js/manage-posts.js', __DIR__ ),
			array(
				'jquery',
				'tasty-recipes-editor-modal',
			),
			(string) $time,
			true
		);
		$script_data = array(
			'recipeDataStore' => array(),
		);
		foreach ( $GLOBALS['wp_query']->posts as $post ) {
			foreach ( Tasty_Recipes::get_recipes_for_post( $post->ID ) as $recipe ) {
				/**
				 * Permit modification of the recipe JSON before it's returned.
				 *
				 * @param array $recipe_json Existing recipe JSON blob.
				 */
				$recipe_json = apply_filters( 'tasty_recipes_shortcode_response_recipe_json', $recipe->to_json() );
				// These fields are stored without paragraph tags,
				// so they need to be added for TinyMCE compat.
				foreach ( array(
					'description',
					'ingredients',
					'instructions',
					'notes',
				) as $field ) {
					$recipe_json[ $field ] = wpautop( $recipe_json[ $field ] );
				}
				$script_data['recipeDataStore'][ $recipe->get_id() ] = $recipe_json;
			}
		}
		wp_localize_script( 'tasty-recipes-manage-posts', 'tastyRecipesManagePosts', $script_data );
		$time = filemtime( dirname( __DIR__ ) . '/assets/dist/editor.css' );
		wp_enqueue_style(
			'tasty-recipes-editor',
			plugins_url( 'assets/dist/editor.css', __DIR__ ),
			array( 'editor-buttons' ),
			(string) $time
		);
		/**
		 * Allows the default author name to be modified.
		 *
		 * @param string $default_author_name
		 */
		$default_author_name = apply_filters( 'tasty_recipes_default_author_name', wp_get_current_user() ? wp_get_current_user()->display_name : '' );
		wp_localize_script(
			'tasty-recipes-editor',
			'tastyRecipesEditor',
			array(
				'i18n'              => array(
					'frameTitle'  => esc_html__( 'Media Library', 'tasty-recipes-lite' ),
					'frameButton' => esc_html__( 'Select Image', 'tasty-recipes-lite' ),
				),
				'currentPostId'     => 0,
				'defaultAuthorName' => $default_author_name,
				'parseNonce'        => wp_create_nonce( 'tasty_recipes_parse_shortcode' ),
				'modifyNonce'       => wp_create_nonce( 'tasty_recipes_modify_recipe' ),
				'pluginURL'         => plugins_url( '', __DIR__ ),
			)
		);
		/**
		 * Allow Tasty Links to enqueue its scripts.
		 */
		do_action( 'tasty_recipes_enqueue_editor_scripts' );
		if ( ! did_action( 'admin_footer' ) && ! doing_action( 'admin_footer' ) ) {
			add_action( 'admin_footer', array( __CLASS__, 'action_admin_footer_render_template' ) );
		} else {
			self::action_admin_footer_render_template();
		}
	}

	/**
	 * Enqueues relevant scripts when the editor is loaded.
	 *
	 * @return void
	 */
	public static function action_wp_enqueue_editor() {
		if ( ! Editor::is_tasty_recipes_editor_view() ) {
			return;
		}

		self::register_modal_editor_script();
		$time = filemtime( dirname( __DIR__ ) . '/assets/js/editor.js' );
		wp_enqueue_script(
			'tasty-recipes-editor',
			plugins_url( 'assets/js/editor.js', __DIR__ ),
			array(
				'jquery',
				'mce-view',
				'tasty-recipes-editor-modal',
			),
			(string) $time,
			true
		);
		$time = filemtime( dirname( __DIR__ ) . '/assets/dist/editor.css' );
		wp_enqueue_style(
			'tasty-recipes-editor',
			plugins_url( 'assets/dist/editor.css', __DIR__ ),
			array(),
			(string) $time
		);
		wp_localize_script(
			'tasty-recipes-editor',
			'tastyRecipesEditor',
			array(
				'i18n'                => array(
					'frameTitle'  => esc_html__( 'Media Library', 'tasty-recipes-lite' ),
					'frameButton' => esc_html__( 'Select Image', 'tasty-recipes-lite' ),
				),
				'currentPostId'       => self::get_current_post_id(),
				'defaultAuthorName'   => wp_get_current_user() ? wp_get_current_user()->display_name : '',
				'parseNonce'          => wp_create_nonce( 'tasty_recipes_parse_shortcode' ),
				'modifyNonce'         => wp_create_nonce( 'tasty_recipes_modify_recipe' ),
				'pluginURL'           => plugins_url( '', __DIR__ ),
				'dismissedConverters' => Editor::get_dismissed_converters(),
			)
		);
		/**
		 * Allow Tasty Links to enqueue its scripts.
		 */
		do_action( 'tasty_recipes_enqueue_editor_scripts' );
		if ( ! did_action( 'admin_footer' ) && ! doing_action( 'admin_footer' ) ) {
			add_action( 'admin_footer', array( __CLASS__, 'action_admin_footer_render_template' ) );
		} else {
			self::action_admin_footer_render_template();
		}
	}

	/**
	 * Registers data we need client-side as a part of the initial page load.
	 *
	 * @return void
	 */
	public static function action_enqueue_block_editor_assets() {
		$blocks_data = array(
			'recipeBlockTitle' => 'Tasty Recipe',
			'recipeDataStore'  => array(),
			'editorNotices'    => array(),
		);
		$post_id     = self::get_current_post_id();
		if ( $post_id ) {
			foreach ( Tasty_Recipes::get_recipes_for_post( $post_id ) as $recipe ) {
				/**
				 * Permit modification of the recipe JSON before it's returned.
				 *
				 * @param array $recipe_json Existing recipe JSON blob.
				 */
				$recipe_json = apply_filters( 'tasty_recipes_shortcode_response_recipe_json', $recipe->to_json() );
				// These fields are stored without paragraph tags,
				// so they need to be added for TinyMCE compat.
				foreach ( array(
					'description',
					'ingredients',
					'instructions',
					'notes',
				) as $field ) {
					$recipe_json[ $field ] = wpautop( $recipe_json[ $field ] );
				}
				$blocks_data['recipeDataStore'][ $recipe->get_id() ] = $recipe_json;
			}
			$blocks_data['editorNotices'] = Editor::get_converter_messages( $post_id );
		}
		wp_localize_script( 'tasty-recipes-block-editor', 'tastyRecipesBlockEditor', $blocks_data );

		self::action_wp_head();
	}

	/**
	 * Enqueue the main style in the Thrive Architect inner frame.
	 *
	 * @since 3.14
	 *
	 * @return void
	 */
	public static function action_enqueue_tcb_frame_styles() {
		/**
		 * If the post was already saved and the page refreshed in the editor,
		 * the styles are already enqueued, so we don't need to do it again.
		 */
		if ( ! wp_style_is( 'tasty-recipes-main' ) ) {
			self::action_wp_head();
		}
	}

	/**
	 * Register the modal editor script with its localization.
	 *
	 * @return void
	 */
	public static function register_modal_editor_script() {

		$time = 0;
		if ( file_exists( dirname( __DIR__ ) . '/assets/dist/recipe-editor.build.js' ) ) {
			$time = filemtime( dirname( __DIR__ ) . '/assets/dist/recipe-editor.build.js' );
		}
		wp_register_script(
			'tasty-recipes-recipe-editor',
			plugins_url( 'assets/dist/recipe-editor.build.js', __DIR__ ),
			array( 'wp-util', 'wplink' ),
			(string) $time,
			true
		);

		$cooking_attributes = Recipe::get_cooking_attributes();
		unset( $cooking_attributes['additional_time_label'] );
		unset( $cooking_attributes['additional_time_value'] );
		// Add Keywords to this set of editable fields.
		$general_attributes             = Recipe::get_general_attributes();
		$cooking_attributes['keywords'] = $general_attributes['keywords'];

		$nutrition_attributes = Recipe::get_nutrition_attributes();
		$nutrition_attributes = array_reverse( $nutrition_attributes, true );
		// Add Nutrifox ID to the beginning of this set of attributes.
		$nutrition_attributes['nutrifox_id'] = $general_attributes['nutrifox_id'];
		$nutrition_attributes                = array_reverse( $nutrition_attributes, true );

		ob_start();
		do_action( 'tasty_recipes_editor_after_video_url' );
		$after_video_url_markup = ob_get_clean();

		wp_localize_script(
			'tasty-recipes-recipe-editor',
			'tastyRecipesRecipeEditorData',
			array(
				'pluginURL'              => plugins_url( '', __DIR__ ),
				'after_video_url_markup' => $after_video_url_markup,
				'labels'                 => array(
					'select_image'      => esc_html__( 'Select Image', 'tasty-recipes-lite' ),
					'details_heading'   => esc_html__( 'Details', 'tasty-recipes-lite' ),
					'video_url_heading' => esc_html__( 'Video URL', 'tasty-recipes-lite' ),
					'nutrition_heading' => esc_html__( 'Nutrition', 'tasty-recipes-lite' ),
					'additional_time'   => array(
						'add_button_text'     => esc_html__( '+ Time', 'tasty-recipes-lite' ),
						'remove_button_text'  => esc_html__( '- Time', 'tasty-recipes-lite' ),
						'add_button_title'    => esc_html__( 'Add an additional time field', 'tasty-recipes-lite' ),
						'remove_button_title' => esc_html__( 'Remove additional time field', 'tasty-recipes-lite' ),
						'label_placeholder'   => esc_html__( 'Additional Time Label', 'tasty-recipes-lite' ),
						'value_placeholder'   => esc_html__( 'Additional Time Value', 'tasty-recipes-lite' ),
					),
				),
				'attributes'             => array(
					'general'   => $general_attributes,
					'cooking'   => $cooking_attributes,
					'nutrition' => $nutrition_attributes,
				),
				'video_settings'         => array(
					'label'    => esc_html__( 'Video Settings: ', 'tasty-recipes-lite' ),
					'settings' => array(
						'autoplay'      => esc_html__( 'Autoplay', 'tasty-recipes-lite' ),
						'mute'          => esc_html__( 'Mute', 'tasty-recipes-lite' ),
						'loop'          => esc_html__( 'Loop', 'tasty-recipes-lite' ),
						'hide-controls' => esc_html__( 'Hide Controls', 'tasty-recipes-lite' ),
					),
				),
			)
		);
		$time = filemtime( dirname( __DIR__ ) . '/assets/js/editor-modal.js' );
		wp_register_script(
			'tasty-recipes-editor-modal',
			plugins_url( 'assets/js/editor-modal.js', __DIR__ ),
			array( 'jquery', 'tasty-recipes-recipe-editor' ),
			(string) $time,
			true
		);
		/**
		 * Allows the default author name to be modified.
		 *
		 * @param string $default_author_name
		 */
		$default_author_name = apply_filters( 'tasty_recipes_default_author_name', wp_get_current_user() ? wp_get_current_user()->display_name : '' );
		wp_localize_script(
			'tasty-recipes-editor-modal',
			'tastyRecipesEditorModalData',
			array(
				'i18n'              => array(
					'frameTitle'  => esc_html__( 'Media Library', 'tasty-recipes-lite' ),
					'frameButton' => esc_html__( 'Select Image', 'tasty-recipes-lite' ),
				),
				'currentPostId'     => self::get_current_post_id(),
				'defaultAuthorName' => $default_author_name,
				'modifyNonce'       => wp_create_nonce( 'tasty_recipes_modify_recipe' ),
				'parseNonce'        => wp_create_nonce( 'tasty_recipes_parse_shortcode' ),
				'pluginURL'         => plugins_url( '', __DIR__ ),
			)
		);
	}

	/**
	 * Stomp on all registered styles in the print view for a recipe.
	 *
	 * @return void
	 */
	public static function action_wp_print_styles() {
		$recipe_id = (int) get_query_var( Content_Model::get_print_query_var() );
		if ( ! is_singular() || ! $recipe_id ) {
			return;
		}

		$recipe_ids = Tasty_Recipes::get_recipe_ids_for_post( get_queried_object_id() );
		if ( ! in_array( $recipe_id, $recipe_ids, true ) ) {
			return;
		}

		// phpcs:ignore WordPressVIPMinimum.UserExperience.AdminBarRemoval.RemovalDetected
		show_admin_bar( false );
		remove_action( 'wp_head', '_admin_bar_bump_cb' );
		$time = filemtime( dirname( __DIR__ ) . '/assets/dist/print.css' );
		wp_enqueue_style(
			'tasty-recipes-print',
			plugins_url( 'assets/dist/print.css', __DIR__ ),
			array(),
			(string) $time
		);

		wp_enqueue_script(
			'tasty-recipes-print-controls',
			plugins_url( 'assets/dist/recipe-print-controls.build.js', TASTY_RECIPES_LITE_FILE ),
			array(),
			TASTY_RECIPES_LITE_VERSION,
			true
		);

		// This must be echoed inside the print view.
		wp_register_style(
			'tasty-recipes-print-controls',
			plugins_url( 'assets/dist/recipe-print-controls.css' ),
			array(),
			TASTY_RECIPES_LITE_VERSION
		);
		// Loads the CSS contents inline.
		wp_style_add_data( 'tasty-recipes-print-controls', 'path', dirname( __DIR__ ) . '/assets/dist/recipe-print-controls.css' );
	}

	/**
	 * Prints the recipe editor modal template, but only once.
	 *
	 * @return void
	 */
	public static function action_admin_footer_render_template() {
		static $rendered_once;
		if ( isset( $rendered_once ) ) {
			return;
		}
		$rendered_once = true;
		// We don't actually care about rendering the textarea; we just want
		// the settings registered in an accessible way.
		ob_start();
		wp_editor(
			'',
			self::$editor_id,
			array(
				'teeny' => true,
			)
		);
		ob_get_clean();
		Tasty_Recipes::echo_template_part( 'recipe-editor' );
	}

	/**
	 * Sets the TinyMCE editor buttons for our editor instance.
	 *
	 * @param array  $buttons   Existing TinyMCE buttons.
	 * @param string $editor_id ID for the editor instance.
	 *
	 * @return array
	 */
	public static function filter_teeny_mce_buttons( $buttons, $editor_id ) {
		if ( self::$editor_id !== $editor_id ) {
			return $buttons;
		}
		return array( 'tr_heading', 'bold', 'italic', 'underline', 'bullist', 'numlist', 'link', 'unlink', 'tr_image', 'tr_video', 'removeformat', 'fullscreen' );
	}

	/**
	 * Filters TinyMCE registration to include our custom TinyMCE plugins.
	 *
	 * @param array  $mce_init  Existing registration details.
	 * @param string $editor_id ID for the editor instance.
	 *
	 * @return array
	 */
	public static function filter_teeny_mce_before_init( $mce_init, $editor_id ) {
		if ( self::$editor_id !== $editor_id ) {
			return $mce_init;
		}
		$mce_init['plugins'] .= ',tr_heading,tr_image,tr_video';
		$external_plugins     = array();
		if ( isset( $mce_init['external_plugins'] ) ) {
			if ( is_string( $mce_init['external_plugins'] ) ) {
				$decoded_plugins  = json_decode( $mce_init['external_plugins'], true );
				$external_plugins = $decoded_plugins ? $decoded_plugins : array();
			} elseif ( is_array( $mce_init['external_plugins'] ) ) {
				$external_plugins = $mce_init['external_plugins'];
			}
		}
		$external_plugins['tr_heading'] = plugins_url(
			'assets/js/tinymce-tr-heading.js?v=' . TASTY_RECIPES_LITE_VERSION,
			__DIR__
		);
		$external_plugins['tr_image']   = plugins_url(
			'assets/js/tinymce-tr-image.js?v=' . TASTY_RECIPES_LITE_VERSION,
			__DIR__
		);
		$external_plugins['tr_video']   = plugins_url(
			'assets/js/tinymce-tr-video.js?v=' . TASTY_RECIPES_LITE_VERSION,
			__DIR__
		);
		$mce_init['external_plugins']   = wp_json_encode( $external_plugins );
		return $mce_init;
	}

	/**
	 * Gets the current post ID from query args when global $post isn't set.
	 *
	 * @return int
	 */
	private static function get_current_post_id() {
		global $post;

		$param_id = Utils::get_param( 'post', 'intval', 0 );

		if ( $post && $post->ID === $param_id ) {
			return $post->ID;
		}

		if ( $param_id === 0 ) {
			return self::get_auto_draft_id();
		}

		return $param_id;
	}

	/**
	 * Retrieve or create an auto-draft post ID.
	 *
	 * @since 3.14
	 *
	 * @return int
	 */
	private static function get_auto_draft_id() {
		global $pagenow;
		
		if ( ! is_admin() || 'post-new.php' !== $pagenow ) {
			return 0;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : 'post';

		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts
		$recent_auto_drafts = get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => 'auto-draft',
				'posts_per_page' => 1,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'date_query'     => array(
					array(
						'after' => '10 minutes ago',
					),
				),
				'author'         => get_current_user_id(),
			)
		);
	
		if ( ! empty( $recent_auto_drafts ) ) {
			return $recent_auto_drafts[0]->ID;
		}

		if ( ! function_exists( 'get_default_post_to_edit' ) ) {
			require_once ABSPATH . 'wp-admin/includes/post.php';
		}
	
		return get_default_post_to_edit( $post_type, false )->ID;
	}

	/**
	 * Get CSS vars.
	 *
	 * @return string
	 */
	public static function get_css_vars() {
		if ( self::$css_loaded ) {
			return '';
		}

		$settings = Tasty_Recipes::get_filtered_customization_settings();
		$css_vars = array();

		$maybe_add_vars = array(
			'star_color',
			'button_color',
			'button_text_color',
			'primary_color',
			'body_color',
			'h3_color',
			'detail_value_color',
			'detail_label_color',
		);

		foreach ( $maybe_add_vars as $var ) {
			if ( ! empty( $settings[ $var ] ) ) {
				$key              = '--tr-' . str_replace( '_', '-', $var );
				$css_vars[ $key ] = $settings[ $var ];
				unset( $key );
			}
		}

		$css_vars = (array) apply_filters( 'tasty_recipes_css_vars', $css_vars, $settings );
		$css_vars = array_filter(
			$css_vars,
			function ( $value ) {
				// Allow 0.
				return $value || is_numeric( $value );
			}
		);
		if ( empty( $css_vars ) ) {
			return '';
		}

		$css = '';
		foreach ( $css_vars as $css_attribute => $css_value ) {
			$css .= $css_attribute . ':' . $css_value . ';';
		}
		return 'body{ ' . $css . ' }' . PHP_EOL;
	}

	/**
	 * Maybe add the TinyMCE style to the shortcode output.
	 *
	 * @since 3.13
	 *
	 * @param string $ret Shortcode output.
	 *
	 * @return void
	 */
	public static function maybe_add_tinymce_style( &$ret ) {
		if ( ! wp_doing_ajax() || Utils::get_post_value( 'action' ) !== 'tasty_recipes_parse_shortcode' ) {
			return;
		}

		global $wp_styles;

		self::action_wp_head();
		ob_start();
		$wp_styles->do_item( 'tasty-recipes-main' );
		$styles_output = ob_get_clean();

		$ret .= $styles_output;
	}

	/**
	 * Prepare the head if the post has a recipe.
	 * This must be loaded before the styles are printed in the head.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public static function maybe_load_head() {
		Distribution_Metadata::action_wp_head();
		if ( ! is_singular() ) {
			return;
		}

		$recipes = Tasty_Recipes::get_recipes_for_post(
			0,
			array( 'disable-json-ld' => false )
		);

		if ( empty( $recipes ) ) {
			return;
		}

		self::action_wp_head();
	}

	/**
	 * Enqueues the main Tasty Recipes stylesheet and loads in the head.
	 *
	 * @since 3.13
	 *
	 * @return void
	 */
	public static function action_wp_head() {
		$styles = Shortcodes::get_styles_as_string();
		self::add_inline_style( $styles );
	}

	/**
	 * Add styles to the recipe.
	 *
	 * @since 3.12.3
	 *
	 * @param string $styles Recipe main styles.
	 *
	 * @return void
	 */
	public static function add_inline_style( $styles ) {
		if ( self::$css_loaded ) {
			return;
		}

		wp_add_inline_style( 'tasty-recipes-before', Utils::minify_css( self::get_css_vars() ) );
		wp_add_inline_style( 'tasty-recipes-main', Utils::minify_css( $styles ) );
		self::$css_loaded = true;
	}

	/**
	 * Get common recipe scripts, like JS vars needed for other scripts.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public static function get_common_recipe_scripts() {
		global $post;

		$vars = array(
			'ajaxurl'     => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
			'ratingNonce' => is_user_logged_in() ? wp_create_nonce( 'tasty-recipes-save-rating' ) : '',
			'postId'      => $post ? $post->ID : 0,
		);

		/**
		 * Filters the common recipe scripts.
		 *
		 * @since 1.0
		 *
		 * @param array $vars Existing variables.
		 */
		$vars = apply_filters( 'tasty_recipes_common_js_vars', $vars );

		wp_enqueue_script( 'tasty-recipes' );
		wp_add_inline_script( 'tasty-recipes', 'window.trCommon=' . wp_json_encode( $vars ) . ';', 'before' );
	}

	/**
	 * Add inline styles to a new block. Without this workaround,
	 * the inline styles were not rendered when a new block was
	 * added.
	 *
	 * @since 3.12.4
	 * @deprecated 1.0
	 *
	 * @param string $block_content Block content.
	 *
	 * @return string
	 */
	public static function add_css_to_block( $block_content ) {
		_deprecated_function( __METHOD__, 'x.x' );
		return $block_content;
	}

	/**
	 * Add some styles to the recipe.
	 *
	 * @deprecated 3.12.3
	 *
	 * @param array $template_vars Recipe template vars.
	 *
	 * @return array
	 */
	public static function add_recipe_styles( $template_vars = array() ) {
		_deprecated_function( __METHOD__, '3.12.3', 'Assets::add_inline_style' );
		$template_vars['recipe_styles'] .= self::get_css_vars();

		return $template_vars;
	}
}
