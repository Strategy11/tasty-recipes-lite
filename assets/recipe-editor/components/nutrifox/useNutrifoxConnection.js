import apiFetch from '@wordpress/api-fetch';
import { useCallback, useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const AUTH_TAB_NAME = 'tasty-recipes-nutrifox-connect';
const POLL_INTERVAL = 1500;
const BLOCKED_TAB_TIMEOUT = 5 * 60 * 1000;

export const getNutrifoxConfig = () =>
	window.tastyRecipesRecipeEditorData?.nutrifox || {};

/**
 * Opens an empty tab synchronously so browsers treat it as user initiated.
 *
 * @return {Window|null} Tab window, or null when blocked.
 */
const openAuthTab = () => window.open( '', AUTH_TAB_NAME );

const resultNotices = {
	denied: {
		type: 'info',
		message: __(
			'Nutrifox was not connected because access was denied.',
			'tasty-recipes-lite'
		),
	},
	error: {
		type: 'error',
		message: __(
			'Nutrifox could not be connected. Please try again.',
			'tasty-recipes-lite'
		),
	},
};

/**
 * Manages the site's Nutrifox connection from the recipe modal.
 *
 * @return {Object} Connection state and actions.
 */
export const useNutrifoxConnection = () => {
	const config = getNutrifoxConfig();
	const [ connection, setConnection ] = useState( {
		status: config.status || 'disconnected',
		email: config.email || '',
	} );
	const [ isConnecting, setIsConnecting ] = useState( false );
	const [ notice, setNotice ] = useState( null );
	const stopWatchingRef = useRef( () => {} );

	const refreshStatus = useCallback( async () => {
		try {
			const response = await apiFetch( {
				path: `/${ config.restBase }/status`,
			} );
			const next = { status: response.status, email: response.email };

			// Keep the localized data fresh so the next modal opens in the right state.
			Object.assign( getNutrifoxConfig(), next );
			setConnection( next );

			return response.status;
		} catch {
			return null;
		}
	}, [ config.restBase ] );

	useEffect( () => {
		refreshStatus();

		return () => stopWatchingRef.current();
	}, [ refreshStatus ] );

	const finish = useCallback(
		async result => {
			stopWatchingRef.current();
			setIsConnecting( false );

			if ( 'connected' === result.status ) {
				setNotice( null );
				await refreshStatus();
				return;
			}

			const notice = resultNotices[ result.status ];
			if ( ! notice ) {
				return;
			}

			setNotice( {
				...notice,
				message:
					'error' === result.status && result.message
						? result.message
						: notice.message,
			} );
		},
		[ refreshStatus ]
	);

	const watch = useCallback(
		authTab => {
			stopWatchingRef.current();

			let isDone = false;
			const startedAt = Date.now();

			const done = result => {
				if ( ! isDone ) {
					isDone = true;
					finish( result );
				}
			};

			const onMessage = event => {
				if (
					event.origin !== window.location.origin ||
					'tasty-nutrifox' !== event.data?.type
				) {
					return;
				}

				done( event.data );
			};

			const poll = async () => {
				if ( authTab && ! authTab.closed ) {
					return;
				}

				const status = await refreshStatus();
				if ( 'connected' === status ) {
					done( { status } );
				} else if (
					authTab ||
					Date.now() - startedAt > BLOCKED_TAB_TIMEOUT
				) {
					done( { status: 'closed' } );
				}
			};

			const pollId = setInterval( poll, POLL_INTERVAL );
			window.addEventListener( 'message', onMessage );

			stopWatchingRef.current = () => {
				isDone = true;
				clearInterval( pollId );
				window.removeEventListener( 'message', onMessage );
			};
		},
		[ finish, refreshStatus ]
	);

	const start = useCallback(
		async intent => {
			const authTab = openAuthTab();

			setNotice( null );
			setIsConnecting( true );

			let url;
			try {
				( { url } = await apiFetch( {
					path: `/${ config.restBase }/connect`,
					method: 'POST',
					data: { intent },
				} ) );
			} catch ( error ) {
				authTab?.close();
				setIsConnecting( false );
				setNotice( {
					type: 'error',
					message: error?.message || resultNotices.error.message,
				} );
				return;
			}

			if ( ! authTab || authTab.closed ) {
				setIsConnecting( false );
				setNotice( {
					type: 'warning',
					message: __(
						'Your browser blocked the Nutrifox tab. Allow pop-ups for this site, or open Nutrifox manually.',
						'tasty-recipes-lite'
					),
					url,
				} );
				watch( null );
				return;
			}

			authTab.location.href = url;
			authTab.focus();
			watch( authTab );
		},
		[ config.restBase, watch ]
	);

	return {
		connection,
		isConnected: 'connected' === connection.status,
		isConnecting,
		notice,
		start,
	};
};
