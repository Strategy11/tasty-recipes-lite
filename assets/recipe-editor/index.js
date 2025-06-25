import { createRoot } from '@wordpress/element';
import RecipeForm from './components/RecipeForm';

let instance;

window.tastyRecipesMountRecipeEditor = ( el, initialRecipe ) => {
	instance = createRoot( el );
	instance.render(
		<RecipeForm
			initialRecipe={ initialRecipe }
			config={ window.tastyRecipesRecipeEditorData }
		/>,
		el
	);
};

window.tastyRecipesDestroyRecipeEditor = () => {
	if ( instance ) {
		instance.unmount();
		instance = null;
	}
};
