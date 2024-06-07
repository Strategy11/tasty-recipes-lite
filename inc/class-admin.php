<?php
/**
 * Manages interactions with the admin.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes_Lite;

use Tasty\Framework\Abstracts\Updater;
use Tasty\Framework\Admin\Plugins\Factory;
use Tasty\Framework\Utils\Url;
use TastyRecipesLiteMain;
use Tasty_Recipes_Lite\Admin\Settings;
use Tasty_Recipes_Lite\Admin\Templates;
use Tasty_Recipes_Lite\Admin\Settings\Settings\Card_Buttons;

/**
 * Manages interactions with the admin.
 */
class Admin {
	/**
	 * URL to the plugin store.
	 *
	 * @var string
	 */
	const STORE_URL = 'https://www.wptasty.com';

	/**
	 * Capability required to manage settings.
	 *
	 * @var string
	 */
	const CAPABILITY = 'manage_options';

	/**
	 * Key used for nonce authentication.
	 *
	 * @var string
	 */
	const NONCE_KEY = 'tasty-recipes-settings';

	/**
	 * Slug for the settings page.
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'tasty-recipes';

	/**
	 * Group used for card settings.
	 *
	 * @var string
	 */
	const SETTINGS_GROUP_CARD = 'tasty-recipes-settings-card';

	/**
	 * Section used for card settings.
	 *
	 * @var string
	 */
	const SETTINGS_SECTION_CARD_DESIGN = 'tasty-recipes';

	/**
	 * ID used for TinyMCE instance in settings.
	 *
	 * @var string
	 */
	private static $editor_id = 'tasty-recipes-settings';

	/**
	 * Register EDD updater.
	 *
	 * @return void
	 */
	public static function register_edd_updater() {
		Updater::run(
			TASTY_RECIPES_LITE_PLUGIN_FILE,
			array(
				'version'   => TASTY_RECIPES_LITE_PLUGIN_VERSION,
				'license'   => '',
				'item_name' => 'Tasty Recipe Lite',
				'author'    => 'Tasty Recipe Lite',
			)
		);
	}

	/**
	 * Registers used settings.
	 */
	public static function action_admin_menu() {
		Settings::instance()->register();
	}

	/**
	 * Add the callback for the plugin menu item.
	 *
	 * @param array $menu_items Admin menu items.
	 *
	 * @return array
	 */
	public static function add_menu_item( $menu_items ) {
		if ( empty( $menu_items['tasty-recipes'] ) ) {
			return $menu_items;
		}

		$menu_items['tasty-recipes']['callback'] = array( __CLASS__, 'handle_settings_page' );
		return $menu_items;
	}

	/**
	 * Registers a new custom column for Tasty Recipes.
	 *
	 * @param array $columns Existing column names.
	 * @return array
	 */
	public static function action_manage_posts_columns( $columns ) {
		$columns['tasty_recipe'] = esc_html__( 'Tasty Recipe', 'tasty-recipes-lite' );
		return $columns;
	}

	/**
	 * Renders an 'Edit Tasty Recipe' button for the column if the post has a recipe.
	 *
	 * @param string  $column_name Name of the column.
	 * @param integer $post_id     ID of the post being displayed.
	 */
	public static function action_manage_posts_custom_column( $column_name, $post_id ) {
		switch ( $column_name ) {
			case 'tasty_recipe':
				$recipe_ids = TastyRecipesLiteMain::get_recipe_ids_for_post( $post_id );
				if ( ! empty( $recipe_ids ) ) {
					$recipe_id = array_shift( $recipe_ids );
					echo '<button class="button tasty-recipes-edit-button" data-recipe-id="' . esc_attr( $recipe_id ) . '">' . esc_html__( 'Edit Tasty Recipe', 'tasty-recipes-lite' ) . '</button>';
				}
				break;
		}
	}

	/**
	 * Renders the 'Edit Tasty Recipe' button on Quick Edit.
	 *
	 * @param string $column_name Name of the column.
	 */
	public static function action_quick_edit_custom_box( $column_name ) {
		switch ( $column_name ) {
			case 'tasty_recipe':
				echo '<button class="button tasty-recipes-edit-button" data-recipe-id="">' . esc_html__( 'Edit Tasty Recipe', 'tasty-recipes-lite' ) . '</button>';
				break;
		}
	}

