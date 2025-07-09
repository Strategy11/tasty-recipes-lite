import { doAction } from '@wordpress/hooks';
import { openEditorExtension } from './editor-extension';

export const registerEvents = () => {
	const addRecipeButton = document.getElementById( 'tasty-create-recipe' );
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
