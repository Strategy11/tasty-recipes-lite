import { __ } from '@wordpress/i18n';

export const formatCreateLabel = ( inputValue ) => (
	<span className="tasty-taxonomy-create-option">
		<span className="tasty-taxonomy-create-option__plus">+</span>
		<span className="tasty-taxonomy-create-option__label">
			{ __( 'Add', 'tasty-recipes-lite' ) }
		</span>
		<span className="tasty-taxonomy-create-option__value">
			{ /* These are HTML codes for quotes, we have to use them to be compliant with the WP Lint rules */ }
			&ldquo;{ inputValue }&rdquo;
		</span>
	</span>
);

export const parseSelectedValues = ( value ) => {
	if ( ! value ) {
		return [];
	}

	return value
		.split( ',' )
		.map( ( item ) => item.trim() )
		.filter( Boolean );
};

export const buildOverridesByValue = ( overridesById, valueAliases ) => {
	const overrides = {};

	Object.values( overridesById ).forEach( ( override ) => {
		if ( override?.value ) {
			overrides[ override.value ] = override;
		}
	} );

	Object.entries( valueAliases ).forEach( ( [ value, id ] ) => {
		const override = overridesById[ id ];
		if ( override ) {
			overrides[ value ] = override;
		}
	} );

	return overrides;
};

export const buildSelectedOptions = ( selectedValues, overridesByValue ) =>
	selectedValues.map( ( value ) => {
		const override = overridesByValue[ value ];

		return {
			value: override?.value ?? value,
			label: override?.label ?? value,
			id: override?.id,
		};
	} );

export const mergeCreatedOptions = ( groups, createdOptions, inputValue ) => {
	const filteredCreated = createdOptions.filter( ( option ) =>
		option.label.toLowerCase().includes( inputValue.toLowerCase() )
	);

	if ( filteredCreated.length === 0 ) {
		return groups;
	}

	const nextGroups = groups.map( ( group ) => ( {
		...group,
		options: [ ...group.options ],
	} ) );
	let customGroup = nextGroups.find( ( group ) => group.label !== '' );

	if ( ! customGroup ) {
		customGroup = {
			label: __( 'Created by you', 'tasty-recipes-lite' ),
			options: [],
		};
		nextGroups.push( customGroup );
	}

	filteredCreated.forEach( ( option ) => {
		if (
			! customGroup.options.some(
				( existingOption ) => existingOption.value === option.value
			)
		) {
			customGroup.options.push( option );
		}
	} );

	return nextGroups;
};
