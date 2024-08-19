<?php
/**
 * Manages Snap template.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\Templates;

use Tasty\Framework\Traits\Singleton;

/**
 * Manages Snap template class.
 */
class Snap extends Abstract_Template {

	use Singleton;

	public function set_vars() {
		$this->id            = 'snap';
		$this->template_name = 'Snap';
	}
}
