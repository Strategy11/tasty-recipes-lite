<?php
/**
 * Default author link option.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Settings\Settings;

use Tasty\Framework\Traits\Singleton;
use Tasty\Recipes\Lite\Admin;
use Tasty\Recipes\Lite\Admin\Settings\AbstractSetting;
use Tasty\Recipes\Lite\Utils;
use TastyRecipesLiteMain;

class DefaultAuthorLink extends AbstractSetting {

	use Singleton;

	public function get_id() {
		return TastyRecipesLiteMain::DEFAULT_AUTHOR_LINK_OPTION;
	}

	public function get_group() {
		return Admin::SETTINGS_GROUP_CARD;
	}

	public function get_name() {
		return __( 'Default Author Link', 'tasty-recipes' );
	}

	public function sanitize( $value ) {
		return Utils::sanitize_item( $value, 'esc_url_raw' );
	}

	public function render_field( $args ) {
		$this->render_input_field( $args );
	}

	public function get_render_args() {
		$args = array(
			'name' => $this->get_id(),
			'type' => 'text',
			'field_class' => 'regular-text',
		);

		return $this->prepare_input_field_args( $args );
	}

	public function ordering() {
		return 80;
	}
}
