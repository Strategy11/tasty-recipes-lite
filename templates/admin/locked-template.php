<?php
/**
 * Template for pro badge in settings page.
 *
 * @package Tasty_Recipes
 */

echo $recipe_styles; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

if ( ! $template_object ) {
	return;
}

/* translators: %s: template name */
$preview_template = sprintf( __( 'Preview %s template', 'tasty-recipes' ), $template_object->get_template_name() );
?>
<div class="tasty-recipes-pro-template">
	<img src="<?php echo esc_url( $template_object->get_template_url() ); ?>images/template.png"
		alt="<?php echo esc_attr( $preview_template ); ?>"
	>
</div>
