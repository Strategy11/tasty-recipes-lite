<?php
/**
 * Abstract setting class.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Settings;

use TastyRecipesLiteMain;

/**
 * Abstract setting class.
 */
abstract class AbstractSetting {

	/**
	 * Setting unique ID.
	 *
	 * @return string
	 */
	abstract public function get_id();

	/**
	 * Setting group ID.
	 *
	 * @return string
	 */
	abstract public function get_group();

	/**
	 * Setting name.
	 *
	 * @return string
	 */
	abstract protected function get_setting_name();

	/**
	 * Get utm_content for this setting.
	 *
	 * @return string
	 */
	protected function get_utm_content() {
		return str_replace( 'tasty_recipes_', '', $this->get_id() );
	}

	/**
	 * Get full setting name with the pro badge when available.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->get_setting_name() . ( $this->is_pro() ? $this->get_pro_badge() : '' );
	}

	/**
	 * If this setting is pro or not.
	 *
	 * @return bool
	 */
	protected function is_pro() {
		return false;
	}

	/**
	 * Sanitize the option value.
	 *
	 * @param mixed $option_value Option value to be sanitized.
	 *
	 * @return mixed
	 */
	public function sanitize( $option_value ) {
		return $option_value;
	}

	/**
	 * Setting ordering in settings page.
	 *
	 * @return int
	 */
	public function ordering() {
		return 0;
	}

	/**
	 * Render setting field based on args.
	 *
	 * @param array $args List of setting arguments.
	 *
	 * @return void
	 */
	public function render_field( $args ) {}

	/**
	 * Get args that is passed to render_field.
	 *
	 * @return array
	 */
	public function get_render_args() {
		return array();
	}

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
	 * Filter field args.
	 *
	 * @param array $args Field args.
	 *
	 * @return array
	 */
	public function filter_field_args( $args ) {
		if ( $this->is_pro() ) {
			$args['container_class'] = 'tasty-show-modal tasty-recipes-disabled';
			$args['tasty_datasets']  = " data-modal_id='tasty_recipes_upgrade_modal' data-replacements='" . esc_attr(
				wp_json_encode(
					array(
						'modal_title'         => $this->get_setting_name(),
						'setting_utm_content' => sanitize_title( $this->get_utm_content() ),
					)
				)
			) . "' ";
		}

		return (array) apply_filters( 'tasty_recipes_admin_settings_field_args', $args );
	}

	/**
	 * Prepare multiselect checkboxes field args.
	 *
	 * @param array $args Field args.
	 *
	 * @return array
	 */
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

	/**
	 * Prepare select field args.
	 *
	 * @param array $args Field args.
	 *
	 * @return array
	 */
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

		$value         = ! is_null( $args['forced_value'] ) ? $args['forced_value'] : $this->get_option_value( $args['default_value'] );
		$args['value'] = $value;

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

	/**
	 * Prepare input field args.
	 *
	 * @param array $args Field args.
	 *
	 * @return array
	 */
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
			$args['value'] = (string) $this->get_option_value( $args['default_value'] );
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

	/**
	 * Get setting option value.
	 *
	 * @param mixed $default Default value.
	 *
	 * @return mixed
	 */
	public function get_option_value( $default = null ) {
		$default = apply_filters( 'tasty_recipes_setting_default', $default, $this );
		return get_option( $this->get_id(), $default );
	}

	/**
	 * Get pro featured link if this is pro setting.
	 *
	 * @return string
	 */
	protected function get_pro_feature_link() {
		return 'https://www.wptasty.com/knowledge-base/getting-started-with-tasty-recipes-settings';
	}

	/**
	 * Get pro badge to be added below setting name if this is a pro setting.
	 *
	 * @return string
	 */
	protected function get_pro_badge() {
		$data = array(
			'tasty_badge_link' => $this->get_pro_feature_link(),
		);
		return TastyRecipesLiteMain::get_template_part( 'admin/settings/pro-badge', $data );
	}

}
