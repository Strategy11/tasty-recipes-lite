import { __ } from '@wordpress/i18n';

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
				<svg
					width="10"
					height="10"
					viewBox="0 0 10 10"
					fill="none"
					xmlns="http://www.w3.org/2000/svg"
				>
					<path
						d="M9 1L1 9M1 1L9 9"
						stroke="currentColor"
						strokeWidth="1.5"
						strokeLinecap="round"
						strokeLinejoin="round"
					/>
				</svg>
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
