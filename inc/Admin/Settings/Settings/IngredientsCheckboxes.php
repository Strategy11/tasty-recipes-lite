<?php
/**
 * Ingredients checkboxes option.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Settings\Settings;

use Tasty\Framework\Traits\Singleton;
use Tasty\Recipes\Lite\Admin;
use Tasty\Recipes\Lite\Admin\Settings\AbstractSetting;
use Tasty\Recipes\Lite\Utils;
use TastyRecipesLiteMain;

class IngredientsCheckboxes extends AbstractSetting {

	use Singleton;

	public function get_id() {
		return TastyRecipesLiteMain::INGREDIENT_CHECKBOXES_OPTION;
	}

	public function get_group() {
		return Admin::SETTINGS_GROUP_CARD;
	}

	public function get_name() {
		return '';
	}

	public function sanitize( $value ) {
		return Utils::sanitize_item( $value, 'sanitize_title' );
	}
}
