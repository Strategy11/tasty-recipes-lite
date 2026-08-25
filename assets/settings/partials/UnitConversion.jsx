import { __ } from '@wordpress/i18n';
import { CheckInput, ToggleInput } from '@library';
import { isSettingDisabled } from '../utils/helpers';
import { optionNames } from '../utils/optionNames.js';

export const UnitConversion = ( {
	formData,
	handleChange,
	openModal,
	generateUpgradeUrl,
} ) => {
	const triggerModal = ( condition ) => {
		if ( condition ) {
			openModal( {
				id: 'tasty_recipes_upgrade_modal',
				title: __( 'Unit Conversion', 'tasty-recipes-lite' ),
				upgradeUrl: generateUpgradeUrl( {
					medium: 'tasty-recipes',
					content: 'unit-conversion',
				} ),
			} );
		}
	};

	return (
		<div className="tasty-recipes-pro-feature tasty-recipes-pro-unit-conversion-wrap">
			<ToggleInput
				onClick={ () => {
					triggerModal(
						isSettingDisabled( formData.unitConversion )
					);
				} }
				label={ __( 'Unit Conversion', 'tasty-recipes-lite' ) }
				id={ optionNames.unitConversion }
				name={ optionNames.unitConversion }
				value={ String( formData.unitConversion ?? 0 ) }
				onValue="1"
				offValue="0"
				setValue={ ( value ) => {
					handleChange( 'unitConversion', value );
				} }
				disabled={ isSettingDisabled( formData.unitConversion ) }
				helper={ __(
					'Allow visitors to convert between US Customary and Metric.',
					'tasty-recipes-lite'
				) }
			/>
			<CheckInput
				onClick={ () => {
					triggerModal(
						isSettingDisabled( formData.automaticUnitConversion )
					);
				} }
				id={ optionNames.automaticUnitConversion }
				name={ optionNames.automaticUnitConversion }
				disabled={ isSettingDisabled( formData.unitConversion ) }
				label={ __(
					'Automatically apply unit conversion to existing recipes.',
					'tasty-recipes-lite'
				) }
				value={ formData.automaticUnitConversion }
				isChecked={ formData.automaticUnitConversion }
				setValue={ ( checked ) =>
					handleChange( 'automaticUnitConversion', checked )
				}
			/>
		</div>
	);
};
