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
use TastyRecipesLiteMain;

class UnitConversion extends AbstractSetting {

	use Singleton;

	public function get_id() {
		return TastyRecipesLiteMain::UNIT_CONVERSION_OPTION;
	}

	public function get_group() {
		return Admin::SETTINGS_GROUP_CARD;
	}

	protected function get_setting_name() {
		return __( 'Unit Conversion', 'tasty-recipes-lite' );
	}

	protected function is_pro() {
		return true;
	}

	protected function get_pro_feature_link() {
		return 'https://www.wptasty.com/knowledge-base/how-to-enable-unit-conversions-for-recipe-ingredients';
	}

	public function sanitize( $value ) {
		return false;
	}

	public function render_field( $args ) {
		$this->render_select_field( $args );
	}

	public function get_render_args() {
		$args = array(
			'name'         => $this->get_id(),
			'description'  => __( 'Allow visitors to convert between US Customary and Metric.', 'tasty-recipes-lite' ),
			'options'      => array(
				false => __( 'Off', 'tasty-recipes-lite' ),
				true  => __( 'On', 'tasty-recipes-lite' ),
			),
			'forced_value' => false,
		);

		return $this->prepare_select_field_args( $args );
	}

	public function ordering() {
		return 30;
	}
}
