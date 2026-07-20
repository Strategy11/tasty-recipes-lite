import { createInterpolateElement, useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export const DescriptionFormatTip = ( {
	isRevealed = false,
	initialDismissed = false,
	modifyNonce = '',
	onDismiss = () => {},
} ) => {
	const [ isDismissed, setIsDismissed ] = useState( !! initialDismissed );

	useEffect( () => {
		if ( initialDismissed ) {
			setIsDismissed( true );
		}
	}, [ initialDismissed ] );

	const handleDismiss = () => {
		setIsDismissed( true );
		onDismiss();

		if ( ! modifyNonce || ! window.ajaxurl ) {
			return;
		}

		const formData = new FormData();
		formData.append(
			'action',
			'tasty_recipes_dismiss_description_format_tip'
		);
		formData.append( 'nonce', modifyNonce );

		fetch( window.ajaxurl, {
			method: 'POST',
			body: formData,
		} );
	};

	if ( isDismissed || ! isRevealed ) {
		return null;
	}

	return (
		<div className="tasty-description-format-tip">
			<span className="tasty-description-format-tip__content">
				<span className="tasty-description-format-tip__badge">
					{ __( 'TIP', 'tasty-recipes-lite' ) }
				</span>
				<span className="tasty-description-format-tip__message">
					{ createInterpolateElement(
						__(
							'Highlight any text to make it <bold>bold</bold>, <italic>italic</italic>, <underline>underline</underline>, or add a <link>link</link>.',
							'tasty-recipes-lite'
						),
						{
							bold: (
								<strong className="tasty-description-format-tip__bold" />
							),
							italic: (
								<em className="tasty-description-format-tip__italic" />
							),
							underline: (
								<span className="tasty-description-format-tip__underline" />
							),
							link: (
								<span className="tasty-description-format-tip__link" />
							),
						}
					) }
				</span>
			</span>
			<button
				type="button"
				className="tasty-description-format-tip__close"
				onClick={ handleDismiss }
				aria-label={ __( 'Dismiss tip', 'tasty-recipes-lite' ) }
			>
				<svg
					width="10"
					height="10"
					viewBox="0 0 10 10"
					fill="none"
					xmlns="http://www.w3.org/2000/svg"
					aria-hidden="true"
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
		</div>
	);
};
