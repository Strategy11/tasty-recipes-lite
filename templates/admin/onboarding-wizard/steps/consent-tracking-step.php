<?php
/**
 * Onboarding Wizard - Never miss an important update step.
 *
 * @package Tasty_Recipes
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

?>
<section id="tasty-recipes-onboarding-consent-tracking-step" class="tasty-recipes-onboarding-step tasty-recipes-card-box tasty-recipes-current" data-step-name="<?php echo esc_attr( $step ); ?>">
	<div class="tasty-recipes-card-box-header">
		<img src="<?php echo esc_url( TASTY_FRAMEWORK_URL_ASSETS_IMAGES . '/header-logo.svg' ); ?>" alt="WP Tasty" title="WP Tasty">
	</div>

	<div class="tasty-recipes-card-box-content">
		<h2 class="tasty-recipes-card-box-title"><?php esc_html_e( 'Never miss an important update', 'tasty-recipes-lite' ); ?></h2>
		<p class="tasty-recipes-card-box-text">
			<?php esc_html_e( 'Get key updates, tips, and occasional offers to enhance your WordPress experience. Opt in and help us improve compatibility with your site!', 'tasty-recipes-lite' ); ?>
		</p>
	</div>

	<div class="tasty-recipes-card-box-footer">
		<a href="#" class="tasty-button tasty-highlight tasty-recipes-onboarding-skip-step">
			<?php esc_html_e( 'Skip', 'tasty-recipes-lite' ); ?>
		</a>

		<a href="#" id="tasty-recipes-onboarding-consent-tracking" class="tasty-button tasty-button-pink">
			<?php
			esc_html_e( 'Allow & Continue', 'tasty-recipes-lite' );
			?>
		</a>
	</div>

	<div class="tasty-recipes-card-box-permission">
		<span class="tasty-recipes-collapsible">
			<?php
			esc_html_e( 'Allow Tasty Recipes Plugin to', 'tasty-recipes-lite' );
			?>
		</span>

		<div class="tasty-recipes-collapsible-content hidden">
			<div class="tasty-recipes-card-box-permission-item">
				<span></span>

				<div class="tasty-recipes-card-box-permission-item-content">
					<h4 title="' . esc_attr( __( 'Never miss important updates, get security warnings before they become public knowledge, and receive notifications about special offers and awesome new features.', 'tasty-recipes-lite' ) ) . '">
						<?php esc_html_e( 'View Basic Profile Info', 'tasty-recipes-lite' ); ?>
					</h4>

					<span><?php esc_html_e( 'Your WordPress user’s: first & last name and email address', 'tasty-recipes-lite' ); ?></span>
				</div>
			</div>

			<div class="tasty-recipes-card-box-permission-item">
				<span></span>

				<div class="tasty-recipes-card-box-permission-item-content">
					<h4 title="' . esc_attr( __( 'To provide additional functionality that’s relevant to your website, avoid WordPress or PHP incompatibilities that can break your website, and recognize which languages & regions the plugin should be translated and tailored to.', 'tasty-recipes-lite' ) ) . '">
						<?php esc_html_e( 'View Basic Website Info', 'tasty-recipes-lite' ); ?>
					</h4>

					<span><?php esc_html_e( 'Homepage URL & title, WP & PHP versions, site language', 'tasty-recipes-lite' ); ?></span>
				</div>
			</div>

			<div class="tasty-recipes-card-box-permission-item">
				<span></span>

				<div class="tasty-recipes-card-box-permission-item-content">
					<h4><?php esc_html_e( 'View Basic Plugin Info', 'tasty-recipes-lite' ); ?></h4>
					<span><?php esc_html_e( 'Current plugin & SDK versions, and if active or uninstalled', 'tasty-recipes-lite' ); ?></span>
				</div>
			</div>

			<div class="tasty-recipes-card-box-permission-item">
				<span></span>

				<div class="tasty-recipes-card-box-permission-item-content">
					<h4><?php esc_html_e( 'View Plugins & Themes List', 'tasty-recipes-lite' ); ?></h4>
					<span><?php esc_html_e( 'Names, slugs, versions, and if active or not', 'tasty-recipes-lite' ); ?></span>
				</div>
			</div>
		</div>
	</div>
</section>
