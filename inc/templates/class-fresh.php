<?php
/**
 * Manages Fresh template.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\Templates;

use Tasty\Framework\Traits\Singleton;

/**
 * Manages Fresh template.
 */
class Fresh extends Abstract_Template {
	use Singleton;

	/**
	 * Sets the variables.
	 */
	public function set_vars() {
		$this->id            = 'fresh';
		$this->template_name = 'Fresh';
		$this->is_pro        = true;
	}
}
