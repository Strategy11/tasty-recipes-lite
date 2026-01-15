import { useState, useCallback, useRef, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { __, sprintf } from '@wordpress/i18n';
import { components } from 'react-select';
import AsyncCreatableSelect from 'react-select/async-creatable';

const CheckboxOption = ( props ) => {
	const { isSelected, label, data, selectProps } = props;
	const {
		deleteCandidate,
		onDeleteClick,
		onCancelDelete,
		onConfirmDelete,
		onEditClick,
		editCandidate,
		editValue,
		onEditInputChange,
		onCancelEdit,
		onConfirmEdit,
	} = selectProps;
	const isDefault = data?.isDefault ?? true;
	const isDeleteCandidate = deleteCandidate?.id
		? deleteCandidate.id === data.id
		: deleteCandidate?.value === data.value;
	const isEditCandidate = editCandidate?.id
		? editCandidate.id === data.id
		: editCandidate?.value === data.value;
	const editInputRef = useRef( null );

	useEffect( () => {
		if ( isEditCandidate && editInputRef.current ) {
			editInputRef.current.focus();
		}
	}, [ isEditCandidate ] );

	if ( isEditCandidate ) {
		return (
			<components.Option
				{ ...props }
				className="tasty-taxonomy-option--editing"
			>
				<div
					className="tasty-taxonomy-edit-container"
					onClick={ ( e ) => {
						if ( e.target.closest( 'input' ) ) {
							return;
						}
						e.stopPropagation();
						e.preventDefault();
					} }
					onMouseDown={ ( e ) => {
						if ( e.target.closest( 'input' ) ) {
							return;
						}
						e.stopPropagation();
						e.preventDefault();
					} }
					role="presentation"
				>
					<div className="tasty-taxonomy-edit-field">
						<input
							ref={ editInputRef }
							type="text"
							className="tasty-taxonomy-edit-input"
							style={ {
								width: '100%',
								outline: 'none',
								boxShadow: 'none',
							} }
							value={ editValue }
							onClick={ ( e ) => {
								e.stopPropagation();
							} }
							onChange={ ( e ) =>
								onEditInputChange( e.target.value )
							}
							onMouseDown={ ( e ) => {
								e.stopPropagation();
							} }
							onFocus={ ( e ) => {
								e.target.style.outline = 'none';
								e.target.style.boxShadow = 'none';
							} }
							onKeyDown={ ( e ) => {
								e.stopPropagation();
								if ( e.key === 'Enter' ) {
									onConfirmEdit( data );
								} else if ( e.key === 'Escape' ) {
									onCancelEdit();
								}
							} }
						/>
					</div>
					<div className="tasty-taxonomy-edit-actions">
						<button
							type="button"
							className="tasty-taxonomy-edit-confirm"
							onClick={ ( e ) => {
								e.stopPropagation();
								onConfirmEdit( data );
							} }
						>
							<svg
								xmlns="http://www.w3.org/2000/svg"
								width="20"
								height="20"
								fill="none"
								viewBox="0 0 20 20"
							>
								<path
									stroke="#22C55E"
									strokeLinecap="round"
									strokeLinejoin="round"
									strokeWidth="2"
									d="M4 10l4 4 8-8"
								/>
							</svg>
						</button>
						<button
							type="button"
							className="tasty-taxonomy-edit-cancel"
							onClick={ ( e ) => {
								e.stopPropagation();
								onCancelEdit();
							} }
						>
							<svg
								xmlns="http://www.w3.org/2000/svg"
								width="20"
								height="20"
								fill="none"
								viewBox="0 0 20 20"
							>
								<path
									stroke="#ED4996"
									strokeLinecap="round"
									strokeLinejoin="round"
									strokeWidth="2"
									d="M5 5l10 10M15 5L5 15"
								/>
							</svg>
						</button>
					</div>
				</div>
			</components.Option>
		);
	}

	if ( isDeleteCandidate ) {
		return (
			<components.Option { ...props }>
				<div
					className="tasty-taxonomy-delete-confirmation"
					onClick={ ( e ) => {
						e.preventDefault();
						e.stopPropagation();
					} }
					onMouseDown={ ( e ) => {
						e.stopPropagation();
						e.preventDefault();
					} }
					role="presentation"
				>
					<p className="tasty-taxonomy-delete-message">
						{ sprintf(
							/* translators: 1: Term name, 2: Number of recipes */
							__(
								'[%1$s] is linked to %2$d recipes. Delete?',
								'tasty-recipes-lite'
							),
							label,
							data.count || 0
						) }
					</p>
					<div className="tasty-taxonomy-delete-actions">
						<button
							type="button"
							className="tasty-taxonomy-delete-cancel"
							onClick={ ( e ) => {
								e.stopPropagation();
								onCancelDelete();
							} }
						>
							{ __( 'Cancel', 'tasty-recipes-lite' ) }
						</button>
						<button
							type="button"
							className="tasty-taxonomy-delete-confirm"
							onClick={ ( e ) => {
								e.stopPropagation();
								onConfirmDelete( data );
							} }
						>
							{ __( 'Delete', 'tasty-recipes-lite' ) }
						</button>
					</div>
				</div>
			</components.Option>
		);
	}

	return (
		<components.Option { ...props }>
			<span
				className={ `tasty-taxonomy-checkbox ${
					isSelected ? 'tasty-taxonomy-checkbox--checked' : ''
				}` }
			>
				{ isSelected && (
					<svg
						viewBox="0 0 24 24"
						width="14"
						height="14"
						fill="currentColor"
					>
						<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
					</svg>
				) }
			</span>
			<span className="tasty-taxonomy-option-label">{ label }</span>
			{ ! isDefault && (
				<>
					<span
						className="tasty-taxonomy-option-edit"
						onClick={ ( e ) => {
							e.stopPropagation();
							onEditClick( data );
						} }
						role="button"
						tabIndex={ 0 }
						onKeyDown={ ( e ) => {
							if ( e.key === 'Enter' || e.key === ' ' ) {
								e.stopPropagation();
								onEditClick( data );
							}
						} }
						onMouseDown={ ( e ) => {
							e.stopPropagation();
							e.preventDefault();
						} }
					>
						<svg
							xmlns="http://www.w3.org/2000/svg"
							width="20"
							height="20"
							fill="none"
						>
							<path
								stroke="#ED4996"
								strokeLinecap="round"
								strokeLinejoin="round"
								strokeWidth="1.5"
								d="m3.958 16.042 3.542-.834 7.744-7.744a.833.833 0 0 0 0-1.178l-1.53-1.53a.833.833 0 0 0-1.178 0L4.792 12.5l-.834 3.542ZM16.042 16.042h-4.584"
							/>
						</svg>
					</span>
					<span
						className="tasty-taxonomy-option-delete"
						onClick={ ( e ) => {
							e.stopPropagation();
							onDeleteClick( data );
						} }
						role="button"
						tabIndex={ 0 }
						onKeyDown={ ( e ) => {
							if ( e.key === 'Enter' || e.key === ' ' ) {
								e.stopPropagation();
								onDeleteClick( data );
							}
						} }
						onMouseDown={ ( e ) => {
							e.stopPropagation();
							e.preventDefault();
						} }
					>
						<svg
							xmlns="http://www.w3.org/2000/svg"
							width="20"
							height="20"
							fill="none"
							viewBox="0 0 20 20"
						>
							<path
								stroke="#ed4996"
								strokeLinecap="round"
								strokeLinejoin="round"
								strokeWidth="1.25"
								d="m4.792 6.458.7 8.061a1.667 1.667 0 0 0 1.661 1.523h4.86c.865 0 1.586-.661 1.661-1.523l.701-8.06zM8.125 8.958v4.584M11.042 8.958v4.584M7.292 6.458v-.833c0-.92.746-1.667 1.666-1.667h1.25c.92 0 1.667.746 1.667 1.667v.833M3.958 6.458h11.25"
							/>
						</svg>
					</span>
				</>
			) }
		</components.Option>
	);
};

const GroupHeading = ( props ) => {
	const { children } = props;

	if ( ! children ) {
		return null;
	}

	return (
		<div className="tasty-taxonomy-group-heading">
			<span className="tasty-taxonomy-group-heading__text">
				{ children }
			</span>
		</div>
	);
};

const formatCreateLabel = ( inputValue ) => (
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

export const TaxonomySelect = ( { name, taxonomy, value = '', onChange } ) => {
	const [ createdOptions, setCreatedOptions ] = useState( [] );
	const [ selectKey, setSelectKey ] = useState( 0 );
	const [ deleteCandidate, setDeleteCandidate ] = useState( null );
	const [ editCandidate, setEditCandidate ] = useState( null );
	const [ editValue, setEditValue ] = useState( '' );
	const [ menuIsOpen, setMenuIsOpen ] = useState( false );
	const [ optionOverrides, setOptionOverrides ] = useState( {} );
	const [ optionOverridesByValue, setOptionOverridesByValue ] = useState( {} );

	const selectedValues = value
		? value
				.split( ',' )
				.map( ( v ) => v.trim() )
				.filter( Boolean )
		: [];

	const selectedOptions = selectedValues.map( ( v ) => {
		const override = optionOverridesByValue[ v ];
		return {
			value: override?.value ?? v,
			label: override?.label ?? v,
			id: override?.id,
		};
	} );

	const handleEditClick = ( option ) => {
		setDeleteCandidate( null );
		setEditCandidate( option );
		setEditValue( option.label );
	};

	const handleEditInputChange = ( newValue ) => {
		setEditValue( newValue );
	};

	const handleCancelEdit = () => {
		setEditCandidate( null );
		setEditValue( '' );
	};

	const handleConfirmEdit = async ( option ) => {
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

			const nextSelectedValues = selectedValues.map( ( v ) =>
				v === option.value ? updatedOption.value : v
			);
			const uniqueSelectedValues = Array.from(
				new Set( nextSelectedValues )
			);
			onChange( uniqueSelectedValues.join( ', ' ) );

			setCreatedOptions( ( prev ) =>
				prev.map( ( opt ) =>
					opt.value === option.value ? updatedOption : opt
				)
			);
			setOptionOverrides( ( prev ) => ( {
				...prev,
				[ updatedOption.id ]: updatedOption,
			} ) );
			setOptionOverridesByValue( ( prev ) => ( {
				...prev,
				[ option.value ]: updatedOption,
				[ updatedOption.value ]: updatedOption,
			} ) );

			setEditCandidate( null );
			setEditValue( '' );
		} catch ( error ) {
			// eslint-disable-next-line no-console
			console.error( error );
		}
	};

	const handleDeleteClick = ( option ) => {
		setDeleteCandidate( option );
	};

	const handleCancelDelete = () => {
		setDeleteCandidate( null );
	};

	const handleConfirmDelete = async ( option ) => {
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
				( v ) => v !== option.value
			);
			if ( newSelectedValues.length !== selectedValues.length ) {
				onChange( newSelectedValues.join( ', ' ) );
			}

			setCreatedOptions( ( prev ) =>
				prev.filter( ( opt ) => opt.value !== option.value )
			);
			if ( option.id ) {
				setOptionOverrides( ( prev ) => {
					const next = { ...prev };
					delete next[ option.id ];
					return next;
				} );
			}
			setOptionOverridesByValue( ( prev ) => {
				const next = { ...prev };
				const override = next[ option.value ];
				delete next[ option.value ];
				if ( override?.value ) {
					delete next[ override.value ];
				}
				return next;
			} );

			setDeleteCandidate( null );

			setSelectKey( ( prev ) => prev + 1 );
		} catch ( error ) {
			// eslint-disable-next-line no-console
			console.error( error );
		}
	};

	const loadOptions = useCallback(
		async ( inputValue ) => {
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

				const filteredCreated = createdOptions.filter( ( opt ) =>
					opt.label.toLowerCase().includes( inputValue.toLowerCase() )
				);

				if ( filteredCreated.length > 0 ) {
					let customGroup = groups.find( ( g ) => g.label !== '' );

					if ( ! customGroup ) {
						customGroup = {
							label: __( 'Created by you', 'tasty-recipes-lite' ),
							options: [],
						};
						groups.push( customGroup );
					}

					filteredCreated.forEach( ( opt ) => {
						if (
							! customGroup.options.some(
								( o ) => o.value === opt.value
							)
						) {
							customGroup.options.push( opt );
						}
					} );
				}

				return groups;
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

	const handleChange = ( selected ) => {
		const newValue = selected
			? selected.map( ( opt ) => opt.value ).join( ', ' )
			: '';
		onChange( newValue );
	};

	const handleCreate = async ( inputValue ) => {
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

			setCreatedOptions( ( prev ) => [ ...prev, newOption ] );

			const newValue = [ ...selectedValues, newOption.value ].join(
				', '
			);
			onChange( newValue );

			setSelectKey( ( prev ) => prev + 1 );
		} catch {
			// eslint-disable-next-line no-console
			console.error( 'Failed to create taxonomy term' );
		}
	};

	const handleMenuOpen = () => {
		setMenuIsOpen( true );
	};

	const handleMenuClose = () => {
		// Only close if we're not in edit or delete mode
		if ( ! editCandidate && ! deleteCandidate ) {
			setMenuIsOpen( false );
		}
	};

	return (
		<>
			<input type="hidden" name={ name } value={ value } />

			<AsyncCreatableSelect
				key={ selectKey }
				isMulti
				value={ selectedOptions }
				loadOptions={ loadOptions }
				defaultOptions
				onChange={ handleChange }
				onCreateOption={ handleCreate }
				formatCreateLabel={ formatCreateLabel }
				components={ { Option: CheckboxOption, GroupHeading } }
				className="tasty-taxonomy-select-container"
				classNamePrefix="tasty-taxonomy-select"
				hideSelectedOptions={ false }
				closeMenuOnSelect={ false }
				isSearchable
				isClearable={ false }
				placeholder=""
				cacheOptions={ false }
				menuIsOpen={ menuIsOpen }
				onMenuOpen={ handleMenuOpen }
				onMenuClose={ handleMenuClose }
				menuShouldScrollIntoView={ false }
				getOptionLabel={ ( option ) =>
					optionOverrides[ option.id ]?.label ?? option.label
				}
				getOptionValue={ ( option ) =>
					optionOverrides[ option.id ]?.value ?? option.value
				}
				deleteCandidate={ deleteCandidate }
				editCandidate={ editCandidate }
				editValue={ editValue }
				onEditClick={ handleEditClick }
				onEditInputChange={ handleEditInputChange }
				onCancelEdit={ handleCancelEdit }
				onConfirmEdit={ handleConfirmEdit }
				onDeleteClick={ handleDeleteClick }
				onCancelDelete={ handleCancelDelete }
				onConfirmDelete={ handleConfirmDelete }
				styles={ {
					control: ( base, state ) => ( {
						...base,
						minHeight: '40px',
						backgroundColor: '#fff',
						borderColor: state.isFocused ? '#ED4996' : '#CACFD8',
						borderRadius: '10px',
						boxShadow: state.isFocused
							? '0 0 0 1px #ED4996'
							: '0px 1px 2px 0px rgba(10, 13, 20, 0.03)',
						'&:hover': {
							borderColor: state.isFocused
								? '#ED4996'
								: '#a0a5ae',
						},
						overflow: 'hidden',
					} ),
					valueContainer: ( base ) => ( {
						...base,
						padding: '4px 8px',
						gap: '4px',
						flexWrap: 'wrap',
						overflow: 'hidden',
					} ),
					multiValue: ( base ) => ( {
						...base,
						backgroundColor: '#F5F7FA',
						borderRadius: '6px',
					} ),
					multiValueLabel: ( base ) => ( {
						...base,
						color: '#0E121B',
						fontSize: '13px',
						padding: '2px 4px 2px 8px',
					} ),
					multiValueRemove: ( base ) => ( {
						...base,
						color: '#525866',
						borderRadius: '0 6px 6px 0',
						'&:hover': {
							backgroundColor: '#E5E7EB',
							color: '#0E121B',
						},
					} ),
					menu: ( base ) => ( {
						...base,
						borderRadius: '10px',
						border: '1px solid #CACFD8',
						boxShadow: '0 4px 12px rgba(0, 0, 0, 0.1)',
						overflow: 'hidden',
						zIndex: 1000,
					} ),
					menuList: ( base ) => ( {
						...base,
						padding: '4px 0',
					} ),
					option: ( base, state ) => ( {
						...base,
						display: 'flex',
						alignItems: 'center',
						gap: '10px',
						padding: '8px 12px',
						backgroundColor: state.isFocused
							? '#F5F7FA'
							: 'transparent',
						color: '#0E121B',
						cursor: 'pointer',
						'&:active': {
							backgroundColor: '#F5F7FA',
						},
					} ),
					input: ( base ) => ( {
						...base,
						margin: 0,
						padding: 0,
						overflow: 'hidden',
					} ),
					indicatorSeparator: () => ( {
						display: 'none',
					} ),
					dropdownIndicator: ( base ) => ( {
						...base,
						color: '#6B7280',
						'&:hover': {
							color: '#6B7280',
						},
					} ),
				} }
			/>
		</>
	);
};
