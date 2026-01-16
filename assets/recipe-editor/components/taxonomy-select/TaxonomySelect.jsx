import AsyncCreatableSelect from 'react-select/async-creatable';
import { CheckboxOption } from './CheckboxOption';
import { GroupHeading } from './GroupHeading';
import { selectStyles } from './selectStyles';
import { useTaxonomyOptions } from './useTaxonomyOptions';
import { formatCreateLabel } from './utils';

export const TaxonomySelect = ( { name, taxonomy, value = '', onChange } ) => {
	const {
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
	} = useTaxonomyOptions( { taxonomy, value, onChange } );

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
				getOptionLabel={ getOptionLabel }
				getOptionValue={ getOptionValue }
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
				styles={ selectStyles }
			/>
		</>
	);
};
