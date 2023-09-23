<?php
/**
 * Invalid license admin notice template.
 *
 * @package Tasty/Framework
 */

?>
<div class="error">
	<p>
		<strong>
		<?php
			// translators: %1$s Plugin name.
			echo esc_html( sprintf( __( 'To enable updates and support for %1$s', 'tasty' ), $data['plugin_name'] ) );
		?>
			</strong>,
		<?php esc_html_e( 'enter a valid license', 'tasty' ); ?>
	</p>
	<p>
		<strong>
			<?php esc_html_e( "Think you've reached this message in error?", 'tasty' ); ?>
		</strong>
		<?php
			printf(
				// translators: %1$s Opening anchor tag, %1$s Closing anchor tag.
				esc_html__( '%1$sSubmit a support ticket%2$s, and we\'ll do our best to help out.', 'tasty' ),
				'<a href="https://support.wptasty.com" target="_blank" rel="noopener">',
				'</a>'
			);
			?>
	</p>
</div>
