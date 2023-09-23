<?php
/**
 * Template for the print button.
 *
 * @package Tasty_Recipes
 *
 * @var object $recipe        Recipe object.
 * @var string $customization Customization options.
 */

$current_post = get_post();
$ext          = is_feed() ? '.png' : '.svg';
$print_link   = '#';
if ( $current_post ) {
	$print_link = tasty_recipes_get_print_url( $current_post->ID, $recipe->get_id() );
}
?>

<a class="button tasty-recipes-print-button tasty-recipes-no-print" href="<?php echo esc_url( $print_link ); ?>" target="_blank" data-tasty-recipes-customization="<?php echo esc_attr( $customization ); ?>">
	<?php if ( '.svg' === $ext ) : ?>
		<svg viewBox="0 0 24 24" class="svg-print" aria-hidden="true"><use xlink:href="#tasty-recipes-icon-print"></use></svg>
	<?php else : ?>
		<img class="svg-print" data-pin-nopin="true" src="<?php echo esc_url( plugins_url( 'images/icon-print.png', __FILE__ ) ); ?>">
	<?php endif; ?>
	<?php esc_html_e( 'Print Recipe', 'tasty-recipes' ); ?>
</a>
