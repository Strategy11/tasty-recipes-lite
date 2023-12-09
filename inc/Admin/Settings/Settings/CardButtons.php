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
	 * {@inheritdoc}
	 */
	public function get_id() {
		return TastyRecipesLiteMain::CARD_BUTTONS_OPTION;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_group() {
		return Admin::SETTINGS_GROUP_CARD;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_setting_name() {
		return __( 'Recipe Card Buttons', 'tasty-recipes-lite' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function sanitize( $option_value ) {
		if ( empty( $option_value ) ) {
			return $option_value;
		}

		$sanitized = array();
		$args      = $this->filter_field_args( $this->get_render_args() );
		foreach ( $this->positions as $position ) {
			if ( empty( $option_value[ $position ] ) || ! in_array( $option_value[ $position ], array_keys( $args['options'] ), true ) ) {
				continue;
			}
			$sanitized[ $position ] = $option_value[ $position ];
		}
		return $sanitized;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render_field( $args ) {
		TastyRecipesLiteMain::echo_template_part( 'admin/settings/multiple-selects', $args );
	}

	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function ordering() {
		return 20;
	}
}
