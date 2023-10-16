<?php
/**
 * Ingredients checkboxes option.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Settings\Settings;

use Tasty\Framework\Traits\Singleton;
use Tasty\Recipes\Lite\Admin;
use Tasty\Recipes\Lite\Admin\Settings\AbstractSetting;
use Tasty\Recipes\Lite\Utils;
use TastyRecipesLiteMain;

class IngredientsCheckboxes extends AbstractSetting {

	use Singleton;

	public function get_id() {
		return TastyRecipesLiteMain::INGREDIENT_CHECKBOXES_OPTION;
	}

	public function get_group() {
		return Admin::SETTINGS_GROUP_CARD;
	}

	public function get_name() {
		return __( 'Ingredient Checkboxes', 'tasty-recipes' ) . $this->get_pro_badge();
	}

	public function sanitize( $value ) {
		return false;
	}

	protected function get_pro_feature_link() {
		return 'https://www.wptasty.com/knowledge-base/';
	}

	public function render_field( $args ) {
		$this->render_select_field( $args );
	}

	public function get_render_args() {
		$args = array(
			'name'            => $this->get_id(),
			'description'     => __( 'Allow visitors to check off recipe ingredients.', 'tasty-recipes' ),
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
		return 50;
	}
}
