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

	<?php if ( isset( $label_before ) ) { ?>
		<label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label_before ); ?></label>
	<?php } ?>

	<input type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>"
		   value="<?php echo esc_attr( $value ); ?>"
		   class="<?php echo esc_attr( isset( $field_class ) ? $field_class : '' ) ?>" <?php if ( isset( $disabled ) ) { disabled( $disabled ); } ?> <?php if ( isset( $checked ) ) { checked( $checked ); } ?> />

	<?php if ( isset( $label_after ) ) { ?>
		<label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label_after ); ?></label>
	<?php } ?>

	<?php if ( ! empty( $description ) ) { ?>
		<p class="description"><?php echo wp_kses_post( $description ); ?></p>
		<?php
	}

	if ( ! empty( $after_field ) ) {
		echo $after_field;
	}
	?>
</div>
