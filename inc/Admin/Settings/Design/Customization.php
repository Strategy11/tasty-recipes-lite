<?php
/**
 * Customization option.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite\Admin\Settings\Design;

use Tasty\Framework\Traits\Singleton;
use Tasty\Recipes\Lite\Admin;
use Tasty\Recipes\Lite\Admin\Settings\AbstractSetting;
use TastyRecipesLiteMain;

class Customization extends AbstractSetting {

	use Singleton;

	public function get_id() {
		return TastyRecipesLiteMain::CUSTOMIZATION_OPTION;
	}

	public function get_group() {
		return Admin::SETTINGS_SECTION_CARD_DESIGN;
	}

	public function get_name() {
		return '';
	}

	public function sanitize( $original ) {
		$original  = stripslashes_deep( (array) $original );
		$sanitized = array();
		$allowed   = array(
			'primary_color'            => array( $this, 'sanitize_color' ),
			'secondary_color'          => array( $this, 'sanitize_color' ),
			'icon_color'               => array( $this, 'sanitize_color' ),
			'button_color'             => array( $this, 'sanitize_color' ),
			'button_text_color'        => array( $this, 'sanitize_color' ),
			'detail_label_color'       => array( $this, 'sanitize_color' ),
			'detail_value_color'       => array( $this, 'sanitize_color' ),
			'h2_color'                 => array( $this, 'sanitize_color' ),
			'h2_transform'             => array(
				'uppercase',
				'initial',
				'lowercase',
			),
			'h3_color'                 => array( $this, 'sanitize_color' ),
			'h3_transform'             => array(
				'uppercase',
				'initial',
				'lowercase',
			),
			'body_color'               => array( $this, 'sanitize_color' ),
			'star_ratings_style'       => array(
				'solid',
				'outline',
			),
			'star_color'               => array( $this, 'sanitize_color' ),
			'nutrifox_display_style'   => array(
				'label',
				'card',
			),
			'footer_social_platform'   => array(
				'instagram',
				'pinterest',
				'facebook',
			),
			'footer_icon_color'        => array( $this, 'sanitize_color' ),
			'footer_heading'           => 'sanitize_text_field',
			'footer_heading_color'     => array( $this, 'sanitize_color' ),
			'footer_description'       => 'wp_filter_post_kses',
			'footer_description_color' => array( $this, 'sanitize_color' ),
		);
		foreach ( $original as $key => $value ) {
			if ( ! isset( $allowed[ $key ] ) ) {
				continue;
			}

			if ( is_callable( $allowed[ $key ] ) ) {
				$sanitized[ $key ] = $allowed[ $key ]( $value );
			} elseif ( is_array( $allowed[ $key ] ) ) {
				if ( in_array( $value, $allowed[ $key ], true ) ) {
					$sanitized[ $key ] = $value;
				} else {
					$sanitized[ $key ] = '';
				}
			}
		}
		if ( ! empty( $sanitized['footer_description'] ) ) {
			$sanitized['footer_description'] = stripslashes( wpautop( $sanitized['footer_description'] ) );
		}
		return $sanitized;
	}

	public function render_field( $args ) {

	}

	public function get_render_args() {

	}
}
