<?php
/**
 * Elegant recipe card template.
 *
 * @package Tasty_Recipes
 */

?>
<?php
echo $recipe_styles; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>
<div class="tasty-recipes-pro-template">
	<img src="<?php echo esc_attr( $template_url ) ?>images/template.png" alt="<?php esc_attr_e( 'Preview Elegant template', 'tasty-recipes-lite' ); ?>">
</div>
