<?php
/**
 * Card Buttons option.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Settings\Settings;

use Tasty\Framework\Traits\Singleton;
use Tasty\Recipes\Lite\Admin;
use Tasty\Recipes\Lite\Admin\Settings\AbstractSetting;
use TastyRecipesLiteMain;

/**
 * Card Buttons setting class.
 */
class CardButtons extends AbstractSetting {

	use Singleton;

	/**
	 * Button positions.
	 *
	 * @var string[]
	 */
	private $positions = array(
		'first',
		'second',
	);

	/**
	 * Setting unique ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return TastyRecipesLiteMain::CARD_BUTTONS_OPTION;
	}

	/**
	 * Setting group ID.
	 *
	 * @return string
	 */
	public function get_group() {
		return Admin::SETTINGS_GROUP_CARD;
	}

	/**
	 * Setting name.
	 *
	 * @return string
	 */
	protected function get_setting_name() {
		return __( 'Recipe Card Buttons', 'tasty-recipes-lite' );
	}

	/**
	 * Sanitize the option value.
	 *
	 * @param mixed $original Option value to be sanitized.
	 *
	 * @return mixed
	 */
	public function sanitize( $value ) {
		if ( empty( $value ) ) {
			return $value;
		}

		$sanitized = array();
		$args      = $this->filter_field_args( $this->get_render_args() );
		foreach ( $this->positions as $position ) {
			if ( empty( $value[ $position ] ) || ! in_array( $value[ $position ], array_keys( $args['options'] ), true ) ) {
				continue;
			}
			$sanitized[ $position ] = $value[ $position ];
		}
		return $sanitized;
	}

	/**
	 * Render setting field based on args.
	 *
	 * @param array $args List of setting arguments.
	 *
	 * @return void
	 */
	public function render_field( $args ) {
		TastyRecipesLiteMain::echo_template_part( 'admin/settings/multiple-selects', $args );
	}

	/**
	 * Get args that is passed to render_field.
	 *
	 * @return array
	 */
	public function get_render_args() {
		return array(
			'name'      => $this->get_id(),
			'options'   => array(
				''          => esc_html__( 'Off', 'tasty-recipes-lite' ),
				'print'     => esc_html__( 'Print', 'tasty-recipes-lite' ),
				'mediavine' => esc_html__( 'Mediavine Grow.me Save', 'tasty-recipes-lite' ),
			),
			'positions' => $this->positions,
			'value'     => $this->get_option_value(),
		);
	}

	/**
	 * Setting ordering in settings page.
	 *
	 * @return int
	 */
	public function ordering() {
		return 20;
	}
}
