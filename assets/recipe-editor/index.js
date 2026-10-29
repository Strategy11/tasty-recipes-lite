import { createRoot } from '@wordpress/element';
import { RecipeForm } from './components/RecipeForm';
import { undoRowListHistory } from './components/recipe-row-list';

window.tastyRecipesUndoRowList = undoRowListHistory;

let instance;

window.tastyRecipesMountRecipeEditor = ( el, initialRecipe, options = {} ) => {
	instance = createRoot( el );
	instance.render(
		<RecipeForm
			initialRecipe={ initialRecipe }
			config={ window.tastyRecipesRecipeEditorData }
			initialView={ options?.initialView }
			entryPoint={ options?.entryPoint }
		/>
	);
};

window.tastyRecipesDestroyRecipeEditor = () => {
	if ( instance ) {
		instance.unmount();
		instance = null;
	}
};
