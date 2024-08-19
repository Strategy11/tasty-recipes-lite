<?php
/**
 * Manages Modern Compact template.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\Templates;

use Tasty\Framework\Traits\Singleton;

/**
 * Manages Modern Compact template.
 */
class Modern_Compact extends Abstract_Template {
	use Singleton;

	public function set_vars() {
		$this->id            = 'modern-compact';
		$this->template_name = 'Modern Compact';
		$this->is_pro        = true;
	}
}
