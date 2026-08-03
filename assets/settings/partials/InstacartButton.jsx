import { createInterpolateElement } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { TextInput, ToggleInput } from '@library';
import { isSettingDisabled } from '../utils/helpers';
import { optionNames } from '../utils/optionNames.js';

export const InstacartButton = ( {
	formData,
	handleChange,
	openModal,
	generateUpgradeUrl,
} ) => {
	const triggerModal = ( condition ) => {
		if ( condition ) {
			openModal( {
				id: 'tasty_recipes_upgrade_modal',
				title: __( 'Instacart Button', 'tasty-recipes-lite' ),
				upgradeUrl: generateUpgradeUrl( {
					medium: 'tasty-recipes',
					content: 'instacart-button',
				} ),
			} );
		}
	};

	const instacartButtonElement = applyFilters(
		'tasty_recipes_instacart_button',
		<ToggleInput
			onClick={ () => {
				triggerModal(
					isSettingDisabled( formData.instacartButton )
				);
			} }
			label={ __( 'Instacart Button', 'tasty-recipes-lite' ) }
			id={ optionNames.instacart }
			name={ optionNames.instacart }
			value={ String( formData.instacartButton ?? 0 ) }
			onValue="1"
			offValue="0"
			setValue={ ( value ) => {
				handleChange( 'instacartButton', value );
			} }
			disabled={ isSettingDisabled( formData.instacartButton ) }
			helper={ createInterpolateElement(
				__(
					'By turning this on, you agree to the <termsOfServiceLink>Instacart terms of service</termsOfServiceLink>, available in the US only at the moment.',
					'tasty-recipes-lite'
				),
				{
					termsOfServiceLink: (
						// eslint-disable-next-line jsx-a11y/anchor-has-content
						<a
							href="https://affiliate.instacart.com/terms"
							target="_blank"
							rel="noreferrer"
						/>
					),
				}
			) }
		/>,
		formData,
		handleChange
	);

	return (
		<div className="tasty-recipes-pro-feature tasty-recipes-instacart-button-wrap">
			{ instacartButtonElement }
			<TextInput
				onClick={ () => {
					triggerModal(
						isSettingDisabled( formData.instacartAffiliateId )
					);
				} }
				label={ __(
					'Instacart Impact Affiliate ID',
					'tasty-recipes-lite'
				) }
				id="tasty-recipes-instacart-affiliate-id"
				name={ optionNames.instacartAffiliateId }
				value={ formData.instacartAffiliateId }
				disabled={ isSettingDisabled( formData.instacartAffiliateId ) }
				setValue={ ( value ) => {
					handleChange( 'instacartAffiliateId', value );
				} }
				helper={ createInterpolateElement(
					__(
						'<a>Get commissions</a> when visitors order ingredients.',
						'tasty-recipes-lite'
					),
					{
						a: (
							// eslint-disable-next-line jsx-a11y/anchor-has-content
							<a
								href="https://www.wptasty.com/go/instacart"
								target="_blank"
								rel="noreferrer"
							/>
						),
					}
				) }
			/>
		</div>
	);
};
