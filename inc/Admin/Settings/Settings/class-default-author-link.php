<?php
/**
 * Default author link option.
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
 * Default author link setting.
 */
class Default_Author_Link extends Abstract_Setting {

	use Singleton;

	/**
	 * {@inheritDoc}
	 */
	public function get_id() {
		return TastyRecipesLiteMain::DEFAULT_AUTHOR_LINK_OPTION;
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
		return __( 'Default Author Link', 'tasty-recipes-lite' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function sanitize( $value ) {
		return Utils::sanitize_item( $value, 'esc_url_raw' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function render_field( $args ) {
		$this->render_input_field( $args );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_render_args() {
		$args = array(
			'name'        => $this->get_id(),
			'type'        => 'text',
			'field_class' => 'regular-text',
		);

		return $this->prepare_input_field_args( $args );
	}

	/**
	 * {@inheritDoc}
	 */
	public function ordering() {
		return 80;
	}
}
