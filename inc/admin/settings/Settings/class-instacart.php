<?php
/**
 * Instacart option.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes_Lite\Admin\Settings\Settings;

use Tasty\Framework\Traits\Singleton;
use Tasty_Recipes_Lite\Admin;
use Tasty_Recipes_Lite\Admin\Settings\Abstract_Setting;
use TastyRecipesLiteMain;

/**
 * Instacart setting class.
 */
class Instacart extends Abstract_Setting {

	use Singleton;

	/**
	 * {@inheritDoc}
	 */
	public function get_id() {
		return TastyRecipesLiteMain::INSTACART_OPTION;
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
		return __( 'Add an Instacart button', 'tasty-recipes-lite' );
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
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_pro_feature_link() {
		return 'https://www.wptasty.com/knowledge-base/how-to-use-instacart-in-recipes';
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
			'description'  => sprintf(
				// translators: %1$s opening anchor tag, %2$s closing tag.
				esc_html__( 'By turning this on, you agree to the %1$sInstacart terms of service%2$s, available in the US only at the moment.', 'tasty-recipes-lite' ),
				'<a target="_blank" rel="noopener" href="https://affiliate.instacart.com/terms">',
				'</a>'
			),
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
		return 90;
	}
}
