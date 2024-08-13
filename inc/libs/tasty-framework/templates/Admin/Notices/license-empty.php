<?php
/**
 * Empty license admin notice template.
 *
 * @package Tasty/Framework
 */

?>
<div class="updated">
	<p>
		<strong>
			<?php
			// translators: %1$s Plugin name.
			echo esc_html( sprintf( __( '%1$s is almost ready.', 'tasty' ), $data['plugin_name'] ) );
			?>
		</strong>
		<a href="<?php echo esc_url( $data['license_url'] ); ?>">
			<?php esc_html_e( 'Enter your license to continue.', 'tasty' ); ?>
		</a>
	</p>
</div>
