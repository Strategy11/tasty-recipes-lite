const { registerBlockType } = wp.blocks;
const { registerPlugin } = wp.plugins;

/**
 * Internal dependencies
 */
import generateNotices from "./admin-notices";
import blockRegistration from "./recipe-block";
import TastyRecipesMetaBox from "./metabox";

registerBlockType("wp-tasty/tasty-recipe", blockRegistration);

window.addEventListener("load", () => {
	generateNotices();
	/* Removes the conversion success query string on browsers that support it. */
	if (window.history) {
		let newLocation = window.location.href;
		newLocation = newLocation.replace(
			"&tasty_recipes_message=converted_recipe_success",
			""
		);
		if (newLocation !== window.location.href) {
			window.history.replaceState("", "", newLocation);
		}
	}
});

if ( Object.keys(window.tastyRecipesEditor.dismissedConverters).length > 0 ) {
	registerPlugin("tasty-recipes-metabox", {
		render: TastyRecipesMetaBox,
	});
}
