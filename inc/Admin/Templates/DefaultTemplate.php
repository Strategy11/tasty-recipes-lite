<?php
/**
 * Manages Default template.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Templates;

use Tasty\Framework\Traits\Singleton;

/**
 * Default template class.
 */
class DefaultTemplate extends AbstractTemplate {

	use Singleton;

	/**
	 * {@inheritDoc}
	 */
	public function get_id() {
		return '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_template_name() {
		return 'Default';
	}
}
