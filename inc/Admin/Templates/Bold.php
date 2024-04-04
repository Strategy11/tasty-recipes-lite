<?php
/**
 * Manages Bold template.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Templates;

use Tasty\Framework\Traits\Singleton;

/**
 * Manages Bold template.
 */
class Bold extends AbstractTemplate {

	use Singleton;

	/**
	 * {@inheritDoc}
	 */
	public function get_id() {
		return 'bold';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_template_name() {
		return 'Bold';
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
