<?php
/**
 * Manages interactions with the settings page.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes;

use Tasty\Framework\Utils\Url;
use Tasty\Framework\Utils\Common;
use Tasty_Recipes;

/**
 * Manages interactions with the settings page.
 */
class Settings {

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
	 * Load hooks.
	 *
	 * @return void
	 */
	public static function load_hooks() {
		if ( ! is_admin() ) {
			return;
		}

		add_filter( 'tasty_framework_admin_menu_items', array( __CLASS__, 'add_menu_item' ) );
		add_action( 'admin_menu', array( __CLASS__, 'register_settings' ) );
		add_filter( 'tasty_recipes_admin_settings_field_args', array( __CLASS__, 'filter_quick_links_backward_compatibility' ) );
	}

	/**
	 * Add the callback for the plugin menu item.
	 *
	 * @param array $menu_items Admin menu items.
	 *
	 * @return array
	 */
	public static function add_menu_item( $menu_items ) {
		if ( ! empty( $menu_items['tasty-recipes'] ) ) {
			$menu_items['tasty-recipes']['callback'] = array( __CLASS__, 'handle_settings_page' );
		}

		return $menu_items;
	}

	/**
	 * Registers used settings.
	 *
	 * @return void
	 */
	public static function register_settings() {
		$sanitize_title = [
			'sanitize_callback' => 'sanitize_title',
		];

		$design_settings = [
			Tasty_Recipes::CUSTOMIZATION_OPTION => [
				'sanitize_callback' => array( __CLASS__, 'sanitize_customization_option' ),
			],
			Tasty_Recipes::TEMPLATE_OPTION      => $sanitize_title,
		];

		/**
		 * Filter the design settings to be registered.
		 *
		 * @since 1.0
		 *
		 * @param array $settings Array of settings to be registered.
		 */
		$design_settings = apply_filters( 'tasty_recipes_register_design_settings', $design_settings );

		foreach ( $design_settings as $option_name => $args ) {
			register_setting(
				self::SETTINGS_SECTION_CARD_DESIGN,
				$option_name,
				$args
			);
		}

		$settings = [
			Tasty_Recipes::QUICK_LINKS_OPTION         => [
				'sanitize_callback' => array( __CLASS__, 'sanitize_quick_links' ),
			],
			Tasty_Recipes::QUICK_LINKS_STYLE          => $sanitize_title,
			Tasty_Recipes::CARD_BUTTONS_OPTION        => [
				'sanitize_callback' => array( __CLASS__, 'sanitize_card_buttons' ),
			],
			Tasty_Recipes::COPY_TO_CLIPBOARD_OPTION   => $sanitize_title,
			Tasty_Recipes::DEFAULT_AUTHOR_LINK_OPTION => [
				'sanitize_callback' => 'esc_url_raw',
			],
			Tasty_Recipes::POWEREDBY_OPTION           => $sanitize_title,
			Tasty_Recipes::SHAREASALE_OPTION          => [
				'sanitize_callback' => 'sanitize_text_field',
			],
		];

		/**
		 * Filter the settings to be registered.
		 *
		 * @since 1.0
		 *
		 * @param array $settings Array of settings to be registered.
		 */
		$settings = apply_filters( 'tasty_recipes_register_settings', $settings );

		foreach ( $settings as $option_name => $args ) {
			register_setting(
				self::SETTINGS_GROUP_CARD,
				$option_name,
				$args
			);
		}
	}

	/**
	 * Renders the Tasty Recipes settings page.
	 *
	 * @return void
	 */
	public static function handle_settings_page() {
		add_settings_section(
			self::SETTINGS_SECTION_CARD_DESIGN,
			'',
			'__return_false',
			self::PAGE_SLUG
		);


		$settings = self::prepare_ordered_settings();
		foreach ( $settings as $option_name => $option ) {
			$option['name'] = $option_name;
			if ( empty( $option['render'] ) ) {
				$option['render'] = empty( $option['options'] ) ? 'input' : 'select';
			}

			add_settings_field(
				$option_name,
				$option['label'],
				[ __CLASS__, 'render_' . $option['render'] . '_field' ],
				self::PAGE_SLUG,
				self::SETTINGS_SECTION_CARD_DESIGN,
				self::filter_settings_args( $option )
			);
		}

		self::show_settings();
		if ( ! did_action( 'admin_footer' ) && ! doing_action( 'admin_footer' ) ) {
			add_action( 'admin_footer', array( __CLASS__, 'action_admin_footer_render_template' ) );
		}
	}

