<?php
/**
 * Abstract template class.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Templates;

abstract class AbstractTemplate {

	abstract public function get_id();
	abstract public function get_name();

	protected function get_base_templates_path() {
		return dirname( TASTY_RECIPES_LITE_PLUGIN_FILE ) . '/templates/';
	}

	public function get_template_path() {
		if ( empty( $this->get_id() ) ) {
			return '';
		}

		return $this->get_base_templates_path() . 'designs/' . $this->get_id() . '/tasty-recipes.php';
	}

	public function get_style_path() {
		if ( empty( $this->get_id() ) ) {
			return '';
		}

		return $this->get_base_templates_path() . 'designs/' . $this->get_id() . '/tasty-recipes.css';
	}

}
