<?php
/**
 * Template for Instacart button.
 *
 * @package Tasty_Recipes
 */

?>
<p
	id="shop-with-instacart-v1" <?php if ( ! empty( $instacart_affiliate_id ) ) { ?>
	data-affiliate_id="<?php echo esc_attr( $instacart_affiliate_id ); ?>"
	data-source_origin="affiliate_hub"
	data-affiliate_platform="recipe_widget"<?php } ?>>
</p>
