<?php
/**
 * Template option.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Settings\Design;

use Tasty\Framework\Traits\Singleton;
use Tasty\Recipes\Lite\Admin;
use Tasty\Recipes\Lite\Admin\Settings\AbstractSetting;
use TastyRecipesLiteMain;

class Template extends AbstractSetting {

	use Singleton;

	/**
	 * Setting unique ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return TastyRecipesLiteMain::TEMPLATE_OPTION;
	}

	/**
	 * Setting group ID.
	 *
	 * @return string
	 */
	public function get_group() {
		return Admin::SETTINGS_SECTION_CARD_DESIGN;
	}

	/**
	 * Setting name.
	 *
	 * @return string
	 */
	protected function get_setting_name() {
		return '';
	}

	/**
	 * Sanitize the option value.
	 *
	 * @param mixed $original Option value to be sanitized.
	 *
	 * @return mixed
	 */
	public function sanitize( $original ) {
		return sanitize_title( $original );
	}
}
