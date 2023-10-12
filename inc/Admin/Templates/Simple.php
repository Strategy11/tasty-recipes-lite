<?php
/**
 * Manages Simple template.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Templates;

use Tasty\Framework\Traits\Singleton;

class Simple extends AbstractTemplate {

	use Singleton;

	public function get_id() {
		return 'simple';
	}

	public function get_name() {
		return 'Simple';
	}
}
