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

const toConnection = data => ( {
	status: data.status || 'disconnected',
	email: data.email || '',
	connectedFor: data.connectedFor || '',
} );

export const useNutrifoxConnection = ( {
	config = getNutrifoxConfig(),
	checkStatus = true,
} = {} ) => {
	const [ connection, setConnection ] = useState( () =>
		toConnection( config )
	);
	const [ isConnecting, setIsConnecting ] = useState( false );
	const [ isDisconnecting, setIsDisconnecting ] = useState( false );
	const [ notice, setNotice ] = useState( null );
	const stopWatchingRef = useRef( () => {} );
	const configRef = useRef( config );

	const applyConnection = useCallback( data => {
		const next = toConnection( data );

		// Keep the localized data fresh so the next modal opens in the right state.
		Object.assign( configRef.current, next );
		setConnection( next );
	}, [] );

	const refreshStatus = useCallback( async () => {
		try {
			const response = await apiFetch( {
				path: `/${ config.restBase }/status`,
			} );
			applyConnection( response );

			return response.status;
		} catch {
			return null;
		}
	}, [ config.restBase, applyConnection ] );

	const hasCheckedRef = useRef( false );

	useEffect( () => {
		if ( checkStatus && ! hasCheckedRef.current ) {
			hasCheckedRef.current = true;
			refreshStatus();
		}
	}, [ checkStatus, refreshStatus ] );

	useEffect( () => () => stopWatchingRef.current(), [] );

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

	const disconnect = useCallback( async () => {
		setNotice( null );
		setIsDisconnecting( true );

		try {
			applyConnection(
				await apiFetch( {
					path: `/${ config.restBase }/disconnect`,
					method: 'POST',
				} )
			);
		} catch ( error ) {
			setNotice( {
				type: 'error',
				message:
					error?.message ||
					__(
						'Nutrifox could not be disconnected. Please try again.',
						'tasty-recipes-lite'
					),
			} );
		} finally {
			setIsDisconnecting( false );
		}
	}, [ config.restBase, applyConnection ] );

	return {
		connection,
		isConnected: 'connected' === connection.status,
		isConnecting,
		isDisconnecting,
		notice,
		start,
		disconnect,
	};
};
