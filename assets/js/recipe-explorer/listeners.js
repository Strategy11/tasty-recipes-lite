import { doAction } from '@wordpress/hooks';
import apiFetch from '@wordpress/api-fetch';
import { openEditorExtension } from './modals/editorExtensionModal';
import { openTrashModal } from './modals/trashModal';
import { openPermanentDeleteModal } from './modals/permanentDeleteModal';
import { toggleFiltersForm } from './manageFiltersForm';

export const registerEvents = () => {
	const addRecipeItems = document.querySelectorAll(
		'.tasty-recipes-add-new-item'
	);
	if ( addRecipeItems.length > 0 ) {
		addRecipeItems.forEach( ( item ) => {
			item.addEventListener( 'click', addRecipeListener );
		} );
	}

	const columnsIconElements = document.querySelectorAll(
		'.tasty-columns-icon'
	);
	if ( columnsIconElements.length > 0 ) {
		columnsIconElements.forEach( ( element ) => {
			element.addEventListener( 'click', columnsIconListener );
		} );
	}

	const trashButtons = document.querySelectorAll(
		'.tasty-recipes-no-delete'
	);
	if ( trashButtons.length > 0 ) {
		trashButtons.forEach( ( button ) => {
			button.addEventListener( 'click', trashListener );
		} );
	}

	const editButtons = document.querySelectorAll(
		'.tasty-recipes-edit-recipe'
	);
	if ( editButtons.length > 0 ) {
		editButtons.forEach( ( button ) => {
			button.addEventListener( 'click', editListener );
		} );
	}

	const permanentDeleteButtons = document.querySelectorAll(
		'.tasty-recipes-permanent-delete'
	);
	if ( permanentDeleteButtons.length > 0 ) {
		permanentDeleteButtons.forEach( ( button ) => {
			button.addEventListener( 'click', permanentDeleteListener );
		} );
	}

	const filtersButton = document.getElementById(
		'tasty-recipes-filters-button'
	);
	if ( filtersButton ) {
		filtersButton.addEventListener( 'click', filtersListener );
	}
};

const handleModalClose = ( response ) => {
	const recipe = response.recipe;

	doAction( 'tasty_recipes_orphan_recipe', recipe );
	openEditorExtension( recipe );
};

const addRecipeListener = ( e ) => {
	e.preventDefault();
	const type = e.currentTarget?.dataset?.recipeType || 'recipe';

	window.tastyRecipesEditorModal.open(
		{
			author_name: window.tastyRecipesEditorModalData.defaultAuthorName,
			title: '',
			diet: '',
			recipe_type: type,
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

	const postUrl = e.target.dataset.postUrl;
	openTrashModal( postUrl );
};

const permanentDeleteListener = ( e ) => {
	e.preventDefault();

	const postId = e.target.dataset.postId;
	const name = e.target.dataset.name;

	openPermanentDeleteModal( postId, name );
};

const filtersListener = ( e ) => {
	e.preventDefault();

	toggleFiltersForm();
};
