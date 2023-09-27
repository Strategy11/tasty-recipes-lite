<?php
/**
 * Unit Conversion option.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Settings\Settings;

use Tasty\Framework\Traits\Singleton;
use Tasty\Recipes\Lite\Admin;
use Tasty\Recipes\Lite\Admin\Settings\AbstractSetting;
use Tasty\Recipes\Lite\Utils;
use TastyRecipesLiteMain;

class UnitConversion extends AbstractSetting {

	use Singleton;

	public function get_id() {
		return TastyRecipesLiteMain::UNIT_CONVERSION_OPTION;
	}

	public function get_group() {
		return Admin::SETTINGS_GROUP_CARD;
	}

	public function get_name() {
		return __( 'Unit Conversion', 'tasty-recipes' );
	}

	public function sanitize( $value ) {
		return false;
	}

	public function render_field( $args ) {
		$this->render_select_field( $args );
	}

	public function get_render_args() {
		return array(
			'name'              => $this->get_id(),
			'description'       => __( 'Allow visitors to convert between US Customary and Metric.', 'tasty-recipes' ),
			'options'           => array(
				false => __( 'Off', 'tasty-recipes' ),
				true  => __( 'On', 'tasty-recipes' ),
			),
		);
	}

	public function requires_pro() {
		return true;
	}
}
