import { createInterpolateElement } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { TextInput, ToggleInput } from '@library';
import { isSettingDisabled } from '../utils/helpers';

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

	return (
		<div className="tasty-recipes-pro-feature tasty-recipes-instacart-button-wrap">
			<ToggleInput
				onClick={ () => {
					triggerModal(
						isSettingDisabled( formData.instacartButton )
					);
				} }
				label={ __( 'Instacart Button', 'tasty-recipes-lite' ) }
				id="tasty_recipes_instacart"
				name="tasty_recipes_instacart"
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
			/>
			<TextInput
				label={ __( 'Instacart API Key', 'tasty-recipes-lite' ) }
				id="tasty-recipes-instacart-api-key"
				name="tasty_recipes_instacart_api_key"
				value={ formData.instacartApiKey }
				setValue={ ( value ) => {
					handleChange( 'instacartApiKey', value );
				} }
				disabled={ isSettingDisabled( formData.instacartApiKey ) }
				onClick={ () => {
					triggerModal(
						isSettingDisabled( formData.instacartApiKey )
					);
				} }
			/>
			<TextInput
				onClick={ () => {
					triggerModal(
						isSettingDisabled( formData.instacartAffiliateId )
					);
				} }
				label={ __(
					'Instacart Tastemakers Affiliate ID',
					'tasty-recipes-lite'
				) }
				id="tasty-recipes-instacart-affiliate-id"
				name="tasty_recipes_instacart_affiliate_id"
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
