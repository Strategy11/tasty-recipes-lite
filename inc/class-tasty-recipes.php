<?php
/**
 * Base controller class.
 *
 * @package Tasty_Recipes
 */

/**
 * Base controller class for the plugin.
 */
class Tasty_Recipes {

	/**
	 * Store of recipe JSON data for current view.
	 *
	 * Used to share state between Shortcode and Admin classes.
	 *
	 * @var array
	 */
	public $recipe_json = array();

	/**
	 * Singleton instance for this class.
	 *
	 * @var Tasty_Recipes
	 */
	private static $instance;

	/**
	 * Option name for customizations.
	 *
	 * @var string
	 */
	const CUSTOMIZATION_OPTION = 'tasty_recipes_customization';

	/**
	 * Option name for the default author link.
	 *
	 * @var string
	 */
	const DEFAULT_AUTHOR_LINK_OPTION = 'tasty_recipes_default_author_link';

	/**
	 * Option name for the Instacart enable field.
	 *
	 * @var string
	 */
	const INSTACART_OPTION = 'tasty_recipes_instacart';

	/**
	 * Option name for the Instacart affiliate ID.
	 *
	 * @deprecated 1.0
	 *
	 * @var string
	 */
	const INSTACART_AFFILIATE_ID_OPTION = '';

	/**
	 * Option name for the Instagram handle.
	 *
	 * @deprecated 1.0
	 *
	 * @var string
	 */
	const INSTAGRAM_HANDLE_OPTION = '';

	/**
	 * Option name for the Instagram tag.
	 *
	 * @deprecated 1.0
	 *
	 * @var string
	 */
	const INSTAGRAM_HASHTAG_OPTION = '';

	/**
	 * Option name for the license key.
	 *
	 * @var string
	 */
	const LICENSE_KEY_OPTION = 'tasty_recipes_license_key';

	/**
	 * Option name for the ShareASale affiliate ID.
	 *
	 * @var string
	 */
	const SHAREASALE_OPTION = 'tasty_recipes_shareasale';

	/**
	 * Option name for plugin activation state.
	 *
	 * @var string
	 */
	const PLUGIN_ACTIVATION_OPTION = 'tasty_recipes_do_activation_redirect';

	/**
	 * Option name for the template.
	 *
	 * @var string
	 */
	const TEMPLATE_OPTION = 'tasty_recipes_template';

	/**
	 * Option name for the quick links.
	 *
	 * @var string
	 */
	const QUICK_LINKS_OPTION = 'tasty_recipes_quick_links';

	/**
	 * Option name for the quick links.
	 *
	 * @var string
	 */
	const QUICK_LINKS_STYLE = 'tasty_recipes_quick_links_style';
	/**
	 * Option name for the card buttons.
	 *
	 * @var string
	 */
	const CARD_BUTTONS_OPTION = 'tasty_recipes_card_buttons';

	/**
	 * Option name for the unit conversion option.
	 *
	 * @var string
	 */
	const UNIT_CONVERSION_OPTION = 'tasty_recipes_unit_conversion';

	/**
	 * Option name for the automatic unit conversion option.
	 *
	 * @deprecated 1.0
	 *
	 * @var string
	 */
	const AUTOMATIC_UNIT_CONVERSION_OPTION = '';

	/**
	 * Option name for the ingredient checkboxes option.
	 *
	 * @var string
	 */
	const INGREDIENT_CHECKBOXES_OPTION = 'tasty_recipes_ingredient_checkboxes';

	/**
	 * Option name for the cook mode.
	 *
	 * @var string
	 */
	const COOK_MODE_OPTION = 'tasty_recipes_cook_mode';

	/**
	 * Option name for the cook mode helper.
	 *
	 * @deprecated 1.0
	 *
	 * @var string
	 */
	const COOK_MODE_HELPER_OPTION = '';

