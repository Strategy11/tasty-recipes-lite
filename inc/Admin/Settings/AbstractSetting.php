<?php
/**
 * Abstract setting class.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Settings;

use TastyRecipesLiteMain;

abstract class AbstractSetting {

	abstract public function get_id();
	abstract public function get_group();

	abstract public function get_name();

	public function sanitize( $option_value ) {
		return $option_value;
	}

	public function render_field( $args ) {}

	public function get_render_args(){}

	/**
	 * Sanitizes a color field.
	 *
	 * @param string $color Existing color value.
	 * @return string
	 */
	protected function sanitize_color( $color ) {
		if ( empty( $color ) || is_array( $color ) ) {
			return '';
		}

		if ( false === strpos( $color, 'rgba' ) ) {
			return sanitize_hex_color( $color );
		}

		$color = str_replace( ' ', '', $color );
		sscanf( $color, 'rgba(%d,%d,%d,%f)', $red, $green, $blue, $alpha );
		return 'rgba(' . $red . ',' . $green . ',' . $blue . ',' . $alpha . ')';
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

		TastyRecipesLiteMain::echo_template_part(
			'admin/settings/multiselect-checkbox',
			apply_filters( 'tasty_recipes_admin_settings_field_args', $args )
		);
	}

	/**
	 * Render a select field.
	 *
	 * @param array $args Configuration arguments used by the select field.
	 */
	public function render_select_field( $args ) {
		$defaults = array(
			'name'          => '',
			'options'       => array(),
			'default_value' => '',
			'disabled'      => false,
			'forced_value'  => null,
		);
		$args     = wp_parse_args( $args, $defaults );

		if ( empty( $args['name'] ) || empty( $args['options'] ) ) {
			return;
		}

		$value = ! is_null( $args['forced_value'] ) ? $args['forced_value'] : $this->get_option_value( $args['default_value'] );

		// Make sure $value is evaluated as boolean for true/false options.
		$args['is_bool'] = false;
		if ( count( $args['options'] ) === 2 ) {
			reset( $args['options'] );
			$first = key( $args['options'] );
			end( $args['options'] );
			$last = key( $args['options'] );
			reset( $args['options'] );
			if ( is_numeric( $first ) && is_numeric( $last ) ) {
				$args['value']   = (bool) $value;
				$args['is_bool'] = true;
			}
		}

		TastyRecipesLiteMain::echo_template_part( 'admin/settings/select', $args );
	}

	/**
	 * Render an input field.
	 *
	 * @param array $args Configuration arguments used by the input field.
	 */
	public function render_input_field( $args ) {
		$defaults = array(
			'type'          => 'text',
			'name'          => '',
			'default_value' => '',
		);
		$args     = wp_parse_args( $args, $defaults );
		if ( empty( $args['name'] ) ) {
			return;
		}
		$args['value'] = $this->get_option_value( $args['default_value'] );

		TastyRecipesLiteMain::echo_template_part( 'admin/settings/input', $args );
	}

	public function get_option_value( $default = null ) {
		$default = apply_filters( 'tasty_recipes_setting_default', $default, $this );
		return get_option( $this->get_id(), $default );
	}

}
