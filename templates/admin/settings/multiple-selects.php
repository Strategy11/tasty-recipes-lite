<?php
/**
 * Template for multiple selects in settings page.
 *
 * @package Tasty_Recipes
 */

?>
<div class="tasty-recipes-multipleselects-container">
	<?php foreach ( $positions as $position ) { ?>
		<select name="<?php echo esc_attr( $name . '[' . $position . ']' ); ?>">
			<?php foreach ( $options as $option_key => $option_label ) : ?>
				<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $option_key, $value[ $position ] ); ?>><?php echo esc_html( $option_label ); ?></option>
			<?php endforeach; ?>
		</select>
	<?php } ?>
</div>
