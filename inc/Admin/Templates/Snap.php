<?php
/**
 * Manages Snap template.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Templates;

use Tasty\Framework\Traits\Singleton;

/**
 * Manages Snap template class.
 */
class Snap extends AbstractTemplate {

	use Singleton;

	/**
	 * {@inheritDoc}
	 */
	public function get_id() {
		return 'snap';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_template_name() {
		return 'Snap';
	}
}
