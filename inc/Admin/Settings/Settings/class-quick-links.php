<?php
/**
 * Quick Links option.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes_Lite\Admin\Settings\Settings;

use Tasty\Framework\Traits\Singleton;
use Tasty_Recipes_Lite\Admin;
use Tasty_Recipes_Lite\Admin\Settings\Abstract_Setting;
use Tasty_Recipes_Lite\Utils;
use TastyRecipesLiteMain;

/**
 * Quick Links setting class.
 */
class Quick_Links extends Abstract_Setting {

	use Singleton;

	/**
	 * {@inheritDoc}
	 */
	public function get_id() {
		return TastyRecipesLiteMain::QUICK_LINKS_OPTION;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_group() {
		return Admin::SETTINGS_GROUP_CARD;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_setting_name() {
		return __( 'Quick Links', 'tasty-recipes-lite' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function sanitize( $value ) {
		return Utils::sanitize_item( $value, 'sanitize_key' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function render_field( $args ) {
		TastyRecipesLiteMain::echo_template_part(
			'admin/settings/multiselect-checkbox',
			$args
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_render_args() {
		$args = array(
			'name'           => $this->get_id(),
			'description'    => __( 'Enable or disable quick links below the post title.', 'tasty-recipes-lite' ),
			'default_values' => array(
				'jump',
				'print',
			),
			'options'        => array(
				'jump'   => __( '\'Jump to Recipe\' link', 'tasty-recipes-lite' ),
				'print'  => __( '\'Print Recipe\' link', 'tasty-recipes-lite' ),
				'rating' => __( '\'Leave a review\' link', 'tasty-recipes-lite' ),
			),
		);
		return $this->prepare_multiselect_checkboxes_field_args( $args );
	}

	/**
	 * {@inheritDoc}
	 */
	public function ordering() {
		return 10;
	}
}
