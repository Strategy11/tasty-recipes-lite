<?php
/**
 * Manages Design templates.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin;

use Tasty\Framework\Traits\Singleton;
use Tasty\Recipes\Lite\Admin\Templates\AbstractTemplate;
use Tasty\Recipes\Lite\Admin\Templates\Bold;
use Tasty\Recipes\Lite\Admin\Templates\DefaultTemplate;
use Tasty\Recipes\Lite\Admin\Templates\Elegant;
use Tasty\Recipes\Lite\Admin\Templates\Fresh;
use Tasty\Recipes\Lite\Admin\Templates\ModernCompact;
use Tasty\Recipes\Lite\Admin\Templates\Simple;
use Tasty\Recipes\Lite\Admin\Templates\Snap;

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
			'default'        => DefaultTemplate::instance(),
			'simple'         => Simple::instance(),
			'snap'           => Snap::instance(),
			'bold'           => Bold::instance(),
			'fresh'          => Fresh::instance(),
			'elegant'        => Elegant::instance(),
			'modern-compact' => ModernCompact::instance(),
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
	 * @return AbstractTemplate
	 */
	public function get_object_by_name( $template ) {
		$this->get_template_objects();

		if ( empty( $this->template_objects ) || ! isset( $this->template_objects[ $template ] ) ) {
			return DefaultTemplate::instance();
		}

		return $this->template_objects[ $template ];
	}

}
