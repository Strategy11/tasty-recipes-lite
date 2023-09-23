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
	<p>
		<strong>
			<?php esc_html_e( "Don't have a license yet?", 'tasty' ); ?>
		</strong>
		<?php
		printf(
			// translators: %1$s Open link HTML, %2$s Close link HTML.
			esc_html__( '%1$sGet one in just a few minutes%2$s, and come back once you\'re done.', 'tasty' ),
			'<a href="' . esc_url( $data['plugin_pricing_url'] ) . '" target="_blank" rel="noopener">',
			'</a>'
		);
		?>
	</p>
</div>
