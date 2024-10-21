<?php
/**
 * Template for the settings page.
 *
 * @package Tasty_Recipes
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

use Tasty_Recipes\Admin;
use Tasty_Recipes\Utils;
use Tasty_Recipes\Designs\Template;
use Tasty\Framework\Utils\Common;
use Tasty\Framework\Utils\Url;

?>

<div class="wrap">
	<div class="wp-header-end"></div>
	<?php Common::show_tabs( $tabs ); ?>


	<?php if ( 'design' === $active_tab ) : ?>
		<?php
		if ( Template::has_custom_template() ) {
			?>
			<div class="tasty-recipes-custom-template-notice tasty-tab-content"><p><?php esc_html_e( 'Looks like you\'re using a custom recipe card template. To enable design customization options, you\'ll need to remove the custom tasty-recipes.php file from your theme folder.', 'tasty-recipes-lite' ); ?></p></div>
			<?php
		} else {
			$recipe_card_preview_url_base = add_query_arg(
				array(
					'action' => 'tasty_recipes_preview_recipe_card',
					'nonce'  => wp_create_nonce( Admin::NONCE_KEY ),
				),
				admin_url( 'admin-ajax.php' )
			);
			?>
			<form method="POST" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" class="tasty-recipes-design-settings-app tasty-tab-content">
				<?php
				settings_fields( Tasty_Recipes\Settings::SETTINGS_SECTION_CARD_DESIGN );
				$initial_options = array(
					'template'      => get_option( Tasty_Recipes::TEMPLATE_OPTION, '' ),
					'customization' => Tasty_Recipes::get_customization_settings(),
					'upgrade_url'   => Url::get_upgrade_url( 'Tasty Recipes', [ 'utm_content' => 'template' ] ),
					'locked'        => Template::get_locked_templates(),
				);
				?>
				<tasty-recipes-design-settings
					recipe-card-preview-url-base="<?php echo esc_url( $recipe_card_preview_url_base ); ?>"
					:template-options="<?php echo esc_attr( wp_json_encode( Template::get_all_templates() ) ); ?>"
					:initial-options="<?php echo esc_attr( wp_json_encode( $initial_options ) ); ?>"></tasty-recipes-design-settings>
			</form>
			<?php
		}
	elseif ( 'about' === $active_tab ) :
		?>
		<?php include __DIR__ . '/about.php'; ?>
		<?php
	elseif ( 'converters' === $active_tab ) :
		?>
	<div class="tasty-recipes-converter tasty-tab-content">
		<div class="tasty-recipes-converter-wrap postbox">
			<div class="tasty-recipes-converter-column">
				<ul class="tasty-recipes-converter-options">
					<?php foreach ( Tasty_Recipes::get_converters() as $converter => $config ) : ?>
						<li><button disabled class="button" data-label="<?php echo esc_attr( $config['label'] ); ?>" data-type="<?php echo esc_attr( $converter ); ?>"><?php echo esc_html( $config['label'] ); ?></button></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="tasty-recipes-converter-column tasty-recipes-converter-panel">
				<div class="tasty-recipes-converter-welcome">
					<h3><?php esc_html_e( 'Convert Existing Recipes', 'tasty-recipes-lite' ); ?></h3>
					<p>
					<?php
					printf(
						// translators: %1$s opening anchor tag to the conversion page, %2$s closing tag.
						esc_html__( 'Just in case, we recommend backing up your database before converting your recipes in bulk to Tasty Recipes. %1$sLearn more about backing up your database%2$s.', 'tasty-recipes-lite' ),
						'<a href="https://www.wptasty.com/convert-all" target="_blank" rel="noopener">',
						'</a>'
					);
					?>
					</p>
					<p><?php esc_html_e( 'Select an option from the left to start converting recipes.', 'tasty-recipes-lite' ); ?></p>
				</div>
				<div class="tasty-recipes-converter-control"></div>
			</div>
		</div>
		<script type="text/html" id="tmpl-tasty-recipes-convert">
			<button type="button" class="tasty-recipes-converter-close"><span class="tasty-recipes-converter-icon"><span class="screen-reader-text"><?php esc_html_e( 'Close Converter', 'tasty-recipes-lite' ); ?></span></span></button>

			<h3>
				<?php
				// translators: Source of the conversion.
				echo esc_html( sprintf( __( 'Convert recipes from %s', 'tasty-recipes-lite' ), '{{ data.label }}' ) );
				?>
			</h3>

			<# if ( data.loading ) { #>
				<p><?php esc_html_e( 'Determining whether there are any recipes to convert.', 'tasty-recipes-lite' ); ?></p>
			<# } #>
			<# if ( data.converting ) {
				if ( data.converted < data.count ) {
				#>
					<p>
					<?php
					// translators: Source of the conversion.
					echo esc_html( sprintf( __( 'Converting recipes from %s. Leave this window open until the conversion is complete.', 'tasty-recipes-lite' ), '{{ data.label }}' ) );
					?>
					</p>
					<progress value="{{ data.converted }}" max="{{ data.count }}">
				<# } else { #>
					<p>
					<?php
					// translators: Source of the conversion.
					echo esc_html( sprintf( __( '%s conversion complete. You may now close this converter.', 'tasty-recipes-lite' ), '{{ data.label }}' ) );
					?>
					</p>
				<# } #>
			<# } #>
			<# if ( data.errorMessage ) { #>
				<p>{{ data.errorMessage }}</p>
			<# } #>
			<# if ( ! data.loading && ! data.converting && ! data.errorMessage ) {
				if ( data.count ) {
					if ( 1 === data.count ) {
					#>
					<p>
					<?php
					// translators: Singular number of recipes, source.
					printf( esc_html__( '%1$s recipe was found to convert from %2$s.', 'tasty-recipes-lite' ), '{{ data.count }}', '{{ data.label }}' );
					?>
					</p>
					<# } else { #>
					<p>
					<?php
					// translators: Plural number of recipes, source.
					printf( esc_html__( '%1$s recipes were found to convert from %2$s.', 'tasty-recipes-lite' ), '{{ data.count }}', '{{ data.label }}' );
					?>
					</p>
					<# } #>
					<button class="tasty-button start-conversion"><?php esc_html_e( 'Start Conversion', 'tasty-recipes-lite' ); ?></button>
				<# } else { #>
					<p>
					<?php
					// translators: Source of the conversion.
					printf( esc_html__( 'No %s recipes were found to convert.', 'tasty-recipes-lite' ), '{{ data.label }}' );
					?>
					</p>
				<# } #>
			<# } #>

		</script>
	</div>
	<?php elseif ( 'settings' === $active_tab ) : ?>
	<div class="tasty-recipes-settings tasty-settings tasty-tab-content">
		<form method="POST" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>">
		<?php
			settings_fields( Tasty_Recipes\Settings::SETTINGS_GROUP_CARD );
			do_settings_sections( Tasty_Recipes\Settings::PAGE_SLUG );
		?>
		<?php submit_button(); ?>
		</form>
		<?php Common::affiliate_banner(); ?>
	</div>
	<?php elseif ( 'debug' === $active_tab ) : ?>
	<div class="tasty-recipes-settings tasty-settings">
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">License Key</th>
					<td><input class="regular-text" type="text" readonly value="<?php echo esc_attr( get_option( Tasty_Recipes::LICENSE_KEY_OPTION ) ); ?>"></td>
				</tr>
				<?php do_action( 'tasty_recipes_debug_settings' ); ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>

</div>