	/**
	 * Hides the 'Tasty Recipe' column by default.
	 *
	 * @param array $columns Existing default hidden columns.
	 * @return array
	 */
	public static function filter_hidden_columns( $columns ) {
		$columns[] = 'tasty_recipe';
		return $columns;
	}

	/**
	 * Adds 'Settings' and 'Remove license' links to plugin settings.
	 *
	 * @param array $links Existing plugin action links.
	 * @return array
	 */
	public static function filter_plugin_action_links( $links ) {
		$links['tr_settings'] = '<a href="' . esc_url( add_query_arg( 'tab', 'settings', menu_page_url( self::PAGE_SLUG, false ) ) ) . '">' . esc_html__( 'Settings', 'tasty-recipes-lite' ) . '</a>';

		if ( ! tasty_recipes_is_pro_active() ) {
			$lite_plugin = Factory::create( 'Tasty Recipes Lite' );
			if ( ! is_null( $lite_plugin ) ) {
				$upgrade_url              = Url::add_utm_params(
					$lite_plugin->get_product_pricing_page_url(),
					array(
						'utm_medium'  => 'upgrade',
						'utm_content' => $lite_plugin->get_plugin_slug(),
					)
				);
				$links['tr_lite_upgrade'] = '<a class="" href="' . esc_url( $upgrade_url ) . '" target="_blank" rel="noopener"><strong style="display: inline-block;">' . esc_html__( 'Upgrade to Pro', 'tasty-recipes-lite' ) . '</strong></a>';
			}
		}
		return $links;
	}

