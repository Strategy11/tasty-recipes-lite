<?php
/**
 * Abstract template class.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Templates;

/**
 * Design template abstract class.
 */
abstract class AbstractTemplate {

	/**
	 * Get template unique ID.
	 *
	 * @return string
	 */
	abstract public function get_id();

	/**
	 * Get template name.
	 *
	 * @return string
	 */
	abstract public function get_name();

	/**
	 * Get templates base path.
	 *
	 * @return string
	 */
	protected function get_base_templates_path() {
		return dirname( TASTY_RECIPES_LITE_PLUGIN_FILE ) . '/templates/';
	}

	/**
	 * Get current template path.
	 *
	 * @return string
	 */
	public function get_template_path() {
		if ( empty( $this->get_id() ) ) {
			return '';
		}

		return $this->get_base_templates_path() . 'designs/' . $this->get_id() . '/tasty-recipes.php';
	}

	/**
	 * Get current template style's path.
	 *
	 * @return string
	 */
	public function get_style_path() {
		if ( empty( $this->get_id() ) ) {
			return '';
		}

		return $this->get_base_templates_path() . 'designs/' . $this->get_id() . '/tasty-recipes.css';
	}

	protected function is_pro() {
		return false;
	}

}
