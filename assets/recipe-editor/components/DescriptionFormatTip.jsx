import { createInterpolateElement, useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { CloseIcon } from './icons';
import { postDismissAction } from './notice-utils';

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

		postDismissAction(
			'tasty_recipes_dismiss_description_format_tip',
			modifyNonce
		);
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
				{ CloseIcon }
			</button>
		</div>
	);
};
