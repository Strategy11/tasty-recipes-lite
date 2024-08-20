<?php
/**
 * Manages Bold template.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\Templates;

use Tasty\Framework\Traits\Singleton;

/**
 * Manages Bold template.
 */
class Bold extends Abstract_Template {
	use Singleton;

	/**
	 * Sets the variables.
	 */
	public function set_vars() {
		$this->id            = 'bold';
		$this->template_name = 'Bold';
		$this->is_pro        = true;
	}
}