	/**
	 * Option name for the disable scaling option.
	 *
	 * @var string
	 */
	const DISABLE_SCALING_OPTION = 'tasty_recipes_disable_scaling';

	/**
	 * Option name for the copy to clipboard.
	 *
	 * @var string
	 */
	const COPY_TO_CLIPBOARD_OPTION = 'tasty_recipes_copy_to_clipboard';

	/**
	 * Instantiates and gets the singleton instance for the class.
	 *
	 * @return Tasty_Recipes
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new Tasty_Recipes();
			self::$instance::require_files();
			self::$instance->setup_actions();
			self::$instance->setup_filters();
		}
		return self::$instance;
	}

	/**
	 * Loads required plugin files and registers autoloader.
	 *
	 * @return void
	 */
	private static function require_files() {
		require_once dirname( TASTY_RECIPES_LITE_FILE ) . '/functions.php';

		load_plugin_textdomain( 'tasty-recipes-lite', false, basename( TASTY_RECIPES_LITE_FILE ) . '/languages' );

		spl_autoload_register(
			function ( $class_name ) {
				self::register_autoloader( $class_name );
			}
		);
	}

	/**
	 * Register the class autoloader.
	 *
	 * @since 1.0
	 *
	 * @param string $class_name The class name to load.
	 * @param string $base The base directory to search in.
	 *
	 * @return void
	 */
	public static function register_autoloader( $class_name, $base = '' ) {
		$lite_plugin = false;
		if ( empty( $base ) ) {
			$base        = dirname( TASTY_RECIPES_LITE_FILE );
			$lite_plugin = true;
		}
		$class_name = ltrim( $class_name, '\\' );
		if ( 0 !== stripos( $class_name, 'Tasty_Recipes' ) ) {
			return;
		}

		$lite_class = stripos( $class_name, 'Pro\\' ) === false;
		if ( $lite_class !== $lite_plugin ) {
			// Don't load Pro classes in Lite.
			return;
		}

		$parts = explode( '\\', $class_name );
		array_shift( $parts ); // Don't need "Tasty_Recipes".
		$last    = array_pop( $parts ); // File should be 'class-[...].php'.
		$last    = 'class-' . $last . '.php';
		$parts[] = $last;
		$file    = $base . '/inc/' . str_replace( '_', '-', strtolower( implode( '/', $parts ) ) );
		if ( file_exists( $file ) ) {
			// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
			require $file;
			return;
		}

		// Might be a trait.
		$file = str_replace( '/class-', '/trait-', $file );
		if ( file_exists( $file ) ) {
			// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
			require $file;
		}
	}

