import { useEffect } from '@wordpress/element';
import { RecipePlaceholder } from './RecipePlaceholder';

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

const Edit = ( { attributes, setAttributes } ) => {
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

	// return (
	// 	<Recipe
	// 		attributes={ attributes }
	// 		setAttributes={ setAttributes }
	// 		handleModalClose={ handleModalClose }
	// 	/>
	// );
};

/**
 * Renders dynamically on the frontend.
 */
const save = () => {
	return null;
};

const blockRegistration = {
	title: window.tastyRecipesBlockEditor.recipeBlockTitle,
	icon: 'carrot',
	category: 'common',
	html: false,
	attributes: {
		id: {
			type: 'number',
		},
		lastUpdated: {
			type: 'number',
		},
		author_link: {
			type: 'string',
			default: '',
		},
	},
	edit: Edit,
	save,
	transforms: {
		from: [
			{
				type: 'shortcode',
				tag: 'tasty-recipe',
				attributes: {
					id: {
						type: 'number',
						shortcode: ( props ) => {
							if ( typeof props.named.id === 'undefined' ) {
								return 0;
							}
							return parseInt( props.named.id );
						},
					},
				},
			},
		],
	},
};

export default blockRegistration;
