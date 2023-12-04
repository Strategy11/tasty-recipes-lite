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
use Tasty\Recipes\Lite\Admin\Settings\Settings\UnitConversion;
use Tasty\Recipes\Lite\Admin\Settings\Settings\AutomaticUnitConversion;
use Tasty\Recipes\Lite\Admin\Settings\Settings\CookMode;
use Tasty\Recipes\Lite\Admin\Settings\Settings\DisableScaling;
use Tasty\Recipes\Lite\Admin\Settings\Settings\Instacart;

/**
 * Manages Settings fields.
 */
class Settings {

	use Singleton;

	/**
	 * Settings objects array.
	 *
	 * @var array
	 */
	private $settings = array();

	/**
	 * Get all settings.
	 *
	 * @return array
	 */
	public function get_all_settings() {
		$settings       = array(
			Admin::SETTINGS_SECTION_CARD_DESIGN => array(
				Customization::instance(),
				Template::instance(),
			),
			Admin::SETTINGS_GROUP_CARD          => array(
				'quick_links'            => QuickLinks::instance(),
				'card_buttons'           => CardButtons::instance(),
				'unit_conversion'        => UnitConversion::instance(),
				'ingredients_checkboxes' => IngredientsCheckboxes::instance(),
				'cook_mode'              => CookMode::instance(),
				'scaling'                => DisableScaling::instance(),
				'default_author_link'    => DefaultAuthorLink::instance(),
				'instacart'              => Instacart::instance(),
				'copy_to_clipboard'      => CopyToClipboard::instance(),
				'shareasale'             => ShareAsale::instance(),
			),
		);
		$this->settings = (array) apply_filters( 'tasty_recipes_settings_objects', $settings );
		if ( empty( $this->settings ) ) {
			return array();
		}
		return $this->settings;
	}

	/**
	 * Register all settings.
	 *
	 * @return void
	 */
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

	/**
	 * Render specific group setting fields.
	 *
	 * @param string $group Group to render.
	 * @param string $page_slug Page slug to render settings inside.
	 *
	 * @return void
	 */
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

		usort( $this->settings[ $group ], array( $this, 'sort_settings' ) );

		foreach ( $this->settings[ $group ] as $setting ) {
			add_settings_field(
				$setting->get_id(),
				$setting->get_name(),
				array( $setting, 'render_field' ),
				$page_slug,
				$group,
				$setting->filter_field_args( $setting->get_render_args() )
			);
		}
	}

	/**
	 * Sort settings with ordering method return.
	 *
	 * @param object $first First setting object.
	 * @param object $second Second setitng object.
	 *
	 * @return int
	 */
	public function sort_settings( $first, $second ) {
		if ( $first->ordering() === $second->ordering() || 0 === $second->ordering() ) {
			return 0;
		}
		return $first->ordering() < $second->ordering() ? -1 : 1;
	}

}
