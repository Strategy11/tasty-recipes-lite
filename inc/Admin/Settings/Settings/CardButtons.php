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

class CardButtons extends AbstractSetting {

	use Singleton;

	private $positions = array(
		'first',
		'second',
	);

	public function get_id() {
		return TastyRecipesLiteMain::CARD_BUTTONS_OPTION;
	}

	public function get_group() {
		return Admin::SETTINGS_GROUP_CARD;
	}

	public function get_name() {
		return __( 'Recipe Card Buttons', 'tasty-recipes-lite' );
	}

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

	public function render_field( $args ) {
		TastyRecipesLiteMain::echo_template_part( 'admin/settings/multiple-selects', $args );
	}

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

	public function ordering() {
		return 20;
	}
}