	/**
	 * Get an array of all the settings to include.
	 *
	 * @return array
	 */
	private static function prepare_ordered_settings() {
		$settings  = [];
		$functions = [
			'quick_links',
			'card_button',
			'author',
			'clipboard',
			'conversion',
			'ingredient_checkbox',
			'cookmode',
			'scaling',
			'instacart',
			'shareasale',
		];

		$order = 0;
		foreach ( $functions as $function ) {
			$order = $order + 10;
			call_user_func_array(
				array( __CLASS__, 'add_' . $function . '_settings' ),
				array( &$settings )
			);

			/**
			 * Let phpstan know that $settings isn't empty.
			 *
			 * @var array<string, array{order?: int}> $settings
			 */
			foreach ( $settings as $option_name => $option ) {
				// Set the default order for settings that don't have an 'order' set.
				if ( ! isset( $option['order'] ) ) {
					$settings[ $option_name ]['order'] = $order;
				}
				++$order;
			}
		}

		/**
		 * Filter the settings array.
		 *
		 * @since 1.0
		 */
		$settings = apply_filters( 'tasty_recipes_settings', $settings );

		uasort( $settings, array( __CLASS__, 'sort_settings' ) );
		return $settings;
	}

	/**
	 * Add quick links settings.
	 *
	 * @param array $settings Settings array.
	 *
	 * @return void
	 */
	private static function add_quick_links_settings( &$settings ) {
		$settings[ Tasty_Recipes::QUICK_LINKS_OPTION ] = [
			'label'          => __( 'Quick Links', 'tasty-recipes-lite' ),
			'render'         => 'multiselect_checkboxes',
			'description'    => __( 'Enable or disable quick links below the post title.', 'tasty-recipes-lite' ),
			'default_values' => array(
				'jump',
				'print',
			),
			'options'        => array(
				'jump'   => __( '\'Jump to Recipe\' link', 'tasty-recipes-lite' ),
				'print'  => __( '\'Print Recipe\' link', 'tasty-recipes-lite' ),
				'rating' => __( '\'Leave a Review\' link', 'tasty-recipes-lite' ),
			),
		];

		$settings[ Tasty_Recipes::QUICK_LINKS_STYLE ] = [
			'label'         => __( 'Quick Links Style', 'tasty-recipes-lite' ),
			'options'       => array(
				'links'   => __( 'Links', 'tasty-recipes-lite' ),
				'buttons' => __( 'Buttons', 'tasty-recipes-lite' ),
			),
			'default_value' => 'links',
			'is_pro'        => true,
		];
	}

	/**
	 * Add card button settings.
	 *
	 * @param array $settings Settings array.
	 *
	 * @return void
	 */
	private static function add_card_button_settings( &$settings ) {
		$settings[ Tasty_Recipes::CARD_BUTTONS_OPTION ] = [
			'label'  => __( 'Recipe Card Buttons', 'tasty-recipes-lite' ),
			'render' => 'card_buttons',
		];
	}

	/**
	 * Add conversion settings.
	 *
	 * @param array $settings Settings array.
	 *
	 * @return void
	 */
	private static function add_conversion_settings( &$settings ) {
		$settings[ Tasty_Recipes::UNIT_CONVERSION_OPTION ] = [
			'label'       => __( 'Unit Conversion', 'tasty-recipes-lite' ),
			'description' => __( 'Allow visitors to convert between US Customary and Metric.', 'tasty-recipes-lite' ),
			'options'     => array(
				false => __( 'Off', 'tasty-recipes-lite' ),
				true  => __( 'On', 'tasty-recipes-lite' ),
			),
			'is_pro'      => true,
		];
	}

