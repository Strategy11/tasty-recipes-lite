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

	public function get_name() {
		return __( 'Unit Conversion', 'tasty-recipes' ) . $this->get_pro_badge();
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
			'name'            => $this->get_id(),
			'description'     => __( 'Allow visitors to convert between US Customary and Metric.', 'tasty-recipes' ),
			'options'         => array(
				false => __( 'Off', 'tasty-recipes' ),
				true  => __( 'On', 'tasty-recipes' ),
			),
			'forced_value'    => false,
			'container_class' => 'tasty-show-modal-tasty_recipes_upgrade_modal tasty-recipes-disabled',
		);

		return $this->prepare_select_field_args( $args );
	}

	public function ordering() {
		return 30;
	}
}
