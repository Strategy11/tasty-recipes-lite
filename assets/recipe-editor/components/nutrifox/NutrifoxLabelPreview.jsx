import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const DEFAULT_HEIGHT = 320;

const parseMessage = data => {
	if ( 'string' !== typeof data ) {
		return null;
	}

	try {
		return JSON.parse( data );
	} catch {
		return null;
	}
};

// The embed measures itself once on load, so it must not load while hidden in a collapsed group.
const useIsRevealed = ref => {
	const [ isRevealed, setIsRevealed ] = useState( false );

	useEffect( () => {
		if ( isRevealed || ! ref.current ) {
			return;
		}

		const observer = new IntersectionObserver( ( [ entry ] ) => {
			if ( entry.intersectionRatio > 0 ) {
				setIsRevealed( true );
			}
		} );

		observer.observe( ref.current );

		return () => observer.disconnect();
	}, [ isRevealed, ref ] );

	return isRevealed;
};

export const NutrifoxLabelPreview = ( { labelId, nutrifoxUrl } ) => {
	const [ height, setHeight ] = useState( DEFAULT_HEIGHT );
	const placeholderRef = useRef( null );
	const isRevealed = useIsRevealed( placeholderRef );

	useEffect( () => {
		const nutrifoxOrigin = new URL( nutrifoxUrl ).origin;

		const onMessage = event => {
			const payload = parseMessage( event.data );

			if (
				event.origin === nutrifoxOrigin &&
				'setHeight' === payload?.event &&
				labelId === Number.parseInt( payload.recipeId, 10 ) &&
				payload.height > 0
			) {
				setHeight( payload.height );
			}
		};

		window.addEventListener( 'message', onMessage );

		return () => window.removeEventListener( 'message', onMessage );
	}, [ labelId, nutrifoxUrl ] );

	if ( ! isRevealed ) {
		return (
			<div
				ref={ placeholderRef }
				className="tasty-recipes-nutrifox-panel__preview-placeholder"
				style={ { height } }
			/>
		);
	}

	return (
		<iframe
			className="tasty-recipes-nutrifox-panel__preview"
			title={ __( 'Nutrifox label preview', 'tasty-recipes-lite' ) }
			src={ `${ nutrifoxUrl }/embed/label/${ labelId }` }
			style={ { height } }
		/>
	);
};
