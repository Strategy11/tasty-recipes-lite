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
use Tasty_Recipes\Designs\Template;

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
		add_action( 'update_option_' . Tasty_Recipes::TEMPLATE_OPTION, array( __CLASS__, 'maybe_remove_default_colors' ), 10, 2 );
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
	 * Maybe remove default colors when the template is changed.
	 * 
	 * @since x.x
	 *
	 * @param string $old_value Old value.
	 * @param string $new_value New value.
	 * 
	 * @return void
	 */
	public static function maybe_remove_default_colors( $old_value, $new_value ) {
		$customizations = get_option( Tasty_Recipes::CUSTOMIZATION_OPTION, array() );

		if ( $old_value === $new_value || empty( $customizations ) ) {
			return;
		}

		$old_default_colors = Template::get_default_colors_for_template( $old_value );
		
		foreach ( $old_default_colors as $key => $value ) {
			if ( isset( $customizations[ $key ] ) && $customizations[ $key ] === $value ) {
				unset( $customizations[ $key ] );
			}
		}

		update_option( Tasty_Recipes::CUSTOMIZATION_OPTION, $customizations );
	}

	/**
	 * Sanitize title.
	 * 
	 * @since 1.0.1
	 *
	 * @param mixed $value Value to sanitize.
	 *
	 * @return string
	 */
	public static function safe_sanitize_title( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}

		return sanitize_title( $value );
	}

	/**
	 * Registers used settings.
	 *
	 * @return void
	 */
	public static function register_settings() {
		$sanitize_title = [
			'sanitize_callback' => array( __CLASS__, 'safe_sanitize_title' ),
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

		$settings = array(
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
		);

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

		self::show_settings();
		if ( ! did_action( 'admin_footer' ) && ! doing_action( 'admin_footer' ) ) {
			add_action( 'admin_footer', array( __CLASS__, 'action_admin_footer_render_template' ) );
		}
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

		$add_link['about'] = __( 'Get Started', 'tasty-recipes-lite' );

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
		
		$allowed = array(
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
		$allowed        = apply_filters( 'tasty_recipes_allowed_customization_settings', $allowed );
		$default_colors = Template::get_default_colors_for_template( get_option( Tasty_Recipes::TEMPLATE_OPTION, 'default' ) );
	
		foreach ( $original as $key => $value ) {
			if ( ! isset( $allowed[ $key ] ) ) {
				continue;
			}
	
			if ( is_callable( $allowed[ $key ] ) ) {
				$sanitized_value = $allowed[ $key ]( $value );

				if ( isset( $default_colors[ $key ] ) && $sanitized_value === $default_colors[ $key ] ) {
					continue;
				}
				
				$sanitized[ $key ] = $sanitized_value;
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
