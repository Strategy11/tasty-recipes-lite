import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const NO_PENDING = { items: [], editUrl: '' };

const hasText = html => !! ( html || '' ).replaceAll( /<[^>]*>/g, '' ).trim();

export const useLabelActions = ( { restBase, recipe, isConnected, onChange } ) => {
	const [ isLinking, setIsLinking ] = useState( false );
	const [ draftId, setDraftId ] = useState( '' );
	const [ isAdding, setIsAdding ] = useState( false );
	const [ isBuilding, setIsBuilding ] = useState( false );
	const [ notice, setNotice ] = useState( null );
	const [ pending, setPending ] = useState( NO_PENDING );

	const labelId = Number.parseInt( recipe.nutrifox_id, 10 ) || 0;
	const isSynced = !! recipe.nutrifox_sync;
	const canBuild = !! recipe.title?.trim() && hasText( recipe.ingredients );

	useEffect( () => {
		if ( ! isConnected || ! labelId ) {
			setPending( NO_PENDING );
			return;
		}

		let isCurrent = true;

		apiFetch( { path: `/${ restBase }/labels/${ labelId }` } )
			.then( response => {
				if ( isCurrent ) {
					setPending( {
						items: response.pending_ingredients || [],
						editUrl: response.edit_url || '',
					} );
				}
			} )
			.catch( () => {
				if ( isCurrent ) {
					setPending( NO_PENDING );
				}
			} );

		return () => {
			isCurrent = false;
		};
	}, [ isConnected, labelId, restBase ] );

	const onLinkLabel = () => {
		setDraftId( labelId ? String( labelId ) : '' );
		setNotice( null );
		setIsLinking( true );
	};

	const onAddLabel = async () => {
		const id = Number.parseInt( ( draftId.match( /\d+/ ) || [] )[ 0 ], 10 );

		if ( ! id ) {
			setNotice( {
				type: 'error',
				message: __(
					'Enter a valid Nutrifox recipe ID.',
					'tasty-recipes-lite'
				),
			} );
			return;
		}

		setIsAdding( true );
		setNotice( null );

		try {
			await apiFetch( { path: `/${ restBase }/labels/${ id }` } );
		} catch ( error ) {
			setNotice( {
				type: 'error',
				message:
					error?.message ||
					__(
						'The label could not be checked. Please try again.',
						'tasty-recipes-lite'
					),
			} );
			return;
		} finally {
			setIsAdding( false );
		}

		onChange( 'nutrifox_id', String( id ) );
		onChange( 'nutrifox_sync', false );
		setIsLinking( false );
	};

	const onGoBack = () => {
		setIsLinking( false );
		setDraftId( '' );
		setNotice( null );
	};

	const onBuildLabel = async () => {
		setIsBuilding( true );
		setNotice( null );

		try {
			const label = await apiFetch( {
				path: `/${ restBase }/labels`,
				method: 'POST',
				data: {
					title: recipe.title || '',
					ingredients: recipe.ingredients || '',
				},
			} );

			onChange( 'nutrifox_id', String( label.id ) );
			onChange( 'nutrifox_sync', true );
		} catch ( error ) {
			setNotice( {
				type: 'error',
				message:
					error?.message ||
					__(
						'The label could not be built. Please try again.',
						'tasty-recipes-lite'
					),
			} );
		} finally {
			setIsBuilding( false );
		}
	};

	const onUnlink = () => {
		onChange( 'nutrifox_id', '' );
		onChange( 'nutrifox_sync', false );
	};

	return {
		labelId,
		isSynced,
		isLinking,
		draftId,
		isAdding,
		isBuilding,
		canBuild,
		notice,
		pending,
		setDraftId,
		onLinkLabel,
		onAddLabel,
		onGoBack,
		onBuildLabel,
		onUnlink,
	};
};
