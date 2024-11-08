<?php
/**
 * Onboarding Wizard Page.
 *
 * @package Tasty_Recipes
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

?>
<div id="wpbdp-onboarding-wizard-page" class="wrap wpbdp-admin-plugin-landing wpbdp-hide-js" data-current-step="consent-tracking">
	<div id="wpbdp-onboarding-container">
		<ul id="wpbdp-onboarding-rootline" class="wpbdp-onboarding-rootline">
			<li class="wpbdp-onboarding-rootline-item" data-step="consent-tracking">
			</li>
			<li class="wpbdp-onboarding-rootline-item" data-step="success">
			</li>
		</ul>

		<?php
		foreach ( $step_parts as $step => $file ) {
			require $view_path . $file;
		}
		?>

		<a id="wpbdp-onboarding-return-dashboard" href="<?php echo esc_url( admin_url( 'admin.php?page=wpbdp_settings' ) ); ?>">
			<?php esc_html_e( 'Exit Onboarding', 'tasty-recipes-lite' ); ?>
		</a>
	</div>
</div>
