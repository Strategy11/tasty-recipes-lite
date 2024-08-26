<?php
/**
 * Manages Default template.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\Designs;

use Tasty\Framework\Traits\Singleton;

/**
 * Default template class.
 */
class Default_Template extends Abstract_Template {
	use Singleton;

	/**
	 * Sets the variables.
	 */
	public function set_vars() {
		$this->id            = '';
		$this->template_name = 'Default';
	}
}
