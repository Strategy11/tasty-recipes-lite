<?php
/**
 * Automatic Unit Conversion option.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Settings\Settings;

use Tasty\Framework\Traits\Singleton;
use Tasty\Recipes\Lite\Admin;
use Tasty\Recipes\Lite\Admin\Settings\AbstractSetting;
use TastyRecipesLiteMain;

class AutomaticUnitConversion extends AbstractSetting {

	use Singleton;

	public function get_id() {
		return TastyRecipesLiteMain::AUTOMATIC_UNIT_CONVERSION_OPTION;
	}

	public function get_group() {
		return Admin::SETTINGS_GROUP_CARD;
	}

	public function get_name() {
		return '';
	}

	public function sanitize( $value ) {
		return false;
	}

	public function render_field( $args ) {
		$this->render_input_field( $args );
	}

	public function get_render_args() {
		return array(
			'name'        => $this->get_id(),
			'type'        => 'checkbox',
			'label_after'       => __( 'Automatically apply unit conversion to existing recipes.', 'tasty-recipes' ),
			'disabled' => true,
			'class' => 'tasty-show-upgrade-modal',
		);
	}
}
