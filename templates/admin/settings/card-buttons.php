<?php
/**
 * Template for card buttons in settings page.
 *
 * @package Tasty_Recipes
 */

?>

<div>
	<?php foreach ( array( 'first', 'second' ) as $position ) : ?>
		<select name="<?php echo esc_attr( Tasty_Recipes::CARD_BUTTONS_OPTION . '[' . $position . ']' ); ?>">
		<?php foreach ( $options as $key => $option ) : ?>
			<option value="<?php echo esc_attr( $key ); ?>"
				<?php selected( $key, $value[ $position ] ); ?>
				<?php echo empty( $option['disabled'] ) ? '' : ' disabled'; ?>
			>
				<?php echo esc_html( $option['label'] ); ?>
			</option>
			<?php endforeach; ?>
		</select>
	<?php endforeach; ?>
</div>