	/**
	 * Handles an AJAX request to preview a recipe template.
	 */
	public static function handle_wp_ajax_preview_recipe_card() {
		global $editor_styles;
		$nonce = Utils::request_param( 'nonce', 'sanitize_key' );
		if ( empty( $nonce ) || ! current_user_can( self::CAPABILITY ) || ! wp_verify_nonce( $nonce, self::NONCE_KEY ) ) {
			wp_die( esc_html__( "Sorry, you don't have permission to do this.", 'tasty-recipes-lite' ) );
		}
		header( 'Content-Type: text/html' );
		$wrapper_classes = array(
			'tasty-recipes',
			'tasty-recipes-display',
		);

		$shareasale = get_option( TastyRecipesLiteMain::SHAREASALE_OPTION );
		if ( $shareasale ) {
			$wrapper_classes[] = 'tasty-recipes-has-plug';
		}

		$get_template               = Utils::get_param( 'template', 'sanitize_key', 'boldaaa' );
		$get_star_ratings_style     = Utils::get_param( 'star_ratings_style', 'sanitize_key' );
		$get_footer_social_platform = Utils::get_param( 'footer_social_platform', 'sanitize_key' );

		$custom_design          = ! empty( $get_template ) && array_key_exists( $get_template, self::get_design_templates() ) ? $get_template : '';
		$star_ratings_style     = ! empty( $get_star_ratings_style ) && in_array( $get_star_ratings_style, array( 'solid', 'outline' ), true ) ? $get_star_ratings_style : null;
		$footer_social_platform = ! empty( $get_footer_social_platform ) && in_array( $get_footer_social_platform, array( 'instagram', 'pinterest', 'facebook' ), true ) ? $get_footer_social_platform : '';

		$settings = TastyRecipesLiteMain::get_filtered_customization_settings();

		$styles  = Shortcodes::get_styles_as_string( $custom_design );
		$styles .= Utils::get_contents( dirname( TASTY_RECIPES_LITE_PLUGIN_FILE ) . '/assets/dist/ratings.css' );
		$styles .= Utils::get_contents( dirname( TASTY_RECIPES_LITE_PLUGIN_FILE ) . '/assets/dist/recipe-card-preview.css' );

		$template_object = Templates::instance()->get_object_by_name( $custom_design );
		$custom_path     = $template_object->get_template_path();
		if ( $custom_design && file_exists( $custom_path ) ) {
			$template = $custom_path;
		} else {
			$template = 'recipe/tasty-recipes';
		}

		$stylesheet = get_option( 'stylesheet' );
		// Clean up Feast directory names.
		$stylesheet  = preg_replace( '#-v[\d]+$#', '', $stylesheet );
		$compat_file = dirname( TASTY_RECIPES_LITE_PLUGIN_FILE ) . '/assets/css/theme-compat-card-previews/' . $stylesheet . '.css';
		if ( file_exists( $compat_file ) ) {
			$styles .= Utils::get_contents( $compat_file );
		}

		$recipe       = new \stdClass();
		$recipe_json  = array();
		$ingredients  = <<<EOT
<ul>
	<li><span data-amount="3/4" data-unit="cup">3/4 cup</span> melted coconut oil</li>
	<li><span data-amount="1/2" data-unit="cup">1/2 cup</span> cocoa powder</li>
	<li><span data-amount="2" data-unit="tablespoon">2 tbsp.</span> natural sweetener (agave or maple syrup)</li>
	<li><span data-amount="3/4" data-unit="cup">3/4 cup</span> almond butter</li>
	<li><span data-amount="" data-unit=""></span>pinch of sea salt, plus more for topping</li>
</ul>
EOT;
		$ingredients  = apply_filters( 'tasty_recipes_the_content', $ingredients, 'ingredients' );
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

		$template_vars = array(
			'recipe'                        => $recipe,
			'recipe_scripts'                => '',
			'recipe_json'                   => $recipe_json,
			'recipe_title'                  => 'Almond Butter Cups',
			'recipe_image'                  => '<img width="183" height="183" src="' . esc_url( plugins_url( 'assets/images/Almond-Butter-Cups-Recipe-370x370.jpg', dirname( __FILE__ ) ) ) . '" class="attachment-thumbnail size-thumbnail" alt="Chocolate Chip Cookies on parchment paper." loading="lazy" data-pin-nopin="true" />',
			'recipe_rating_icons'           => Ratings::get_rendered_rating(
				4.6,
				'star-color.color',
				$star_ratings_style
			),
			'recipe_rating_label'           => '<span data-tasty-recipes-customization="detail-label-color.color" class="rating-label"><span class="average">4.6</span> from <span class="count">1376</span> reviews</span>',
			'recipe_author_name'            => '',
			'recipe_details'                => array(
				'author'     =>
				array(
					'label' => 'Author',
					'value' => '<a data-tasty-recipes-customization="detail-value-color.color" class="tasty-recipes-author-name" href="https://pinchofyum.com/about">Pinch of Yum</a>',
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
					'value' => '<span data-tasty-recipes-customization="detail-value-color.color" class="tasty-recipes-yield"><span data-amount="12">12</span> almond butter cups<span class="tasty-recipes-yield-scale"><span data-amount="1">1</span>x</span></span>',
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
			'recipe_scalable'               => '<button class="tasty-recipes-scale-button tasty-recipes-scale-button-active" data-amount="1">1x</button><button class="tasty-recipes-scale-button" data-amount="2">2x</button><button class="tasty-recipes-scale-button" data-amount="3">3x</button>',
			'recipe_instructions'           => $instructions,
			'recipe_keywords'               => '',
			'recipe_notes'                  => $notes,
			'recipe_video_embed'            => '',
			'recipe_hidden_nutrition'       => array(),
			'copy_ingredients'              => get_option( TastyRecipesLiteMain::COPY_TO_CLIPBOARD_OPTION ) ? '<button aria-label="' . __( 'Copy ingredients to clipboard', 'tasty-recipes-lite' ) . '" class="tasty-recipes-copy-button" id="copy-ingredients" data-text="' . __( 'Copy ingredients', 'tasty-recipes-lite' ) . '">' . Utils::get_contents( dirname( dirname( __FILE__ ) ) . '/assets/images/icons/icon-copy-to-clipboard.svg' ) . '</button>' : false,
			'first_button'                  => Shortcodes::get_card_button( $recipe, 'first', $custom_design ),
			'second_button'                 => Shortcodes::get_card_button( $recipe, 'second', $custom_design ),
			'instagram_handle'              => get_option( TastyRecipesLiteMain::INSTAGRAM_HANDLE_OPTION ),
			'instagram_hashtag'             => get_option( TastyRecipesLiteMain::INSTAGRAM_HASHTAG_OPTION ),
			'footer_social_platform'        => $footer_social_platform,
			'footer_heading'                => $settings['footer_heading'],
			'footer_description'            => $settings['footer_description'],
		);

		if ( isset( $template_object ) ) {
			$template_vars['template_url'] = $template_object->get_template_url();
		}

		if ( ! empty( $template_vars['recipe_image'] ) ) {
			$wrapper_classes[] = 'tasty-recipes-has-image';
		} else {
			$wrapper_classes[] = 'tasty-recipes-no-image';
		}
		$stylesheets = '';
		if ( $editor_styles && current_theme_supports( 'editor-styles' ) ) {
			foreach ( $editor_styles as $style ) {
				if ( preg_match( '~^(https?:)?//~', $style ) ) {
					$stylesheets .= '<link rel="stylesheet" href="' . esc_url( $style ) . '">' . PHP_EOL; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
				} else {
					$stylesheets .= '<link rel="stylesheet" href="' . esc_url( get_theme_file_uri( $style ) ) . '">' . PHP_EOL; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
				}
			}
		}

		$template_vars['recipe_styles'] = '<style type="text/css">' . PHP_EOL . $styles . PHP_EOL . '</style>' . PHP_EOL;

		$template_vars = apply_filters( 'tasty_recipes_admin_preview_template_vars', $template_vars, $settings );

		$ret            = '<!DOCTYPE html><html><head>' . $stylesheets . '</head><body>';
		$ret           .= '<div class="' . esc_attr( implode( ' ', $wrapper_classes ) ) . '"';
		$customizations = Shortcodes::get_card_container_customizations( $custom_design );
		if ( $customizations ) {
			$ret .= ' data-tasty-recipes-customization="' . esc_attr( $customizations ) . '"';
		}
		$ret .= '>' . PHP_EOL;
		$ret .= TastyRecipesLiteMain::get_template_part( $template, $template_vars );
		$ret .= '</div>';
		$ret .= '<script type="text/javascript">' . PHP_EOL . Utils::get_contents( dirname( dirname( __FILE__ ) ) . '/assets/dist/settings-recipe-card-preview.build.js' ) . PHP_EOL . '</script>';
		$ret .= '</body></html>';
		echo apply_filters( 'tasty_recipes_recipe_card_output', $ret, 'admin', compact( 'recipe' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Handles an AJAX request to get the number of matching recipes to convert.
	 */
	public static function handle_wp_ajax_get_count() {
		$nonce = Utils::get_param( 'nonce', 'sanitize_key' );
		if ( empty( $nonce ) || ! current_user_can( self::CAPABILITY ) || ! wp_verify_nonce( $nonce, self::NONCE_KEY ) ) {
			Utils::send_json_error( __( 'Sorry, you don\'t have permission to do this.', 'tasty-recipes-lite' ) );
		}

		$class      = false;
		$converters = TastyRecipesLiteMain::get_converters();
		$get_type   = Utils::get_param( 'type' );

		if ( ! empty( $get_type ) && isset( $converters[ $get_type ] ) ) {
			$class = $converters[ $get_type ]['class'];
		} else {
			Utils::send_json_error( __( 'Couldn\'t count recipes. Please contact support for assistance.', 'tasty-recipes-lite' ) );
		}

		wp_send_json_success(
			array(
				'count' => (int) $class::get_count(),
			)
		);
	}

	/**
	 * Handles an AJAX request to batch convert some recipes.
	 */
	public static function handle_wp_ajax_convert() {
		$nonce = Utils::get_param( 'nonce', 'sanitize_key' );
		if ( empty( $nonce ) || ! current_user_can( self::CAPABILITY ) || ! wp_verify_nonce( $nonce, self::NONCE_KEY ) ) {
			Utils::send_json_error( __( 'Sorry, you don\'t have permission to do this.', 'tasty-recipes-lite' ) );
		}

		$class      = false;
		$converters = TastyRecipesLiteMain::get_converters();
		$get_type   = Utils::get_param( 'type' );

		if ( ! empty( $get_type ) && isset( $converters[ $get_type ] ) ) {
			$class = $converters[ $get_type ]['class'];
		} else {
			Utils::send_json_error(
				__( 'Couldn\'t convert recipes. Please contact support for assistance.', 'tasty-recipes-lite' )
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
				$converted++;
			} else {
				Utils::send_json_error(
					sprintf(
						/* translators: %1$s: post title, %2$d: post ID */
						__( 'Couldn\'t convert recipe in \'%1$s\' (post %2$d). Please contact support for assistance.', 'tasty-recipes-lite' ),
						get_the_title( $post_id ),
						$post_id
					)
				);
			}
		}

		$after_count = $class::get_count();
		wp_send_json_success(
			array(
				'converted' => (int) $converted,
				'count'     => (int) $after_count,
			)
		);
	}

	/**
	 * Renders the Tasty Recipes settings page.
	 */
	public static function handle_settings_page() {
		Settings::instance()->render_group_settings( self::SETTINGS_GROUP_CARD, self::PAGE_SLUG );

		self::show_settings();
		if ( ! did_action( 'admin_footer' ) && ! doing_action( 'admin_footer' ) ) {
			add_action( 'admin_footer', array( __CLASS__, 'action_admin_footer_render_template' ) );
		}
	}

	/**
	 * Set the tab links and include the settings template.
	 *
	 * @since 3.11.1
	 *
	 * @return void
	 */
	private static function show_settings() {
		$active_tab = Utils::sanitize_get_key( 'tab', 'design' );
		$base_url   = menu_page_url( self::PAGE_SLUG, false );
		$debug      = Utils::sanitize_get_key( 'debug' );
		$tabs       = array();

		$add_link = array(
			'design'     => __( 'Design', 'tasty-recipes-lite' ),
			'settings'   => __( 'Settings', 'tasty-recipes-lite' ),
			'converters' => __( 'Converters', 'tasty-recipes-lite' ),
		);

		if ( ! empty( $debug ) || 'debug' === $active_tab ) {
			$add_link['debug'] = __( 'Debug', 'tasty-recipes-lite' );
		}

		$add_link['about'] = __( 'About', 'tasty-recipes-lite' );

		foreach ( $add_link as $key => $title ) {
			$tabs[] = array(
				'title'  => $title,
				'url'    => add_query_arg( array( 'tab' => $key ), $base_url ),
				'active' => $key === $active_tab,
			);
		}

		TastyRecipesLiteMain::echo_template_part( 'settings', compact( 'tabs', 'active_tab' ) );
	}

	/**
	 * Sets the TinyMCE editor buttons for our editor instance.
	 *
	 * @param array  $buttons   Existing TinyMCE buttons.
	 * @param string $editor_id ID for the editor instance.
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
				'teeny'   => true,
				'wpautop' => true,
			)
		);
		ob_get_clean();
	}

	/**
	 * Validate the current user capability and exit with json error in case of permission denied.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function validate_ajax_capability() {
		if ( current_user_can( self::CAPABILITY ) ) {
			return;
		}
		Utils::send_json_error( __( 'You don\'t have permission to do this.', 'tasty-recipes-lite' ) );
	}

	/**
	 * Handle the removed item "both" and cast the string option to an array.
	 *
	 * @param mixed $option_value Option value to be handled.
	 *
	 * @return array|string[]
	 */
	public static function handle_quick_links_backward_compatibility( $option_value ) {
		if ( empty( $option_value ) ) {
			return array();
		}

		if ( 'both' === $option_value ) {
			return array(
				'jump',
				'print',
			);
		}

		return (array) $option_value;
	}

	/**
	 * Get array of available design templates.
	 *
	 * @return array
	 */
	public static function get_design_templates() {
		return Templates::instance()->get_all_templates();
	}

	/**
	 * Filter the default setting for card buttons.
	 *
	 * @param string[] $default Setting default value.
	 * @param object   $setting Current setting object.
	 *
	 * @return string[]
	 */
	public static function filter_default_card_buttons_setting( $default, $setting ) {
		if ( $setting->get_id() !== Card_Buttons::instance()->get_id() ) {
			return $default;
		}
		return array(
			'first'  => 'print',
			'second' => '',
		);
	}

	/**
	 * Add lite update modal html.
	 *
	 * @return void
	 */
	public static function add_lite_upgrade_modal() {
		$tasty_recipes_lite_upgrade_url = Url::add_utm_params(
			'https://www.wptasty.com/pricing-recipes', // @Todo: replace with lite upgrade url.
			array(
				'utm_medium'   => 'modal',
				'utm_campaign' => 'lite-plugin',
				'utm_content'  => '__setting_utm_content__',
			)
		);
		TastyRecipesLiteMain::echo_template_part( 'admin/upgrade-modal', compact( 'tasty_recipes_lite_upgrade_url' ) );
	}

}
