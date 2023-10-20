<?php
/**
 * Template for No Rating icons.
 *
 * @package Tasty_Recipes
 */

$no_ratings = isset( $no_ratings ) ? $no_ratings : false;

?>
<span class="tasty-recipes-ratings-buttons <?php echo $no_ratings ? 'tasty-recipes-no-ratings-buttons' : ''; ?>">
<?php
for ( $i = 5; $i >= 1; $i-- ) {
	$aria_label = sprintf(
		// translators: The aria label for a specific star rating.
		_n( 'Rate this recipe %d star', 'Rate this recipe %d stars', $i, 'tasty-recipes-lite' ),
		$i
	);
	?>
	<?php if ( ! $no_ratings ) { ?>
	<input
		aria-label="<?php echo esc_attr( $aria_label ); ?>"
		type="radio"
		name="tasty-recipes-rating"
		class="tasty-recipes-rating"
		value="<?php echo absint( $i ); ?>" />
	<?php } ?>
	<span class="tasty-recipes-rating">
		<i class="checked" data-rating="<?php echo esc_attr( $i ); ?>">
			<?php echo esc_html( $icons['checked'] ); ?>
			<span class="tasty-recipes-screen-reader">
				<?php
				printf(
					// translators: The number of stars.
					esc_html( _n( '%d Star', '%d Stars', $i, 'tasty-recipes-lite' ) ),
					(int) $i
				);
				?>
			</span>
		</i>
		<i class="unchecked"><?php echo esc_html( $icons['unchecked'] ); ?></i>
	</span>
<?php } ?>
</span>
