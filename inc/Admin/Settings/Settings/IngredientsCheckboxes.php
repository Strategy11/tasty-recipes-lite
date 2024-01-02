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
use TastyRecipesLiteMain;

/**
 * Ingredients checkboxes setting class.
 */
class IngredientsCheckboxes extends AbstractSetting {

	use Singleton;

	/**
	 * {@inheritDoc}
	 */
	public function get_id() {
		return TastyRecipesLiteMain::INGREDIENT_CHECKBOXES_OPTION;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_group() {
		return Admin::SETTINGS_GROUP_CARD;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_setting_name() {
		return __( 'Ingredient Checkboxes', 'tasty-recipes-lite' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function is_pro() {
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sanitize( $value ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_pro_feature_link() {
		return 'https://www.wptasty.com/knowledge-base/';
	}

	/**
	 * {@inheritDoc}
	 */
	public function render_field( $args ) {
		$this->render_select_field( $args );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_render_args() {
		$args = array(
			'name'         => $this->get_id(),
			'description'  => __( 'Allow visitors to check off recipe ingredients.', 'tasty-recipes-lite' ),
			'options'      => array(
				false => __( 'Off', 'tasty-recipes-lite' ),
				true  => __( 'On', 'tasty-recipes-lite' ),
			),
			'forced_value' => false,
		);

		return $this->prepare_select_field_args( $args );
	}

	/**
	 * {@inheritDoc}
	 */
	public function ordering() {
		return 50;
	}
}
