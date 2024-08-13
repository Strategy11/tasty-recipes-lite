<?php
/**
 * Fresh recipe card template.
 *
 * @package Tasty_Recipes
 */

$ext = is_feed() ? '.png' : '.svg';

$print_view_options = tasty_recipes_get_print_view_options();
?>
<?php echo $recipe_styles; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

<?php
if ( '.svg' === $ext ) {
	echo str_replace( array( "\n", "\t" ), '', \Tasty_Recipes\Utils::get_contents( __DIR__ . '/images/icon-sprite.svg' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
?>

<header class="tasty-recipes-entry-header">
	<?php if ( ! empty( $recipe_image ) && isset( $print_view_options['images'] ) ) : ?>
		<div class="tasty-recipes-image">
			<?php \Tasty_Recipes\Utils::kses( $recipe_image, true ); ?>
		</div>
	<?php elseif ( isset( $print_view_options['images'] ) ) : ?>
		<div class="tasty-recipes-image">
			<img class="detail-icon" data-pin-nopin="true" src="<?php echo esc_url( plugins_url( 'images/placeholder.png', __FILE__ ) ); ?>">
		</div>
	<?php endif; ?>
	<div class="tasty-recipes-header-content clearfix">
		<h2 class="tasty-recipes-title" data-tasty-recipes-customization="h2-color.color h2-transform.text-transform"><?php \Tasty_Recipes\Utils::kses( $recipe_title, true ); ?></h2>
		<?php if ( ! empty( $recipe_rating_icons ) || ! empty( $recipe_rating_label ) ) : ?>
			<div class="tasty-recipes-rating">
				<div class="tasty-recipes-rating-outer">
					<div class="tasty-recipes-rating-inner">
						<?php if ( ! empty( $recipe_rating_icons ) ) : ?>
							<span class="tasty-recipes-rating-stars"><?php echo $recipe_rating_icons; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php endif; ?>
						<?php if ( ! empty( $recipe_rating_label ) ) : ?>
							<span class="tasty-recipes-rating-label"><?php \Tasty_Recipes\Utils::kses( $recipe_rating_label, true ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php if ( ! empty( $recipe_details ) ) : ?>
			<div class="tasty-recipes-details">
				<ul>
					<?php foreach ( $recipe_details as $key => $detail ) : ?>
						<li class="<?php echo esc_attr( $detail['class'] ); ?>"><span class="tasty-recipes-label" data-tasty-recipes-customization="detail-label-color.color">
							<?php
							$icons = array(
								'cook_time'       => 'icon-clock',
								'prep_time'       => 'icon-clock',
								'additional_time' => 'icon-clock',
								'total_time'      => 'icon-clock',
								'method'          => 'icon-squares',
								'cuisine'         => 'icon-flag',
								'diet'            => 'icon-heart-regular',
								'category'        => 'icon-folder',
								'yield'           => 'icon-cutlery',
							);
							if ( isset( $icons[ $key ] ) ) {
								if ( '.svg' === $ext ) {
									?>
									<svg viewBox="0 0 24 24" class="detail-icon" aria-hidden="true" data-tasty-recipes-customization="icon-color.color"><use xlink:href="#tasty-recipes-<?php echo esc_attr( $icons[ $key ] ); ?>"></use></svg>
									<?php
								} else {
									?>
									<img class="detail-icon" data-pin-nopin="true" src="<?php echo esc_url( plugins_url( 'images/' . $icons[ $key ] . '-gray.png', __FILE__ ) ); ?>">
									<?php
								}
							}
							?>
						<?php echo esc_html( $detail['label'] ); ?>:</span> <?php \Tasty_Recipes\Utils::kses( $detail['value'], true ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<?php if ( ! tasty_recipes_is_print() ) : ?>
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

	<?php endif; ?>

</header>

<div class="tasty-recipes-entry-content">
	<hr data-tasty-recipes-customization="secondary-color.border-color secondary-color.background-color">

	<?php
	$show_hr = false;
	if ( ! empty( $recipe_description ) && '<div itemprop="description"></div>' !== $recipe_description && isset( $print_view_options['description'] ) ) :
		$show_hr = true;
		?>
		<div class="tasty-recipes-description">
			<h3 data-tasty-recipes-customization="h3-color.color h3-transform.text-transform"><?php esc_html_e( 'Description', 'tasty-recipes' ); ?></h3>
			<div class="tasty-recipes-description-body" data-tasty-recipes-customization="body-color.color">
				<?php \Tasty_Recipes\Utils::kses( $recipe_description, true ); ?>
			</div>
		</div>
	<?php endif; ?>

	<?php
	if ( $show_hr ) :
		$show_hr = false;
		?>
		<hr data-tasty-recipes-customization="secondary-color.border-color secondary-color.background-color">
	<?php endif; ?>

	<?php
	if ( ! empty( $recipe_ingredients ) ) :
		$show_hr = true;
		?>
		<div class="tasty-recipes-ingredients">
			<div class="tasty-recipes-ingredients-header">
				<div class="tasty-recipes-ingredients-clipboard-container">
					<h3 data-tasty-recipes-customization="h3-color.color h3-transform.text-transform"><?php esc_html_e( 'Ingredients', 'tasty-recipes' ); ?></h3>
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
					<?php if ( ! empty( $recipe_scalable ) ) : ?>
						<span class="tasty-recipes-scale-container">
							<span class="tasty-recipes-scale-label"><?php esc_html_e( 'Scale', 'tasty-recipes' ); ?></span>
							<?php \Tasty_Recipes\Utils::kses( $recipe_scalable, true ); ?>
						</span>
					<?php endif; ?>
				</div>
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

	<?php
	if ( $show_hr ) :
		$show_hr = false;
		?>
		<hr data-tasty-recipes-customization="secondary-color.border-color secondary-color.background-color">
	<?php endif; ?>

	<?php
	if ( ! empty( $recipe_instructions ) ) :
		$show_hr = true;
		?>
		<div class="tasty-recipes-instructions">
			<div class="tasty-recipes-instructions-header">
				<h3 data-tasty-recipes-customization="h3-color.color h3-transform.text-transform"><?php esc_html_e( 'Instructions', 'tasty-recipes' ); ?></h3>
				<?php if ( ! empty( $recipe_instructions_has_video ) ) : ?>
				<div class="tasty-recipes-video-toggle-container">
					<label for="tasty-recipes-video-toggle"><?php esc_html_e( 'Video', 'tasty-recipes' ); ?></label>
					<button type="button" role="switch" aria-checked="true" name="tasty-recipes-video-toggle">
						<span><?php esc_html_e( 'On', 'tasty-recipes' ); ?></span>
						<span><?php esc_html_e( 'Off', 'tasty-recipes' ); ?></span>
					</button>
				</div>
				<?php endif; ?>
			</div>
			<div class="tasty-recipes-instructions-body" data-tasty-recipes-customization="body-color.color">
				<?php echo $recipe_instructions; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
	<?php endif; ?>

	<?php
	if ( ! empty( $recipe_video_embed ) ) :
		$show_hr = false;
		?>
		<div class="tasty-recipe-video-embed" id="<?php echo esc_attr( 'tasty-recipe-video-embed-' . $recipe->get_id() ); ?>">
			<?php echo $recipe_video_embed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php endif; ?>

	<?php
	if ( $show_hr ) :
		$show_hr = false;
		?>
		<hr data-tasty-recipes-customization="secondary-color.border-color secondary-color.background-color">
	<?php endif; ?>

	<?php
	if ( ! empty( $recipe_equipment ) ) :
		$show_hr = true;
		?>
		<div class="tasty-recipes-equipment">
			<h3 data-tasty-recipes-customization="h3-color.color h3-transform.text-transform"><?php esc_html_e( 'Equipment', 'tasty-recipes' ); ?></h3>
			<?php \Tasty_Recipes\Utils::kses( $recipe_equipment, true ); ?>
		</div>
	<?php endif; ?>

	<?php
	if ( $show_hr ) :
		$show_hr = false;
		?>
		<hr data-tasty-recipes-customization="secondary-color.border-color secondary-color.background-color">
	<?php endif; ?>

	<?php if ( ! empty( $recipe_notes ) && isset( $print_view_options['notes'] ) ) : ?>
		<div class="tasty-recipes-notes">
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
		<?php if ( empty( $recipe_nutrifox_id ) ) : ?>
			<hr data-tasty-recipes-customization="secondary-color.border-color secondary-color.background-color">
		<?php endif; ?>
		<div class="tasty-recipes-nutrition">
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
		<div class="tasty-recipes-footer-content">
			<?php if ( ! empty( $footer_social_platform ) ) : ?>
				<?php if ( '.svg' === $ext ) : ?>
					<svg viewBox="0 0 24 24" class="<?php echo esc_attr( 'svg-' . $footer_social_platform ); ?>" aria-hidden="true" data-tasty-recipes-customization="footer-icon-color.color"><use xlink:href="#tasty-recipes-icon-<?php echo esc_attr( $footer_social_platform ); ?>"></use></svg>
				<?php else : ?>
					<img class="<?php echo esc_attr( 'svg-' . $footer_social_platform ); ?>" data-pin-nopin="true" src="<?php echo esc_url( plugins_url( 'images/icon-' . $footer_social_platform . '.png', __FILE__ ) ); ?>">
				<?php endif; ?>
			<?php endif; ?>
			<div class="tasty-recipes-footer-copy">
				<h3 data-tasty-recipes-customization="footer-heading-color.color h3-transform.text-transform footer-heading.innerText"><?php echo esc_html( $footer_heading ); ?></h3>
				<div data-tasty-recipes-customization="footer-description-color.color footer-description.innerHTML"><?php echo wp_kses_post( $footer_description ); ?></div>
			</div>
		</div>
	</footer>
</div>
<?php if ( tasty_recipes_is_print() && get_post() ) : ?>
<div class="tasty-recipes-source-link">
	<p><strong class="tasty-recipes-label"><?php esc_html_e( 'Find it online', 'tasty-recipes' ); ?></strong>: <a href="<?php echo esc_url( get_permalink( get_the_ID() ) ); ?>"><?php echo esc_url( get_permalink( get_the_ID() ) ); ?></a></p>
</div>
<?php endif; ?>

<?php echo $recipe_scripts; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
