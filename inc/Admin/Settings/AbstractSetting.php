<?php
/**
 * Abstract setting class.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Settings;

use Tasty\Framework\Utils\Url;
use TastyRecipesLiteMain;

abstract class AbstractSetting {

	abstract public function get_id();
	abstract public function get_group();

	abstract public function get_name();

	public function sanitize( $option_value ) {
		return $option_value;
	}

	public function ordering() {
		return 0;
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

	public function filter_field_args( $args ) {
		return (array) apply_filters( 'tasty_recipes_admin_settings_field_args', $args );
	}

	public function prepare_multiselect_checkboxes_field_args( $args ) {
		$defaults = array(
			'name'           => '',
			'options'        => array(),
			'default_values' => array(),
			'description'    => '',
		);
		$args     = array_merge( $defaults, $args );
		if ( empty( $args['name'] ) || empty( $args['options'] ) ) {
			return $defaults;
		}

		$args['checked'] = get_option( $args['name'], $args['default_values'] );

		return $args;
	}

	public function prepare_select_field_args( $args ) {
		$defaults = array(
			'name'          => '',
			'options'       => array(),
			'default_value' => '',
			'disabled'      => false,
			'forced_value'  => null,
		);
		$args     = wp_parse_args( $args, $defaults );

		if ( empty( $args['name'] ) || empty( $args['options'] ) ) {
			return $args;
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
		return $args;
	}

	/**
	 * Render a select field.
	 *
	 * @param array $args Configuration arguments used by the select field.
	 */
	public function render_select_field( $args ) {
		TastyRecipesLiteMain::echo_template_part( 'admin/settings/select', $args );
	}

	public function prepare_input_field_args( $args ) {
		$defaults = array(
			'type'          => 'text',
			'name'          => '',
			'default_value' => '',
		);
		$args     = wp_parse_args( $args, $defaults );
		if ( empty( $args['name'] ) ) {
			return $args;
		}
		if ( ! isset( $args['value'] ) ) {
			$args['value'] = $this->get_option_value( $args['default_value'] );
		}

		return $args;
	}

	/**
	 * Render an input field.
	 *
	 * @param array $args Configuration arguments used by the input field.
	 */
	public function render_input_field( $args ) {
		TastyRecipesLiteMain::echo_template_part( 'admin/settings/input', $args );
	}

	public function get_option_value( $default = null ) {
		$default = apply_filters( 'tasty_recipes_setting_default', $default, $this );
		return get_option( $this->get_id(), $default );
	}

	protected function get_pro_feature_link() {
		return 'https://www.wptasty.com/knowledge-base/getting-started-with-tasty-recipes-settings';
	}

	protected function get_pro_badge() {
		$data = array(
			'tasty_badge_link' => Url::add_utm_params(
				$this->get_pro_feature_link(), array(
					'utm_medium'  => 'settings',
					'utm_content' => 'lite',
				)
			),
		);
		return TastyRecipesLiteMain::get_template_part( 'admin/settings/pro-badge', $data );
	}

}
