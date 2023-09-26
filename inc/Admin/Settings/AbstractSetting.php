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

	abstract public function render_field( $args );

	abstract public function get_render_args();

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

}
