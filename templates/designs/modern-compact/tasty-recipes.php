<?php
/**
 * Modern Compact recipe template.
 *
 * @package Tasty_Recipes
 */

$print_view_options = tasty_recipes_get_print_view_options();
?>

<?php echo $recipe_styles; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

<div class="tasty-recipes-header">
	<?php if ( ! empty( $recipe_print_button ) && ! tasty_recipes_is_print() ) : ?>
		<?php echo $recipe_print_button; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php endif; ?>

	<h2 class="tasty-recipes-title" data-tasty-recipes-customization="h2-color.color h2-transform.text-transform"><?php \Tasty_Recipes\Utils::kses( $recipe_title, true ); ?></h2>
	<div class="tasty-recipes-image-button-container">
		<?php if ( ! empty( $recipe_image ) && isset( $print_view_options['images'] ) ) : ?>
			<div class="tasty-recipes-image">
				<?php \Tasty_Recipes\Utils::kses( $recipe_image, true ); ?>
			</div>
		<?php endif; ?>
		<div class="tasty-recipes-buttons">
			<?php if ( ! empty( $first_button ) ) : ?>
			<div class="tasty-recipes-button-wrap">
				<?php echo $first_button; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<?php endif; ?>
			<?php if ( ! empty( $second_button ) ) : ?>
			<div class="tasty-recipes-button-wrap">
				<?php echo $second_button; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<?php endif; ?>
		</div>
	</div>
	<?php if ( ! empty( $recipe_rating_icons ) || ! empty( $recipe_rating_label ) ) : ?>
		<div class="tasty-recipes-rating">
			<?php if ( Tasty_Recipes\Ratings::is_enabled() ) : ?>
				<a href="#respond">
			<?php endif; ?>
				<?php if ( ! empty( $recipe_rating_icons ) ) : ?>
					<p><?php echo $recipe_rating_icons; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $recipe_rating_label ) ) : ?>
					<p><?php \Tasty_Recipes\Utils::kses( $recipe_rating_label, true ); ?></p>
				<?php endif; ?>
			<?php if ( Tasty_Recipes\Ratings::is_enabled() ) : ?>
				</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $recipe_description ) && isset( $print_view_options['description'] ) ) : ?>
		<div class="tasty-recipes-description" data-tasty-recipes-customization="body-color.color">
			<?php \Tasty_Recipes\Utils::kses( $recipe_description, true ); ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $recipe_details ) ) : ?>
		<div class="tasty-recipes-details">
			<ul>
				<?php foreach ( $recipe_details as $detail ) : ?>
					<li class="<?php echo esc_attr( $detail['class'] ); ?>"><strong data-tasty-recipes-customization="detail-label-color.color" class="tasty-recipes-label"><?php echo esc_html( $detail['label'] ); ?>:</strong> <?php \Tasty_Recipes\Utils::kses( $detail['value'], true ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
</div>

<div class="tasty-recipes-content tasty-recipes-modern-compact-content">
	<?php if ( ! empty( $recipe_ingredients ) ) : ?>
		<div class="tasty-recipe-ingredients" data-tasty-recipes-customization="secondary-color.border-color">
			<div class="tasty-recipes-ingredients-clipboard-container" data-tasty-recipes-customization="secondary-color.background-color">
				<h3 data-tasty-recipes-customization="secondary-color.background-color h3-color.color h3-transform.text-transform"><?php esc_html_e( 'Ingredients', 'tasty-recipes' ); ?></h3>
				<?php if ( $copy_ingredients ) : ?>
					<?php echo $copy_ingredients; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
			</div>
			<div class="tasty-recipes-units-scale-container">
				<?php if ( ! empty( $recipe_convertable ) ) : ?>
					<span class="tasty-recipes-convert-container">
						<span class="tasty-recipes-convert-label"><?php esc_html_e( 'Units', 'tasty-recipes' ); ?></span>
						<?php \Tasty_Recipes\Utils::kses( $recipe_convertable, true ); ?>
					</span>
				<?php endif; ?>
					<span class="tasty-recipes-scale-container">
				<?php if ( ! empty( $recipe_scalable ) ) : ?>
						<span class="tasty-recipes-scale-label"><?php esc_html_e( 'Scale', 'tasty-recipes' ); ?></span>
						<?php \Tasty_Recipes\Utils::kses( $recipe_scalable, true ); ?>
					</span>
				<?php endif; ?>
			</div>
			<div class="tasty-recipes-ingredients-body" data-tasty-recipes-customization="body-color.color">
				<?php echo $recipe_ingredients; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<?php
			/**
			 * Add more information after the ingredient list.
			 *
			 * @var object $recipe The recipe to display.
			 */
			do_action( 'tasty_recipes_card_after_ingredients', $recipe );
			?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $recipe_instructions ) ) : ?>
		<div class="tasty-recipe-instructions" data-tasty-recipes-customization="secondary-color.border-color">
			<h3 data-tasty-recipes-customization="secondary-color.background-color h3-color.color h3-transform.text-transform" <?php if ( $copy_ingredients ) : ?>
				data-copyable="true"
				<?php endif; ?>
			>
				<?php esc_html_e( 'Instructions', 'tasty-recipes' ); ?>
			</h3>
			<?php if ( ! empty( $recipe_instructions_has_video ) ) : ?>
			<div class="tasty-recipes-video-toggle-container">
				<label for="tasty-recipes-video-toggle"><?php esc_html_e( 'Video', 'tasty-recipes' ); ?></label>
				<button type="button" role="switch" aria-checked="true" name="tasty-recipes-video-toggle">
					<span><?php esc_html_e( 'On', 'tasty-recipes' ); ?></span>
					<span><?php esc_html_e( 'Off', 'tasty-recipes' ); ?></span>
				</button>
			</div>
			<?php endif; ?>
			<div class="tasty-recipes-instructions-body" data-tasty-recipes-customization="body-color.color">
				<?php echo $recipe_instructions; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
	<?php endif; ?>
