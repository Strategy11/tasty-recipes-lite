import { RecipeForm } from './components/RecipeForm';
import { createRootCompat } from '../js/utils/reactCompat';

let rootInstance;

window.tastyRecipesMountRecipeEditor = ( el, initialRecipe ) => {
	rootInstance = createRootCompat( el );
	rootInstance.render(
		<RecipeForm
			initialRecipe={ initialRecipe }
			config={ window.tastyRecipesRecipeEditorData }
		/>
	);
};

window.tastyRecipesDestroyRecipeEditor = () => {
	if ( rootInstance ) {
		rootInstance.unmount();
		rootInstance = null;
	}
};
