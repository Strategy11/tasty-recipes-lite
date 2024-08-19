<?php
/**
 * Manages interactions with the settings page.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes;

use Tasty\Framework\Utils\Url;
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

	public static function load_hooks() {
		add_action( 'admin_menu', array( __CLASS__, 'register_settings' ) );
		add_filter( 'tasty_recipes_admin_settings_field_args', array( __CLASS__, 'filter_quick_links_backward_compatibility' ) );

		if ( is_admin() ) {
			add_filter( 'tasty_framework_admin_menu_items', array( __CLASS__, 'add_menu_item' ) );
		}
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
	public static function register_settings() { // phpcs:ignore SlevomatCodingStandard
		register_setting(
			self::SETTINGS_SECTION_CARD_DESIGN,
			Tasty_Recipes::CUSTOMIZATION_OPTION,
			array(
				'sanitize_callback' => array( __CLASS__, 'sanitize_customization_option' ),
			)
		);
		register_setting(
			self::SETTINGS_SECTION_CARD_DESIGN,
			Tasty_Recipes::TEMPLATE_OPTION,
			array(
				'sanitize_callback' => 'sanitize_title',
			)
		);
		register_setting(
			self::SETTINGS_GROUP_CARD,
			Tasty_Recipes::QUICK_LINKS_OPTION,
			array(
				'sanitize_callback' => array( __CLASS__, 'sanitize_quick_links' ),
			)
		);
		register_setting(
			self::SETTINGS_GROUP_CARD,
			Tasty_Recipes::QUICK_LINKS_STYLE,
			array(
				'sanitize_callback' => 'sanitize_title',
			)
		);
		register_setting(
			self::SETTINGS_GROUP_CARD,
			Tasty_Recipes::CARD_BUTTONS_OPTION,
			array(
				'sanitize_callback' => array( __CLASS__, 'sanitize_card_buttons' ),
			)
		);

		register_setting(
			self::SETTINGS_GROUP_CARD,
			Tasty_Recipes::UNIT_CONVERSION_OPTION,
			array(
				'sanitize_callback' => 'sanitize_title',
			)
		);

		register_setting(
			self::SETTINGS_GROUP_CARD,
			Tasty_Recipes::AUTOMATIC_UNIT_CONVERSION_OPTION,
			array(
				'sanitize_callback' => 'sanitize_title',
			)
		);

		register_setting(
			self::SETTINGS_GROUP_CARD,
			Tasty_Recipes::INGREDIENT_CHECKBOXES_OPTION,
			array(
				'sanitize_callback' => 'sanitize_title',
			)
		);

		register_setting(
			self::SETTINGS_GROUP_CARD,
			Tasty_Recipes::COOK_MODE_OPTION,
			array(
				'sanitize_callback' => 'sanitize_title',
			)
		);

		register_setting(
			self::SETTINGS_GROUP_CARD,
			Tasty_Recipes::COOK_MODE_HELPER_OPTION,
			array(
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		register_setting(
			self::SETTINGS_GROUP_CARD,
			Tasty_Recipes::INSTACART_OPTION,
			array(
				'sanitize_callback' => 'sanitize_title',
				'is_pro'            => true,
			)
		);

		register_setting(
			self::SETTINGS_GROUP_CARD,
			Tasty_Recipes::INSTACART_AFFILIATE_ID_OPTION,
			array(
				'sanitize_callback' => 'sanitize_title',
				'is_pro'            => true,
			)
		);

		register_setting(
			self::SETTINGS_GROUP_CARD,
			Tasty_Recipes::DISABLE_SCALING_OPTION,
			array(
				'sanitize_callback' => 'sanitize_title',
			)
		);

		register_setting(
			self::SETTINGS_GROUP_CARD,
			Tasty_Recipes::COPY_TO_CLIPBOARD_OPTION,
			array(
				'sanitize_callback' => 'sanitize_title',
			)
		);

		register_setting(
			self::SETTINGS_GROUP_CARD,
			Tasty_Recipes::DEFAULT_AUTHOR_LINK_OPTION,
			array(
				'sanitize_callback' => 'esc_url_raw',
			)
		);
		register_setting(
			self::SETTINGS_GROUP_CARD,
			Tasty_Recipes::SHAREASALE_OPTION,
			array(
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
	}

	/**
	 * Renders the Tasty Recipes settings page.
	 *
	 * @return void
	 */
	public static function handle_settings_page() { // phpcs:ignore SlevomatCodingStandard
		add_settings_section(
			self::SETTINGS_SECTION_CARD_DESIGN,
			'',
			'__return_false',
			self::PAGE_SLUG
		);
		add_settings_field(
			Tasty_Recipes::QUICK_LINKS_OPTION,
			__( 'Quick Links', 'tasty-recipes' ),
			array( __CLASS__, 'render_multiselect_checkboxes_field' ),
			self::PAGE_SLUG,
			self::SETTINGS_SECTION_CARD_DESIGN,
			array(
				'name'           => Tasty_Recipes::QUICK_LINKS_OPTION,
				'description'    => __( 'Enable or disable quick links below the post title.', 'tasty-recipes' ),
				'default_values' => array(
					'jump',
					'print',
				),
				'options'        => array(
					'jump'   => __( '\'Jump to Recipe\' link', 'tasty-recipes' ),
					'print'  => __( '\'Print Recipe\' link', 'tasty-recipes' ),
					'rating' => __( '\'Leave a Review\' link', 'tasty-recipes' ),
				),
			)
		);
		add_settings_field(
			Tasty_Recipes::QUICK_LINKS_STYLE,
			__( 'Quick Links Style', 'tasty-recipes' ),
			array( __CLASS__, 'render_select_field' ),
			self::PAGE_SLUG,
			self::SETTINGS_SECTION_CARD_DESIGN,
			array(
				'name'          => Tasty_Recipes::QUICK_LINKS_STYLE,
				'options'       => array(
					'links'   => __( 'Links', 'tasty-recipes' ),
					'buttons' => __( 'Buttons', 'tasty-recipes' ),
				),
				'default_value' => 'links',
			)
		);
		add_settings_field(
			Tasty_Recipes::CARD_BUTTONS_OPTION,
			__( 'Recipe Card Buttons', 'tasty-recipes' ),
			array( __CLASS__, 'render_card_buttons_field' ),
			self::PAGE_SLUG,
			self::SETTINGS_SECTION_CARD_DESIGN
		);
		add_settings_field(
			Tasty_Recipes::UNIT_CONVERSION_OPTION,
			__( 'Unit Conversion', 'tasty-recipes' ),
			array( __CLASS__, 'render_select_field' ),
			self::PAGE_SLUG,
			self::SETTINGS_SECTION_CARD_DESIGN,
			array(
				'name'              => Tasty_Recipes::UNIT_CONVERSION_OPTION,
				'description'       => __( 'Allow visitors to convert between US Customary and Metric.', 'tasty-recipes' ),
				'options'           => array(
					false => __( 'Off', 'tasty-recipes' ),
					true  => __( 'On', 'tasty-recipes' ),
				),
				'after_description' => function () {
					$value = get_option( Tasty_Recipes::AUTOMATIC_UNIT_CONVERSION_OPTION, false );
					echo '<p class="description tasty-recipes-automatic-unit-conversion-container" style="display:none;">';
					echo '<label>';
					echo '<input type="checkbox" name="' . esc_attr( Tasty_Recipes::AUTOMATIC_UNIT_CONVERSION_OPTION ) . '" ' .
						checked( (bool) $value, true, false ) .
						'>';
					esc_html_e( 'Automatically apply unit conversion to existing recipes.', 'tasty-recipes' );
					echo '</label>';
					echo '</p>';
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo <<<EOT
<script>
(function(){
	var select = document.querySelector('select[name="tasty_recipes_unit_conversion"]');
	var container = document.querySelector('.tasty-recipes-automatic-unit-conversion-container');
	if (1 === parseInt(select.value)) {
		container.style.display = null;
	}
	select.addEventListener('change', function() {
		if (1 === parseInt(select.value)) {
			container.style.display = null;
		} else {
			container.style.display = 'none';
		}
	});
}())
</script>
EOT;
				},
			)
		);
		add_settings_field(
			Tasty_Recipes::INGREDIENT_CHECKBOXES_OPTION,
			__( 'Ingredient Checkboxes', 'tasty-recipes' ),
			array( __CLASS__, 'render_select_field' ),
			self::PAGE_SLUG,
			self::SETTINGS_SECTION_CARD_DESIGN,
			array(
				'name'        => Tasty_Recipes::INGREDIENT_CHECKBOXES_OPTION,
				'description' => __( 'Allow visitors to check off recipe ingredients.', 'tasty-recipes' ),
				'options'     => array(
					false => __( 'Off', 'tasty-recipes' ),
					true  => __( 'On', 'tasty-recipes' ),
				),
			)
		);

		add_settings_field(
			Tasty_Recipes::COOK_MODE_OPTION,
			__( 'Enable Cook Mode', 'tasty-recipes' ),
			array( __CLASS__, 'render_select_field' ),
			self::PAGE_SLUG,
			self::SETTINGS_SECTION_CARD_DESIGN,
			array(
				'name'              => Tasty_Recipes::COOK_MODE_OPTION,
				'description'       => __( 'Show Cook Mode toggle to let visitors prevent sleep mode for supported devices.', 'tasty-recipes' ),
				'options'           => array(
					true  => __( 'On', 'tasty-recipes' ),
					false => __( 'Off', 'tasty-recipes' ),
				),
				'disabled'          => ! self::can_enable_cook_mode(),
				'default_value'     => self::can_enable_cook_mode(),
				'forced_value'      => self::can_enable_cook_mode() ? null : false,
				'after_description' => array( __CLASS__, 'show_ssl_error' ),
			)
		);

		add_settings_field(
			Tasty_Recipes::COOK_MODE_HELPER_OPTION,
			__( 'Cook Mode Description', 'tasty-recipes' ),
			array( __CLASS__, 'render_input_field' ),
			self::PAGE_SLUG,
			self::SETTINGS_SECTION_CARD_DESIGN,
			array(
				'name'          => Tasty_Recipes::COOK_MODE_HELPER_OPTION,
				'default_value' => esc_html__( 'Prevent your screen from going dark', 'tasty-recipes' ),
			)
		);

		add_settings_field(
			Tasty_Recipes::DISABLE_SCALING_OPTION,
			__( 'Scaling', 'tasty-recipes' ),
			array( __CLASS__, 'render_select_field' ),
			self::PAGE_SLUG,
			self::SETTINGS_SECTION_CARD_DESIGN,
			array(
				'name'        => Tasty_Recipes::DISABLE_SCALING_OPTION,
				'description' => __( 'Allow visitors to scale the recipe 1x, 2x, or 3x.', 'tasty-recipes' ),
				'options'     => array(
					true  => __( 'Off', 'tasty-recipes' ),
					false => __( 'On', 'tasty-recipes' ),
				),
			)
		);
		add_settings_field(
			Tasty_Recipes::COPY_TO_CLIPBOARD_OPTION,
			__( 'Copy to Clipboard', 'tasty-recipes' ),
			array( __CLASS__, 'render_select_field' ),
			self::PAGE_SLUG,
			self::SETTINGS_SECTION_CARD_DESIGN,
			array(
				'name'        => Tasty_Recipes::COPY_TO_CLIPBOARD_OPTION,
				'description' => __( 'Allow visitors to copy ingredients to clipboard.', 'tasty-recipes' ),
				'options'     => array(
					false => __( 'Off', 'tasty-recipes' ),
					true  => __( 'On', 'tasty-recipes' ),
				),
			)
		);
		add_settings_field(
			Tasty_Recipes::DEFAULT_AUTHOR_LINK_OPTION,
			__( 'Default Author Link', 'tasty-recipes' ),
			array( __CLASS__, 'render_input_field' ),
			self::PAGE_SLUG,
			self::SETTINGS_SECTION_CARD_DESIGN,
			array(
				'name' => Tasty_Recipes::DEFAULT_AUTHOR_LINK_OPTION,
			)
		);

		add_settings_field(
			Tasty_Recipes::INSTACART_OPTION,
			__( 'Add an Instacart button', 'tasty-recipes' ),
			array( __CLASS__, 'render_select_field' ),
			self::PAGE_SLUG,
			self::SETTINGS_SECTION_CARD_DESIGN,
			array(
				'name'          => Tasty_Recipes::INSTACART_OPTION,
				'description'   => sprintf(
					// translators: %1$s opening anchor tag, %2$s closing tag.
					esc_html__( 'By turning this on, you agree to the %1$sInstacart terms of service%2$s, available in the US only at the moment.', 'tasty-recipes' ),
					'<a target="_blank" rel="noopener" href="https://affiliate.instacart.com/terms">',
					'</a>'
				),
				'options'       => array(
					true  => __( 'On', 'tasty-recipes' ),
					false => __( 'Off', 'tasty-recipes' ),
				),
				'default_value' => false,
				'is_pro'        => true,
			)
		);

		add_settings_field(
			Tasty_Recipes::INSTACART_AFFILIATE_ID_OPTION,
			__( 'Instacart Tastemakers Affiliate ID', 'tasty-recipes' ),
			array( __CLASS__, 'render_input_field' ),
			self::PAGE_SLUG,
			self::SETTINGS_SECTION_CARD_DESIGN,
			array(
				'name'        => Tasty_Recipes::INSTACART_AFFILIATE_ID_OPTION,
				'description' => sprintf(
					// translators: %1$s opening anchor tag, %2$s closing tag.
					__( '%1$sGet commissions%2$s when visitors order ingredients.', 'tasty-recipes' ),
					'<a target="_blank" rel="noopener" href="https://www.wptasty.com/go/instacart">',
					'</a>'
				),
			)
		);

		add_settings_field(
			Tasty_Recipes::SHAREASALE_OPTION,
			__( 'ShareASale Affiliate ID', 'tasty-recipes' ),
			array( __CLASS__, 'render_input_field' ),
			self::PAGE_SLUG,
			self::SETTINGS_SECTION_CARD_DESIGN,
			array(
				'name'        => Tasty_Recipes::SHAREASALE_OPTION,
				'description' => sprintf(
					// translators: %1$s opening link tag, %2$s closing tag, %3$s opening link tag, %4$s closing tag.
					esc_html__( '%1$sApply for the affiliate program%2$s, or %3$sfind your affiliate ID%4$s.', 'tasty-recipes' ),
					'<a href="https://www.wptasty.com/affiliate" target="_blank">',
					'</a>',
					'<a href="https://www.wptasty.com/affiliate-id" target="_blank">',
					'</a>'
				),
			)
		);

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
			'design'     => __( 'Design', 'tasty-recipes' ),
			'settings'   => __( 'Settings', 'tasty-recipes' ),
			'converters' => __( 'Converters', 'tasty-recipes' ),
		);

		if ( ! empty( $debug ) || 'debug' === $active_tab ) {
			$add_link['debug'] = __( 'Debug', 'tasty-recipes' );
		}

		$add_link['about'] = __( 'About', 'tasty-recipes' );

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
		$value = get_option( $args['name'], $args['default_value'] );
		?>
		<input type="<?php echo esc_attr( $args['type'] ); ?>"
			name="<?php echo esc_attr( $args['name'] ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			class="regular-text"
		>
		<?php if ( ! empty( $args['description'] ) ) : ?>
			<p class="description"><?php echo wp_kses_post( $args['description'] ); ?></p>
			<?php
		endif;
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
			'primary_color'            => array( __CLASS__, 'sanitize_color' ),
			'secondary_color'          => array( __CLASS__, 'sanitize_color' ),
			'icon_color'               => array( __CLASS__, 'sanitize_color' ),
			'button_color'             => array( __CLASS__, 'sanitize_color' ),
			'button_text_color'        => array( __CLASS__, 'sanitize_color' ),
			'detail_label_color'       => array( __CLASS__, 'sanitize_color' ),
			'detail_value_color'       => array( __CLASS__, 'sanitize_color' ),
			'h2_color'                 => array( __CLASS__, 'sanitize_color' ),
			'h2_transform'             => array(
				'uppercase',
				'initial',
				'lowercase',
			),
			'h3_color'                 => array( __CLASS__, 'sanitize_color' ),
			'h3_transform'             => array(
				'uppercase',
				'initial',
				'lowercase',
			),
			'body_color'               => array( __CLASS__, 'sanitize_color' ),
			'star_ratings_style'       => array(
				'solid',
				'outline',
			),
			'star_color'               => array( __CLASS__, 'sanitize_color' ),
			'nutrifox_display_style'   => array(
				'label',
				'card',
			),
			'footer_social_platform'   => array(
				'instagram',
				'pinterest',
				'facebook',
			),
			'footer_icon_color'        => array( __CLASS__, 'sanitize_color' ),
			'footer_heading'           => 'sanitize_text_field',
			'footer_heading_color'     => array( __CLASS__, 'sanitize_color' ),
			'footer_description'       => 'wp_filter_post_kses',
			'footer_description_color' => array( __CLASS__, 'sanitize_color' ),
		);
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
		$value = ! is_null( $args['forced_value'] ) ? $args['forced_value'] : get_option( $args['name'], $args['default_value'] );

		// Make sure $value is evaluated as boolean for true/false options.
		$is_bool = false;
		if ( count( $args['options'] ) === 2 ) {
			reset( $args['options'] );
			$first = key( $args['options'] );
			end( $args['options'] );
			$last = key( $args['options'] );
			reset( $args['options'] );
			if ( is_numeric( $first ) && is_numeric( $last ) ) {
				$value   = (bool) $value;
				$is_bool = true;
			}
		}
		?>
		<select name="<?php echo esc_attr( $args['name'] ); ?>"
		<?php
		if ( ! empty( $args['disabled'] ) ) {
			echo 'disabled';
		}
		?>
		>
		<?php
		foreach ( $args['options'] as $key => $label ) :
			$compare_key = $key;
			if ( $is_bool ) {
				$compare_key = (bool) $key;
			}
			?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $compare_key, $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php if ( ! empty( $args['description'] ) ) : ?>
			<p class="description"><?php echo wp_kses_post( $args['description'] ); ?></p>
			<?php
			endif;
		if ( ! empty( $args['after_description'] ) && is_callable( $args['after_description'] ) ) {
			$args['after_description']( $args );
		}
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
				'label' => __( 'Off', 'tasty-recipes' ),
			),
			'print'       => array(
				'label' => __( 'Print', 'tasty-recipes' ),
			),
			'pin'         => array(
				'label' => __( 'Pin this recipe', 'tasty-recipes' ),
			),
			'mediavine'   => array(
				'label' => __( 'Mediavine Grow.me Save', 'tasty-recipes' ),
			),
			'slickstream' => array(
				'label'    => __( 'Slickstream Favorites', 'tasty-recipes' ),
				'disabled' => true,
			),
		);

		$options = apply_filters( 'tasty_recipes_button_options', $options );

		$value = Tasty_Recipes::get_card_button_settings();
		?>
		<div>
			<?php foreach ( array( 'first', 'second' ) as $position ) : ?>
				<select name="<?php echo esc_attr( Tasty_Recipes::CARD_BUTTONS_OPTION . '[' . $position . ']' ); ?>">
				<?php foreach ( $options as $key => $option ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"
						<?php selected( $key, $value[ $position ] ); ?>
						<?php echo empty( $option['disabled'] ) ? '' : ' disabled'; ?>
						>
						<?php echo esc_html( $option['label'] ); ?>
					</option>
					<?php endforeach; ?>
				</select>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Check if Cook Mode can be enabled in this site or not.
	 *
	 * @since 3.8
	 *
	 * @return bool
	 */
	public static function can_enable_cook_mode() {
		/**
		 * Filters the capability of enabling cook mode be enabled in this site.
		 *
		 * @param bool $can_enable True to enable, false otherwise.
		 */
		return (bool) apply_filters( 'tasty_recipes_enable_cook_mode', is_ssl() );
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
		if ( Tasty_Recipes::QUICK_LINKS_OPTION !== $args['name'] ) {
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
