<?php
/**
 * Template for multiple selects in settings page.
 *
 * @package Tasty_Recipes
 */

?>
<div class="tasty-recipes-input-container">
	<?php
	if ( ! empty( $before_field ) ) {
		echo $before_field;
	}
	?>
	<input type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
	<?php if ( ! empty( $description ) ) { ?>
		<p class="description"><?php echo wp_kses_post( $description ); ?></p>
		<?php
	}
	if ( ! empty( $after_field ) ) {
		echo $after_field;
	}
	?>
</div>
