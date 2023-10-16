<?php
/**
 * Manages Design templates.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin;

use Tasty\Framework\Traits\Singleton;
use Tasty\Recipes\Lite\Admin\Templates\DefaultTemplate;
use Tasty\Recipes\Lite\Admin\Templates\Simple;
use Tasty\Recipes\Lite\Admin\Templates\Snap;

class Templates {

	use Singleton;

	private $template_objects = null;

	private function get_template_objects() {
		if ( ! is_null( $this->template_objects ) ) {
			return;
		}

		$template_objects       = array(
			'default' => DefaultTemplate::instance(),
			'simple'  => Simple::instance(),
			'snap'    => Snap::instance(),
		);
		$this->template_objects = (array) apply_filters( 'tasty_recipes_template_objects', $template_objects );
	}

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

	public function get_object_by_name( $template ) {
		$this->get_template_objects();

		if ( empty( $this->template_objects ) || ! isset( $this->template_objects[ $template ] ) ) {
			return DefaultTemplate::instance();
		}

		return $this->template_objects[ $template ];
	}

}
