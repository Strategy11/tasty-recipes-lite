<?php
/**
 * Manages interactions with the admin.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes;

use Tasty\Framework\Utils\Url;
use Tasty\Framework\Admin\License\Updater;
use Tasty_Recipes\Settings;
use Tasty_Recipes;

/**
 * Manages interactions with the admin.
 */
class Admin {

	/**
	 * Capability required to manage settings.
	 *
	 * @var string
	 */
	const CAPABILITY = 'manage_options';

	/**
	 * Plugin name for EDD registration.
	 *
	 * @var string
	 */
	const ITEM_AUTHOR = 'Tasty Recipes';

	/**
	 * Plugin author for EDD registration.
	 *
	 * @var string
	 */
	const ITEM_NAME = 'Tasty Recipes';

	/**
	 * Cache key used to store license check data.
	 *
	 * @var string
	 */
	const LICENSE_CHECK_CACHE_KEY = 'tasty-recipes-license-check';

	/**
	 * Key used for nonce authentication.
	 *
	 * @var string
	 */
	const NONCE_KEY = 'tasty-recipes-settings';

	/**
	 * URL to the plugin store.
	 *
	 * @var string
	 */
	const STORE_URL = 'https://www.wptasty.com';

	/**
	 * Parent page for the settings page.
	 *
	 * @var string
	 */
	const PAGE_BASE = 'options-general.php';

	/**
	 * Slug for the settings page.
	 *
	 * @deprecated x.x
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'tasty-recipes';

	/**
	 * Group used for authentication settings.
	 *
	 * @deprecated x.x
	 *
	 * @var string
	 */
	const SETTINGS_GROUP_LICENSE = '';

	/**
	 * Section used for authentication settings.
	 *
	 * @deprecated x.x
	 *
	 * @var string
	 */
	const SETTINGS_SECTION_LICENSE = '';

	/**
	 * Group used for card settings.
	 *
	 * @deprecated x.x
	 *
	 * @var string
	 */
	const SETTINGS_GROUP_CARD = '';

	/**
	 * Section used for card settings.
	 *
	 * @deprecated x.x
	 *
	 * @var string
	 */
	const SETTINGS_SECTION_CARD_DESIGN = '';

	/**
	 * Available template options.
	 *
	 * @deprecated x.x
	 *
	 * @var array
	 */
	const TEMPLATE_OPTIONS = array(
		''               => 'Default',
		'bold'           => 'Bold',
		'snap'           => 'Snap',
		'fresh'          => 'Fresh',
		'simple'         => 'Simple',
		'modern-compact' => 'Modern Compact',
		'elegant'        => 'Elegant',
	);

	/**
	 * ID used for TinyMCE instance in settings.
	 *
	 * @var string
	 */
	private static $editor_id = 'tasty-recipes-settings';

	/**
	 * Registers the updater object.
	 *
	 * @return void
	 */
	public static function action_admin_init() {
		new Updater(
			self::STORE_URL,
			TASTY_RECIPES_PLUGIN_FILE,
			array(
				'version'   => TASTY_RECIPES_PLUGIN_VERSION,
				'license'   => get_option( Tasty_Recipes::LICENSE_KEY_OPTION ),
				'item_name' => self::ITEM_NAME,
				'author'    => self::ITEM_AUTHOR,
			)
		);
		if ( get_option( Tasty_Recipes::PLUGIN_ACTIVATION_OPTION, false ) ) {
			delete_option( Tasty_Recipes::PLUGIN_ACTIVATION_OPTION );
			if ( empty( Utils::get_param( 'activate-multi' ) ) ) {
				wp_safe_redirect( add_query_arg( 'tab', 'about', menu_page_url( Settings::PAGE_SLUG, false ) ) );
				exit;
			}
		}
	}

	/**
	 * Includes PHP and plugin versions in the user agent for update checks.
	 *
	 * @param array  $r   An array of HTTP request arguments.
	 * @param string $url The request URL.
	 *
	 * @return array
	 */
	public static function filter_http_request_args( $r, $url ) {
		if ( self::STORE_URL !== $url
			|| 'POST' !== $r['method']
			|| empty( $r['body'] )
			|| ! is_array( $r['body'] )
			|| empty( $r['body']['item_name'] )
			|| self::ITEM_NAME !== $r['body']['item_name'] ) {
			return $r;
		}

		$r['user-agent'] = rtrim( $r['user-agent'], ';' )
			. '; PHP/' . PHP_VERSION . '; '
			. self::ITEM_NAME . '/' . TASTY_RECIPES_PLUGIN_VERSION;
		return $r;
	}

