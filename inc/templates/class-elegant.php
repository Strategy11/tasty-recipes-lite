<?php
/**
 * Manages Elegant template.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\Templates;

use Tasty\Framework\Traits\Singleton;

/**
 * Manages Elegant template.
 */
class Elegant extends Abstract_Template {
	use Singleton;

	public function set_vars() {
		$this->id            = 'elegant';
		$this->template_name = 'Elegant';
		$this->is_pro        = true;
	}
}