	/**
	 * Registry of actions used in the plugin.
	 *
	 * @return void
	 */
	private function setup_actions() {
		// Bootstrap.
		Tasty_Recipes\Assets::load_hooks();
		Tasty_Recipes\Shortcodes::load_hooks();
		add_action( 'init', array( 'Tasty_Recipes\Block_Editor', 'action_init_register' ) );
		add_action( 'init', array( 'Tasty_Recipes\Content_Model', 'init_post_type' ) );
		add_action( 'tasty_recipes_process_thumbnails', array( 'Tasty_Recipes\Content_Model', 'action_tasty_recipes_process_thumbnails' ) );

		// Frontend.
		add_action( 'body_class', array( 'Tasty_Recipes\Content_Model', 'filter_body_class' ) );
		add_action( 'wpseo_robots', array( 'Tasty_Recipes\Distribution_Metadata', 'action_wpseo_robots' ) );
		add_filter( 'wpseo_schema_graph_pieces', array( 'Tasty_Recipes\Distribution_Metadata', 'filter_wpseo_schema_graph_pieces' ), 10, 2 );
		foreach ( array( 'wp_insert_comment', 'wp_update_comment', 'wp_set_comment_status' ) as $hook ) {
			add_action( $hook, array( 'Tasty_Recipes\Ratings', 'action_modify_comment_update_recipe_ratings' ) );
		}
		add_action( 'rest_insert_comment', array( 'Tasty_Recipes\Ratings', 'action_rest_insert_comment' ), 10, 2 );
		add_action( 'admin_head', array( 'Tasty_Recipes\Ratings', 'action_admin_head' ) );

		add_filter( 'tasty_recipes_customization_settings', array( 'Tasty_Recipes\Ratings', 'apply_default_rating_stars_color' ) );

		if ( ! is_admin() && ! Tasty_Recipes\Utils::is_rest_api_request() ) {
			Tasty_Recipes\Frontend::get_instance();
		}

		// Admin.
		Tasty_Recipes\Admin::load_hooks();
		add_action( 'admin_notices', array( 'Tasty_Recipes\Editor', 'action_admin_notices' ) );
		add_action( 'update_option_' . self::LICENSE_KEY_OPTION, array( 'Tasty_Recipes\Admin', 'action_update_option_clear_transient' ) );
		add_action( 'media_buttons', array( 'Tasty_Recipes\Editor', 'action_media_buttons' ) );

		add_action( 'wp_ajax_tasty_recipes_save_rating', array( 'Tasty_Recipes\Ratings', 'save_rating' ) );
		add_action( 'wp_ajax_nopriv_tasty_recipes_save_rating', array( 'Tasty_Recipes\Ratings', 'save_rating' ) );
		add_action( 'wp_ajax_tasty_recipes_preview_recipe_card', array( 'Tasty_Recipes\Designs\Template', 'handle_wp_ajax_preview_recipe_card' ) );
		add_action( 'wp_ajax_tasty_recipes_ignore_convert', array( 'Tasty_Recipes\Editor', 'handle_wp_ajax_ignore_convert' ) );
		add_action( 'wp_ajax_tasty_recipes_convert_recipe', array( 'Tasty_Recipes\Editor', 'handle_wp_ajax_convert_recipe' ) );
		add_action( 'wp_ajax_tasty_recipes_ignore_type_convert', array( 'Tasty_Recipes\Editor', 'handle_wp_ajax_ignore_type_convert' ) );
		add_action( 'wp_ajax_tasty_recipes_revert_ignore_type_convert', array( 'Tasty_Recipes\Editor', 'handle_wp_ajax_revert_ignore_type_convert' ) );
		add_action( 'wp_ajax_tasty_recipes_parse_shortcode', array( 'Tasty_Recipes\Editor', 'handle_wp_ajax_parse_shortcode' ) );
		add_action( 'wp_ajax_tasty_recipes_modify_recipe', array( 'Tasty_Recipes\Editor', 'handle_wp_ajax_modify_recipe' ) );

		add_action( 'wp_insert_post', array( 'Tasty_Recipes\Editor', 'handle_add_new_post' ), 10, 3 );
		add_action( 'post_updated', array( 'Tasty_Recipes\Editor', 'handle_edit_post' ), 10, 3 );

		if ( is_admin() ) {
			Tasty_Recipes\MetaBox::get_instance();
		}
	}

