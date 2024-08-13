<?php
/**
 * Tabs template.
 *
 * @package Tasty/Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Tasty\Framework\Utils\Url;
?>

<div class="tasty-tabs nav-tab-wrapper">
	<?php foreach ( $data['tabs'] as $tasty_tab ) { ?>
	<p>
		<a
			class="nav-tab <?php echo esc_attr( $tasty_tab['active'] ? 'nav-tab-active' : '' ); ?>"
			href="<?php echo esc_url( $tasty_tab['url'] ); ?>"
		>
			<?php echo esc_html( $tasty_tab['title'] ); ?>
		</a>
	</p>
	<?php } ?>
	<p>
		<a class="nav-support" target="_blank" href="<?php echo esc_url( Url::add_utm_params( 'support', array( 'utm_content' => 'tabs' ) ) ); ?>">
			<?php esc_html_e( 'Support and documentation', 'tasty' ); ?>
			<svg width="15" height="15" viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg">
				<path d="M12.66 9.38h-.94a.47.47 0 0 0-.47.46v3.29H1.87V3.74H6.1c.26 0 .47-.21.47-.47v-.94a.47.47 0 0 0-.47-.46H1.41C.63 1.88 0 2.5 0 3.27V13.6C0 14.37.63 15 1.4 15h10.32c.78 0 1.4-.63 1.4-1.4V9.83a.47.47 0 0 0-.46-.46ZM14.3 0h-3.75a.7.7 0 0 0-.5 1.2l1.05 1.05-7.14 7.14a.7.7 0 0 0 0 1l.66.65a.7.7 0 0 0 1 0l7.13-7.13 1.05 1.04a.7.7 0 0 0 1.2-.5V.7a.7.7 0 0 0-.7-.7Z" fill="currentColor"/>
			</svg>
		</a>
	</p>
</div>
