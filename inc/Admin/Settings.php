<?php
/**
 * Manages Settings fields.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin;

use Tasty\Framework\Traits\Singleton;
use Tasty\Recipes\Lite\Admin;
use Tasty\Recipes\Lite\Admin\Settings\Design\Customization;
use Tasty\Recipes\Lite\Admin\Settings\Design\Template;
use Tasty\Recipes\Lite\Admin\Settings\Settings\CardButtons;
use Tasty\Recipes\Lite\Admin\Settings\Settings\CopyToClipboard;
use Tasty\Recipes\Lite\Admin\Settings\Settings\DefaultAuthorLink;
use Tasty\Recipes\Lite\Admin\Settings\Settings\IngredientsCheckboxes;
use Tasty\Recipes\Lite\Admin\Settings\Settings\QuickLinks;
use Tasty\Recipes\Lite\Admin\Settings\Settings\ShareAsale;

class Settings {

	use Singleton;

	private $settings = array();

	public function get_all_settings() {
		$settings = array(
			Admin::SETTINGS_SECTION_CARD_DESIGN => array(
				Customization::instance(),
				Template::instance(),
			),
			Admin::SETTINGS_GROUP_CARD => array(
				QuickLinks::instance(),
				CardButtons::instance(),
				IngredientsCheckboxes::instance(),
				DefaultAuthorLink::instance(),
				CopyToClipboard::instance(),
				ShareAsale::instance(),
			),
		);
		$this->settings = apply_filters( 'tasty_recipes_settings_objects', $settings );
		return $this->settings;
	}

	public function register() {
		foreach ( $this->get_all_settings() as $group_settings ) {
			foreach ( $group_settings as $setting ) {
				register_setting(
					$setting->get_group(),
					$setting->get_id(),
					array(
						'sanitize_callback' => array( $setting, 'sanitize' ),
					)
				);
			}
		}
	}

	public function render_group_settings( $group, $page_slug ) {
		if ( ! isset( $this->settings[ $group ] ) ) {
			return;
		}

		add_settings_section(
			$group,
			false,
			'__return_null',
			$page_slug
		);

		foreach ( $this->settings[ $group ] as $setting ) {
			add_settings_field(
				$setting->get_id(),
				$setting->get_name(),
				array( $setting, 'render_field' ),
				$page_slug,
				$group,
				$setting->get_render_args()
			);
		}
	}

}
