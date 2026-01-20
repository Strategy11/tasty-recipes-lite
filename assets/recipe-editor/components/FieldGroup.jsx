import { useState, useEffect, useRef } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

export const FieldGroup = ( {
	heading,
	group = '',
	fields,
	initialFieldValues,
	showLabels = true,
	showAdditionalTime = true,
	onFieldChange = () => {},
	renderBeforeFields,
	className = '',
	recipe,
} ) => {
	const [ fieldValues, setFieldValues ] = useState( initialFieldValues );
	const [ isAdditionalTimeOpen, setIsAdditionalTimeOpen ] = useState( false );
	const isInitialMount = useRef( true );

	useEffect( () => {
		if (
			fieldValues.additional_time_label ||
			fieldValues.additional_time_value
		) {
			setIsAdditionalTimeOpen( true );
		}
	}, [
		fieldValues.additional_time_label,
		fieldValues.additional_time_value,
	] );

	useEffect( () => {
		if ( isInitialMount.current ) {
			isInitialMount.current = false;
			return;
		}
		if ( ! isAdditionalTimeOpen ) {
			setFieldValues( ( prev ) => ( {
				...prev,
				additional_time_label: '',
				additional_time_value: '',
			} ) );
		}
	}, [ isAdditionalTimeOpen ] );

	const handleFieldChange = ( field, value ) => {
		setFieldValues( ( prev ) => ( { ...prev, [ field ]: value } ) );
		onFieldChange( field, value );
	};

	const beforeSettingHook = applyFilters(
		'tasty_recipes_before_setting_hook',
		null,
		group,
		recipe,
		fields,
		fieldValues
	);

	const afterSettingHook = applyFilters(
		'tasty_recipes_after_setting_hook',
		null,
		group,
		recipe,
		fields,
		fieldValues
	);

	const showField = ( field ) =>
		! ( field.is_pro || ( group === 'nutrition' && field.nutrifox_id ) );
	const title = ! isAdditionalTimeOpen
		? __( '+ Time', 'tasty-recipes-lite' )
		: __( '- Time', 'tasty-recipes-lite' );

	if (
		group === 'nutrition' &&
		initialFieldValues?.nutrifox_id?.length > 1
	) {
		return (
			<>
				{ beforeSettingHook }
				<div className={ className }>
					{ heading && <h3>{ heading }</h3> }
					{ renderBeforeFields &&
						renderBeforeFields( { fieldValues } ) }
				</div>
			</>
		);
	}

	const renderField = ( name, field ) => (
		<>
			<fieldset
				key={ name }
				className={ `tasty-recipes-recipe-${ name }-container` }
			>
				{ showLabels && (
					<label htmlFor={ `tasty-recipes-recipe-${ name }` }>
						{ field.label }
						{ field.tooltip && (
							<span
								className="tasty-tooltip-icon"
								data-tasty-tooltip={ field.tooltip }
							></span>
						) }
						{ name === 'prep_time' && showAdditionalTime && (
							<button
								className="tasty-recipes-add-additional-time"
								type="button"
								onClick={ ( e ) => {
									e.preventDefault();
									setIsAdditionalTimeOpen(
										! isAdditionalTimeOpen
									);
								} }
								title={ title }
							>
								{ title }
							</button>
						) }
					</label>
				) }
				{ field.options ? (
					<select
						id={ `tasty-recipes-recipe-${ name }` }
						name={ name }
						value={ fieldValues[ field.object_key || name ] || '' }
						className="inline-field"
						onChange={ ( e ) =>
							handleFieldChange(
								field.object_key || name,
								e.target.value
							)
						}
					>
						{ Object.entries( field.options ).map(
							( [ value, label ] ) => (
								<option key={ value } value={ value }>
									{ label }
								</option>
							)
						) }
					</select>
				) : (
					<input
						id={ `tasty-recipes-recipe-${ name }` }
						type="text"
						placeholder={ field.placeholder }
						name={ name }
						className="inline-field"
						value={ fieldValues[ field.object_key || name ] || '' }
						onChange={ ( e ) =>
							handleFieldChange(
								field.object_key || name,
								e.target.value
							)
						}
					/>
				) }
				{ name === 'prep_time' &&
					showAdditionalTime &&
					! isAdditionalTimeOpen && (
						<span style={ { display: 'none' } }>
							<input
								id="tasty-recipes-recipe-additional-time-label"
								type="hidden"
								name="additional_time_label"
							/>
							<input
								id="tasty-recipes-recipe-additional-time-value"
								type="hidden"
								name="additional_time_value"
							/>
						</span>
					) }
				{ name === 'prep_time' && ! showAdditionalTime && (
					<span style={ { display: 'none' } }>
						<input
							id="tasty-recipes-recipe-additional-time-label"
							type="hidden"
							name="additional_time_label"
							value=""
						/>
						<input
							id="tasty-recipes-recipe-additional-time-value"
							type="hidden"
							name="additional_time_value"
							value=""
						/>
					</span>
				) }
			</fieldset>
			{ name === 'prep_time' &&
				showAdditionalTime &&
				isAdditionalTimeOpen && (
					<fieldset className="tasty-recipes-additional-time-container">
						<input
							id="tasty-recipes-recipe-additional-time-label"
							type="text"
							name="additional_time_label"
							placeholder={ __(
								'Additional Time Label',
								'tasty-recipes-lite'
							) }
							value={ fieldValues.additional_time_label || '' }
							onChange={ ( e ) =>
								handleFieldChange(
									'additional_time_label',
									e.target.value
								)
							}
						/>
						<input
							id="tasty-recipes-recipe-additional-time-value"
							type="text"
							name="additional_time_value"
							placeholder={ __(
								'Additional Time Value',
								'tasty-recipes-lite'
							) }
							value={ fieldValues.additional_time_value || '' }
							onChange={ ( e ) =>
								handleFieldChange(
									'additional_time_value',
									e.target.value
								)
							}
						/>
					</fieldset>
				) }
		</>
	);

	return (
		<>
			{ beforeSettingHook }
			<div className={ className }>
				{ heading && <h3>{ heading }</h3> }
				{ renderBeforeFields && renderBeforeFields( { fieldValues } ) }
				<div className="tasty-recipes-fields-container">
					{ Object.entries( fields ).map(
						( [ name, field ] ) =>
							showField( field ) && renderField( name, field )
					) }
				</div>
			</div>
			{ afterSettingHook }
		</>
	);
};
