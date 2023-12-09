<?php
/**
 * Scaling option.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Settings\Settings;

use Tasty\Framework\Traits\Singleton;
use Tasty\Recipes\Lite\Admin;
use Tasty\Recipes\Lite\Admin\Settings\AbstractSetting;
use TastyRecipesLiteMain;

/**
 * Scaling setting class.
 */
class DisableScaling extends AbstractSetting {

	use Singleton;

	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return TastyRecipesLiteMain::DISABLE_SCALING_OPTION;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_group() {
		return Admin::SETTINGS_GROUP_CARD;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_setting_name() {
		return __( 'Scaling', 'tasty-recipes-lite' );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function is_pro() {
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sanitize( $value ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_pro_feature_link() {
		return 'https://www.wptasty.com/knowledge-base/how-to-disable-recipe-scaling-on-a-single-recipe';
	}

	/**
	 * {@inheritdoc}
	 */
	public function render_field( $args ) {
		$this->render_select_field( $args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_render_args() {
		$args = array(
			'name'         => $this->get_id(),
			'description'  => __( 'Allow visitors to scale the recipe 1x, 2x, or 3x.', 'tasty-recipes-lite' ),
			'options'      => array(
				true  => __( 'Off', 'tasty-recipes-lite' ),
				false => __( 'On', 'tasty-recipes-lite' ),
			),
			'forced_value' => true,
		);

		return $this->prepare_select_field_args( $args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function ordering() {
		return 70;
	}
}
