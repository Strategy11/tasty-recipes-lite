<?php
/**
 * Manages Simple template.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Templates;

abstract class AbstractTemplate {

	abstract public function get_id();
	abstract public function get_name();

	abstract public function get_path();

}
