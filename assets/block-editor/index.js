import { registerBlockType } from '@wordpress/blocks';
import { registerPlugin } from '@wordpress/plugins';
import generateNotices from './admin-notices';
import TastyRecipesMetaBox from './metabox';
import { RecipeIcon } from './icons';
import { Edit } from './recipeBlock';
import metadata from './block.json';
import '../scss/block-editor.scss';

registerBlockType( metadata.name, {
	icon: RecipeIcon,
	edit: Edit,
	save: () => null,
	transforms: {
		from: [
			{
				type: 'shortcode',
				tag: 'tasty-recipe',
				attributes: {
					id: {
						type: 'number',
						shortcode: props => {
							if ( props.named.id === undefined ) {
								return 0;
							}
							return parseInt( props.named.id );
						},
					},
				},
			},
		],
	},
} );

window.addEventListener( 'load', () => {
	generateNotices();
	/* Removes the conversion success query string on browsers that support it. */
	if ( window.history ) {
		let newLocation = window.location.href;
		newLocation = newLocation.replace(
			'&tasty_recipes_message=converted_recipe_success',
			''
		);
		if ( newLocation !== window.location.href ) {
			window.history.replaceState( '', '', newLocation );
		}
	}
} );

if ( Object.keys( window.tastyRecipesEditor.dismissedConverters ).length > 0 ) {
	registerPlugin( 'tasty-recipes-metabox', {
		render: TastyRecipesMetaBox,
	} );
}
