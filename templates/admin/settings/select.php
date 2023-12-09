<?php
/**
 * Template for multiple selects in settings page.
 *
 * @package Tasty_Recipes
 */

?>
<div class="tasty-recipes-select-container <?php echo isset( $container_class ) ? esc_attr( $container_class ) : ''; ?>" <?php echo isset( $tasty_datasets ) ? esc_html( $tasty_datasets ) : ''; ?>>
	<?php
	if ( ! empty( $before_field ) ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $before_field;
	}
	?>
	<select name="<?php echo esc_attr( $name ); ?>"
			id="<?php echo esc_attr( $name ); ?>"
			class="<?php echo esc_attr( isset( $field_class ) ? $field_class : '' ); ?>" <?php disabled( ! empty( $disabled ) ); ?>>
		<?php
		foreach ( $options as $option_key => $option_label ) {
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
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $after_field;
	}
	?>
</div>
