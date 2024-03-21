<?php
/**
 * Manages Snap template.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Templates;

use Tasty\Framework\Traits\Singleton;

class Elegant extends AbstractTemplate {

	use Singleton;

	public function get_id() {
		return 'elegant';
	}

	public function get_name() {
		return 'Elegant';
	}

	protected function get_base_templates_path() {
		return dirname( TASTY_RECIPES_LITE_PLUGIN_FILE ) . '/templates/';
	}

	protected function is_pro() {
		return true;
	}
}
