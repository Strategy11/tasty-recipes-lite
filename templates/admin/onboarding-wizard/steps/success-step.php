<?php
/**
 * Onboarding Wizard - Success (You're All Set!) Step.
 *
 * @package Tasty_Recipes
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

?>
<section id="tasty-recipes-onboarding-success-step" class="tasty-recipes-onboarding-step tasty-recipes-card-box hidden" data-step-name="<?php echo esc_attr( $step ); ?>">
	<div class="tasty-recipes-card-box-header">
		<img src="<?php echo esc_url( TASTY_FRAMEWORK_URL_ASSETS_IMAGES . '/header-logo.svg' ); ?>" alt="WP Tasty" title="WP Tasty">
	</div>

	<div class="tasty-recipes-card-box-content">
		<h2 class="tasty-recipes-card-box-title"><?php esc_html_e( 'You\'re All Set!', 'tasty-recipes-lite' ); ?></h2>
		<p class="tasty-recipes-card-box-text">
			<?php esc_html_e( 'Congratulations on completing the onboarding process! We hope you enjoy using Business Directory Plugin.', 'tasty-recipes-lite' ); ?>
		</p>
	</div>

	<div class="tasty-recipes-card-box-footer">
		<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=tasty-recipes_listing' ) ); ?>" class="button button-secondary tasty-recipes-button-secondary tasty-recipes-button-large">
			<?php esc_html_e( 'Create a Listing', 'tasty-recipes-lite' ); ?>
		</a>

		<a href="<?php echo esc_url( admin_url( 'admin.php?page=tasty-recipes_settings' ) ); ?>" class="button button-primary tasty-recipes-button-primary tasty-recipes-button-large">
			<?php esc_html_e( 'Go to Settings', 'tasty-recipes-lite' ); ?>
		</a>
	</div>
</section>
