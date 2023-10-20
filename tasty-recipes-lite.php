<?php
/**
 * Plugin Name:     Tasty Recipes Lite
 * Plugin URI:      https://www.wptasty.com/tasty-recipes
 * Description:     Tasty Recipes is the easiest way to publish recipes on your WordPress blog.
 * Author:          WP Tasty
 * Author URI:      https://www.wptasty.com
 * Text Domain:     tasty-recipes-lite
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         Tasty_Recipes
 */

use Tasty\Recipes\Lite\MetaBox;
use Tasty\Recipes\Lite\Frontend;
use Tasty\Recipes\Lite\Utils;
use Tasty\Recipes\Lite\Integrations\Akismet;
use Tasty\Recipes\Lite\ContentModel;
use Tasty\Recipes\Lite\BlockEditor;
use Tasty\Recipes\Lite\Shortcodes;
use Tasty\Recipes\Lite\Assets;
use Tasty\Recipes\Lite\DistributionMetadata;
use Tasty\Recipes\Lite\Ratings;
use Tasty\Recipes\Lite\Admin;
use Tasty\Recipes\Lite\Editor;
use Tasty\Recipes\Lite\Integrations\Elementor;
use Tasty\Recipes\Lite\Objects\Recipe;
use Tasty\Recipes\Lite\Converters\Cookbook;
use Tasty\Recipes\Lite\Converters\MediavineCreate;
use Tasty\Recipes\Lite\Converters\EasyRecipe;
use Tasty\Recipes\Lite\Converters\MealPlannerPro;
use Tasty\Recipes\Lite\Converters\SimpleRecipePro;
use Tasty\Recipes\Lite\Converters\WPCom;
use Tasty\Recipes\Lite\Converters\WPRecipeMaker;
use Tasty\Recipes\Lite\Converters\WPUltimateRecipe;
use Tasty\Recipes\Lite\Converters\Yummly;
use Tasty\Recipes\Lite\Converters\YumPrint;
use Tasty\Recipes\Lite\Converters\ZipList;

define( 'TASTY_RECIPES_LITE_PLUGIN_VERSION', '0.0.1' );
define( 'TASTY_RECIPES_LITE_PLUGIN_FILE', __FILE__ );

if ( ! defined( 'TASTY_RECIPES_NUTRIFOX_DOMAIN' ) ) {
	define( 'TASTY_RECIPES_NUTRIFOX_DOMAIN', 'nutrifox.com' );
}

/**
 * Base controller class for the plugin.
 */
class TastyRecipesLiteMain {

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
	 * @var self
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
	 * @var string
	 */
	const INSTACART_AFFILIATE_ID_OPTION = 'tasty_recipes_instacart_affiliate_id';

	/**
	 * Option name for the Instagram handle.
	 *
	 * @var string
	 */
	const INSTAGRAM_HANDLE_OPTION = 'tasty_recipes_instagram_handle';

