<?php
/**
 * Quick Links style.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes_Lite\Admin\Settings\Settings;

use Tasty\Framework\Traits\Singleton;
use Tasty_Recipes_Lite\Admin;
use Tasty_Recipes_Lite\Admin\Settings\Abstract_Setting;
use Tasty_Recipes_Lite\Utils;
use TastyRecipesLiteMain;

/**
 * Quick Links setting class.
 */
class Quick_Links_Style extends Abstract_Setting {

	use Singleton;

	/**
	 * {@inheritDoc}
	 */
	public function get_id() {
		return TastyRecipesLiteMain::QUICK_LINKS_STYLE;
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
		return __( 'Quick Links Style', 'tasty-recipes-lite' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function sanitize( $value ) {
		return Utils::sanitize_item( $value, 'sanitize_title' );
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
			'name'          => $this->get_id(),
			'default_value' => 'links',
			'options'       => array(
				'links'   => __( 'Links', 'tasty-recipes-lite' ),
				'buttons' => __( 'Buttons', 'tasty-recipes-lite' ),
			),
		);
		return $this->prepare_select_field_args( $args );
	}

	/**
	 * {@inheritDoc}
	 */
	public function ordering() {
		return 11;
	}
}
