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

class Instacart extends AbstractSetting {

	use Singleton;

	public function get_id() {
		return TastyRecipesLiteMain::INSTACART_OPTION;
	}

	public function get_group() {
		return Admin::SETTINGS_GROUP_CARD;
	}

	public function get_name() {
		return __( 'Add an Instacart button', 'tasty-recipes' ) . $this->get_pro_badge();
	}

	public function sanitize( $value ) {
		return true;
	}

	protected function get_pro_feature_link() {
		return 'https://www.wptasty.com/knowledge-base/how-to-use-instacart-in-recipes';
	}

	public function render_field( $args ) {
		$this->render_select_field( $args );
	}

	public function get_render_args() {
		$args = array(
			'name'         => $this->get_id(),
			'description'  => sprintf(
				// translators: %1$s opening anchor tag, %2$s closing tag.
				esc_html__( 'By turning this on, you agree to the %1$sInstacart terms of service%2$s, available in the US only at the moment.', 'tasty-recipes' ),
				'<a target="_blank" rel="noopener" href="https://affiliate.instacart.com/terms">',
				'</a>'
			),
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
		return 90;
	}
}