	/**
	 * Registers used settings.
	 *
	 * @return void
	 */
	public static function action_admin_menu() { // phpcs:ignore SlevomatCodingStandard
		Settings::register_settings();
	}

	/**
	 * Renders the admin notice nag when license key isn't set or invalid.
	 *
	 * @deprecated 3.10
	 *
	 * @return void
	 */
	public static function action_admin_notices_license_key() {
		_deprecated_function( __METHOD__, '3.10' );
	}

	/**
	 * Add the callback for the plugin menu item.
	 *
	 * @param array $menu_items Admin menu items.
	 *
	 * @return array
	 */
	public static function add_menu_item( $menu_items ) {
		__deprecated_function( __METHOD__, 'x.x', 'Settings::add_menu_item' );
		return Settings::add_menu_item( $menu_items );
	}

	/**
	 * Registers a new custom column for Tasty Recipes.
	 *
	 * @param array $columns Existing column names.
	 *
	 * @return array
	 */
	public static function action_manage_posts_columns( $columns ) {
		$columns['tasty_recipe'] = esc_html__( 'Tasty Recipe', 'tasty-recipes' );
		return $columns;
	}

	/**
	 * Renders an 'Edit Tasty Recipe' button for the column if the post has a recipe.
	 *
	 * @param string $column_name Name of the column.
	 * @param int    $post_id     ID of the post being displayed.
	 *
	 * @return void
	 */
	public static function action_manage_posts_custom_column( $column_name, $post_id ) {
		switch ( $column_name ) {
			case 'tasty_recipe':
				$recipe_ids = Tasty_Recipes::get_recipe_ids_for_post( $post_id );
				if ( ! empty( $recipe_ids ) ) {
					$recipe_id = array_shift( $recipe_ids );
					echo '<button class="button tasty-recipes-edit-button" data-recipe-id="' . esc_attr( $recipe_id ) . '">' .
						esc_html__( 'Edit Tasty Recipe', 'tasty-recipes' ) .
						'</button>';
				}
				break;
		}
	}

	/**
	 * Renders the 'Edit Tasty Recipe' button on Quick Edit.
	 *
	 * @param string $column_name Name of the column.
	 *
	 * @return void
	 */
	public static function action_quick_edit_custom_box( $column_name ) {
		switch ( $column_name ) {
			case 'tasty_recipe':
				echo '<button class="button tasty-recipes-edit-button" data-recipe-id="">' . esc_html__( 'Edit Tasty Recipe', 'tasty-recipes' ) . '</button>';
				break;
		}
	}

	/**
	 * Hides the 'Tasty Recipe' column by default.
	 *
	 * @param array $columns Existing default hidden columns.
	 *
	 * @return array
	 */
	public static function filter_hidden_columns( $columns ) {
		$columns[] = 'tasty_recipe';
		return $columns;
	}

	/**
	 * Activates the license when the option is updated.
	 *
	 * @deprecated 3.10
	 *
	 * @return void
	 */
	public static function action_update_option_register_license() {
		_deprecated_function( __METHOD__, '3.10' );
	}

	/**
	 * Clears the license and version check cache when license key is updated.
	 *
	 * @return void
	 */
	public static function action_update_option_clear_transient() {
		delete_transient( self::LICENSE_CHECK_CACHE_KEY );
		$cache_key = md5( 'edd_plugin_' . sanitize_key( plugin_basename( TASTY_RECIPES_PLUGIN_FILE ) ) . '_version_info' );
		delete_site_transient( $cache_key );
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
		$cache_key = 'edd_api_request_' . substr( md5( serialize( basename( TASTY_RECIPES_PLUGIN_FILE, '.php' ) ) ), 0, 15 );
		delete_site_transient( $cache_key );
		delete_site_transient( 'update_plugins' );
	}

