<?php
/**
 * Manages Snap template.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Templates;

use Tasty\Framework\Traits\Singleton;

class Snap extends AbstractTemplate {

	use Singleton;

	public function get_id() {
		return 'snap';
	}

	public function get_name() {
		return 'Snap';
	}

	public function get_path() {
		// TODO: Implement get_path() method.
	}
}
