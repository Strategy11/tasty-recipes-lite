<?php
/**
 * Manages Default template.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes_Lite\Admin\Templates;

use Tasty\Framework\Traits\Singleton;

/**
 * Default template class.
 */
class Default_Template extends Abstract_Template {

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
