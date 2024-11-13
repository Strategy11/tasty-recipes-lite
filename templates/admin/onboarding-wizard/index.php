<?php
/**
 * Onboarding Wizard Page.
 *
 * @package Tasty_Recipes
 */

use Tasty\Framework\Utils\Url;
use Tasty_Recipes\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

?>
<div id="tasty-recipes-onboarding-wizard-page" class="wrap tasty-recipes-admin-plugin-landing tasty-recipes-hide-js" data-current-step="consent-tracking">
	<div id="tasty-recipes-onboarding-container">
		<ul id="tasty-recipes-onboarding-rootline" class="tasty-recipes-onboarding-rootline">
			<li class="tasty-recipes-onboarding-rootline-item" data-step="consent-tracking">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo Utils::get_contents( TASTY_FRAMEWORK_PATH_ASSETS . '/images/icon-check.svg' );
				?>
			</li>
			<li class="tasty-recipes-onboarding-rootline-item" data-step="success">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo Utils::get_contents( TASTY_FRAMEWORK_PATH_ASSETS . '/images/icon-check.svg' );
				?>
			</li>
		</ul>

		<?php
		foreach ( $step_parts as $step => $file ) {
			require $view_path . $file; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		}
		?>

		<a id="tasty-recipes-onboarding-return-dashboard" href="<?php echo esc_url( Url::get_main_admin_url() ); ?>">
			<?php esc_html_e( 'Exit Onboarding', 'tasty-recipes-lite' ); ?>
		</a>
	</div>
</div>
