<?php
/**
 * Onboarding Wizard - Success (You're All Set!) Step.
 *
 * @package Tasty_Recipes
 */

use Tasty\Framework\Utils\Url;
use Tasty_Recipes\Settings;
use Tasty_Recipes\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

?>
<section id="tasty-recipes-onboarding-success-step" class="tasty-recipes-onboarding-step tasty-recipes-card-box hidden" data-step-name="<?php echo esc_attr( $step ); ?>">
	<div class="tasty-recipes-card-box-header">
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo Utils::get_contents( TASTY_FRAMEWORK_PATH_ASSETS . '/images/logo.svg' );
		?>
	</div>

	<div class="tasty-recipes-card-box-content">
		<h2 class="tasty-recipes-card-box-title"><?php esc_html_e( 'You\'re All Set!', 'tasty-recipes-lite' ); ?></h2>
		<p class="tasty-recipes-card-box-text">
			<?php esc_html_e( 'Congratulations on completing the onboarding process! We hope you enjoy using Tasty Recipes Lite Plugin.', 'tasty-recipes-lite' ); ?>
		</p>
	</div>

	<div class="tasty-recipes-card-box-footer">
		<a href="<?php echo esc_url( Url::get_main_admin_url() ); ?>" class="tasty-button tasty-highlight">
			<?php esc_html_e( 'Go to Dashboard', 'tasty-recipes-lite' ); ?>
		</a>

		<a href="<?php echo esc_url( add_query_arg( 'tab', 'settings', menu_page_url( Settings::PAGE_SLUG, false ) ) ); ?>" class="tasty-button tasty-button-pink">
			<?php esc_html_e( 'Go to Settings', 'tasty-recipes-lite' ); ?>
		</a>
	</div>
</section>
