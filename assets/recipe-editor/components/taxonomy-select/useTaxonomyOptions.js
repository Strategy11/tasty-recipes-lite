import { useCallback, useMemo, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';
import {
	buildOverridesByValue,
	buildSelectedOptions,
	mergeCreatedOptions,
	parseSelectedValues,
} from './utils';

export const useTaxonomyOptions = ( { taxonomy, value, onChange } ) => {
	const [ createdOptions, setCreatedOptions ] = useState( [] );
	const [ selectKey, setSelectKey ] = useState( 0 );
	const [ deleteCandidate, setDeleteCandidate ] = useState( null );
	const [ editCandidate, setEditCandidate ] = useState( null );
	const [ editValue, setEditValue ] = useState( '' );
	const [ menuIsOpen, setMenuIsOpen ] = useState( false );
	const [ optionOverridesById, setOptionOverridesById ] = useState( {} );
	const [ optionValueAliases, setOptionValueAliases ] = useState( {} );

	const selectedValues = useMemo(
		() => parseSelectedValues( value ),
		[ value ]
	);
	const overridesByValue = useMemo(
		() => buildOverridesByValue( optionOverridesById, optionValueAliases ),
		[ optionOverridesById, optionValueAliases ]
	);
	const selectedOptions = useMemo(
		() => buildSelectedOptions( selectedValues, overridesByValue ),
		[ selectedValues, overridesByValue ]
	);

	const handleEditClick = useCallback( option => {
		setDeleteCandidate( null );
		setEditCandidate( option );
		setEditValue( option.label );
	}, [] );

	const handleEditInputChange = useCallback( newValue => {
		setEditValue( newValue );
	}, [] );

	const handleCancelEdit = useCallback( () => {
		setEditCandidate( null );
		setEditValue( '' );
	}, [] );

	const handleConfirmEdit = useCallback(
		async option => {
			const trimmedValue = editValue.trim();
			if ( ! option.id || ! trimmedValue ) {
				return;
			}

			if ( trimmedValue === option.value ) {
				setEditCandidate( null );
				setEditValue( '' );
				return;
			}

			try {
				const updatedOption = await apiFetch( {
					path: '/tasty-recipes/v1/taxonomy-terms',
					method: 'PUT',
					data: {
						taxonomy,
						id: option.id,
						name: trimmedValue,
					},
				} );

				const nextSelectedValues = selectedValues.map( item =>
					item === option.value ? updatedOption.value : item
				);
				const uniqueSelectedValues = Array.from(
					new Set( nextSelectedValues )
				);
				onChange( uniqueSelectedValues.join( ', ' ) );

				setCreatedOptions( previous =>
					previous.map( existingOption =>
						existingOption.value === option.value
							? updatedOption
							: existingOption
					)
				);
				setOptionOverridesById( previous => ( {
					...previous,
					[ updatedOption.id ]: updatedOption,
				} ) );
				setOptionValueAliases( previous => ( {
					...previous,
					[ option.value ]: updatedOption.id,
				} ) );

				setEditCandidate( null );
				setEditValue( '' );
			} catch ( error ) {
				// eslint-disable-next-line no-console
				console.error( error );
			}
		},
		[ editValue, onChange, selectedValues, taxonomy ]
	);

	const handleDeleteClick = useCallback( option => {
		setDeleteCandidate( option );
	}, [] );

	const handleCancelDelete = useCallback( () => {
		setDeleteCandidate( null );
	}, [] );

	const handleConfirmDelete = useCallback(
		async option => {
			if ( ! option.id ) {
				return;
			}

			try {
				await apiFetch( {
					path: '/tasty-recipes/v1/taxonomy-terms',
					method: 'DELETE',
					data: {
						taxonomy,
						id: option.id,
					},
				} );

				const newSelectedValues = selectedValues.filter(
					item => item !== option.value
				);
				if ( newSelectedValues.length !== selectedValues.length ) {
					onChange( newSelectedValues.join( ', ' ) );
				}

				setCreatedOptions( previous =>
					previous.filter(
						existingOption => existingOption.value !== option.value
					)
				);
				setOptionOverridesById( previous => {
					const next = { ...previous };
					delete next[ option.id ];
					return next;
				} );
				setOptionValueAliases( previous => {
					const next = { ...previous };
					Object.keys( next ).forEach( valueKey => {
						if (
							valueKey === option.value ||
							next[ valueKey ] === option.id
						) {
							delete next[ valueKey ];
						}
					} );
					return next;
				} );

				setDeleteCandidate( null );
				setSelectKey( previous => previous + 1 );
			} catch ( error ) {
				// eslint-disable-next-line no-console
				console.error( error );
			}
		},
		[ onChange, selectedValues, taxonomy ]
	);

	const loadOptions = useCallback(
		async inputValue => {
			if ( ! taxonomy ) {
				return [];
			}

			try {
				const groups = await apiFetch( {
					path: addQueryArgs( '/tasty-recipes/v1/taxonomy-terms', {
						taxonomy,
						search: inputValue,
					} ),
				} );

				return mergeCreatedOptions( groups, createdOptions, inputValue );
			} catch {
				if ( createdOptions.length > 0 ) {
					return [
						{
							label: __( 'Created by you', 'tasty-recipes-lite' ),
							options: createdOptions,
						},
					];
				}
				return [];
			}
		},
		[ taxonomy, createdOptions ]
	);

	const handleChange = useCallback(
		selected => {
			const newValue = selected
				? selected.map( option => option.value ).join( ', ' )
				: '';
			onChange( newValue );
		},
		[ onChange ]
	);

	const handleCreate = useCallback(
		async inputValue => {
			if ( ! taxonomy ) {
				return;
			}

			try {
				const newOption = await apiFetch( {
					path: '/tasty-recipes/v1/taxonomy-terms',
					method: 'POST',
					data: {
						taxonomy,
						name: inputValue,
					},
				} );

				newOption.isDefault = false;

				setCreatedOptions( previous => [ ...previous, newOption ] );

				const newValue = [ ...selectedValues, newOption.value ].join(
					', '
				);
				onChange( newValue );

				setSelectKey( previous => previous + 1 );
			} catch {
				// eslint-disable-next-line no-console
				console.error( 'Failed to create taxonomy term' );
			}
		},
		[ onChange, selectedValues, taxonomy ]
	);

	const handleMenuOpen = useCallback( () => {
		setMenuIsOpen( true );
	}, [] );

	const handleMenuClose = useCallback( () => {
		if ( ! editCandidate && ! deleteCandidate ) {
			setMenuIsOpen( false );
		}
	}, [ deleteCandidate, editCandidate ] );

	const getOptionLabel = useCallback(
		option =>
			optionOverridesById[ option.id ]?.label ?? option.label,
		[ optionOverridesById ]
	);
	const getOptionValue = useCallback(
		option =>
			optionOverridesById[ option.id ]?.value ?? option.value,
		[ optionOverridesById ]
	);

	return {
		selectKey,
		selectedOptions,
		menuIsOpen,
		deleteCandidate,
		editCandidate,
		editValue,
		loadOptions,
		handleChange,
		handleCreate,
		handleMenuOpen,
		handleMenuClose,
		handleEditClick,
		handleEditInputChange,
		handleCancelEdit,
		handleConfirmEdit,
		handleDeleteClick,
		handleCancelDelete,
		handleConfirmDelete,
		getOptionLabel,
		getOptionValue,
	};
};