	/**
	 * Adds 'Settings' and 'Remove license' links to plugin settings.
	 *
	 * @param array $links Existing plugin action links.
	 *
	 * @return array
	 */
	public static function filter_plugin_action_links( $links ) {
		$links['tr_settings']        = '<a href="' . esc_url( add_query_arg( 'tab', 'settings', menu_page_url( Settings::PAGE_SLUG, false ) ) ) . '">' .
			esc_html__( 'Settings', 'tasty-recipes' ) .
			'</a>';
		$links['tr_license_manager'] = '<a href="' . esc_url( Url::get_main_admin_url() ) . '">' . esc_html__( 'License manager', 'tasty-recipes' ) . '</a>';
		return $links;
	}

	/**
	 * Handles a request to remove the license key.
	 *
	 * @deprecated 3.10
	 *
	 * @return void
	 */
	public static function handle_wp_ajax_remove_license_key() {
		_deprecated_function( __METHOD__, '3.10' );

		wp_safe_redirect( admin_url( 'plugins.php' ) );
		exit;
	}

	/**
	 * Handles an AJAX request to preview a recipe template.
	 *
	 * @return void
	 */
	public static function handle_wp_ajax_preview_recipe_card() { // phpcs:ignore SlevomatCodingStandard
		global $editor_styles;
		$nonce = Utils::request_param( 'nonce', 'sanitize_key' );
		if ( empty( $nonce ) || ! current_user_can( self::CAPABILITY ) || ! wp_verify_nonce( $nonce, self::NONCE_KEY ) ) {
			wp_die( esc_html__( "Sorry, you don't have permission to do this.", 'tasty-recipes' ) );
		}
		header( 'Content-Type: text/html' );
		$wrapper_classes = array(
			'tasty-recipes',
			'tasty-recipes-display',
			'tasty-recipes-has-image',
		);

		$shareasale = get_option( Tasty_Recipes::SHAREASALE_OPTION );
		if ( $shareasale ) {
			$wrapper_classes[] = 'tasty-recipes-has-plug';
		}

		$get_template               = Utils::get_param( 'template', 'sanitize_key' );
		$get_star_ratings_style     = Utils::get_param( 'star_ratings_style', 'sanitize_key' );
		$get_nutrifox_display_style = Utils::get_param( 'nutrifox_display_style', 'sanitize_key' );
		$get_footer_social_platform = Utils::get_param( 'footer_social_platform', 'sanitize_key' );

		$custom_design          = ! empty( $get_template ) && array_key_exists( $get_template, self::TEMPLATE_OPTIONS ) ? $get_template : '';
		$star_ratings_style     = ! empty( $get_star_ratings_style ) && in_array( $get_star_ratings_style, array( 'solid', 'outline' ), true ) ? $get_star_ratings_style : null;
		$show_nutrifox          = ! empty( $get_nutrifox_display_style ) && in_array( $get_nutrifox_display_style, array( 'label', 'card' ), true );
		$nutrifox_display_style = $show_nutrifox ? $get_nutrifox_display_style : null;
		$show_socials           = ! empty( $get_footer_social_platform ) && in_array( $get_footer_social_platform, array( 'instagram', 'pinterest', 'facebook' ), true );
		$footer_social_platform = $show_socials ? $get_footer_social_platform : '';

		$settings = Tasty_Recipes::get_filtered_customization_settings();

		$styles  = Shortcodes::get_styles_as_string( $custom_design );
		$styles .= Utils::get_contents( dirname( TASTY_RECIPES_PLUGIN_FILE ) . '/assets/dist/ratings.css' );
		$styles .= Utils::get_contents( dirname( TASTY_RECIPES_PLUGIN_FILE ) . '/assets/dist/recipe-card-preview.css' );

		$custom_path = dirname( TASTY_RECIPES_PLUGIN_FILE ) . '/templates/designs/' . $custom_design . '/tasty-recipes.php';
		if ( $custom_design && file_exists( $custom_path ) ) {
			$template = $custom_path;
		} else {
			$template = 'recipe/tasty-recipes';
		}

		$stylesheet = get_option( 'stylesheet' );
		// Clean up Feast directory names.
		$stylesheet  = preg_replace( '#-v[\d]+$#', '', $stylesheet );
		$compat_file = dirname( TASTY_RECIPES_PLUGIN_FILE ) . '/assets/css/theme-compat-card-previews/' . $stylesheet . '.css';
		if ( file_exists( $compat_file ) ) {
			$styles .= Utils::get_contents( $compat_file );
		}

		$recipe      = new \stdClass();
		$recipe_json = array();
		$ingredients = <<<EOT
<ul>
	<li><span data-amount="3/4" data-unit="cup">3/4 cup</span> melted coconut oil</li>
	<li><span data-amount="1/2" data-unit="cup">1/2 cup</span> cocoa powder</li>
	<li><span data-amount="2" data-unit="tablespoon">2 tbsp.</span> natural sweetener (agave or maple syrup)</li>
	<li><span data-amount="3/4" data-unit="cup">3/4 cup</span> almond butter</li>
	<li><span data-amount="" data-unit=""></span>pinch of sea salt, plus more for topping</li>
</ul>
EOT;
		$ingredients = apply_filters( 'tasty_recipes_the_content', $ingredients, 'ingredients' );

		// phpcs:disable SlevomatCodingStandard.Files.LineLength.LineTooLong
		$instructions = <<<EOT
<ol>
<li id="instruction-step-1">Whisk the coconut oil, cocoa powder, sweetener, and a pinch of salt.</li>
<li id="instruction-step-2">Fill a regular size muffin tin with paper liners. Pour a small amount of the cocoa mixture (1-2 tablespoons) into the paper cups. Drop a small spoonful of the almond butter (2-3 teaspoons) into the center of each cup. Divide remaining chocolate amongst the cups.</li>
<li id="instruction-step-3">If almond butter is sticking up, just gently press it down so each cup has a smooth top layer. Sprinkle each almond butter cup with a pinch of coarse sea salt. Freeze for one hour or until solid. YUM TOWN.</li>
</ol>
EOT;
		$notes        = <<<EOT
<p>These are very adaptable – I’ve used as much as 3/4 cup cocoa, and as little as 2 tablespoons agave. It just depends on how sweet / dark you want them to be. I’ve also used peanut butter which is (obviously) delicious!</p>
EOT;
		// phpcs:enable

		if ( 'card' === $nutrifox_display_style ) {
			$nutrifox_id           = '';
			$recipe_nutrition      = array(
				'serving_size' =>
				array(
					'label' => 'Serving Size',
					'value' => '<span data-tasty-recipes-customization="body-color.color" class="tasty-recipes-serving-size">12</span>',
				),
				'calories'     =>
				array(
					'label' => 'Calories',
					'value' => '<span data-tasty-recipes-customization="body-color.color" class="tasty-recipes-calories">238</span>',
				),
				'sugar'        =>
				array(
					'label' => 'Sugar',
					'value' => '<span data-tasty-recipes-customization="body-color.color" class="tasty-recipes-sugar">3.8g</span>',
				),
				'sodium'       =>
				array(
					'label' => 'Sodium',
					'value' => '<span data-tasty-recipes-customization="body-color.color" class="tasty-recipes-sodium">99.3mg</span>',
				),
				'fat'          =>
				array(
					'label' => 'Fat',
					'value' => '<span data-tasty-recipes-customization="body-color.color" class="tasty-recipes-fat">22.6g</span>',
				),
				'fiber'        =>
				array(
					'label' => 'Fiber',
					'value' => '<span data-tasty-recipes-customization="body-color.color" class="tasty-recipes-fiber">2.7g</span>',
				),
				'protein'      =>
				array(
					'label' => 'Protein',
					'value' => '<span data-tasty-recipes-customization="body-color.color" class="tasty-recipes-protein">3.9g</span>',
				),
			);
			$recipe_nutrifox_embed = '';
		} else {
			$recipe_nutrition       = array();
			$nutrifox_id            = 26460;
			$nutrifox_iframe_url    = sprintf(
				'%s/embed/label/%d',
				Tasty_Recipes::nutrifox_url(),
				$nutrifox_id
			);
			$nutrifox_resize_script = Utils::get_contents( dirname( __DIR__ ) . '/assets/js/nutrifox-resize.js' );

			$recipe_nutrifox_embed = <<<EOT
<script data-cfasync="false">
{$nutrifox_resize_script}
</script>
<iframe title="nutritional information" id="nutrifox-label-{$nutrifox_id}" src="{$nutrifox_iframe_url}" style="width:100%;border-width:0;"></iframe>
EOT;
		}

		$template_vars = array(
			'recipe'                        => $recipe,
			'recipe_styles'                 => '',
			'recipe_scripts'                => '',
			'recipe_json'                   => $recipe_json,
			'recipe_title'                  => 'Almond Butter Cups',
			'recipe_image'                  => '<img width="183" height="183" ' .
				'src="' . esc_url( plugins_url( 'assets/images/Almond-Butter-Cups-Recipe-370x370.jpg', __DIR__ ) ) . '" ' .
				'class="attachment-thumbnail size-thumbnail" alt="Chocolate Chip Cookies on parchment paper." ' .
				'loading="lazy" data-pin-nopin="true">',
			'recipe_rating_icons'           => Ratings::get_rendered_rating(
				4.6,
				'star-color.color',
				$star_ratings_style
			),
			'recipe_rating_label'           => '<span data-tasty-recipes-customization="detail-label-color.color" class="rating-label">' .
				'<span class="average">4.6</span> from <span class="count">1376</span> reviews</span>',
			'recipe_author_name'            => '',
			'recipe_details'                => array(
				'author'     =>
				array(
					'label' => 'Author',
					'value' => '<a data-tasty-recipes-customization="detail-value-color.color" class="tasty-recipes-author-name" ' .
						'href="https://pinchofyum.com/about">Pinch of Yum</a>',
					'class' => 'author',
				),
				'prep_time'  =>
				array(
					'label' => 'Prep Time',
					'value' => '<span data-tasty-recipes-customization="detail-value-color.color" class="tasty-recipes-prep-time">10 mins</span>',
					'class' => 'prep-time',
				),
				'cook_time'  =>
				array(
					'label' => 'Cook Time',
					'value' => '<span data-tasty-recipes-customization="detail-value-color.color" class="tasty-recipes-cook-time">1 hour (for freezing)</span>',
					'class' => 'cook-time',
				),
				'total_time' =>
				array(
					'label' => 'Total Time',
					'value' => '<span data-tasty-recipes-customization="detail-value-color.color" class="tasty-recipes-total-time">1 hour, 10 minutes</span>',
					'class' => 'total-time',
				),
				'yield'      =>
				array(
					'label' => 'Yield',
					'value' => '<span data-tasty-recipes-customization="detail-value-color.color" class="tasty-recipes-yield">' .
						'<span data-amount="12">12</span> almond butter cups' .
						'<span class="tasty-recipes-yield-scale"><span data-amount="1">1</span>x</span></span>',
					'class' => 'yield',
				),
				'category'   =>
				array(
					'label' => 'Category',
					'value' => '<span data-tasty-recipes-customization="detail-value-color.color" class="tasty-recipes-category">Dessert</span>',
					'class' => 'category',
				),
				'method'     =>
				array(
					'label' => 'Method',
					'value' => '<span data-tasty-recipes-customization="detail-value-color.color" class="tasty-recipes-method">No bake</span>',
					'class' => 'method',
				),
				'cuisine'    =>
				array(
					'label' => 'Cuisine',
					'value' => '<span data-tasty-recipes-customization="detail-value-color.color" class="tasty-recipes-cuisine">American</span>',
					'class' => 'cuisine',
				),
			),
			'recipe_description'            => '<p>Almond Butter Cups: made with five ingredients and no refined sugar. So creamy, rich, and yummy!</p>',
			'recipe_ingredients'            => $ingredients,
			'recipe_instructions_has_video' => false,
			'recipe_scalable'               => '<button class="tasty-recipes-scale-button tasty-recipes-scale-button-active" data-amount="1">1x</button>' .
				'<button class="tasty-recipes-scale-button" data-amount="2">2x</button>' .
				'<button class="tasty-recipes-scale-button" data-amount="3">3x</button>',
			'recipe_instructions'           => $instructions,
			'recipe_keywords'               => '',
			'recipe_notes'                  => $notes,
			'recipe_nutrifox_id'            => $nutrifox_id,
			'recipe_nutrifox_embed'         => $recipe_nutrifox_embed,
			'recipe_video_embed'            => '',
			'recipe_nutrition'              => $recipe_nutrition,
			'recipe_hidden_nutrition'       => array(),
			'copy_ingredients'              => get_option( Tasty_Recipes::COPY_TO_CLIPBOARD_OPTION )
				? '<button aria-label="' . esc_attr__( 'Copy ingredients to clipboard', 'tasty-recipes' ) . '" ' .
				'class="tasty-recipes-copy-button" id="copy-ingredients" data-text="' . esc_attr__( 'Copy ingredients', 'tasty-recipes' ) . '">' .
				Utils::get_contents( dirname( __DIR__ ) . '/assets/images/icons/icon-copy-to-clipboard.svg' ) .
				'</button>'
				: false,
			'first_button'                  => Shortcodes::get_card_button( $recipe, 'first', $custom_design ),
			'second_button'                 => Shortcodes::get_card_button( $recipe, 'second', $custom_design ),
			'instagram_handle'              => get_option( Tasty_Recipes::INSTAGRAM_HANDLE_OPTION ),
			'instagram_hashtag'             => get_option( Tasty_Recipes::INSTAGRAM_HASHTAG_OPTION ),
			'footer_social_platform'        => $footer_social_platform,
			'footer_heading'                => $settings['footer_heading'],
			'footer_description'            => $settings['footer_description'],
		);

		$stylesheets = '';
		if ( $editor_styles && current_theme_supports( 'editor-styles' ) ) {
			foreach ( $editor_styles as $style ) {
				if ( preg_match( '~^(https?:)?//~', $style ) ) {
					// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
					$stylesheets .= '<link rel="stylesheet" href="' . esc_url( $style ) . '">' . PHP_EOL;
				} else {
					 // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
					$stylesheets .= '<link rel="stylesheet" href="' . esc_url( get_theme_file_uri( $style ) ) . '">' . PHP_EOL;
				}
			}
		}

		$template_vars['recipe_styles'] = '<style>' . $styles . PHP_EOL . '</style>' . PHP_EOL;

		$ret            = '<!DOCTYPE html><html><head>' .
			$stylesheets . self::load_enqueued_assets() .
			'</head><body>';
		$ret           .= '<div class="' . esc_attr( implode( ' ', $wrapper_classes ) ) . '"';
		$customizations = Shortcodes::get_card_container_customizations( $custom_design );
		if ( $customizations ) {
			$ret .= ' data-tasty-recipes-customization="' . esc_attr( $customizations ) . '"';
		}
		$ret .= '>' . PHP_EOL;
		$ret .= Tasty_Recipes::get_template_part( $template, $template_vars );
		$ret .= '</div>';
		$ret .= '<script>' . PHP_EOL . Utils::get_contents( dirname( __DIR__ ) . '/assets/dist/settings-recipe-card-preview.build.js' ) . PHP_EOL . '</script>';
		$ret .= '</body></html>';
		echo apply_filters( 'tasty_recipes_recipe_card_output', $ret, 'admin', compact( 'recipe' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Get the html for enqueued assets for the recipe card preview when doing ajax.
	 *
	 * @since 3.12.3
	 *
	 * @return string
	 */
	private static function load_enqueued_assets() {
		ob_start();
		wp_print_styles();
		wp_print_scripts();
		$assets = ob_get_clean();
		if ( ! $assets ) {
			$assets = '';
		}

		return $assets;
	}

	/**
	 * Handles an AJAX request to get the number of matching recipes to convert.
	 *
	 * @return void
	 */
	public static function handle_wp_ajax_get_count() {
		$nonce = Utils::get_param( 'nonce', 'sanitize_key' );
		if ( empty( $nonce ) || ! current_user_can( self::CAPABILITY ) || ! wp_verify_nonce( $nonce, self::NONCE_KEY ) ) {
			Utils::send_json_error( __( 'Sorry, you don\'t have permission to do this.', 'tasty-recipes' ) );
		}

		$class      = false;
		$converters = Tasty_Recipes::get_converters();
		$get_type   = Utils::get_param( 'type' );

		if ( ! empty( $get_type ) && isset( $converters[ $get_type ] ) ) {
			$class = $converters[ $get_type ]['class'];
		} else {
			Utils::send_json_error( __( 'Couldn\'t count recipes. Please contact support for assistance.', 'tasty-recipes' ) );
		}

		wp_send_json_success(
			array(
				'count' => (int) $class::get_count(),
			)
		);
	}

	/**
	 * Handles an AJAX request to batch convert some recipes.
	 *
	 * @return void
	 */
	public static function handle_wp_ajax_convert() {
		$nonce = Utils::get_param( 'nonce', 'sanitize_key' );
		if ( empty( $nonce ) || ! current_user_can( self::CAPABILITY ) || ! wp_verify_nonce( $nonce, self::NONCE_KEY ) ) {
			Utils::send_json_error( __( 'Sorry, you don\'t have permission to do this.', 'tasty-recipes' ) );
		}

		$class      = false;
		$converters = Tasty_Recipes::get_converters();
		$get_type   = Utils::get_param( 'type' );

		if ( ! empty( $get_type ) && isset( $converters[ $get_type ] ) ) {
			$class = $converters[ $get_type ]['class'];
		} else {
			Utils::send_json_error(
				__( 'Couldn\'t convert recipes. Please contact support for assistance.', 'tasty-recipes' )
			);
		}

		$post_ids  = $class::get_post_ids();
		$converted = 0;
		foreach ( $post_ids as $post_id ) {
			$type = 'shortcode';
			if ( function_exists( 'has_blocks' )
				&& has_blocks( $post_id ) ) {
				$type = 'block';
			}
			$recipe = $class::convert_post( $post_id, $type );
			if ( $recipe ) {
				++$converted;
			} else {
				Utils::send_json_error(
					sprintf(
						/* translators: %1$s: post title, %2$d: post ID */
						__( 'Couldn\'t convert recipe in \'%1$s\' (post %2$d). Please contact support for assistance.', 'tasty-recipes' ),
						get_the_title( $post_id ),
						$post_id
					)
				);
			}
		}

		$after_count = $class::get_count();
		wp_send_json_success(
			array(
				'converted' => $converted,
				'count'     => (int) $after_count,
			)
		);
	}

	/**
	 * Renders in the admin header.
	 *
	 * @deprecated 3.10
	 *
	 * @return void
	 */
	public static function action_in_admin_header() {
		_deprecated_function( __METHOD__, '3.10' );
	}

	/**
	 * Renders the Tasty Recipes settings page.
	 *
	 * @return void
	 */
	public static function handle_settings_page() {
		_deprecated_function( __METHOD__, 'x.x', 'Settings::handle_settings_page' );
		Settings::handle_settings_page();
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
		return array( 'bold', 'italic', 'underline', 'bullist', 'numlist', 'link', 'unlink', 'removeformat' );
	}

	/**
	 * Prints the recipe editor modal template, but only once.
	 *
	 * @deprecated x.x
	 *
	 * @return void
	 */
	public static function action_admin_footer_render_template() {
		_deprecated_function( __METHOD__, 'x.x', 'Settings::action_admin_footer_render_template' );
		Settings::action_admin_footer_render_template();
	}

	/**
	 * Render an input field.
	 *
	 * @deprecated x.x
	 *
	 * @param array $args Configuration arguments used by the input field.
	 *
	 * @return void
	 */
	public static function render_input_field( $args ) {
		_deprecated_function( __METHOD__, 'x.x', 'Settings::render_input_field' );
		Settings::render_input_field( $args );
	}

	/**
	 * Sanitize quick links option field.
	 *
	 * @deprecated x.x
	 *
	 * @param array $item Array of items being selected.
	 *
	 * @return array
	 */
	public static function sanitize_quick_links( $item ) {
		_deprecated_function( __METHOD__, 'x.x', 'Settings::sanitize_quick_links' );
		return Settings::sanitize_quick_links( $item );
	}

	/**
	 * Sanitizes the card buttons option.
	 *
	 * @deprecated x.x
	 *
	 * @param array $original Original card button option value.
	 *
	 * @return array
	 */
	public static function sanitize_card_buttons( $original ) {
		_deprecated_function( __METHOD__, 'x.x', 'Settings::sanitize_card_buttons' );
		return Settings::sanitize_card_buttons( $original );
	}

	/**
	 * Sanitizes the customization option to make sure there are only expected values.
	 *
	 * @deprecated x.x
	 *
	 * @param array $original Original customization option value.
	 *
	 * @return array
	 */
	public static function sanitize_customization_option( $original ) {
		_deprecated_function( __METHOD__, 'x.x', 'Settings::sanitize_customization_option' );
		return Settings::sanitize_customization_option( $original );
	}

	/**
	 * Render a select field.
	 *
	 * @deprecated x.x
	 *
	 * @param array $args Configuration arguments used by the select field.
	 *
	 * @return void
	 */
	public static function render_select_field( $args ) {
		_deprecated_function( __METHOD__, 'x.x', 'Settings::render_select_field' );
		Settings::render_select_field( $args );
	}

	/**
	 * Renders multiselect checkbox field.
	 *
	 * @deprecated x.x
	 *
	 * @param array $args Field args.
	 *
	 * @return void
	 */
	public static function render_multiselect_checkboxes_field( $args ) {
		_deprecated_function( __METHOD__, 'x.x', 'Settings::render_multiselect_checkboxes_field' );
		Settings::render_multiselect_checkboxes_field( $args );
	}

	/**
	 * Renders the card buttons field.
	 *
	 * @deprecated x.x
	 *
	 * @return void
	 */
	public static function render_card_buttons_field() {
		_deprecated_function( __METHOD__, 'x.x', 'Settings::render_card_buttons_field' );
		Settings::render_card_buttons_field();
	}

	/**
	 * Gets the license check object.
	 *
	 * @deprecated 3.10
	 *
	 * @return bool|object
	 */
	public static function get_license_check() {
		_deprecated_function( __METHOD__, '3.10' );

		return false;
	}

	/**
	 * Check if Cook Mode can be enabled in this site or not.
	 *
	 * @deprecated x.x
	 *
	 * @since 3.8
	 *
	 * @return bool
	 */
	public static function can_enable_cook_mode() {
		_deprecated_fuction( __METHOD__, 'x.x', 'Settings::can_enable_cook_mode' );
		return Settings::can_enable_cook_mode();
	}

	/**
	 * Is Cook Mode enabled or not.
	 *
	 * @since 3.8
	 *
	 * @return bool
	 */
	public static function is_cook_mode_enabled() {
		return (bool) get_option( Tasty_Recipes::COOK_MODE_OPTION, self::can_enable_cook_mode() );
	}

	/**
	 * Show SSL certificate error message.
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	public static function show_ssl_error() {
		if ( self::can_enable_cook_mode() ) {
			return;
		}
		$error_message = __( 'Cook Mode requires SSL (HTTPS)', 'tasty-recipes' );
		echo "<div class='tasty-recipes-settings-error'>" . esc_html( $error_message ) . '</div>';
	}

	/**
	 * Get Cook Mode helper message.
	 *
	 * @since 3.8
	 *
	 * @return string
	 */
	public static function get_cook_mode_helper() {
		return get_option( Tasty_Recipes::COOK_MODE_HELPER_OPTION, __( 'Prevent your screen from going dark', 'tasty-recipes' ) );
	}

	/**
	 * Validate the current user capability and exit with json error in case of permission denied.
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	public static function validate_ajax_capability() {
		if ( current_user_can( self::CAPABILITY ) ) {
			return;
		}

		Utils::send_json_error( __( 'You don\'t have permission to do this.', 'tasty-recipes' ) );
	}

	/**
	 * Check if instacart option is enabled and affiliate ID option is not empty.
	 *
	 * @since 3.8
	 *
	 * @return bool
	 */
	public static function can_show_instacart_button() {
		return (bool) get_option( Tasty_Recipes::INSTACART_OPTION, false );
	}

	/**
	 * Print instacart image in admin instead of real iframe.
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	public static function print_mock_instacart_html() {
		if ( ! self::can_show_instacart_button() ) {
			return;
		}

		echo '<p><a href=""><img src="' . esc_url( plugins_url( 'assets/images/instacart.svg', TASTY_RECIPES_PLUGIN_FILE ) ) . '"></a></p>';
	}

	/**
	 * Filter quick links admin field to handle the removed item "both"
	 * and cast the string option to an array.
	 *
	 * @deprecated x.x
	 *
	 * @param array $args Admin field args.
	 *
	 * @return array
	 */
	public static function filter_quick_links_backward_compatibility( $args ) {
		_deprecated_function( __METHOD__, 'x.x', 'Settings::filter_quick_links_backward_compatibility' );
		return Settings::filter_quick_links_backward_compatibility( $args );
	}

	/**
	 * Handle the removed item "both" and cast the string option to an array.
	 *
	 * @deprecated x.x
	 *
	 * @param mixed $option_value Option value to be handled.
	 *
	 * @return array|string[]
	 */
	public static function handle_quick_links_backward_compatibility( $option_value ) {
		_deprecated_function( __METHOD__, 'x.x', 'Settings::handle_quick_links_backward_compatibility' );
		return Settings::handle_quick_links_backward_compatibility( $option_value );
	}
}
