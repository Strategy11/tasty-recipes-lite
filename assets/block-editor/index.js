import { registerBlockType } from '@wordpress/blocks';
import { registerPlugin } from '@wordpress/plugins';
import { __ } from '@wordpress/i18n';
import generateNotices from './admin-notices';
import TastyRecipesMetaBox from './metabox';
import { RecipeIcon } from './icons';
import { Edit } from './recipeBlock';
import '../scss/block-editor.scss';

registerBlockType( 'wp-tasty/tasty-recipe', {
	apiVersion: 2,
	title: window.tastyRecipesBlockEditor.recipeBlockTitle,
	icon: RecipeIcon,
	category: 'common',
	html: false,
	keywords: [ __( 'recipe', 'tasty-recipes-lite' ) ],
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
		override_author_link: {
			type: 'boolean',
			default: false,
		},
	},
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
