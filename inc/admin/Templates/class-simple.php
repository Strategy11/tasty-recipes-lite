<?php
/**
 * Manages Simple template.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes_Lite\Admin\Templates;

use Tasty\Framework\Traits\Singleton;

/**
 * Simple template class.
 */
class Simple extends Abstract_Template {

	use Singleton;

	/**
	 * {@inheritDoc}
	 */
	public function get_id() {
		return 'simple';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_template_name() {
		return 'Simple';
	}
}
