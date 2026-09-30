import { useState } from '@wordpress/element';

/**
 * Label actions for the Nutrifox panel.
 *
 * @param {string} nutrifoxUrl Nutrifox base URL.
 *
 * @return {Object} Linking state and label action handlers.
 */
export const useLabelActions = nutrifoxUrl => {
	const [ isLinking, setIsLinking ] = useState( false );

	return {
		isLinking,
		onBuildLabel: () =>
			window.open( `${ nutrifoxUrl }/recipes/create`, '_blank', 'noopener' ),
		onLinkLabel: () => setIsLinking( true ),
		onLinkDone: () => setIsLinking( false ),
	};
};
