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

class CopyToClipboard extends AbstractSetting {

	use Singleton;

	public function get_id() {
		return TastyRecipesLiteMain::COPY_TO_CLIPBOARD_OPTION;
	}

	public function get_group() {
		return Admin::SETTINGS_GROUP_CARD;
	}

	public function get_name() {
		return __( 'Copy to Clipboard', 'tasty-recipes' );
	}

	public function sanitize( $value ) {
		return Utils::sanitize_item( $value, 'sanitize_title' );
	}

	public function render_field( $args ) {
		$this->render_select_field( $args );
	}

	public function get_render_args() {
		return array(
			'name'        => $this->get_id(),
			'description' => __( 'Allow visitors to copy ingredients to clipboard.', 'tasty-recipes' ),
			'options'     => array(
				false => __( 'Off', 'tasty-recipes' ),
				true  => __( 'On', 'tasty-recipes' ),
			),
			'value' => $this->get_option_value(),
		);
	}

	public function ordering() {
		return 100;
	}
}
