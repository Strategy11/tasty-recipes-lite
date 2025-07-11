import { doAction } from '@wordpress/hooks';
import apiFetch from '@wordpress/api-fetch';
import { openEditorExtension } from './editor-extension';

export const registerEvents = () => {
	const addRecipeButton = document.getElementById( 'tasty-create-recipe' );
	const trashButtons = document.querySelectorAll(
		'.tasty-recipes-no-delete'
	);
	const editButtons = document.querySelectorAll(
		'.tasty-recipes-edit-recipe'
	);
	const columnsIconElements = document.querySelectorAll(
		'.tasty-columns-icon'
	);

	if ( addRecipeButton ) {
		addRecipeButton.addEventListener( 'click', addRecipeListener );
	}

	if ( columnsIconElements.length > 0 ) {
		columnsIconElements.forEach( ( element ) => {
			element.addEventListener( 'click', columnsIconListener );
		} );
	}

	if ( trashButtons.length > 0 ) {
		trashButtons.forEach( ( button ) => {
			button.addEventListener( 'click', trashListener );
		} );
	}

	if ( editButtons.length > 0 ) {
		editButtons.forEach( ( button ) => {
			button.addEventListener( 'click', editListener );
		} );
	}
};

const handleModalClose = ( response ) => {
	const recipe = response.recipe;

	doAction( 'tasty_recipes_orphan_recipe', recipe );
	openEditorExtension( recipe );
};

const addRecipeListener = ( e ) => {
	e.preventDefault();

	window.tastyRecipesEditorModal.open(
		{
			author_name: window.tastyRecipesEditorModalData.defaultAuthorName,
			title: '',
			diet: '',
		},
		handleModalClose
	);
};

const columnsIconListener = ( e ) => {
	e.preventDefault();

	const screenMeta = document.getElementById( 'screen-meta' );
	const contextualHelpColumns = document.getElementById(
		'screen-options-wrap'
	);

	screenMeta.style.display =
		screenMeta.style.display === 'block' ? 'none' : 'block';
	contextualHelpColumns.classList.toggle( 'hidden' );
};

const editListener = async ( e ) => {
	e.preventDefault();

	const recipeId = e.target.dataset.recipeId;
	const recipe = await apiFetch( {
		path: `/tasty-recipes/v1/recipe-explorer/recipe/${ recipeId }`,
		method: 'GET',
	} );

	window.tastyRecipesEditorModal.open( recipe.data, editModalClose );
};

const editModalClose = ( response ) => {
	doAction( 'tasty_recipes_updated_recipe', response.recipe );
	window.location.reload();
};

const trashListener = ( e ) => {
	e.preventDefault();

	console.log( 'go to trash' );
};
