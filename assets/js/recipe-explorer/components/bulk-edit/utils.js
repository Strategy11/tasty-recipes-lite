import { __, sprintf } from '@wordpress/i18n';

export const MODE_LABELS = {
	add: __( 'Add', 'tasty-recipes-lite' ),
	remove: __( 'Remove', 'tasty-recipes-lite' ),
	replace: __( 'Replace', 'tasty-recipes-lite' ),
};

export const getConfig = () => window.tastyRecipesExplorerPage.bulkEdit;

export const groupIdsByType = ( ids ) => {
	const { types, recipeTypes } = getConfig();
	const groups = {};

	ids.forEach( ( id ) => {
		const type = types[ recipeTypes[ id ] ] ? recipeTypes[ id ] : 'recipe';

		groups[ type ] = groups[ type ] || [];
		groups[ type ].push( id );
	} );

	return Object.keys( types )
		.filter( ( type ) => groups[ type ] )
		.map( ( type ) => ( { type, ids: groups[ type ] } ) );
};

export const getTypeLabel = ( type, count ) => {
	const labels = getConfig().types[ type ];

	return count === 1 ? labels.singular : labels.plural;
};

export const formatCount = ( type, count ) =>
	sprintf(
		/* translators: 1: number of items, 2: item type, e.g. "recipes". */
		__( '%1$d %2$s', 'tasty-recipes-lite' ),
		count,
		getTypeLabel( type, count )
	);

export const splitValue = ( value ) =>
	value
		.split( ',' )
		.map( ( item ) => item.trim() )
		.filter( Boolean );

// Fields start empty, which means "no change"; picking values defaults to adding them.
export const getEmptyChange = ( field ) => ( {
	mode: field.modes.includes( 'add' ) ? 'add' : 'replace',
	value: '',
} );

export const isActive = ( change ) =>
	!! change && splitValue( change.value ).length > 0;

export const chunk = ( items, size ) => {
	const chunks = [];

	for ( let i = 0; i < items.length; i += size ) {
		chunks.push( items.slice( i, i + size ) );
	}

	return chunks;
};
