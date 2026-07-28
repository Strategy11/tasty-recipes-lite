import { __ } from '@wordpress/i18n';
import { CloseIcon } from './icons';

export const ImprovedKeysNotice = ( { onDismiss, isDismissed } ) => {
	if ( isDismissed ) {
		return null;
	}

	return (
		<div className="tasty-improved-keys-notice">
			<button
				type="button"
				onClick={ onDismiss }
				className="tasty-improved-keys-notice__close"
				aria-label={ __( 'Dismiss notice', 'tasty-recipes-lite' ) }
			>
				{ CloseIcon }
			</button>
			<div className="tasty-improved-keys-notice__header">
				<span className="tasty-improved-keys-notice__badge">
					{ __( 'New', 'tasty-recipes-lite' ) }
				</span>
				<strong className="tasty-improved-keys-notice__title">
					{ __( 'Improved Recipe Keys!', 'tasty-recipes-lite' ) }
				</strong>
			</div>
			<p className="tasty-improved-keys-notice__description">
				{ __(
					'You can now select your recipe keys from a predefined list of popular options, multi-select options for the same key, and add new (or delete old) custom keys instantly.',
					'tasty-recipes-lite'
				) }
			</p>
		</div>
	);
};
