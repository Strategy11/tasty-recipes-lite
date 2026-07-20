import { useState, useEffect, useRef, Fragment } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import classNames from 'classnames';
import { TaxonomySelect } from './taxonomy-select';
import { ChevronIcon } from './icons';

export const FieldGroup = ( {
	heading,
	group = '',
	fields,
	initialFieldValues,
	showLabels = true,
	showAdditionalTime = true,
	onFieldChange = () => {},
	renderBeforeFields,
	renderAfterField = null,
	className = '',
	recipe,
	defaultCollapsed = false,
	collapsed: collapsedProp,
	onCollapseChange,
} ) => {
	const [ fieldValues, setFieldValues ] = useState( initialFieldValues );
	const [ isAdditionalTimeOpen, setIsAdditionalTimeOpen ] = useState( false );
	const [ internalCollapsed, setInternalCollapsed ] = useState(
		defaultCollapsed
	);
	const isInitialMount = useRef( true );
	const isCollapseControlled = typeof collapsedProp === 'boolean';
	const isCollapsed = isCollapseControlled
		? collapsedProp
		: internalCollapsed;

	const setCollapsed = nextCollapsed => {
		if ( ! isCollapseControlled ) {
			setInternalCollapsed( nextCollapsed );
		}
		onCollapseChange?.( nextCollapsed );
	};

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
			setFieldValues( prev => ( {
				...prev,
				additional_time_label: '',
				additional_time_value: '',
			} ) );
		}
	}, [ isAdditionalTimeOpen ] );

	const handleFieldChange = ( field, value ) => {
		setFieldValues( prev => ( { ...prev, [ field ]: value } ) );
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

	const showField = field =>
		! ( field.is_pro || ( group === 'nutrition' && field.nutrifox_id ) );
	const title = ! isAdditionalTimeOpen
		? __( '+ Time', 'tasty-recipes-lite' )
		: __( '- Time', 'tasty-recipes-lite' );
	const fieldPlaceholders = {
		prep_time: __( 'e.g. 15 min', 'tasty-recipes-lite' ),
		cook_time: __( 'e.g. 30 min', 'tasty-recipes-lite' ),
		total_time: __( 'Defaults to prep + cook', 'tasty-recipes-lite' ),
		yield: __( 'e.g. 4 servings', 'tasty-recipes-lite' ),
		category: __( 'Select category...', 'tasty-recipes-lite' ),
		method: __( 'Select method...', 'tasty-recipes-lite' ),
		cuisine: __( 'Select cuisine...', 'tasty-recipes-lite' ),
		diet: __( 'Select diet...', 'tasty-recipes-lite' ),
		keywords: __(
			'e.g. easy, weeknight, kid-friendly',
			'tasty-recipes-lite'
		),
	};

	const renderToggle = () => {
		if ( ! heading ) {
			return null;
		}

		return (
			<button
				type="button"
				className="tasty-recipes-field-group__head"
				aria-expanded={ ! isCollapsed }
				onClick={ () => setCollapsed( ! isCollapsed ) }
			>
				<span className="tasty-recipes-field-group__title">
					{ heading }
				</span>
				<span
					className="tasty-recipes-field-group__chev"
					aria-hidden="true"
				>
					{ ChevronIcon }
				</span>
			</button>
		);
	};

	const renderCollapsibleBody = content => (
		<div className="tasty-recipes-field-group__body">
			<div className="tasty-recipes-field-group__body-inner">
				{ content }
			</div>
		</div>
	);

	if (
		group === 'nutrition' &&
		initialFieldValues?.nutrifox_id?.length > 1
	) {
		return (
			<>
				{ beforeSettingHook }
				<div
					className={ classNames(
						className,
						'tasty-recipes-field-group',
						{
							'is-collapsed': isCollapsed,
						}
					) }
				>
					{ renderToggle() }
					{ renderCollapsibleBody(
						renderBeforeFields &&
							renderBeforeFields( { fieldValues } )
					) }
				</div>
				{ afterSettingHook }
			</>
		);
	}

	const renderFieldInput = ( name, field ) => {
		if ( field.taxonomy ) {
			return (
				<TaxonomySelect
					name={ name }
					taxonomy={ field.taxonomy }
					placeholder={
						field.placeholder || fieldPlaceholders[ name ] || ''
					}
					value={ fieldValues[ field.object_key || name ] || '' }
					onChange={ newValue =>
						handleFieldChange( field.object_key || name, newValue )
					}
				/>
			);
		}

		if ( field.options ) {
			return (
				<select
					id={ `tasty-recipes-recipe-${ name }` }
					name={ name }
					value={ fieldValues[ field.object_key || name ] || '' }
					className="inline-field"
					placeholder={
						field.placeholder || fieldPlaceholders[ name ] || ''
					}
					onChange={ e =>
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
			);
		}

		return (
			<input
				id={ `tasty-recipes-recipe-${ name }` }
				type="text"
				placeholder={
					fieldPlaceholders[ name ] || field.placeholder || ''
				}
				name={ name }
				className="inline-field"
				value={ fieldValues[ field.object_key || name ] || '' }
				onChange={ e =>
					handleFieldChange(
						field.object_key || name,
						e.target.value
					)
				}
			/>
		);
	};

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
								onClick={ e => {
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
				{ renderFieldInput( name, field ) }
				{ name === 'prep_time' && ! isAdditionalTimeOpen && (
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
							onChange={ e =>
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
							onChange={ e =>
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

	const fieldsContent = (
		<>
			{ renderBeforeFields && renderBeforeFields( { fieldValues } ) }
			<div className="tasty-recipes-fields-container">
				{ Object.entries( fields ).map( ( [ name, field ] ) => {
					if ( ! showField( field ) ) {
						return null;
					}

					const AfterField = renderAfterField?.[ name ];

					return (
						<Fragment key={ name }>
							{ renderField( name, field ) }
							{ AfterField ? (
								<div className="tasty-recipes-fields-insert">
									<AfterField fieldValues={ fieldValues } />
								</div>
							) : null }
						</Fragment>
					);
				} ) }
			</div>
		</>
	);

	if ( ! heading ) {
		return (
			<>
				{ beforeSettingHook }
				<div className={ className }>{ fieldsContent }</div>
				{ afterSettingHook }
			</>
		);
	}

	return (
		<>
			{ beforeSettingHook }
			<div
				className={ classNames(
					className,
					'tasty-recipes-field-group',
					{
						'is-collapsed': isCollapsed,
					}
				) }
			>
				{ renderToggle() }
				{ renderCollapsibleBody( fieldsContent ) }
			</div>
			{ afterSettingHook }
		</>
	);
};
