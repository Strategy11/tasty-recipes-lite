/**
 * Option name map localized from PHP Options registries.
 *
 * Defaults cover Pro-owned form fields rendered in Lite locked UI so names
 * remain valid before Pro merges its map.
 */
const defaults = {
	enableHowTos: 'tasty_recipes_enable_how_tos',
	saveRecipeNewsletter: 'tasty_recipes_save_recipe_newsletter',
	cookModeHelper: 'tasty_recipes_cook_mode_helper',
	instacartAffiliateId: 'tasty_recipes_instacart_affiliate_id',
	instacartButtonType: 'tasty_recipes_instacart_button_type',
	enableHalfScaling: 'tasty_recipes_enable_half_scaling',
	enableTripleScaling: 'tasty_recipes_enable_triple_scaling',
};

const localized = window.tastyRecipesSettings?.optionNames;

export const optionNames = {
	...defaults,
	...( localized || {} ),
};
