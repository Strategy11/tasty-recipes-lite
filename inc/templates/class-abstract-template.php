<?php
/**
 * Abstract template class.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes\Templates;

use Tasty_Recipes;

/**
 * Design template abstract class.
 */
abstract class Abstract_Template {

	/**
	 * Template ID.
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Template name.
	 *
	 * @var string
	 */
	protected $template_name = '';

	/**
	 * If this is a pro template.
	 *
	 * @var bool
	 */
	protected $is_pro = false;

	/**
	 * Get template unique ID.
	 *
	 * @return string
	 */
	public function get_id() {
		$this->set_vars();
		return $this->id;
	}

	/**
	 * Get template name.
	 *
	 * @return string
	 */
	public function get_template_name() {
		$this->set_vars();
		return $this->template_name;
	}

	/**
	 * If current template is pro or lite.
	 *
	 * @return false
	 */
	public function is_pro() {
		$this->set_vars();
		return $this->is_pro;
	}

	/**
	 * Get current template plugin file.
	 *
	 * @return string
	 */
	protected function get_template_plugin() {
		return TASTY_RECIPES_LITE_FILE;
	}

	/**
	 * Get templates base path.
	 *
	 * @return string
	 */
	private function get_base_templates_path() {
		return dirname( $this->get_template_plugin() ) . '/templates/';
	}

	/**
	 * Get current template path.
	 *
	 * @return string
	 */
	public function get_template_path() {
		if ( empty( $this->id ) ) {
			return '';
		}

		return $this->get_base_templates_path() . 'designs/' . $this->id . '/tasty-recipes.php';
	}

	/**
	 * Get current template style's path.
	 *
	 * @return string
	 */
	public function get_style_path() {
		if ( empty( $this->id ) ) {
			return '';
		}

		return $this->get_base_templates_path() . 'designs/' . $this->id . '/tasty-recipes.css';
	}

	/**
	 * Get current template design url.
	 *
	 * @return string
	 */
	public function get_template_url() {
		if ( empty( $this->id ) ) {
			return '';
		}
		return plugins_url( 'templates/designs/' . $this->id . '/', $this->get_template_plugin() );
	}
}
