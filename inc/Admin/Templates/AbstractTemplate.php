<?php
/**
 * Abstract template class.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Templates;

use TastyRecipesLiteMain;

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
	abstract public function get_template_name();

	/**
	 * Get full template name with the pro badge when available.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->get_template_name() . __( ' Template', 'tasty-recipes-lite' ) . ( $this->is_pro() ? $this->get_pro_badge() : '' );
	}

	protected function get_template_plugin() {
		return TASTY_RECIPES_LITE_PLUGIN_FILE;
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

	public function get_template_url() {
		if ( empty( $this->get_id() ) ) {
			return '';
		}
		return plugins_url( 'templates/designs/' . $this->get_id() . '/', $this->get_template_plugin() );
	}

	protected function is_pro() {
		return false;
	}

	/**
	 * Get pro featured link if this is pro setting.
	 *
	 * @return string
	 */
	protected function get_pro_feature_link() {
		return 'https://www.wptasty.com/knowledge-base/what-do-the-recipe-card-templates-look-like';
	}

	/**
	 * Get pro badge to be added below setting name if this is a pro setting.
	 *
	 * @return string
	 */
	protected function get_pro_badge() {
		$data = array(
			'tasty_badge_link'    => $this->get_pro_feature_link(),
			'tasty_badge_keyword' => __( 'Premium', 'tasty-recipes-lite' ),
		);
		return TastyRecipesLiteMain::get_template_part( 'admin/pro-badge', $data );
	}

}
