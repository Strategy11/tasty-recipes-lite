<?php
/**
 * Template for the Grow button.
 *
 * @package Tasty_Recipes
 *
 * @var object $recipe        Recipe object.
 * @var string $customization Customization options.
 */

namespace Tasty_Recipes;

use Tasty_Recipes\Objects\Recipe;
use Tasty_Recipes\Integrations;

Integrations\Mediavine::load_scripts();

?>

<a class="button tasty-recipes-mediavine-button tasty-recipes-no-print" href="https://app.grow.me" target="_blank" data-recipe-id="<?php echo esc_attr( $recipe instanceof Recipe ? $recipe->get_id() : '' ); ?>" data-tasty-recipes-customization="<?php echo esc_attr( $customization ); ?>">
	<span class="tasty-recipes-not-saved">
		<svg viewBox="0 0 24 24" class="svg-heart-regular" aria-hidden="true"><use xlink:href="#tasty-recipes-icon-heart-regular"></use></svg>
		<?php esc_html_e( 'Save Recipe', 'tasty-recipes' ); ?>
	</span>
	<span class="tasty-recipes-saved" style="display: none;">
		<svg viewBox="0 0 24 24" class="svg-heart-solid" aria-hidden="true"><use xlink:href="#tasty-recipes-icon-heart-solid"></use></svg>
		<?php esc_html_e( 'Recipe Saved', 'tasty-recipes' ); ?>
	</span>
</a>