	/**
	 * Registry of filters used in the plugin.
	 *
	 * @return void
	 */
	private function setup_filters() {
		global $wp_embed;

		// Bootstrap.
		add_filter( 'rewrite_rules_array', array( 'Tasty_Recipes\Content_Model', 'filter_rewrite_rules_array' ) );

		// WordPress' standard text formatting filters.
		add_filter( 'tasty_recipes_the_title', 'wptexturize' );
		add_filter( 'tasty_recipes_the_title', 'convert_chars' );
		add_filter( 'tasty_recipes_the_title', 'trim' );
		add_filter( 'tasty_recipes_the_content', array( $wp_embed, 'autoembed' ), 8 );
		add_filter( 'tasty_recipes_the_content', array( 'Tasty_Recipes\Content_Model', 'autoembed_advanced' ), 8 );
		add_filter( 'tasty_recipes_the_content', 'wptexturize' );
		add_filter( 'tasty_recipes_the_content', 'convert_smilies', 20 );
		add_filter( 'tasty_recipes_the_content', 'wpautop' );
		add_filter( 'tasty_recipes_the_content', 'shortcode_unautop' );
		add_filter( 'tasty_recipes_the_content', 'prepend_attachment' );
		// Responsive images for WordPress 4.4 to 5.4.
		if ( function_exists( 'wp_make_content_images_responsive' ) && ! function_exists( 'wp_filter_content_tags' ) ) {
			add_filter( 'tasty_recipes_the_content', 'wp_make_content_images_responsive' );
		}
		// Lazyloading images for WP 5.5.
		if ( function_exists( 'wp_filter_content_tags' ) ) {
			add_filter( 'tasty_recipes_the_content', 'wp_filter_content_tags' );
		}

		// Plugin-specific filters.
		add_filter( 'teeny_mce_buttons', array( 'Tasty_Recipes\Admin', 'filter_teeny_mce_buttons' ), 10, 2 );
		add_filter( 'teeny_mce_buttons', array( 'Tasty_Recipes\Assets', 'filter_teeny_mce_buttons' ), 10, 2 );
		add_filter( 'teeny_mce_before_init', array( 'Tasty_Recipes\Assets', 'filter_teeny_mce_before_init' ), 10, 2 );
		add_filter( 'update_post_metadata', array( 'Tasty_Recipes\Content_Model', 'filter_update_post_metadata_nutrifox_id' ), 10, 4 );
		add_filter( 'update_post_metadata', array( 'Tasty_Recipes\Content_Model', 'filter_update_post_metadata_video_url' ), 10, 4 );
		add_filter( 'update_post_metadata', array( 'Tasty_Recipes\Content_Model', 'filter_update_post_metadata_thumbnail_id' ), 10, 4 );
		add_filter( 'template_include', array( 'Tasty_Recipes\Content_Model', 'filter_template_include' ), 1000 );

		add_filter( 'allow_empty_comment', array( 'Tasty_Recipes\Ratings', 'filter_allow_rating_empty_comment' ), 10, 2 );
		add_filter( 'preprocess_comment', array( 'Tasty_Recipes\Ratings', 'filter_preprocess_comment' ), 0 );
		add_filter( 'comment_form_field_comment', array( 'Tasty_Recipes\Ratings', 'filter_comment_form_field_comment' ) );
		add_filter( 'comment_text', array( 'Tasty_Recipes\Ratings', 'filter_comment_text' ), 10, 2 );

		Tasty_Recipes\Settings::load_hooks();

		// Integrations.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'tasty-recipes', 'Tasty_Recipes\CLI' );
		}

		add_action( 'elementor/init', array( 'Tasty_Recipes\Integrations\Elementor', 'register_hooks' ) );

		// Integrations.
		add_filter(
			'jetpack_content_options_featured_image_exclude_cpt',
			array(
				'Tasty_Recipes\Integrations\Jetpack',
				'filter_jetpack_content_options_featured_image_exclude_cpt',
			)
		);

		// Rank Math.
		add_action(
			'rank_math/admin/enqueue_scripts',
			array(
				'Tasty_Recipes\Integrations\Rank_Math',
				'action_admin_enqueue_scripts',
			)
		);

		// Thrive.
		add_filter(
			'thrive_theme_shortcode_prefixes',
			array(
				'Tasty_Recipes\Integrations\Thrive',
				'filter_thrive_theme_shortcode_prefixes',
			)
		);
		add_action(
			'tve_editor_print_footer_scripts',
			array(
				'Tasty_Recipes\Integrations\Thrive',
				'action_tve_editor_print_footer_scripts',
			)
		);

		add_filter(
			'wpdiscuz_after_comment_post',
			array(
				'Tasty_Recipes\Integrations\WpDiscuz',
				'action_wpdiscuz_after_comment_post',
			)
		);

