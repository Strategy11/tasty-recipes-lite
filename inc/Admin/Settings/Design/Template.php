<?php
/**
 * Template option.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Settings\Design;

use Tasty\Framework\Traits\Singleton;
use Tasty\Recipes\Lite\Admin;
use Tasty\Recipes\Lite\Admin\Settings\AbstractSetting;
use TastyRecipesLiteMain;

class Template extends AbstractSetting {

	use Singleton;

	public function get_id() {
		return TastyRecipesLiteMain::TEMPLATE_OPTION;
	}

	public function get_group() {
		return Admin::SETTINGS_SECTION_CARD_DESIGN;
	}

	public function get_name() {
		return '';
	}

	public function sanitize( $original ) {
		return sanitize_title( $original );
	}

	public function render_field( $args ) {

	}

	public function get_render_args() {

	}
}
