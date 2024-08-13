<?php
/**
 * Dashboard modal template.
 *
 * @package Tasty/Framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Tasty\Framework\Utils\Url;
?>
<section class="tasty-framework-modal tasty-framework-dashboard-modal" id="tasty_framework_license_modal">
	<div class="tasty-framework-modal-container">
		<a href="#" class="tasty-framework-close-modal tasty-framework-modal-x"></a>
		<div class="tasty-framework-modal-loader" id="tasty_framework_modal_loader"></div>
		<div id="tasty_framework_license_modal_form_page">
			<div class="tasty-framework-modal-header">
				<h2>
					<?php esc_html_e( 'Enter License', 'tasty' ); ?>
				</h2>
			</div>
			<div class="tasty-framework-modal-body">
				<div class="tasty-framework-modal-body-message">
					<a href="<?php echo esc_url( Url::add_utm_params( 'account/downloads', array( 'utm_content' => 'license-modal' ) ) ); ?>" target="_blank">
						<?php esc_html_e( 'Already have a license?', 'tasty' ); ?>
					</a>
				</div>
				<div class="tasty-framework-modal-body-form">
					<div class="tasty-framework-modal-body-form-field">
						<label for="tasty_framework_modal_license_key">
							<?php esc_html_e( 'License', 'tasty' ); ?>
						</label>
						<input type="text" name="tasty_framework_license_key" id="tasty_framework_modal_license_key">
					</div>

					<div class="tasty-framework-modal-body-form-field">
						<label for="tasty_framework_modal_license_plugin">
							<?php esc_html_e( 'Plugin(s) to activate', 'tasty' ); ?>
						</label>
						<select name="tasty_framework_license_plugin" id="tasty_framework_modal_license_plugin"
								data-default="<?php esc_attr_e( 'Add a valid license first', 'tasty' ); ?>">
							<option value="">
								<?php esc_html_e( 'Add a valid license first', 'tasty' ); ?>
							</option>
						</select>
					</div>

					<a href="#" class="tasty-button tasty-button-pink tasty-button-disabled tasty-button-large tasty-button-full" id="tasty_framework_activate_license">
						<?php esc_html_e( 'Save License', 'tasty' ); ?>
					</a>
				</div>
			</div>
		</div>
		<div id="tasty_framework_license_modal_success_page" class="tasty-framework-modal-success-page">
			<div class="tasty-framework-modal-header">
				<div class="tasty-framework-modal-success-page-animation">
					<span></span>
				</div>
				<h2>
					<?php esc_html_e( 'License Saved', 'tasty' ); ?>
				</h2>
			</div>
			<div class="tasty-framework-modal-body">
				<div class="tasty-framework-modal-for-not-installed">
					<?php esc_html_e( 'After activating your plugin license, you must download the plugin before you can use it. Here are the steps:', 'tasty' ); ?>
					<ol>
						<li><?php esc_html_e( 'Download plugin files', 'tasty' ); ?></li>
						<li><?php esc_html_e( 'Upload plugin', 'tasty' ); ?></li>
						<li><?php esc_html_e( 'Activate and install it', 'tasty' ); ?></li>
						<li><?php esc_html_e( 'Enjoy!', 'tasty' ); ?></li>
					</ol>
				</div>

				<div class="tasty-framework-modal-for-installed">
					<?php esc_html_e( 'Just one more step and you’ll be ready to start.', 'tasty' ); ?>
				</div>

				<div class="tasty-framework-modal-for-active">
					<?php esc_html_e( 'Thanks for choosing WP Tasty. Enjoy!', 'tasty' ); ?>
				</div>

				<div class="tasty-framework-spacer"></div>

				<div class="tasty-button-vertical-group">
					<a href="#" class="tasty-button tasty-button-pink tasty-button-large tasty-button-full tasty-framework-modal-for-not-installed"
					id="tasty_framework_download_plugin">
						<?php esc_html_e( 'Download Plugin', 'tasty' ); ?>
					</a>

					<a href="#" class="tasty-button tasty-button-pink tasty-button-large tasty-button-full tasty-framework-modal-for-installed"
					id="tasty_framework_activate_plugin">
						<?php esc_html_e( 'Activate Plugin', 'tasty' ); ?>
					</a>

					<a href="#" class="tasty-button tasty-button-secondary tasty-button-large tasty-button-full tasty-framework-close-modal" id="tasty_framework_modal_close_button">
						<?php esc_html_e( 'Close', 'tasty' ); ?>
					</a>
				</div>
			</div>

		</div>
	</div>
</section>