		Tasty_Recipes\Integrations\Akismet::instance();
	}

	/**
	 * Actions to perform when activating the plugin.
	 *
	 * @return void
	 */
	public static function plugin_activation() {
		self::require_files();
		\Tasty_Recipes\Content_Model::action_init_register_rewrite_rules();
		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.flush_rewrite_rules_flush_rewrite_rules
		flush_rewrite_rules();
		update_option( self::PLUGIN_ACTIVATION_OPTION, true );
	}

	/**
	 * Determine whether there's a recipe present in the post.
	 *
	 * @param int $post_id ID for the post to inspect.
	 *
	 * @return bool
	 */
	public static function has_recipe( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}
		if ( false !== stripos( $post->post_content, '[' . Tasty_Recipes\Shortcodes::RECIPE_SHORTCODE ) ) {
			return true;
		}
		if ( false !== stripos( $post->post_content, '<!-- wp:' . Tasty_Recipes\Block_Editor::RECIPE_BLOCK_TYPE . ' ' ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Get the Nutrifox url. If it's missing https, add it.
	 *
	 * @since 3.7.4
	 *
	 * @return string
	 */
	public static function nutrifox_url() {
		$url = TASTY_RECIPES_NUTRIFOX_DOMAIN;
		if ( stripos( $url, 'http' ) !== 0 ) {
			$url = 'https://' . $url;
		}
		return $url;
	}

	/**
	 * Get the recipe ids embedded within a given post.
	 *
	 * @param int   $post_id ID for the post to parse.
	 * @param array $options Any options to configure the bheavior.
	 *
	 * @return array
	 */
	public static function get_recipe_ids_for_post( $post_id, $options = array() ) {
		$post = get_post( $post_id );
		if ( ! $post_id || ! $post ) {
			return array();
		}
		return self::get_recipe_ids_from_content( $post->post_content, $options );
	}

	/**
	 * Get the recipe ids embedded within a given string.
	 *
	 * @param string $content Content to search for recipe ids.
	 * @param array  $options Configure return value behavior.
	 *
	 * @return array
	 */
	public static function get_recipe_ids_from_content( $content, $options = array() ) {

		$defaults = array(
			'disable-json-ld' => null,
			'full-result'     => false,
		);
		$options  = array_merge( $defaults, $options );

		$recipes = array();
		if ( preg_match_all( '#\[' . Tasty_Recipes\Shortcodes::RECIPE_SHORTCODE . '(.+)\]#Us', $content, $matches ) ) {
			foreach ( $matches[0] as $i => $shortcode ) {
				$atts = shortcode_parse_atts( $matches[1][ $i ] );
				if ( empty( $atts['id'] ) ) {
					continue;
				}

				if ( false === $options['disable-json-ld']
					&& in_array( 'disable-json-ld', $atts, true ) ) {
					continue;
				}

				if ( ! empty( $options['full-result'] ) ) {
					$recipes[] = $atts;
				} else {
					$recipes[] = (int) $atts['id'];
				}
			}
		}
		if ( function_exists( 'parse_blocks' ) ) {
			self::recursively_search_blocks( parse_blocks( $content ), $options, $recipes );
		}

		if ( preg_match_all( '#tasty-recipes-(?<recipe_id>\d+)-jump-target#iUs', $content, $recipe_matches ) ) {
			foreach ( $recipe_matches['recipe_id'] as $recipe_match ) {
				$recipes[] = (int) $recipe_match;
			}
		}

		return $recipes;
	}

	/**
	 * Parses blocks recursively for recipe IDs.
	 *
	 * @param array $blocks  Blocks to inspect.
	 * @param array $options Inspection options.
	 * @param array &$recipes Array of recipe ids.
	 *
	 * @return void
	 */
	public static function recursively_search_blocks( $blocks, $options, &$recipes ) {
		foreach ( $blocks as $untyped_block ) {
			$block = (array) $untyped_block;
			if ( ! empty( $block['blockName'] ) && Tasty_Recipes\Block_Editor::RECIPE_BLOCK_TYPE === $block['blockName'] ) {
				/**
				 * Filter to disable the use of custom schema for a block.
				 *
				 * @since 1.0
				 *
				 * @param bool $disable_json Whether to disable the custom schema.
				 */
				$disable_json = apply_filters( 'tasty_recipes_use_custom_schema', false, compact( 'block', 'options' ) );
				if ( $disable_json ) {
					continue;
				}
				if ( ! empty( $block['attrs']['id'] ) ) {
					if ( ! empty( $options['full-result'] ) ) {
						$recipes[] = (array) $block['attrs'];
					} else {
						$recipes[] = (int) $block['attrs']['id'];
					}
				}
			}
			if ( ! empty( $block['innerBlocks'] ) ) {
				self::recursively_search_blocks( $block['innerBlocks'], $options, $recipes );
			}
		}
	}

	/**
	 * Get a dictionary of converters supported by Tasty Recipes.
	 *
	 * @return array
	 */
	public static function get_converters() {
		return array(
			'cookbook'       => array(
				'class' => 'Tasty_Recipes\Converters\Cookbook',
				'label' => 'Cookbook',
			),
			'create'         => array(
				'class' => 'Tasty_Recipes\Converters\Mediavine_Create',
				'label' => 'Mediavine Create',
			),
			'easyrecipe'     => array(
				'class' => 'Tasty_Recipes\Converters\EasyRecipe',
				'label' => 'EasyRecipe',
			),
			'mealplannerpro' => array(
				'class' => 'Tasty_Recipes\Converters\MealPlannerPro',
				'label' => 'Meal Planner Pro',
			),
			'srp'            => array(
				'class' => 'Tasty_Recipes\Converters\Simple_Recipe_Pro',
				'label' => 'Simple Recipe Pro',
			),
			'wpcom'          => array(
				'class' => 'Tasty_Recipes\Converters\WPCom',
				'label' => 'WordPress.com',
			),
			'wprm'           => array(
				'class' => 'Tasty_Recipes\Converters\WP_Recipe_Maker',
				'label' => 'WP Recipe Maker',
			),
			'wpur'           => array(
				'class' => 'Tasty_Recipes\Converters\WP_Ultimate_Recipe',
				'label' => 'WP Ultimate Recipe',
			),
			'yummly'         => array(
				'class' => 'Tasty_Recipes\Converters\Yummly',
				'label' => 'Yummly',
			),
			'yumprint'       => array(
				'class' => 'Tasty_Recipes\Converters\YumPrint',
				'label' => 'YumPrint Recipe Card',
			),
			'ziplist'        => array(
				'class' => 'Tasty_Recipes\Converters\ZipList',
				'label' => 'ZipList (or Zip Recipes)',
			),
		);
	}

	/**
	 * Get the recipes embedded within a given post.
	 *
	 * @param int   $post_id ID for the post to inspect.
	 * @param array $options Any options to pass through to the parser.
	 *
	 * @return array
	 */
	public static function get_recipes_for_post( $post_id = 0, $options = array() ) {
		if ( ! $post_id ) {
			$post_id = get_queried_object()->ID;
		}
		$recipes = array();
		foreach ( self::get_recipe_ids_for_post( $post_id, $options ) as $id ) {
			$recipe = Tasty_Recipes\Objects\Recipe::get_by_id( $id );
			if ( $recipe ) {
				$recipes[ $id ] = $recipe;
			}
		}
		return $recipes;
	}

	/**
	 * Gets the customization options for Tasty Recipes.
	 *
	 * @return array
	 */
	public static function get_customization_settings() {
		$settings = array_merge(
			array(
				'primary_color'          => '',
				'secondary_color'        => '',
				'icon_color'             => '',
				'button_color'           => '',
				'detail_label_color'     => '',
				'detail_value_color'     => '',
				'h2_color'               => '',
				'h2_transform'           => '',
				'h3_color'               => '',
				'h3_transform'           => '',
				'body_color'             => '',
				'star_ratings_style'     => 'solid',
				'star_color'             => '',
				'nutrifox_display_style' => 'label',
			),
			(array) get_option( self::CUSTOMIZATION_OPTION, array() )
		);

		return $settings;
	}

	/**
	 * Get filtered customization settings.
	 *
	 * @since 3.9
	 *
	 * @return array
	 */
	public static function get_filtered_customization_settings() {
		/**
		 * Allow the customization settings to be modified based on context.
		 *
		 * @param array $settings Customization settings.
		 */
		return (array) apply_filters( 'tasty_recipes_customization_settings', self::get_customization_settings() );
	}

	/**
	 * Gets the card button settings.
	 *
	 * @param string $template Name of the template.
	 *
	 * @return array
	 */
	public static function get_card_button_settings( $template = null ) {
		$value = get_option( self::CARD_BUTTONS_OPTION );
		// Set defaults based on template.
		if ( empty( $value ) ) {
			if ( null === $template ) {
				$template = get_option( self::TEMPLATE_OPTION, '' );
			}
			if ( in_array( $template, array( 'bold', 'fresh' ), true ) ) {
				$value = array(
					'first'  => 'print',
					'second' => 'pin',
				);
			} else {
				$value = array(
					'first'  => 'print',
					'second' => '',
				);
			}
		}
		return $value;
	}

	/**
	 * Get a fully-qualified path to a template.
	 *
	 * @param string $template Template name.
	 *
	 * @return string
	 */
	public static function get_template_path( $template ) {
		$full_path = dirname( TASTY_RECIPES_LITE_FILE ) . '/templates/' . $template . '.php';
		return apply_filters( 'tasty_recipes_template_path', $full_path, $template );
	}

	/**
	 * Get a rendered template.
	 *
	 * @param string $template Fully-qualified template path.
	 * @param array  $vars     Variables to pass into the template
	 *                         you can pass here remove_new_lines with true to remove new lines from the template when rendered.
	 *
	 * @return false|string
	 */
	public static function get_template_part( $template, $vars = array() ) {
		$full_path = self::get_full_path( $template, $vars );
		if ( ! $full_path ) {
			return '';
		}

		$remove_new_lines = ! empty( $vars['remove_new_lines'] );
		if ( isset( $vars['remove_new_lines'] ) ) {
			unset( $vars['remove_new_lines'] );
		}

		ob_start();
		// @codingStandardsIgnoreStart
		if ( ! empty( $vars ) ) {
			extract( $vars );
		}
		// @codingStandardsIgnoreEnd

		// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		include $full_path;
		$contents = ob_get_clean();
		if ( ! $remove_new_lines || ! is_string( $contents ) ) {
			return $contents;
		}
		return str_replace( array( "\r\n", "\r", "\n" ), '', $contents );
	}

	/**
	 * Get the full path to a template.
	 *
	 * @param string $template Template to render.
	 * @param array  $vars     Variables to pass to the template.
	 *
	 * @return string
	 */
	private static function get_full_path( $template, $vars ) {
		$full_path = self::get_template_path( $template );

		// Provided template may already be a full path.
		if ( ! file_exists( $full_path ) ) {
			$full_path = $template;
		}

		if ( ! file_exists( $full_path ) && isset( $vars['fallback'] ) ) {
			$full_path = self::get_template_path( $vars['fallback'] );
		}

		if ( ! file_exists( $full_path ) ) {
			$full_path = '';
		}

		return $full_path;
	}

	/**
	 * Echo a rendered template that has already been escaped.
	 *
	 * @param string $template Template to render.
	 * @param array  $vars     Variables to pass to the template.
	 *
	 * @return void
	 */
	public static function echo_template_part( $template, $vars = array() ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo self::get_template_part( $template, $vars );
	}
}
