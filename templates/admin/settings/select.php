<?php
/**
 * Template for multiple selects in settings page.
 *
 * @package Tasty_Recipes
 */

?>
<div class="tasty-recipes-select-container">
	<?php
	if ( ! empty( $before_field ) ) {
		echo $before_field;
	}
	?>
	<select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" <?php disabled( ! empty( $disabled ) ); ?>>
		<?php
		foreach ( $options as $option_key => $option_label ){
			$compare_key = $option_key;
			if ( $is_bool ) {
				$compare_key = (bool) $option_key;
			}
			?>
			<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $compare_key, $value ); ?>><?php echo esc_html( $option_label ); ?></option>
		<?php } ?>
	</select>
	<?php if ( ! empty( $description ) ) { ?>
		<p class="description"><?php echo wp_kses_post( $description ); ?></p>
		<?php
	}
	if ( ! empty( $after_field ) ) {
		echo $after_field;
	}
	?>
</div>
