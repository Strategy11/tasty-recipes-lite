<?php
/**
 * Template for multiselect checkboxes in settings page.
 *
 * @package Tasty_Recipes
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.
?>
<div class="tasty-recipes-multiselect-container">
	<?php foreach ( $options as $key => $label ) { ?>
		<?php $key_unique_id = wp_unique_id( 'tasty_recipes_multi_' ); ?>
	<div <?php Tasty_Recipes\Settings::echo_container_atts( $container_atts, $key ); ?>>
		<div class="tasty-recipes-multiselect-item">
			<input id="<?php echo esc_attr( $key_unique_id ); ?>" type="checkbox" name="<?php echo esc_attr( $name ); ?>[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $checked, true ) ); ?>>
			<label for="<?php echo esc_attr( $key_unique_id ); ?>">
				<?php echo esc_html( $label ); ?>
			</label>
		</div>
	</div>
	<?php } ?>

	<?php if ( ! empty( $description ) ) { ?>
		<p class="description"><?php echo wp_kses_post( $description ); ?></p>
	<?php } ?>
</div>
