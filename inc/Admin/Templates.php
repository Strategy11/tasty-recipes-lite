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

	public function get_all_templates() {
		$templates = array();

		$template_objects = array(
			DefaultTemplate::instance(),
			Simple::instance(),
			Snap::instance(),
		);
		$template_objects = (array) apply_filters( 'tasty_recipes_template_objects', $template_objects );
		if ( empty( $template_objects ) ) {
			return $templates;
		}

		foreach ( $template_objects as $template ) {
			$templates[ $template->get_id() ] = $template->get_name();
		}

		return $templates;
	}

}
