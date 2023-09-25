<?php
/**
 * Template for the settings page.
 *
 * @package Tasty_Recipes
 */

use Tasty\Recipes\Lite\Admin;
use Tasty\Recipes\Lite\Utils;
use Tasty\Framework\Utils\Common;

?>

<?php

$active_tab = Utils::sanitize_get_key( 'tab', 'design' );
$debug      = Utils::sanitize_get_key( 'debug' );
?>

<div class="wrap">
	<div class="wp-header-end"></div>
	<div class="tasty-recipes-nav tasty-tabs nav-tab-wrapper">
		<p>
			<a class="nav-tab<?php echo 'design' === $active_tab ? ' nav-tab-active' : ''; ?>" href="<?php menu_page_url( Admin::PAGE_SLUG ); ?>">
				<?php esc_html_e( 'Design', 'tasty-recipes' ); ?>
			</a>
		</p>
		<p>
			<a class="nav-tab<?php echo 'settings' === $active_tab ? ' nav-tab-active' : ''; ?>" href="<?php menu_page_url( Admin::PAGE_SLUG ); ?>&tab=settings">
				<?php esc_html_e( 'Settings', 'tasty-recipes' ); ?>
			</a>
		</p>
		<p>
			<a class="nav-tab<?php echo 'converters' === $active_tab ? ' nav-tab-active' : ''; ?>" href="<?php menu_page_url( Admin::PAGE_SLUG ); ?>&tab=converters">
				<?php esc_html_e( 'Converters', 'tasty-recipes' ); ?>
			</a>
		</p>
		<?php if ( ! empty( $debug ) || 'debug' === $active_tab ) { ?>
		<p>
			<a class="nav-tab<?php echo 'debug' === $active_tab ? ' nav-tab-active' : ''; ?>" href="<?php menu_page_url( Admin::PAGE_SLUG ); ?>&tab=debug">
				<?php esc_html_e( 'Debug', 'tasty-recipes' ); ?>
			</a>
		</p>
		<?php } ?>
		<p>
			<a class="nav-tab<?php echo 'about' === $active_tab ? ' nav-tab-active' : ''; ?>" href="<?php menu_page_url( Admin::PAGE_SLUG ); ?>&tab=about">
				<?php esc_html_e( 'About', 'tasty-recipes' ); ?>
			</a>
		</p>
		<p><a class="nav-support" target="_blank" href="http://support.wptasty.com"><?php esc_html_e( 'Support and documentation', 'tasty-recipes' ); ?>
			<svg width="15px" height="15px" viewBox="0 0 15 15" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
				<g id="Tasty-Recipes" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
					<g id="tasty-recipes-external-link" transform="translate(-985.000000, -102.000000)" fill="#353547" fill-rule="nonzero">
						<g id="external-link-alt-solid-copy" transform="translate(985.000000, 102.000000)">
							<path d="M12.65625,9.375 L11.71875,9.375 C11.4598665,9.375 11.25,9.58486652 11.25,9.84375 L11.25,13.125 L1.875,13.125 L1.875,3.75 L6.09375,3.75 C6.35263348,3.75 6.5625,3.54013348 6.5625,3.28125 L6.5625,2.34375 C6.5625,2.08486652 6.35263348,1.875 6.09375,1.875 L1.40625,1.875 C0.629599571,1.875 0,2.50459957 0,3.28125 L0,13.59375 C0,14.3704004 0.629599571,15 1.40625,15 L11.71875,15 C12.4954004,15 13.125,14.3704004 13.125,13.59375 L13.125,9.84375 C13.125,9.58486652 12.9151335,9.375 12.65625,9.375 Z M14.296875,0 L10.546875,0 C9.92080078,0 9.60791016,0.759082031 10.0488281,1.20117187 L11.0956055,2.24794922 L3.95507812,9.38583984 C3.82268823,9.51777085 3.74827366,9.69698334 3.74827366,9.88388672 C3.74827366,10.0707901 3.82268823,10.2500026 3.95507812,10.3819336 L4.61923828,11.0449219 C4.75116929,11.1773118 4.93038177,11.2517263 5.11728516,11.2517263 C5.30418854,11.2517263 5.48340103,11.1773118 5.61533203,11.0449219 L12.7523437,3.90585937 L13.7988281,4.95117187 C14.2382812,5.390625 15,5.08300781 15,4.453125 L15,0.703125 C15,0.314799785 14.6852002,0 14.296875,0 Z" id="Shape"></path>
						</g>
					</g>
				</g>
			</svg>
		</a></p>
	</div>

	<?php if ( 'design' === $active_tab ) : ?>
		<?php
		if ( file_exists( get_stylesheet_directory() . '/tasty-recipes.php' ) ) {
			?>
			<div class="tasty-recipes-custom-template-notice"><p><?php esc_html_e( 'Looks like you\'re using a custom recipe card template. To enable design customization options, you\'ll need to remove the custom tasty-recipes.php file from your theme folder.', 'tasty-recipes' ); ?></p></div>
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
				settings_fields( Admin::SETTINGS_SECTION_CARD_DESIGN );
				$initial_options = array(
					'template'      => get_option( TastyRecipesLiteMain::TEMPLATE_OPTION, '' ),
					'customization' => TastyRecipesLiteMain::get_customization_settings(),
				);
				?>
				<tasty-recipes-design-settings
					recipe-card-preview-url-base="<?php echo esc_url( $recipe_card_preview_url_base ); ?>"
					:template-options="<?php echo esc_attr( wp_json_encode( Admin::TEMPLATE_OPTIONS ) ); ?>"
					:initial-options="<?php echo esc_attr( wp_json_encode( $initial_options ) ); ?>"></tasty-recipes-design-settings>
			</form>
			<?php
		}
	elseif ( 'about' === $active_tab ) :
		?>
		<?php include dirname( __FILE__ ) . '/about.php'; ?>
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
					<h3><?php esc_html_e( 'Convert Existing Recipes', 'tasty-recipes' ); ?></h3>
					<p>
					<?php
					echo sprintf(
						// translators: %1$s opening anchor tag to the conversion page, %2$s closing tag.
						esc_html__( 'Just in case, we recommend backing up your database before converting your recipes in bulk to Tasty Recipes. %1$sLearn more about backing up your database%2$s.', 'tasty-recipes' ),
						'<a href="https://www.wptasty.com/convert-all" target="_blank" rel="noopener">',
						'</a>'
					);
					?>
					</p>
					<p><?php esc_html_e( 'Select an option from the left to start converting recipes.', 'tasty-recipes' ); ?></p>
				</div>
				<div class="tasty-recipes-converter-control"></div>
			</div>
		</div>
		<script type="text/html" id="tmpl-tasty-recipes-convert">
			<button type="button" class="tasty-recipes-converter-close"><span class="tasty-recipes-converter-icon"><span class="screen-reader-text"><?php esc_html_e( 'Close Converter', 'tasty-recipes' ); ?></span></span></button>

			<h3>
				<?php
				// translators: Source of the conversion.
				echo esc_html( sprintf( __( 'Convert recipes from %s', 'tasty-recipes' ), '{{ data.label }}' ) );
				?>
			</h3>

			<# if ( data.loading ) { #>
				<p><?php esc_html_e( 'Determining whether there are any recipes to convert.', 'tasty-recipes' ); ?></p>
			<# } #>
			<# if ( data.converting ) {
				if ( data.converted < data.count ) {
				#>
					<p>
					<?php
					// translators: Source of the conversion.
					echo esc_html( sprintf( __( 'Converting recipes from %s. Leave this window open until the conversion is complete.', 'tasty-recipes' ), '{{ data.label }}' ) );
					?>
					</p>
					<progress value="{{ data.converted }}" max="{{ data.count }}">
				<# } else { #>
					<p>
					<?php
					// translators: Source of the conversion.
					echo esc_html( sprintf( __( '%s conversion complete. You may now close this converter.', 'tasty-recipes' ), '{{ data.label }}' ) );
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
					echo sprintf( esc_html__( '%1$s recipe was found to convert from %2$s.', 'tasty-recipes' ), '{{ data.count }}', '{{ data.label }}' );
					?>
					</p>
					<# } else { #>
					<p>
					<?php
					// translators: Plural number of recipes, source.
					echo sprintf( esc_html__( '%1$s recipes were found to convert from %2$s.', 'tasty-recipes' ), '{{ data.count }}', '{{ data.label }}' );
					?>
					</p>
					<# } #>
					<button class="button start-conversion"><?php esc_html_e( 'Start Conversion', 'tasty-recipes' ); ?></button>
				<# } else { #>
					<p>
					<?php
					// translators: Source of the conversion.
					echo sprintf( esc_html__( 'No %s recipes were found to convert.', 'tasty-recipes' ), '{{ data.label }}' );
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
			settings_fields( Tasty_Recipes\Admin::SETTINGS_GROUP_CARD );
			do_settings_sections( Tasty_Recipes\Admin::PAGE_SLUG );
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
				<?php
				if ( get_option( Tasty_Recipes::UNIT_CONVERSION_OPTION ) ) :
					$ingredients = <<<EOT
1 cup flour
1 stick butter
EOT;
					$response    = wp_remote_post(
						sprintf( '%s/api/tasty-recipes/conversion-enrichment', Tasty_Recipes::nutrifox_url() ),
						array(
							'headers' => array(
								'Content-Type'        => 'application/json',
								'X-Tasty-Recipes-License-Key' => get_option( Tasty_Recipes::LICENSE_KEY_OPTION ),
								'X-Tasty-Recipes-Url' => home_url(),
								'X-Tasty-Recipes-Recipe-Id' => 0,
							),
							'body'    => wp_json_encode(
								array(
									'ingredients' => $ingredients,
								)
							),
							'timeout' => 20,
						)
					);
					if ( is_wp_error( $response ) ) {
						$response_body = $response->get_error_message();
					} else {
						$response_body = wp_remote_retrieve_body( $response );
					}
					?>
				<tr>
					<th scope="row">Nutrifox Unit Conversion API</th>
					<td><pre style="white-space:pre-wrap;"><?php echo esc_html( $response_body ); ?></pre></td>
				</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>

</div>