	/**
	 * Add ingredient checkbox settings.
	 *
	 * @param array $settings Settings array.
	 *
	 * @return void
	 */
	private static function add_ingredient_checkbox_settings( &$settings ) {
		$settings[ Tasty_Recipes::INGREDIENT_CHECKBOXES_OPTION ] = [
			'label'       => __( 'Ingredient Checkboxes', 'tasty-recipes-lite' ),
			'description' => __( 'Allow visitors to check off recipe ingredients.', 'tasty-recipes-lite' ),
			'options'     => array(
				false => __( 'Off', 'tasty-recipes-lite' ),
				true  => __( 'On', 'tasty-recipes-lite' ),
			),
			'is_pro'      => true,
		];
	}

	/**
	 * Add cook mode settings.
	 *
	 * @param array $settings Settings array.
	 *
	 * @return void
	 */
	private static function add_cookmode_settings( &$settings ) {
		$settings[ Tasty_Recipes::COOK_MODE_OPTION ] = [
			'label'       => __( 'Cook Mode', 'tasty-recipes-lite' ),
			'description' => __( 'Show Cook Mode toggle to let visitors prevent sleep mode for supported devices.', 'tasty-recipes-lite' ),
			'options'     => array(
				true  => __( 'On', 'tasty-recipes-lite' ),
				false => __( 'Off', 'tasty-recipes-lite' ),
			),
			'is_pro'      => true,
		];
	}

	/**
	 * Add scaling settings.
	 *
	 * @param array $settings Settings array.
	 *
	 * @return void
	 */
	private static function add_scaling_settings( &$settings ) {
		$settings[ Tasty_Recipes::DISABLE_SCALING_OPTION ] = [
			'label'       => __( 'Scaling', 'tasty-recipes-lite' ),
			'description' => __( 'Allow visitors to scale the recipe 1x, 2x, or 3x.', 'tasty-recipes-lite' ),
			'options'     => array(
				true  => __( 'Off', 'tasty-recipes-lite' ),
				false => __( 'On', 'tasty-recipes-lite' ),
			),
			'is_pro'      => true,
		];
	}

	/**
	 * Add clipboard settings.
	 *
	 * @param array $settings Settings array.
	 *
	 * @return void
	 */
	private static function add_clipboard_settings( &$settings ) {
		$settings[ Tasty_Recipes::COPY_TO_CLIPBOARD_OPTION ] = [
			'label'       => __( 'Copy to Clipboard', 'tasty-recipes-lite' ),
			'description' => __( 'Allow visitors to copy ingredients to clipboard.', 'tasty-recipes-lite' ),
			'options'     => array(
				false => __( 'Off', 'tasty-recipes-lite' ),
				true  => __( 'On', 'tasty-recipes-lite' ),
			),
			'is_pro'      => true,
		];
	}

	/**
	 * Add author settings.
	 *
	 * @param array $settings Settings array.
	 *
	 * @return void
	 */
	private static function add_author_settings( &$settings ) {
		$settings[ Tasty_Recipes::DEFAULT_AUTHOR_LINK_OPTION ] = [
			'label' => __( 'Default Author Link', 'tasty-recipes-lite' ),
		];
	}

	/**
	 * Add Instacart settings if the user is a pro user.
	 *
	 * @param array $settings Settings array.
	 *
	 * @return void
	 */
	private static function add_instacart_settings( &$settings ) {
		$settings[ Tasty_Recipes::INSTACART_OPTION ] = [
			'label'         => __( 'Instacart Button', 'tasty-recipes-lite' ),
			'options'       => array(
				true  => __( 'On', 'tasty-recipes-lite' ),
				false => __( 'Off', 'tasty-recipes-lite' ),
			),
			'default_value' => false,
			'is_pro'        => true,
		];
	}