</div>

<?php if ( ! empty( $recipe_video_embed ) ) : ?>
<div class="tasty-recipe-video-embed" id="<?php echo esc_attr( 'tasty-recipe-video-embed-' . $recipe->get_id() ); ?>">
	<?php echo $recipe_video_embed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
<?php endif; ?>

<?php
if ( ! empty( $recipe_equipment ) ) :
	?>
	<div class="tasty-recipes-equipment">
		<h3 data-tasty-recipes-customization="h3-color.color h3-transform.text-transform"><?php esc_html_e( 'Equipment', 'tasty-recipes' ); ?></h3>
		<?php \Tasty_Recipes\Utils::kses( $recipe_equipment, true ); ?>
	</div>
<?php endif; ?>

<?php if ( ! empty( $recipe_notes ) && isset( $print_view_options['notes'] ) ) : ?>
	<div class="tasty-recipes-notes" data-tasty-recipes-customization="secondary-color.border-color">
		<h3 data-tasty-recipes-customization="h3-color.color h3-transform.text-transform"><?php esc_html_e( 'Notes', 'tasty-recipes' ); ?></h3>
		<div class="tasty-recipes-notes-body" data-tasty-recipes-customization="body-color.color">
			<?php \Tasty_Recipes\Utils::kses( $recipe_notes, true ); ?>
		</div>
	</div>
<?php endif; ?>

<?php do_action( 'tasty_recipes_card_before_nutrition' ); ?>

<?php if ( ! empty( $recipe_nutrifox_embed ) && isset( $print_view_options['nutrition'] ) ) : ?>
	<div class="tasty-recipes-nutrifox">
		<?php echo $recipe_nutrifox_embed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
<?php endif; ?>

<?php if ( ! empty( $recipe_nutrition ) && isset( $print_view_options['nutrition'] ) ) : ?>
	<div class="tasty-recipes-nutrition" data-tasty-recipes-customization="secondary-color.border-color">
		<h3 data-tasty-recipes-customization="h3-color.color h3-transform.text-transform"><?php esc_html_e( 'Nutrition', 'tasty-recipes' ); ?></h3>
		<ul>
			<?php foreach ( $recipe_nutrition as $nutrition ) : ?>
				<li><strong class="tasty-recipes-label" data-tasty-recipes-customization="body-color.color"><?php echo esc_html( $nutrition['label'] ); ?>:</strong> <?php \Tasty_Recipes\Utils::kses( $nutrition['value'], true ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>

<?php if ( ! empty( $recipe_keywords ) ) : ?>
	<div class="tasty-recipes-keywords" data-tasty-recipes-customization="detail-value-color.color">
		<p><em><strong data-tasty-recipes-customization="detail-label-color.color"><?php esc_html_e( 'Keywords', 'tasty-recipes' ); ?>:</strong> <?php \Tasty_Recipes\Utils::kses( $recipe_keywords, true ); ?></em></p>
	</div>
<?php endif; ?>

<footer class="tasty-recipes-entry-footer">
	<h3 data-tasty-recipes-customization="footer-heading-color.color h3-transform.text-transform footer-heading.innerText"><?php echo esc_html( $footer_heading ); ?></h3>
	<div data-tasty-recipes-customization="footer-description-color.color footer-description.innerHTML"><?php echo wp_kses_post( $footer_description ); ?></div>
</footer>

<?php if ( tasty_recipes_is_print() && get_post() ) : ?>
	<div class="tasty-recipes-source-link">
		<p><strong class="tasty-recipes-label"><?php esc_html_e( 'Find it online', 'tasty-recipes' ); ?></strong>: <a href="<?php echo esc_url( get_permalink( get_the_ID() ) ); ?>"><?php echo esc_url( get_permalink( get_the_ID() ) ); ?></a></p>
	</div>
<?php endif; ?>

<?php echo $recipe_scripts; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
