<?php
/**
 * Template for the pin button.
 *
 * @package Tasty_Recipes
 *
 * @var object $recipe        Recipe object.
 * @var string $customization Customization options.
 */

$current_post = get_post();
$ext          = is_feed() ? '.png' : '.svg';

$permalink = '#';
if ( $current_post ) {
	$permalink = get_permalink( $current_post->ID );
}
$pin_query_args      = array(
	'url' => rawurlencode( $permalink ),
);
$force_pin_image_url = apply_filters( 'tasty_recipes_force_pin_image_url', '', $recipe, $current_post );
if ( ! empty( $force_pin_image_url ) ) {
	$pin_query_args['media'] = rawurlencode( $force_pin_image_url );
}
$pin_url = add_query_arg(
	$pin_query_args,
	'https://www.pinterest.com/pin/create/bookmarklet/'
);

?>

<a class="share-pin button" data-pin-custom="true" data-href="<?php echo esc_url( $pin_url ); ?>" href="<?php echo esc_url( $pin_url ); ?>" data-tasty-recipes-customization="<?php echo esc_attr( $customization ); ?>">
	<?php if ( '.svg' === $ext ) : ?>
		<svg viewBox="0 0 24 24" class="svg-print" aria-hidden="true"><use xlink:href="#tasty-recipes-icon-pinterest"></use></svg>
	<?php else : ?>
		<img class="svg-pinterest" data-pin-nopin="true" src="<?php echo esc_url( plugins_url( 'images/icon-pinterest.png', __FILE__ ) ); ?>">
	<?php endif; ?>
	<?php esc_html_e( 'Pin Recipe', 'tasty-recipes' ); ?>
</a>
<script>
	const share_pin_buttons = document.getElementsByClassName( 'share-pin button' );
	if ( share_pin_buttons ) {
		for ( let share_key = 0; share_key < share_pin_buttons.length; share_key++ ) {
			share_pin_buttons[share_key].addEventListener( 'click', (e) => {
				e.stopPropagation();
				window.open(e.target.dataset.href,'targetWindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=500,height=500');
				return false;
			} );
		}
	}
</script>
