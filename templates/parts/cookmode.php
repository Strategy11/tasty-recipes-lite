<?php
/**
 * Template for cook mode feature.
 *
 * @package Tasty_Recipes
 */

$cook_mode_id = $recipe_unique_id . '_cookmode';

?>
<div class="tasty-recipes-cook-mode">
	<div class="tasty-recipes-cook-mode__container">
		<label class="tasty-recipes-cook-mode__switch">
			<input type="checkbox" id="<?php echo esc_attr( $cook_mode_id ); ?>"/>
			<span class="tasty-recipes-cook-mode__switch-slider tasty-recipes-cook-mode__switch-round"
				data-tasty-recipes-customization="button-color.background button-text-color.color"></span>
		</label>
		<label for="<?php echo esc_attr( $cook_mode_id ); ?>">
			<span class="tasty-recipes-cook-mode__label"><?php echo esc_html( $recipe_show_cook_mode_label ); ?></span>
			<span class="tasty-recipes-cook-mode__helper">
				<?php echo esc_html( isset( $recipe_show_cook_mode_help ) ? $recipe_show_cook_mode_help : __( 'Prevent your screen from going dark', 'tasty-recipes' ) ); ?>
			</span>
		</label>
	</div>
</div>