	/**
	 * Option name for the Instagram tag.
	 *
	 * @var string
	 */
	const INSTAGRAM_HASHTAG_OPTION = 'tasty_recipes_instagram_tag';

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
	const PLUGIN_ACTIVATION_OPTION = 'tasty_recipes_lite_do_activation_redirect';

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
	 * @var string
	 */
	const AUTOMATIC_UNIT_CONVERSION_OPTION = 'tasty_recipes_automatic_unit_conversion';

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
	 * @var string
	 */
	const COOK_MODE_HELPER_OPTION = 'tasty_recipes_cook_mode_helper';

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
	 * @return self
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->require_files();
			self::$instance->setup_actions();
			self::$instance->setup_filters();
		}
		return self::$instance;
	}

	/**
	 * Loads required plugin files and registers autoloader.
	 */
	private function require_files() {

		require_once dirname( __FILE__ ) . '/functions.php';

		load_plugin_textdomain( 'tasty-recipes-lite', false, basename( dirname( __FILE__ ) ) . '/languages' );

		/**
		 * Register the class autoloader
		 */
		spl_autoload_register( array( __CLASS__, 'autoloader' ) );
	}

	public function autoloader( $current_class ) {
		$prefix   = 'Tasty\\Recipes\\Lite\\';
		$base_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR;

		// does the class use the namespace prefix?
		$len = strlen( $prefix );
		if ( 0 !== strncmp( $prefix, $current_class, $len ) ) {
			// no, move to the next registered autoloader.
			return;
		}

		$relative_class = substr( $current_class, $len );
		$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

		// if the file exists, require it.
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}

	/**
	 * Registry of actions used in the plugin.
	 */
	private function setup_actions() {
		// Bootstrap.
		add_action( 'init', array( BlockEditor::class, 'action_init_register' ) );
		add_action( 'init', array( ContentModel::class, 'action_init_register_cron_events' ) );
		add_action( 'tasty_recipes_process_thumbnails', array( ContentModel::class, 'action_tasty_recipes_process_thumbnails' ) );
		add_action( 'init', array( ContentModel::class, 'action_init_register_post_types' ) );
		add_action( 'init', array( ContentModel::class, 'action_init_register_rewrite_rules' ) );
		add_action( 'init', array( Shortcodes::class, 'action_init_register_shortcode' ) );
		// Frontend.
		add_action( 'wp_print_styles', array( Assets::class, 'action_wp_print_styles' ) );
		add_action( 'body_class', array( ContentModel::class, 'filter_body_class' ) );
		add_action( 'wp_head', array( DistributionMetadata::class, 'action_wp_head_google_schema' ) );
		add_action( 'wp_head', array( DistributionMetadata::class, 'action_wp_head_noindex' ) );
		add_action( 'wp_head', array( Ratings::class, 'action_wp_head' ) );
		add_action( 'admin_head', array( Ratings::class, 'action_admin_head' ) );
		add_action( 'wpseo_robots', array( DistributionMetadata::class, 'action_wpseo_robots' ) );
		add_filter( 'wpseo_schema_graph_pieces', array( DistributionMetadata::class, 'filter_wpseo_schema_graph_pieces' ), 10, 2 );
		foreach ( array( 'wp_insert_comment', 'wp_update_comment', 'wp_set_comment_status' ) as $hook ) {
			add_action( $hook, array( Ratings::class, 'action_modify_comment_update_recipe_ratings' ) );
		}
		add_action( 'rest_insert_comment', array( Ratings::class, 'action_rest_insert_comment' ), 10, 2 );

		add_filter( 'tasty_recipes_customization_settings', array( Ratings::class, 'apply_default_rating_stars_color' ) );

		if ( ! is_admin() && ! Utils::is_rest_api_request() ) {
			Frontend::get_instance();
		}

		// Admin.
		add_action( 'admin_menu', array( Admin::class, 'action_admin_menu' ) );
		add_filter( 'manage_posts_columns', array( Admin::class, 'action_manage_posts_columns' ) );
		add_filter( 'manage_pages_columns', array( Admin::class, 'action_manage_posts_columns' ) );
		add_action( 'manage_posts_custom_column', array( Admin::class, 'action_manage_posts_custom_column' ), 10, 2 );
		add_action( 'manage_pages_custom_column', array( Admin::class, 'action_manage_posts_custom_column' ), 10, 2 );
		add_action( 'quick_edit_custom_box', array( Admin::class, 'action_quick_edit_custom_box' ) );
		add_filter( 'hidden_columns', array( Admin::class, 'filter_hidden_columns' ) );
		add_action( 'admin_enqueue_scripts', array( Assets::class, 'action_admin_enqueue_scripts' ) );
		add_action( 'wp_enqueue_editor', array( Assets::class, 'action_wp_enqueue_editor' ) );
		add_action( 'enqueue_block_editor_assets', array( Assets::class, 'action_enqueue_block_editor_assets' ) );
		add_action( 'admin_notices', array( Editor::class, 'action_admin_notices' ) );
		add_action( 'after_wp_tiny_mce', array( Editor::class, 'action_after_wp_tiny_mce' ) );
		add_action( 'update_option_' . self::LICENSE_KEY_OPTION, array( Admin::class, 'action_update_option_clear_transient' ) );
		add_action( 'media_buttons', array( Editor::class, 'action_media_buttons' ) );
		add_action( 'wp_ajax_tasty_recipes_preview_recipe_card', array( Admin::class, 'handle_wp_ajax_preview_recipe_card' ) );
		add_action( 'wp_ajax_tasty_recipes_get_count', array( Admin::class, 'handle_wp_ajax_get_count' ) );
		add_action( 'wp_ajax_tasty_recipes_convert', array( Admin::class, 'handle_wp_ajax_convert' ) );
		add_action( 'wp_ajax_tasty_recipes_ignore_convert', array( Editor::class, 'handle_wp_ajax_ignore_convert' ) );
		add_action( 'wp_ajax_tasty_recipes_convert_recipe', array( Editor::class, 'handle_wp_ajax_convert_recipe' ) );
		add_action( 'wp_ajax_tasty_recipes_ignore_type_convert', array( Editor::class, 'handle_wp_ajax_ignore_type_convert' ) );
		add_action( 'wp_ajax_tasty_recipes_revert_ignore_type_convert', array( Editor::class, 'handle_wp_ajax_revert_ignore_type_convert' ) );
		add_action( 'wp_ajax_tasty_recipes_parse_shortcode', array( Editor::class, 'handle_wp_ajax_parse_shortcode' ) );
		add_action( 'wp_ajax_tasty_recipes_modify_recipe', array( Editor::class, 'handle_wp_ajax_modify_recipe' ) );

		add_action( 'wp_insert_post', array( Editor::class, 'handle_add_new_post' ), 10, 3 );
		add_action( 'post_updated', array( Editor::class, 'handle_edit_post' ), 10, 3 );
		add_action( 'tasty_framework_admin_footer_after', array( Admin::class, 'add_lite_upgrade_modal' ) );

		if ( is_admin() ) {
			MetaBox::get_instance();
		}
	}

	/**
	 * Registry of filters used in the plugin.
	 */
	private function setup_filters() {
		global $wp_embed;

		// Bootstrap.
		add_filter( 'rewrite_rules_array', array( ContentModel::class, 'filter_rewrite_rules_array' ) );

		add_filter( 'get_the_excerpt', array( Shortcodes::class, 'filter_get_the_excerpt_early' ), 1 );
		add_filter( 'get_the_excerpt', array( Shortcodes::class, 'filter_get_the_excerpt_late' ), 1000 );
		add_filter( 'the_content', array( Shortcodes::class, 'filter_the_content_late' ), 100 );
		add_filter( 'tasty_recipes_recipe_card_output', array( Shortcodes::class, 'filter_tasty_recipes_recipe_card_output' ), 10, 2 );

		// WordPress' standard text formatting filters.
		add_filter( 'tasty_recipes_the_title', 'wptexturize' );
		add_filter( 'tasty_recipes_the_title', 'convert_chars' );
		add_filter( 'tasty_recipes_the_title', 'trim' );
		add_filter( 'tasty_recipes_the_content', array( $wp_embed, 'autoembed' ), 8 );
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

		add_filter( 'tasty_recipes_the_content', array( Shortcodes::class, 'add_inside_ingredients' ), 10, 2 );
		add_filter( 'tasty_recipes_recipe_card_output', array( Shortcodes::class, 'inside_ingredients_handler' ), 10, 3 );
		add_action( 'tasty_recipes_card_after_ingredients', array( Shortcodes::class, 'run_after_ingredients' ) );

		// Plugin-specific filters.
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( Admin::class, 'filter_plugin_action_links' ) );
		add_filter( 'teeny_mce_buttons', array( Admin::class, 'filter_teeny_mce_buttons' ), 10, 2 );
		add_filter( 'teeny_mce_buttons', array( Assets::class, 'filter_teeny_mce_buttons' ), 10, 2 );
		add_filter( 'teeny_mce_before_init', array( Assets::class, 'filter_teeny_mce_before_init' ), 10, 2 );

		add_filter( 'update_post_metadata', array( ContentModel::class, 'filter_update_post_metadata_thumbnail_id' ), 10, 4 );
		add_filter( 'template_include', array( ContentModel::class, 'filter_template_include' ), 1000 );
		add_filter( 'allow_empty_comment', array( Ratings::class, 'filter_allow_rating_empty_comment' ), 10, 2 );
		add_filter( 'preprocess_comment', array( Ratings::class, 'filter_preprocess_comment' ), 0 );
		add_filter( 'comment_form_field_comment', array( Ratings::class, 'filter_comment_form_field_comment' ) );
		add_filter( 'comment_text', array( Ratings::class, 'filter_comment_text' ), 10, 2 );
		add_filter( 'tasty_recipes_setting_default', array( Admin::class, 'filter_default_card_buttons_setting' ) );

		if ( is_admin() ) {
			add_filter( 'tasty_framework_admin_menu_items', array( Admin::class, 'add_menu_item' ) );
		}

		// Integrations.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'tasty-recipes', 'Tasty_Recipes\CLI' );
		}

		add_action( 'elementor/init', array( Elementor::class, 'register_hooks' ) );

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

		Akismet::instance();
	}

	/**
	 * Actions to perform when activating the plugin.
	 */
	public function plugin_activation() {
		$this->require_files();
		ContentModel::action_init_register_rewrite_rules();
		flush_rewrite_rules();
		update_option( self::PLUGIN_ACTIVATION_OPTION, true );
	}

	/**
	 * Determine whether there's a recipe present in the post.
	 *
	 * @param integer $post_id ID for the post to inspect.
	 * @return boolean
	 */
	public static function has_recipe( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}
		if ( false !== stripos( $post->post_content, '[' . Shortcodes::RECIPE_SHORTCODE ) ) {
			return true;
		}
		if ( false !== stripos( $post->post_content, '<!-- wp:' . BlockEditor::RECIPE_BLOCK_TYPE . ' ' ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Get the Nutrifox url. If it's missing https, add it.
	 *
	 * @since 1.0.0
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
	 * @param integer $post_id ID for the post to parse.
	 * @param array   $options Any options to configure the bheavior.
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
	 * @return array
	 */
	public static function get_recipe_ids_from_content( $content, $options = array() ) {

		$defaults = array(
			'disable-json-ld' => null,
			'full-result'     => false,
		);
		$options  = array_merge( $defaults, $options );

		$recipes = array();
		if ( preg_match_all( '#\[' . Shortcodes::RECIPE_SHORTCODE . '(.+)\]#Us', $content, $matches ) ) {
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
	 * @param array $recipes Array of recipe ids.
	 */
	public static function recursively_search_blocks( $blocks, $options, &$recipes ) {
		foreach ( $blocks as $block ) {
			if ( is_object( $block ) && ! empty( $block->blockName )
				&& Tasty_Recipes\Block_Editor::RECIPE_BLOCK_TYPE === $block->blockName ) {
				$disable_json = isset( $block->attrs->disableJSON ) ? (bool) $block->attrs->disableJSON : false;
				if ( false === $options['disable-json-ld'] && $disable_json ) {
					continue;
				}
				if ( ! empty( $block->attrs->id ) ) {
					if ( ! empty( $options['full-result'] ) ) {
						$recipes[] = (array) $block->attrs;
					} else {
						$recipes[] = (int) $block->attrs->id;
					}
				}
			}
			if ( is_array( $block ) && ! empty( $block['blockName'] )
				&& BlockEditor::RECIPE_BLOCK_TYPE === $block['blockName'] ) {
				$disable_json = isset( $block['attrs']['disableJSON'] ) ? (bool) $block['attrs']['disableJSON'] : false;
				if ( false === $options['disable-json-ld'] && $disable_json ) {
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
				'class' => Cookbook::class,
				'label' => 'Cookbook',
			),
			'create'         => array(
				'class' => MediavineCreate::class,
				'label' => 'Mediavine Create',
			),
			'easyrecipe'     => array(
				'class' => EasyRecipe::class,
				'label' => 'EasyRecipe',
			),
			'mealplannerpro' => array(
				'class' => MealPlannerPro::class,
				'label' => 'Meal Planner Pro',
			),
			'srp'            => array(
				'class' => SimpleRecipePro::class,
				'label' => 'Simple Recipe Pro',
			),
			'wpcom'          => array(
				'class' => WPCom::class,
				'label' => 'WordPress.com',
			),
			'wprm'           => array(
				'class' => WPRecipeMaker::class,
				'label' => 'WP Recipe Maker',
			),
			'wpur'           => array(
				'class' => WPUltimateRecipe::class,
				'label' => 'WP Ultimate Recipe',
			),
			'yummly'         => array(
				'class' => Yummly::class,
				'label' => 'Yummly',
			),
			'yumprint'       => array(
				'class' => YumPrint::class,
				'label' => 'YumPrint Recipe Card',
			),
			'ziplist'        => array(
				'class' => ZipList::class,
				'label' => 'ZipList (or Zip Recipes)',
			),
		);
	}

	/**
	 * Get the recipes embedded within a given post.
	 *
	 * @param integer $post_id ID for the post to inspect.
	 * @param array   $options Any options to pass through to the parser.
	 * @return array
	 */
	public static function get_recipes_for_post( $post_id, $options = array() ) {
		$recipes = array();
		foreach ( self::get_recipe_ids_for_post( $post_id, $options ) as $id ) {
			$recipe = Recipe::get_by_id( $id );
			if ( $recipe ) {
				$recipes[] = $recipe;
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
		$default_description = '<p>' . esc_html__( "Share a photo and tag us — we can't wait to see what you've made!", 'tasty-recipes-lite' ) . '</p>';
		$instagram_handle    = get_option( self::INSTAGRAM_HANDLE_OPTION );
		if ( $instagram_handle ) {
			$default_description = '<p>' . esc_html__( 'Tag', 'tasty-recipes-lite' ) . ' <a href="' . esc_url( 'https://www.instagram.com/' . $instagram_handle ) . '" target="_blank" rel="noreferrer noopener">@' . esc_html( $instagram_handle ) . '</a> ' . esc_html__( 'on Instagram', 'tasty-recipes-lite' );
			$instagram_hashtag   = get_option( self::INSTAGRAM_HASHTAG_OPTION );
			if ( $instagram_hashtag ) {
				$default_description .= ' ' . esc_html__( 'and hashtag it', 'tasty-recipes-lite' ) . ' <a href="' . esc_url( 'https://www.instagram.com/explore/tags/' . $instagram_hashtag ) . '" target="_blank" rel="noreferrer noopener">#' . esc_html( $instagram_hashtag ) . '</a>';
			}
			$default_description .= '</p>';
		}

		$settings = array_merge(
			array(
				'primary_color'            => '',
				'secondary_color'          => '',
				'icon_color'               => '',
				'button_color'             => '',
				'detail_label_color'       => '',
				'detail_value_color'       => '',
				'h2_color'                 => '',
				'h2_transform'             => '',
				'h3_color'                 => '',
				'h3_transform'             => '',
				'body_color'               => '',
				'star_ratings_style'       => 'solid',
				'star_color'               => '',
				'nutrifox_display_style'   => 'label',
				'footer_social_platform'   => 'instagram',
				'footer_icon_color'        => '',
				'footer_heading'           => esc_html__( 'Did you make this recipe?', 'tasty-recipes-lite' ),
				'footer_heading_color'     => '',
				'footer_description'       => $default_description,
				'footer_description_color' => '',
			),
			get_option( self::CUSTOMIZATION_OPTION, array() )
		);

		$settings['footer_description'] = htmlspecialchars_decode( html_entity_decode( $settings['footer_description'], ENT_QUOTES ), ENT_QUOTES );
		return $settings;
	}

	/**
	 * Get filtered customization settings.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function get_filtered_customization_settings() {
		/**
		 * Allow the customization settings to be modified based on context.
		 *
		 * @var array $settings Customization settings.
		 */
		return (array) apply_filters( 'tasty_recipes_customization_settings', self::get_customization_settings() );
	}

	/**
	 * Gets the card button settings.
	 *
	 * @param string $template Name of the template.
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
	 * @return string
	 */
	public static function get_template_path( $template ) {
		$full_path = dirname( __FILE__ ) . '/templates/' . $template . '.php';
		return apply_filters( 'tasty_recipes_template_path', $full_path, $template );
	}

	/**
	 * Get a rendered template.
	 *
	 * @param string $template Fully-qualified template path.
	 * @param array  $vars     Variables to pass into the template
	 * you can pass here remove_new_lines with true to remove new lines from the template when rendered.
	 *
	 * @return string
	 */
	public static function get_template_part( $template, $vars = array() ) {
		$full_path = self::get_template_path( $template );
		// Provided template may already be a full path.
		if ( ! file_exists( $full_path ) ) {
			$full_path = $template;
		}
		if ( ! file_exists( $full_path ) ) {
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
		include $full_path;
		$contents = ob_get_clean();
		if ( ! $remove_new_lines ) {
			return $contents;
		}
		return str_replace( array( "\r\n", "\r", "\n" ), '', $contents );
	}

	/**
	 * Echo a rendered template.
	 *
	 * @param string $template Template to render.
	 * @param array  $vars     Variables to pass to the template.
	 * @return void
	 */
	public static function echo_template_part( $template, $vars = array() ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo self::get_template_part( $template, $vars );
	}


}

// Load Tasty framework.
require_once implode(
	DIRECTORY_SEPARATOR,
	array(
		dirname( TASTY_RECIPES_LITE_PLUGIN_FILE ),
		'inc',
		'libs',
		'tasty-framework',
		'tasty-framework.php',
	)
);

/**
 * Access the Tasty Recipes instance.
 *
 * @return TastyRecipesLiteMain
 */
// @codingStandardsIgnoreStart
function Tasty_Recipes_Lite() {
// @codingStandardsIgnoreEnd
	return TastyRecipesLiteMain::get_instance();
}
add_action( 'plugins_loaded', 'Tasty_Recipes_Lite' );

/**
 * Register the plugin activation hook
 */
register_activation_hook( __FILE__, array( Tasty_Recipes_Lite(), 'plugin_activation' ) );
