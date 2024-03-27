<?php
/**
 * Manages Snap template.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Templates;

use Tasty\Framework\Traits\Singleton;

class Fresh extends AbstractTemplate {

	use Singleton;

	public function get_id() {
		return 'fresh';
	}

	public function get_template_name() {
		return 'Fresh';
	}

	protected function get_base_templates_path() {
		return dirname( TASTY_RECIPES_LITE_PLUGIN_FILE ) . '/templates/';
	}

	protected function is_pro() {
		return true;
	}
}
