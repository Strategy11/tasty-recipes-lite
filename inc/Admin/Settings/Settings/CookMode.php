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

class CookMode extends AbstractSetting {

	use Singleton;

	public function get_id() {
		return TastyRecipesLiteMain::COOK_MODE_OPTION;
	}

	public function get_group() {
		return Admin::SETTINGS_GROUP_CARD;
	}

	public function get_name() {
		return __( 'Enable Cook Mode', 'tasty-recipes' ) . $this->get_pro_badge();
	}

	public function sanitize( $value ) {
		return false;
	}

	protected function get_pro_feature_link() {
		return 'https://www.wptasty.com/knowledge-base/cook-mode';
	}

	public function render_field( $args ) {
		$this->render_select_field( $args );
	}

	public function get_render_args() {
		$args = array(
			'name'         => $this->get_id(),
			'description'  => __( 'Show Cook Mode toggle to let visitors prevent sleep mode for supported devices.', 'tasty-recipes' ),
			'options'           => array(
				false => __( 'Off', 'tasty-recipes' ),
				true  => __( 'On', 'tasty-recipes' ),
			),
			'forced_value' => false,
			'container_class' => 'tasty-show-modal-tasty_recipes_upgrade_modal tasty-recipes-disabled',
		);

		return $this->prepare_select_field_args( $args );
	}

	public function ordering() {
		return 60;
	}
}
