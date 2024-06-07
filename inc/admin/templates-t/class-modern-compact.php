<?php
/**
 * Manages Modern Compact template.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes_Lite\Admin\Templates;

use Tasty\Framework\Traits\Singleton;

/**
 * Manages Modern Compact template.
 */
class Modern_Compact extends Abstract_Template {

	use Singleton;

	/**
	 * {@inheritDoc}
	 */
	public function get_id() {
		return 'modern-compact';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_template_name() {
		return 'Modern Compact';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_base_templates_path() {
		return dirname( TASTY_RECIPES_LITE_PLUGIN_FILE ) . '/templates/';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function is_pro() {
		return true;
	}
}
