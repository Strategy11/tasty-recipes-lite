<?php
/**
 * Copy to clipboard option.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Settings\Settings;

use Tasty\Framework\Traits\Singleton;
use Tasty\Recipes\Lite\Admin;
use Tasty\Recipes\Lite\Admin\Settings\AbstractSetting;
use Tasty\Recipes\Lite\Utils;
use TastyRecipesLiteMain;

/**
 * Copy to clipboard setting class.
 */
class CopyToClipboard extends AbstractSetting {

	use Singleton;

	/**
	 * {@inheritDoc}
	 */
	public function get_id() {
		return TastyRecipesLiteMain::COPY_TO_CLIPBOARD_OPTION;
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
		return __( 'Copy to Clipboard', 'tasty-recipes-lite' );
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
			'name'        => $this->get_id(),
			'description' => __( 'Allow visitors to copy ingredients to clipboard.', 'tasty-recipes-lite' ),
			'options'     => array(
				false => __( 'Off', 'tasty-recipes-lite' ),
				true  => __( 'On', 'tasty-recipes-lite' ),
			),
			'value'       => $this->get_option_value(),
		);

		return $this->prepare_select_field_args( $args );
	}

	/**
	 * {@inheritDoc}
	 */
	public function ordering() {
		return 100;
	}
}
