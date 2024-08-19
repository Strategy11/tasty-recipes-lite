<?php
/**
 * Manages Default template.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\Templates;

use Tasty\Framework\Traits\Singleton;

/**
 * Default template class.
 */
class Default_Template extends Abstract_Template {

	use Singleton;

	public function set_vars() {
		$this->id            = '';
		$this->template_name = 'Default';
	}
}
