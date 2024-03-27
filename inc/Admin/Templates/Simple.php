<?php
/**
 * Manages Simple template.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Templates;

use Tasty\Framework\Traits\Singleton;

/**
 * Simple template class.
 */
class Simple extends AbstractTemplate {

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
