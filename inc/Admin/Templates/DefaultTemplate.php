<?php
/**
 * Manages Simple template.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Templates;

use Tasty\Framework\Traits\Singleton;

class DefaultTemplate extends AbstractTemplate {

	use Singleton;

	public function get_id() {
		return '';
	}

	public function get_name() {
		return 'Default';
	}

	public function get_path() {
		// TODO: Implement get_path() method.
	}
}
