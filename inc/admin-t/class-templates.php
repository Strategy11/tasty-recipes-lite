<?php
/**
 * Manages Design templates.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes_Lite\Admin;

use Tasty\Framework\Traits\Singleton;
use Tasty_Recipes_Lite\Admin\Templates\Abstract_Template;
use Tasty_Recipes_Lite\Admin\Templates\Bold;
use Tasty_Recipes_Lite\Admin\Templates\Default_Template;
use Tasty_Recipes_Lite\Admin\Templates\Elegant;
use Tasty_Recipes_Lite\Admin\Templates\Fresh;
use Tasty_Recipes_Lite\Admin\Templates\Modern_Compact;
use Tasty_Recipes_Lite\Admin\Templates\Simple;
use Tasty_Recipes_Lite\Admin\Templates\Snap;

/**
 * Design templates class.
 */
class Templates {

	use Singleton;

	/**
	 * Design template objects.
	 *
	 * @var array
	 */
	private $template_objects = null;

	/**
	 * Get filtered design template objects.
	 *
	 * @return void
	 */
	private function get_template_objects() {
		if ( ! is_null( $this->template_objects ) ) {
			return;
		}

		$template_objects       = array(
			'default'        => Default_Template::instance(),
			'simple'         => Simple::instance(),
			'snap'           => Snap::instance(),
			'bold'           => Bold::instance(),
			'fresh'          => Fresh::instance(),
			'elegant'        => Elegant::instance(),
			'modern-compact' => Modern_Compact::instance(),
		);
		$this->template_objects = (array) apply_filters( 'tasty_recipes_template_objects', $template_objects );
	}

	/**
	 * Get templates as an array, key is template ID and value is the template name.
	 *
	 * @return array
	 */
	public function get_all_templates() {
		$templates = array();
		$this->get_template_objects();

		if ( empty( $this->template_objects ) ) {
			return $templates;
		}

		foreach ( $this->template_objects as $template ) {
			$templates[ $template->get_id() ] = $template->get_name();
		}

		return $templates;
	}

	/**
	 * Get template object with its ID.
	 *
	 * @param string $template Template ID.
	 *
	 * @return Abstract_Template
	 */
	public function get_object_by_name( $template ) {
		$this->get_template_objects();

		if ( empty( $this->template_objects ) || ! isset( $this->template_objects[ $template ] ) ) {
			return Default_Template::instance();
		}

		return $this->template_objects[ $template ];
	}

}
