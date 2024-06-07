<?php
/**
 * Our header template.
 *
 * @package Tasty/Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="tasty_framework_loader"></div>

<div class="tasty-framework-header tasty-flex">
	<div class="tasty-framework-header-container tasty-flex">
		<h1 class="tasty-framework-header-title">
			<?php echo esc_html( $data['title'] ); ?>
		</h1>
		<div class="tasty-framework-header-logo">
			<img src="<?php echo esc_url( $data['logo'] ); ?>" alt="WP Tasty" title="WP Tasty">
		</div>
	</div>
</div>
