import { __, sprintf } from '@wordpress/i18n';
import Select from 'react-select';
import { TaxonomySelect } from '../../../../recipe-editor/components/taxonomy-select';
import { selectStyles } from '../../../../recipe-editor/components/taxonomy-select/selectStyles';
import { MODE_LABELS, splitValue } from './utils';

const NO_CHANGE_LABEL = __( 'No change', 'tasty-recipes-lite' );

const getValueId = ( field ) => `tasty-recipes-bulk-edit-${ field.key }-value`;

const OptionsSelect = ( { field, change, onChange } ) => {
	const selected = splitValue( change.value )
		.map( ( value ) =>
			field.options.find( ( option ) => option.value === value )
		)
		.filter( Boolean );

	return (
		<Select
			inputId={ getValueId( field ) }
			isMulti
			options={ field.options }
			value={ selected }
			placeholder={ NO_CHANGE_LABEL }
			className="tasty-recipes-bulk-edit__options"
			classNamePrefix="tasty-recipes-react-select"
			styles={ selectStyles }
			onChange={ ( options ) =>
				onChange(
					( options || [] ).map( ( option ) => option.value ).join( ',' )
				)
			}
		/>
	);
};

const ValueControl = ( { field, change, onChange } ) => {
	if ( field.type === 'options' ) {
		return (
			<OptionsSelect field={ field } change={ change } onChange={ onChange } />
		);
	}

	if ( field.type === 'taxonomy' ) {
		return (
			<TaxonomySelect
				name={ getValueId( field ) }
				taxonomy={ field.taxonomy }
				value={ change.value }
				placeholder={ NO_CHANGE_LABEL }
				onChange={ onChange }
			/>
		);
	}

	return (
		<input
			type="text"
			id={ getValueId( field ) }
			value={ change.value }
			placeholder={
				field.type === 'list'
					? __( 'No change (separate with commas)', 'tasty-recipes-lite' )
					: NO_CHANGE_LABEL
			}
			onChange={ ( e ) => onChange( e.target.value ) }
		/>
	);
};

// Same markup as the framework CheckBox, so the pink checkmark styles apply.
const ModeChoice = ( { field, change, onChange } ) => (
	<fieldset className="tasty-recipes-bulk-edit__modes">
		<legend className="screen-reader-text">
			{ sprintf(
				/* translators: %s: field label. */
				__( 'How to change %s', 'tasty-recipes-lite' ),
				field.label
			) }
		</legend>
		{ field.modes
			.filter( ( mode ) => MODE_LABELS[ mode ] )
			.map( ( mode ) => {
				const id = `tasty-recipes-bulk-edit-${ field.key }-${ mode }`;

				return (
					<label key={ mode } className="tasty-checkbox-container" htmlFor={ id }>
						<input
							type="radio"
							id={ id }
							name={ `tasty-recipes-bulk-edit-${ field.key }-mode` }
							value={ mode }
							checked={ change.mode === mode }
							onChange={ () => onChange( mode ) }
						/>
						{ MODE_LABELS[ mode ] }
						<span className="tasty-checkmark"></span>
					</label>
				);
			} ) }
	</fieldset>
);

export const BulkEditForm = ( { fields, changes, setChange } ) => (
	<div className="tasty-recipes-bulk-edit__fields">
		{ fields.map( ( field ) => {
			const change = changes[ field.key ];
			const showModes =
				splitValue( change.value ).length > 0 &&
				field.modes.includes( 'add' );

			return (
				<div className="tasty-recipes-bulk-edit__row" key={ field.key }>
					{ /* The term picker doesn't expose an input id to point to. */ }
					<label
						className="tasty-recipes-bulk-edit__label"
						htmlFor={
							field.type === 'taxonomy' ? undefined : getValueId( field )
						}
					>
						{ field.label }
					</label>
					<div className="tasty-recipes-bulk-edit__controls">
						<ValueControl
							field={ field }
							change={ change }
							onChange={ ( value ) => setChange( field.key, { value } ) }
						/>
						{ showModes && (
							<ModeChoice
								field={ field }
								change={ change }
								onChange={ ( mode ) => setChange( field.key, { mode } ) }
							/>
						) }
					</div>
				</div>
			);
		} ) }
	</div>
);
