<?php
/**
 * Backwards-compat template for sites with EasyRecipe styling.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes_Lite\Utils;
?>
<?php echo $recipe_styles; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

<?php if ( ! empty( $recipe_rating_label ) ) : ?>
<div class="ERSRatings" itemprop="aggregateRating" itemscope="" itemtype="https://schema.org/AggregateRating">
	<div class="ERSRatingOuter">
		<?php $average_rating = $recipe->get_average_rating(); ?>
		<div class="ERSRatingInner" style="width: <?php echo esc_attr( round( $average_rating * 2 * 10 ) ); ?>%"></div>
		<div class="review"><span class="rating"><span class="average" itemprop="ratingValue"><?php echo esc_html( $average_rating ); ?></span> from <span class="count" itemprop="ratingCount"><?php echo esc_html( $recipe->get_total_reviews() ); ?></span> reviews</span></div>
	</div>
</div>
<?php endif; ?>

<div itemprop="name" class="ERSName"><?php Utils::kses( $recipe_json['title_rendered'], true ); ?></div>

<div class="ERSClear"></div>

<div class="ERSDetails">
	<?php if ( ! empty( $recipe_details['author'] ) ) : ?>
	<div class="ERSAuthor"><?php echo esc_html( $recipe_details['author']['label'] ); ?>:&nbsp;<?php Utils::kses( $recipe_details['author']['value'], true ); ?></div>
	<?php endif; ?>
	<?php
	foreach ( array( 'category', 'cuisine' ) as $detail ) :
		if ( empty( $recipe_details[ $detail ] ) ) {
			continue;
		}
		?>
		<div class="ERSHead"><?php echo esc_html( $recipe_details[ $detail ]['label'] ); ?>:&nbsp;<?php Utils::kses( $recipe_details[ $detail ]['value'], true ); ?></div>
	<?php endforeach; ?>
	<div class="ERSClear"></div>
	<?php
	if ( ! empty( $recipe_details['prep_time'] ) || ! empty( $recipe_details['cook_time'] ) ) :
		?>
		<div class="ERSTimes">
			<?php
			foreach ( array( 'prep_time', 'cook_time', 'total_time' ) as $detail ) :
				if ( empty( $recipe_details[ $detail ] ) ) {
					continue;
				}
				?>
			<div class="ERSHead"><?php echo esc_html( $recipe_details[ $detail ]['label'] ); ?>:&nbsp;<?php Utils::kses( $recipe_details[ $detail ]['value'], true ); ?></div>
		<?php endforeach; ?>
		</div>
		<div class="ERSClear"></div>
	<?php endif; ?>
	<?php if ( ! empty( $recipe_details['yield'] ) ) : ?>
	<div class="ERSHead"><?php echo esc_html( $recipe_details['yield']['label'] ); ?>:&nbsp;<?php Utils::kses( $recipe_details['yield']['value'], true ); ?></div>
	<div class="ERSClear"></div>
	<?php endif; ?>
</div>

<?php if ( ! empty( $recipe_ingredients ) ) : ?>
	<div class="ERSIngredients">
		<div class="ERSIngredientsHeader ERSHeading"><?php esc_html_e( 'Ingredients', 'tasty-recipes-lite' ); ?></div>
		<?php
		$recipe_ingredients = str_replace( '<li', '<li class="ingredient"', $recipe_ingredients );
		Utils::kses( $recipe_ingredients, true );
		?>
		<div class="ERSClear"></div>
	</div>
<?php endif; ?>

<?php if ( ! empty( $recipe_instructions ) ) : ?>
	<div class="ERSInstructions">
		<div class="ERSInstructionsHeader ERSHeading"><?php esc_html_e( 'Instructions', 'tasty-recipes-lite' ); ?></div>
		<?php
		$recipe_instructions = str_replace( '<li', '<li class="instruction"', $recipe_instructions );
		Utils::kses( $recipe_instructions, true );
		?>
		<div class="ERSClear"></div>
	</div>
<?php endif; ?>

<?php if ( ! empty( $recipe_notes ) ) : ?>
	<div class="ERSNotesDiv">
		<div class="ERSNotesHeader"><?php esc_html_e( 'Notes', 'tasty-recipes-lite' ); ?></div>
		<div class="ERSNotes">
			<?php Utils::kses( $recipe_notes, true ); ?>
		</div>
		<div class="ERSClear"></div>
	</div>
<?php endif; ?>

<?php if ( ! empty( $recipe_nutrition ) ) : ?>
	<div class="nutrition" itemscope itemprop="nutrition" itemtype="https://schema.org/NutritionInformation">
		<div class="ERSNutritionHeader"><?php esc_html_e( 'Nutrition', 'tasty-recipes-lite' ); ?></div>
		<div class="ERSNutrionDetails">
			<?php foreach ( $recipe_nutrition as $nutrition ) : ?>
				<?php echo esc_html( $nutrition['label'] ); ?>:</strong>&nbsp;<?php Utils::kses( $nutrition['value'], true ); ?>&nbsp;
			<?php endforeach; ?>
		</div>
	</div>
<?php endif; ?>
