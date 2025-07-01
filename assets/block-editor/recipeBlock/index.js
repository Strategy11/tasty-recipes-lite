import { useEffect } from '@wordpress/element';
import { RecipePlaceholder } from './RecipePlaceholder';
import { Recipe } from './Recipe';

const handleEditorModalUpdate = ( { setAttributes }, response ) => {
	const date = new Date();
	window.tastyRecipesBlockEditor.recipeDataStore[ response.recipe.id ] =
		response.recipe;
	setAttributes( {
		id: response.recipe.id,
		lastUpdated: date.getTime(),
	} );
	if ( typeof wp.hooks !== 'undefined' ) {
		wp.hooks.doAction( 'tasty_recipes_updated_recipe', response.recipe );
	}
};

export const Edit = ( { attributes, setAttributes } ) => {
	useEffect( () => {
		if ( 'undefined' === typeof attributes.id ) {
			setAttributes( { id: 0 } );
		}
	}, [ attributes.id, setAttributes ] );

	const handleModalClose = ( response ) => {
		handleEditorModalUpdate( { attributes, setAttributes }, response );
	};

	if ( typeof attributes.id === 'undefined' || 0 === attributes.id ) {
		return <RecipePlaceholder handleModalClose={ handleModalClose } />;
	}

	return (
		<Recipe
			attributes={ attributes }
			setAttributes={ setAttributes }
			handleModalClose={ handleModalClose }
		/>
	);
};
