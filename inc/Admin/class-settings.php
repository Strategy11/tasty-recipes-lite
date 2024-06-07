<?php
/**
 * Manages Settings fields.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes_Lite\Admin;

use Tasty\Framework\Traits\Singleton;
use Tasty_Recipes_Lite\Admin;
use Tasty_Recipes_Lite\Admin\Settings\Design\Customization;
use Tasty_Recipes_Lite\Admin\Settings\Design\Template;
use Tasty_Recipes_Lite\Admin\Settings\Settings\Card_Buttons;
use Tasty_Recipes_Lite\Admin\Settings\Settings\Copy_Clipboard;
use Tasty_Recipes_Lite\Admin\Settings\Settings\Default_Author_Link;
use Tasty_Recipes_Lite\Admin\Settings\Settings\Ingredients_Checkboxes;
use Tasty_Recipes_Lite\Admin\Settings\Settings\Quick_Links;
use Tasty_Recipes_Lite\Admin\Settings\Settings\Quick_Links_Style;
use Tasty_Recipes_Lite\Admin\Settings\Settings\ShareAsale;
use Tasty_Recipes_Lite\Admin\Settings\Settings\Unit_Conversion;
use Tasty_Recipes_Lite\Admin\Settings\Settings\Cook_Mode;
use Tasty_Recipes_Lite\Admin\Settings\Settings\Disable_Scaling;
use Tasty_Recipes_Lite\Admin\Settings\Settings\Instacart;

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
				'quick_links'            => Quick_Links::instance(),
				'quick_links_style'      => Quick_Links_Style::instance(),
				'card_buttons'           => Card_Buttons::instance(),
				'unit_conversion'        => Unit_Conversion::instance(),
				'ingredients_checkboxes' => Ingredients_Checkboxes::instance(),
				'cook_mode'              => Cook_Mode::instance(),
				'scaling'                => Disable_Scaling::instance(),
				'default_author_link'    => Default_Author_Link::instance(),
				'instacart'              => Instacart::instance(),
				'copy_to_clipboard'      => Copy_Clipboard::instance(),
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
