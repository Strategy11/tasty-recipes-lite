<?php
/**
 * Template for upgrade modal.
 *
 * @package Tasty_Recipes
 */

?>

<section class="tasty-framework-modal" id="tasty_recipes_upgrade_modal">
	<div class="tasty-framework-modal-container">
		<a href="#" class="tasty-hide-modal tasty-framework-close-modal tasty-framework-modal-x" data-modal_id="tasty_recipes_upgrade_modal"></a>
		<div class="tasty-framework-modal-header">
			<div class="tasty-recipes-lock"></div>
			<h2>__modal_title__</h2>
		</div>

		<div class="tasty-framework-spacer"></div>

		<div class="tasty-framework-modal-body">
			<?php esc_html_e( 'This is a premium feature. Consider upgrading to a paid plan to get this and more.', 'tasty-recipes-lite' ); ?>

			<div class="tasty-framework-spacer"></div>

			<a href="<?php echo esc_url( $tasty_recipes_lite_upgrade_url ); ?>" target="_blank" rel="noopener" class="tasty-button tasty-button-pink tasty-button-large tasty-button-full">
				<?php esc_html_e( 'View prices >', 'tasty' ); ?>
			</a>
		</div>
	</div>
</section>
