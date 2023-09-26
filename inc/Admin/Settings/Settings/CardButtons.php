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
use Tasty\Recipes\Lite\Utils;
use TastyRecipesLiteMain;

class CardButtons extends AbstractSetting {

	use Singleton;

	public function get_id() {
		return TastyRecipesLiteMain::CARD_BUTTONS_OPTION;
	}

	public function get_group() {
		return Admin::SETTINGS_GROUP_CARD;
	}

	public function get_name() {
		return __( 'Recipe Card Buttons', 'tasty-recipes' );
	}

	public function sanitize( $value ) {
		$sanitized = array();
		foreach ( array( 'first', 'second' ) as $position ) {
			if ( ! in_array( $value[ $position ], array( '', 'print', 'pin', 'mediavine', 'slickstream' ), true ) ) {
				continue;
			}
			$sanitized[ $position ] = $value[ $position ];
		}
		return $sanitized;
	}


	public function render_field( $args ) {
		$options = array(
			''            => esc_html__( 'Off', 'tasty-recipes' ),
			'print'       => esc_html__( 'Print', 'tasty-recipes' ),
			'pin'         => esc_html__( 'Pin this recipe', 'tasty-recipes' ),
			'slickstream' => esc_html__( 'Slickstream Favorites', 'tasty-recipes' ),
			'mediavine'   => esc_html__( 'Mediavine Grow.me Save', 'tasty-recipes' ),
		);

		$value = TastyRecipesLiteMain::get_card_button_settings();
		?>
		<div>
			<?php foreach ( array( 'first', 'second' ) as $position ) : ?>
				<select name="<?php echo esc_attr( TastyRecipesLiteMain::CARD_BUTTONS_OPTION . '[' . $position . ']' ); ?>">
					<?php foreach ( $options as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $value[ $position ] ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			<?php endforeach; ?>
		</div>
		<?php
	}

	public function get_render_args() {

	}
}
