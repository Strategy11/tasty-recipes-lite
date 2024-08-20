<?php
/**
 * Manages Simple template.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\Templates;

use Tasty\Framework\Traits\Singleton;

/**
 * Manages Simple template.
 */
class Simple extends Abstract_Template {
	use Singleton;

	/**
	 * Sets the variables.
	 */
	public function set_vars() {
		$this->id            = 'simple';
		$this->template_name = 'Simple';
	}
}