	/**
	 * Add ShareASale settings if the user is a pro user.
	 *
	 * @param array $settings Settings array.
	 *
	 * @return void
	 */
	private static function add_shareasale_settings( &$settings ) {
		$settings[ Tasty_Recipes::SHAREASALE_OPTION ] = [
			'label'       => __( 'ShareASale Affiliate ID', 'tasty-recipes-lite' ),
			'order'       => 200,
			'description' => sprintf(
				// translators: %1$s opening link tag, %2$s closing tag, %3$s opening link tag, %4$s closing tag.
				esc_html__( '%1$sApply for the affiliate program%2$s, or %3$sfind your affiliate ID%4$s.', 'tasty-recipes-lite' ),
				'<a href="https://www.wptasty.com/affiliate" target="_blank">',
				'</a>',
				'<a href="https://www.wptasty.com/affiliate-id" target="_blank">',
				'</a>'
			),
		];

		$shareasale = get_option( Tasty_Recipes::SHAREASALE_OPTION );
		if ( $shareasale ) {
			// Don't show powered by option if ShareASale is already enabled.
			return;
		}

		$settings[ Tasty_Recipes::POWEREDBY_OPTION ] = [
			'label'   => __( 'Show Powered By', 'tasty-recipes-lite' ),
			'options' => array(
				false => __( 'Off', 'tasty-recipes-lite' ),
				true  => __( 'On', 'tasty-recipes-lite' ),
			),
			'order'   => 199,
		];
	}

	/**
	 * Sort settings with ordering method return.
	 *
	 * @param array $first First setting array.
	 * @param array $second Second setitng array.
	 *
	 * @return int
	 */
	public static function sort_settings( $first, $second ) {
		$first['order']  = ! empty( $first['order'] ) ? $first['order'] : 0;
		$second['order'] = ! empty( $second['order'] ) ? $second['order'] : 0;

		if ( $first['order'] === $second['order'] || 0 === $second['order'] ) {
			return 0;
		}
		return $first['order'] < $second['order'] ? -1 : 1;
	}

