<?php
/**
 * Share Asale option.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Settings\Settings;

use Tasty\Framework\Traits\Singleton;
use Tasty\Recipes\Lite\Admin;
use Tasty\Recipes\Lite\Admin\Settings\AbstractSetting;
use Tasty\Recipes\Lite\Utils;
use TastyRecipesLiteMain;

class ShareAsale extends AbstractSetting {

	use Singleton;

	public function get_id() {
		return TastyRecipesLiteMain::SHAREASALE_OPTION;
	}

	public function get_group() {
		return Admin::SETTINGS_GROUP_CARD;
	}

	public function get_name() {
		return __( 'ShareASale Affiliate ID', 'tasty-recipes-lite' );
	}

	public function sanitize( $value ) {
		return Utils::sanitize_item( $value, 'sanitize_text_field' );
	}

	public function render_field( $args ) {
		$this->render_input_field( $args );
	}

	public function get_render_args() {
		$args = array(
			'name'        => $this->get_id(),
			// translators: Various links.
			'description' => sprintf(
				__( '%1$sApply for the affiliate program%2$s, or %3$sfind your affiliate ID%4$s.', 'tasty-recipes-lite' ),
				'<a href="https://www.wptasty.com/affiliate" target="_blank" rel="noopener">',
				'</a>',
				'<a href="https://www.wptasty.com/affiliate-id" target="_blank" rel="noopener">',
				'</a>'
			),
		);

		return $this->prepare_input_field_args( $args );
	}

	public function ordering() {
		return 110;
	}
}
