<?php
/**
 * Template for dropdown select in settings page.
 *
 * @package Tasty_Recipes
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.
?>

<select name="<?php echo esc_attr( $name ); ?>"
	<?php echo ! empty( $disabled ) ? 'disabled' : ''; ?>
>
	<?php
	foreach ( $options as $key => $label ) :
		$compare_key = $key;
		if ( $is_bool ) {
			$compare_key = (bool) $key;
		}
		?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $compare_key, $value ); ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
</select>
<?php if ( ! empty( $description ) ) { ?>
	<p class="description"><?php echo wp_kses_post( $description ); ?></p>
<?php } ?>
