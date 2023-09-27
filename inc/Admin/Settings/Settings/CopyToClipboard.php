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
		return '';
	}

	public function sanitize( $value ) {
		return Utils::sanitize_item( $value, 'sanitize_title' );
	}

	public function render_field( $args ) {
		// TODO: Implement render_field() method.
	}

	public function get_render_args() {
		// TODO: Implement get_render_args() method.
	}
}
