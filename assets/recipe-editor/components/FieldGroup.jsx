import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export const FieldGroup = ( {
	heading,
	group = '',
	fields,
	initialFieldValues,
	showLabels = true,
	onFieldChange = () => {},
	renderBeforeFields,
	className = '',
} ) => {
	const [ fieldValues, setFieldValues ] = useState( initialFieldValues );
	const [ isAdditionalTimeOpen, setIsAdditionalTimeOpen ] = useState( false );

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

	const showField = ( field ) =>
		! ( field.is_pro || ( group === 'nutrition' && field.nutrifox_id ) );
	const title = ! isAdditionalTimeOpen
		? __( '+ Time', 'tasty-recipes-lite' )
		: __( '- Time', 'tasty-recipes-lite' );

	return (
		<div className={ className }>
			{ heading && <h3>{ heading }</h3> }
			{ renderBeforeFields && renderBeforeFields( { fieldValues } ) }
			{ Object.entries( fields ).map(
				( [ name, field ] ) =>
					showField( field ) && (
						<fieldset
							key={ name }
							className={ `tasty-recipes-recipe-${ name }-container` }
						>
							<span>
								{ showLabels && (
									<label
										htmlFor={ `tasty-recipes-recipe-${ name }` }
									>
										{ field.label }
										{ field.tooltip && (
											<span
												className="tasty-tooltip-icon"
												data-tasty-tooltip={
													field.tooltip
												}
											></span>
										) }
										{ name === 'prep_time' && (
											// eslint-disable-next-line jsx-a11y/anchor-is-valid, jsx-a11y/click-events-have-key-events, jsx-a11y/no-static-element-interactions
											<a
												className="tasty-recipes-add-additional-time button secondary-button"
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
											</a>
										) }
									</label>
								) }
								{ field.options ? (
									<select
										id={ `tasty-recipes-recipe-${ name }` }
										name={ name }
										value={
											fieldValues[
												field.object_key || name
											] || ''
										}
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
												<option
													key={ value }
													value={ value }
												>
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
										value={
											fieldValues[
												field.object_key || name
											] || ''
										}
										onChange={ ( e ) =>
											handleFieldChange(
												field.object_key || name,
												e.target.value
											)
										}
									/>
								) }
							</span>
							{ name === 'prep_time' && (
								<span
									style={ {
										display: isAdditionalTimeOpen
											? 'inline-block'
											: 'none',
										marginLeft: '2px',
									} }
								>
									<input
										id="tasty-recipes-recipe-additional-time-label"
										type="text"
										name="additional_time_label"
										placeholder={ __(
											'Additional Time Label',
											'tasty-recipes-lite'
										) }
										className="inline-field"
										value={
											fieldValues.additional_time_label ||
											''
										}
										onChange={ ( e ) =>
											handleFieldChange(
												'additional_time_label',
												e.target.value
											)
										}
									/>
									<br />
									<input
										id="tasty-recipes-recipe-additional-time-value"
										type="text"
										name="additional_time_value"
										placeholder={ __(
											'Additional Time Value',
											'tasty-recipes-lite'
										) }
										className="inline-field"
										value={
											fieldValues.additional_time_value ||
											''
										}
										onChange={ ( e ) =>
											handleFieldChange(
												'additional_time_value',
												e.target.value
											)
										}
									/>
								</span>
							) }
							{ name === 'prep_time' &&
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
						</fieldset>
					)
			) }
		</div>
	);
};
