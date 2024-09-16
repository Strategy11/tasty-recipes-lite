<?php
/**
 * Template for input fields in settings page.
 *
 * @package Tasty_Recipes
 */

?>

<input type="<?php echo esc_attr( $type ); ?>"
	name="<?php echo esc_attr( $name ); ?>"
	value="<?php echo esc_attr( $value ); ?>"
	class="regular-text"
	<?php echo ! empty( $disabled ) ? ' disabled' : ''; ?>
>
<?php if ( ! empty( $description ) ) { ?>
	<p class="description"><?php echo wp_kses_post( $description ); ?></p>
<?php } ?>
