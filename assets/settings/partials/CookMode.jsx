import { __ } from '@wordpress/i18n';
import { TextInput, ToggleInput } from '@library';
import classNames from 'classnames';
import { isSettingDisabled } from '../utils/helpers';

const isCookModeEnabled =
	window.tastyRecipesSettings.settings.canEnableCookMode;

export const CookMode = ( {
	formData,
	handleChange,
	openModal,
	generateUpgradeUrl,
} ) => {
	const triggerModal = ( condition ) => {
		if ( condition ) {
			openModal(
				'tasty_recipes_upgrade_modal',
				__( 'Enable Cook Mode', 'tasty-recipes-lite' ),
				generateUpgradeUrl( {
					medium: 'tasty-recipes',
					content: 'cook-mode',
				} )
			);
		}
	};

	return (
		<>
			<div
				className={ classNames( 'tasty-recipes-pro-feature', {
					'tasty-recipes-pro-feature-with-error': ! isCookModeEnabled,
				} ) }
			>
				<ToggleInput
					onClick={ () => {
						triggerModal( isSettingDisabled( formData.cookMode ) );
					} }
					label={ __( 'Enable Cook Mode', 'tasty-recipes-lite' ) }
					id="tasty_recipes_cook_mode"
					name="tasty_recipes_cook_mode"
					value={ String( formData.cookMode ?? 0 ) }
					onValue="1"
					offValue="0"
					setValue={ ( value ) => {
						handleChange( 'cookMode', value );
					} }
					disabled={ isSettingDisabled( formData.cookMode ) }
					helper={ __(
						'Show Cook Mode toggle to let visitors prevent sleep mode for supported devices.',
						'tasty-recipes-lite'
					) }
				/>
				{ ! isCookModeEnabled && (
					<div className="tasty-form-field tasty-recipes-error-message">
						{ /* eslint-disable-next-line jsx-a11y/label-has-associated-control */ }
						<label></label>
						<div className="tasty-recipes-settings-error">
							{ __(
								'Cook Mode requires SSL (HTTPS)',
								'tasty-recipes-lite'
							) }
						</div>
					</div>
				) }
			</div>
			<TextInput
				onClick={ () => {
					triggerModal(
						isSettingDisabled( formData.cookModeDescription )
					);
				} }
				label={ __( 'Cook Mode description', 'tasty-recipes-lite' ) }
				id="tasty-recipes-cook-mode-description"
				name="tasty_recipes_cook_mode_helper"
				value={ formData.cookModeDescription }
				disabled={ isSettingDisabled( formData.cookModeDescription ) }
				setValue={ ( value ) => {
					handleChange( 'cookModeDescription', value );
				} }
			/>
		</>
	);
};