	/**
	 * Filter field args.
	 *
	 * @param array $args Field args.
	 *
	 * @return array
	 */
	public static function filter_settings_args( $args ) {
		$args = (array) apply_filters( 'tasty_recipes_admin_settings_field_args', $args );
		if ( empty( $args['is_pro'] ) && empty( $args['pro_option'] ) ) {
			return $args;
		}

		$args['container_class'] = 'tasty-show-modal tasty-recipes-disabled';
		$utm_content             = str_replace( 'tasty_recipes_', '', sanitize_title( $args['name'] ) );
		$args['tasty_dataset']   = [
			'data-modal_id'     => 'tasty_recipes_upgrade_modal',
			'data-modal_parent' => 'tr',
			'data-replacements' => wp_json_encode(
				[
					'modal_title'         => $args['label'],
					'setting_utm_content' => $utm_content,
				]
			),
		];

		return $args;
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

		Tasty_Recipes::echo_template_part( 'settings', compact( 'tabs', 'active_tab' ) );
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
				'teeny'   => true,
				'wpautop' => true,
			)
		);
		ob_get_clean();
	}

	/**
	 * Sanitize quick links option field.
	 *
	 * @param array $item Array of items being selected.
	 *
	 * @return array
	 */
	public static function sanitize_quick_links( $item ) {
		return Utils::sanitize_item( $item, 'sanitize_key' );
	}

	/**
	 * Sanitizes the card buttons option.
	 *
	 * @param array $original Original card button option value.
	 *
	 * @return array
	 */
	public static function sanitize_card_buttons( $original ) {
		$sanitized = array();
		foreach ( array( 'first', 'second' ) as $position ) {
			if ( in_array( $original[ $position ], array( '', 'print', 'pin', 'mediavine', 'slickstream' ), true ) ) {
				$sanitized[ $position ] = $original[ $position ];
			} else {
				$sanitized[ $position ] = '';
			}
		}
		return $sanitized;
	}

	/**
	 * Sanitizes the customization option to make sure there are only expected values.
	 *
	 * @param array $original Original customization option value.
	 *
	 * @return array
	 */
	public static function sanitize_customization_option( $original ) {
		$original  = stripslashes_deep( (array) $original );
		$sanitized = array();
		$allowed   = array(
			'primary_color'          => array( __CLASS__, 'sanitize_color' ),
			'secondary_color'        => array( __CLASS__, 'sanitize_color' ),
			'icon_color'             => array( __CLASS__, 'sanitize_color' ),
			'button_color'           => array( __CLASS__, 'sanitize_color' ),
			'button_text_color'      => array( __CLASS__, 'sanitize_color' ),
			'detail_label_color'     => array( __CLASS__, 'sanitize_color' ),
			'detail_value_color'     => array( __CLASS__, 'sanitize_color' ),
			'h2_color'               => array( __CLASS__, 'sanitize_color' ),
			'h2_transform'           => array(
				'uppercase',
				'initial',
				'lowercase',
			),
			'h3_color'               => array( __CLASS__, 'sanitize_color' ),
			'h3_transform'           => array(
				'uppercase',
				'initial',
				'lowercase',
			),
			'body_color'             => array( __CLASS__, 'sanitize_color' ),
			'star_ratings_style'     => array(
				'solid',
				'outline',
			),
			'star_color'             => array( __CLASS__, 'sanitize_color' ),
			'nutrifox_display_style' => array(
				'label',
				'card',
			),
		);

		/**
		 * Filter the allowed settings.
		 *
		 * @since 1.0
		 *
		 * @param array $allowed Array of allowed settings and how to sanitize them.
		 */
		$allowed = apply_filters( 'tasty_recipes_allowed_customization_settings', $allowed );

		foreach ( $original as $key => $value ) {
			if ( ! isset( $allowed[ $key ] ) ) {
				continue;
			}

			if ( is_callable( $allowed[ $key ] ) ) {
				$sanitized[ $key ] = $allowed[ $key ]( $value );
			} elseif ( is_array( $allowed[ $key ] ) ) {
				if ( in_array( $value, $allowed[ $key ], true ) ) {
					$sanitized[ $key ] = $value;
				} else {
					$sanitized[ $key ] = '';
				}
			}
		}
		if ( ! empty( $sanitized['footer_description'] ) ) {
			$sanitized['footer_description'] = stripslashes( wpautop( $sanitized['footer_description'] ) );
		}
		return $sanitized;
	}

	/**
	 * Sanitizes a color field.
	 *
	 * @param string $color Existing color value.
	 *
	 * @return string
	 */
	private static function sanitize_color( $color ) {
		if ( empty( $color ) || is_array( $color ) ) {
			return '';
		}

		if ( false === strpos( $color, 'rgba' ) ) {
			return self::sanitize_hex_color( $color );
		}

		$color = str_replace( ' ', '', $color );
		sscanf( $color, 'rgba(%d,%d,%d,%f)', $red, $green, $blue, $alpha );
		return 'rgba(' . $red . ',' . $green . ',' . $blue . ',' . $alpha . ')';
	}

	/**
	 * This is a modified version of the WordPress core function.
	 * This one also allows for 8 digit hex colors.
	 *
	 * @since 3.12.4
	 *
	 * @param string $color The color to sanitize.
	 *
	 * @return string The sanitized color.
	 */
	private static function sanitize_hex_color( $color ) {
		// 3, 6, or 8 hex digits, or the empty string.
		if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}([A-Fa-f0-9]{2})?$|', $color ) ) {
			return $color;
		}

		return '';
	}

	/**
	 * Render an input field.
	 *
	 * @param array $args Configuration arguments used by the input field.
	 *
	 * @return void
	 */
	public static function render_input_field( $args ) {
		$defaults = array(
			'type'          => 'text',
			'name'          => '',
			'default_value' => '',
		);
		$args     = array_merge( $defaults, $args );
		if ( empty( $args['name'] ) ) {
			return;
		}
		$args['value'] = get_option( $args['name'], $args['default_value'] );

		echo '<div ';
		self::echo_container_atts( $args );
		echo '>';
		Tasty_Recipes::echo_template_part(
			'admin/settings/input',
			self::filter_settings_args( $args )
		);
		echo '</div>';
	}

	/**
	 * Render a select field.
	 *
	 * @param array $args Configuration arguments used by the select field.
	 *
	 * @return void
	 */
	public static function render_select_field( $args ) {
		$defaults = array(
			'name'          => '',
			'options'       => array(),
			'default_value' => '',
			'disabled'      => false,
			'forced_value'  => null,
		);
		$args     = array_merge( $defaults, $args );
		if ( empty( $args['name'] ) || empty( $args['options'] ) ) {
			return;
		}
		$args['value'] = ! is_null( $args['forced_value'] ) ? $args['forced_value'] : get_option( $args['name'], $args['default_value'] );

		// Make sure $value is evaluated as boolean for true/false options.
		$args['is_bool'] = false;
		if ( count( $args['options'] ) === 2 ) {
			reset( $args['options'] );
			$first = key( $args['options'] );
			end( $args['options'] );
			$last = key( $args['options'] );
			reset( $args['options'] );
			if ( is_numeric( $first ) && is_numeric( $last ) ) {
				$args['value']   = (bool) $args['value'];
				$args['is_bool'] = true;
			}
		}

		echo '<div ';
		self::echo_container_atts( $args );
		echo '>';
		Tasty_Recipes::echo_template_part(
			'admin/settings/select',
			apply_filters( 'tasty_recipes_admin_settings_field_args', $args )
		);
		echo '</div>';

		if ( ! empty( $args['after_description'] ) && is_callable( $args['after_description'] ) ) {
			$args['after_description']( $args );
		}
	}

	/**
	 * Add container around the field for modal handling.
	 *
	 * @since 1.0
	 *
	 * @param array  $args       Field args.
	 * @param string $option_key Option key.
	 *
	 * @return void
	 */
	public static function echo_container_atts( $args, $option_key = '' ) {
		if ( empty( $args['container_class'] ) ) {
			return;
		}

		$option_matches = ! $option_key || in_array( $option_key, $args['pro_option'], true );
		if ( ! $option_matches ) {
			return;
		}

		$params = [
			'class' => $args['container_class'],
		];

		if ( ! empty( $args['tasty_dataset'] ) ) {
			$params = array_merge( $params, $args['tasty_dataset'] );
		}

		Common::render_html_attributes( $params );
	}

	/**
	 * Renders multiselect checkbox field.
	 *
	 * @param array $args Field args.
	 *
	 * @return void
	 */
	public static function render_multiselect_checkboxes_field( $args ) {
		$defaults = array(
			'name'           => '',
			'options'        => array(),
			'default_values' => array(),
			'description'    => '',
		);
		$args     = array_merge( $defaults, $args );
		if ( empty( $args['name'] ) || empty( $args['options'] ) ) {
			return;
		}

		$args['checked'] = get_option( $args['name'], $args['default_values'] );

		$args['container_atts'] = [];
		if ( ! empty( $args['tasty_dataset'] ) ) {
			$args['container_atts']['tasty_dataset']   = $args['tasty_dataset'];
			$args['container_atts']['container_class'] = $args['container_class'];
			$args['container_atts']['pro_option']      = $args['pro_option'];
		}

		Tasty_Recipes::echo_template_part(
			'admin/settings/multiselect-checkbox',
			apply_filters( 'tasty_recipes_admin_settings_field_args', $args )
		);
	}

	/**
	 * Renders the card buttons field.
	 *
	 * @return void
	 */
	public static function render_card_buttons_field() {

		$options = array(
			''            => array(
				'label' => __( 'Off', 'tasty-recipes-lite' ),
			),
			'print'       => array(
				'label' => __( 'Print', 'tasty-recipes-lite' ),
			),
			'pin'         => array(
				'label'    => __( 'Pin this recipe', 'tasty-recipes-lite' ),
				'disabled' => true,
			),
			'mediavine'   => array(
				'label'    => __( 'Mediavine Grow.me Save', 'tasty-recipes-lite' ),
				'disabled' => true,
			),
			'slickstream' => array(
				'label'    => __( 'Slickstream Favorites', 'tasty-recipes-lite' ),
				'disabled' => true,
			),
		);

		Tasty_Recipes::echo_template_part(
			'admin/settings/card-buttons',
			[
				'options' => apply_filters( 'tasty_recipes_button_options', $options ),
				'value'   => Tasty_Recipes::get_card_button_settings(),
			]
		);
	}

	/**
	 * Filter quick links admin field to handle the removed item "both"
	 * and cast the string option to an array.
	 *
	 * @param array $args Admin field args.
	 *
	 * @return array
	 */
	public static function filter_quick_links_backward_compatibility( $args ) {
		if ( Tasty_Recipes::QUICK_LINKS_OPTION !== $args['name'] || empty( $args['checked'] ) ) {
			return $args;
		}

		$args['checked'] = self::handle_quick_links_backward_compatibility( $args['checked'] );

		return $args;
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
}
