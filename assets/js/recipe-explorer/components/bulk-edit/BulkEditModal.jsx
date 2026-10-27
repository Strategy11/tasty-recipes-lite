import { useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { Modal, useModal } from '@library';
import { BulkEditForm } from './BulkEditForm';
import {
	chunk,
	formatCount,
	getConfig,
	groupIdsByType,
	getEmptyChange,
	isActive,
} from './utils';

const MODAL_ID = 'tasty-recipes-bulk-edit-modal';

const getEmptyChanges = ( fields ) =>
	Object.fromEntries(
		fields.map( ( field ) => [ field.key, getEmptyChange( field ) ] )
	);

const TypeSplit = ( { groups, onSelect } ) => (
	<div className="tasty-recipes-modal-actions tasty-recipes-bulk-edit__footer">
		{ groups.map( ( group ) => (
			<button
				type="button"
				key={ group.type }
				className="tasty-secondary-button"
				onClick={ () => onSelect( group ) }
			>
				{ sprintf(
					/* translators: %s: number and type of items, e.g. "3 How Tos". */
					__( 'Edit %s', 'tasty-recipes-lite' ),
					formatCount( group.type, group.ids.length )
				) }
			</button>
		) ) }
	</div>
);

const Progress = ( { done, total } ) => (
	<div className="tasty-recipes-bulk-edit__progress">
		<div
			className="tasty-recipes-bulk-edit__progress-track"
			role="progressbar"
			aria-valuemin={ 0 }
			aria-valuemax={ total }
			aria-valuenow={ done }
		>
			<div
				className="tasty-recipes-bulk-edit__progress-bar"
				style={ { width: `${ total ? ( done / total ) * 100 : 0 }%` } }
			/>
		</div>
		<p className="tasty-recipes-bulk-edit__meta">
			{ sprintf(
				/* translators: 1: number of processed items, 2: total number of items. */
				__( 'Updating %1$d of %2$d…', 'tasty-recipes-lite' ),
				done,
				total
			) }
		</p>
	</div>
);

// Reload with the args core uses for its "X updated." notice.
const showUpdatedNotice = ( type, updated, skipped ) => {
	const { typeArg, skippedArg } = getConfig();
	const url = new URL( window.location.href );

	url.searchParams.set( 'updated', updated );
	url.searchParams.set( typeArg, type );
	url.searchParams.delete( skippedArg );

	if ( skipped ) {
		url.searchParams.set( skippedArg, skipped );
	}

	window.location.assign( url.toString() );
};

export const BulkEditModal = ( { ids, onClose } ) => {
	const config = getConfig();
	const groups = useMemo( () => groupIdsByType( ids ), [ ids ] );
	const { openModal, modalData, closeModal } = useModal();
	const wasOpened = useRef( false );

	const [ group, setGroup ] = useState(
		groups.length === 1 ? groups[ 0 ] : null
	);
	const [ changes, setChanges ] = useState( {} );
	const [ step, setStep ] = useState( 'edit' );
	const [ done, setDone ] = useState( 0 );
	const [ errors, setErrors ] = useState( [] );

	const fields = group ? config.types[ group.type ].fields : [];
	const activeFields = fields.filter( ( field ) =>
		isActive( changes[ field.key ] )
	);
	const canSubmit = activeFields.length > 0;

	useEffect( () => {
		if ( group ) {
			setChanges( getEmptyChanges( config.types[ group.type ].fields ) );
		}
	}, [ group, config.types ] );

	let title = __( 'Edit one type at a time', 'tasty-recipes-lite' );
	let subtitle = sprintf(
		/* translators: %s: list of item counts, e.g. "8 recipes, 3 How Tos". */
		__(
			'Your selection has %s. Each type is edited separately.',
			'tasty-recipes-lite'
		),
		groups
			.map( ( item ) => formatCount( item.type, item.ids.length ) )
			.join( ', ' )
	);

	if ( group ) {
		title = sprintf(
			/* translators: %s: number and type of items, e.g. "12 recipes". */
			__( 'Bulk edit %s', 'tasty-recipes-lite' ),
			formatCount( group.type, group.ids.length )
		);
		subtitle = __(
			'Only the fields you change are updated. Everything else stays the same.',
			'tasty-recipes-lite'
		);
	}

	useEffect( () => {
		openModal( {
			id: MODAL_ID,
			title,
			subtitle,
			disableClosing: step === 'saving',
		} );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ title, subtitle, step ] );

	useEffect( () => {
		if ( modalData.open ) {
			wasOpened.current = true;
			return;
		}

		if ( wasOpened.current ) {
			onClose();
		}
	}, [ modalData.open, onClose ] );

	const setChange = ( key, change ) => {
		setChanges( ( prev ) => ( {
			...prev,
			[ key ]: { ...prev[ key ], ...change },
		} ) );
	};

	const save = async () => {
		const payload = Object.fromEntries(
			activeFields.map( ( field ) => [
				field.key,
				{
					mode: changes[ field.key ].mode,
					value: changes[ field.key ].value,
				},
			] )
		);
		const updated = [];
		const skipped = [];
		let processed = 0;

		setDone( 0 );
		setErrors( [] );
		setStep( 'saving' );

		for ( const batch of chunk( group.ids, config.batchSize ) ) {
			try {
				// Batches run one at a time so a large selection can't time out.
				// eslint-disable-next-line no-await-in-loop
				const response = await apiFetch( {
					path: '/tasty-recipes/v1/recipe-explorer/bulk-edit',
					method: 'POST',
					data: {
						recipe_type: group.type,
						ids: batch,
						changes: payload,
					},
				} );

				updated.push( ...response.updated );
				skipped.push( ...response.skipped.map( ( item ) => item.reason ) );
			} catch ( error ) {
				const reason =
					error?.message || __( 'Something went wrong.', 'tasty-recipes-lite' );

				skipped.push( ...batch.map( () => reason ) );
			}

			processed += batch.length;
			setDone( processed );
		}

		if ( updated.length ) {
			showUpdatedNotice( group.type, updated.length, skipped.length );
			return;
		}

		setErrors( [ ...new Set( skipped ) ] );
		setStep( 'edit' );
	};

	let body;
	let footer;

	if ( ! group ) {
		body = null;
		footer = <TypeSplit groups={ groups } onSelect={ setGroup } />;
	} else if ( step === 'saving' ) {
		body = <Progress done={ done } total={ group.ids.length } />;
		footer = null;
	} else {
		body = Object.keys( changes ).length > 0 && (
			<>
				{ errors.length > 0 && (
					<div className="notice notice-error inline tasty-recipes-bulk-edit__error">
						<p>
							{ sprintf(
								/* translators: %s: item type, e.g. "recipes". */
								__( 'No %s were updated.', 'tasty-recipes-lite' ),
								config.types[ group.type ].plural
							) }{ ' ' }
							{ errors.join( ' ' ) }
						</p>
					</div>
				) }
				<BulkEditForm
					fields={ fields }
					changes={ changes }
					setChange={ setChange }
				/>
			</>
		);
		footer = (
			<div className="tasty-recipes-modal-actions tasty-recipes-bulk-edit__footer">
				<button
					type="button"
					className="tasty-secondary-button"
					onClick={ () => closeModal() }
				>
					{ __( 'Cancel', 'tasty-recipes-lite' ) }
				</button>
				<button
					type="button"
					className="tasty-button-primary"
					disabled={ ! canSubmit }
					onClick={ save }
				>
					{ sprintf(
						/* translators: %s: number and type of items, e.g. "12 recipes". */
						__( 'Update %s', 'tasty-recipes-lite' ),
						formatCount( group.type, group.ids.length )
					) }
				</button>
			</div>
		);
	}

	// Escape inside a picker should only close its menu, not the whole modal.
	const handleKeyDown = ( e ) => {
		if (
			e.key === 'Escape' &&
			e.target.closest(
				'.tasty-taxonomy-select-container, .tasty-recipes-bulk-edit__options'
			)
		) {
			e.stopPropagation();
		}
	};

	return (
		<Modal modalData={ modalData } closeModal={ closeModal }>
			{ /* eslint-disable-next-line jsx-a11y/no-static-element-interactions */ }
			<div className="tasty-recipes-bulk-edit" onKeyDown={ handleKeyDown }>
				{ body }
				{ footer }
			</div>
		</Modal>
	);
};
