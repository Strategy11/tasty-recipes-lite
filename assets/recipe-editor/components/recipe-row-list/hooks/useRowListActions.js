import { getMediaImage, openImageMediaLibrary } from '../../media-library';
import { DEFAULT_GROUP_LEVEL } from '../lib/constants';
import { captureRowTops } from './flip';
import { parseClipboardParts } from '../lib/paste';
import {
	clearIsNew,
	createGroupRow,
	createItemRow,
	escapeHtml,
	moveRow,
	sanitizeInlineHtml,
} from '../lib/utils';

export const useRowListActions = ( {
	rows,
	rowsRef,
	isInstructions,
	lastChangeRef,
	pendingScrollRowIdRef,
	pendingFlipRef,
	listRef,
	commitRows,
	pushHistory,
} ) => {
	const handleValueChange = ( id, value ) => {
		lastChangeRef.current = 'text';
		const nextRows = rows.map( row => {
			if ( row.id !== id ) {
				return clearIsNew( row );
			}

			return {
				...row,
				value,
				isNew: false,
			};
		} );
		commitRows( nextRows );
	};

	const handleAddItem = () => {
		pushHistory();
		commitRows( [
			...rows.map( clearIsNew ),
			createItemRow( { isNew: true } ),
		] );
	};

	const handleAddSectionTitle = () => {
		pushHistory();
		commitRows( [
			...rows.map( clearIsNew ),
			createGroupRow( { isNew: true } ),
		] );
	};

	const handleSplitRow = ( rowId, split ) => {
		const fromIndex = rows.findIndex( row => row.id === rowId );
		if ( fromIndex < 0 ) {
			return;
		}

		const current = rows[ fromIndex ];
		const nextRow = createItemRow( {
			value: split ? split.after : '',
			isNew: true,
		} );

		const nextRows = [
			...rows.slice( 0, fromIndex ).map( clearIsNew ),
			{
				...current,
				value: split ? split.before : current.value,
				isNew: false,
			},
			nextRow,
			...rows.slice( fromIndex + 1 ).map( clearIsNew ),
		];

		pendingScrollRowIdRef.current = nextRow.id;
		pushHistory();
		commitRows( nextRows );
	};

	const handlePaste = ( event, rowId ) => {
		const clipboard = event.clipboardData;
		if ( ! clipboard ) {
			return;
		}

		const text = clipboard.getData( 'text' ) || '';
		const html = clipboard.getData( 'text/html' ) || '';
		if ( ! text && ! html ) {
			return;
		}

		const parsed = parseClipboardParts( text, html );
		if ( ! parsed.length ) {
			event.preventDefault();
			return;
		}

		const fromIndex = rows.findIndex( row => row.id === rowId );
		if ( fromIndex < 0 ) {
			return;
		}

		const current = rows[ fromIndex ];

		// Single plain item — insert as plain text into the current field.
		if ( 1 === parsed.length && 'item' === parsed[ 0 ].type ) {
			event.preventDefault();
			const plain = parsed[ 0 ].text || text;
			if (
				'function' === typeof document.queryCommandSupported &&
				document.queryCommandSupported( 'insertText' )
			) {
				document.execCommand( 'insertText', false, plain );
				return;
			}

			const selection =
				event.currentTarget?.ownerDocument?.defaultView?.getSelection();
			if ( selection?.rangeCount ) {
				const range = selection.getRangeAt( 0 );
				range.deleteContents();
				range.insertNode( document.createTextNode( plain ) );
				range.collapse( false );
				const el = event.currentTarget;
				if ( el ) {
					handleValueChange(
						rowId,
						sanitizeInlineHtml( el.innerHTML || '' )
					);
				}
			}
			return;
		}

		event.preventDefault();

		const existing = ( current.value || '' ).trim();
		const parts = parsed.map( part => ( {
			type: part.type,
			html: escapeHtml( part.text ),
		} ) );
		if ( existing ) {
			parts.unshift( { type: 'item', html: existing } );
		}

		const created = parts.map( ( part, index ) => {
			const isLast = index === parts.length - 1;
			if ( 'heading' === part.type ) {
				return createGroupRow( {
					value: part.html,
					isNew: isLast,
				} );
			}

			return createItemRow( {
				value: part.html,
				isNew: isLast,
			} );
		} );

		const [ first ] = created;
		created[ 0 ] = {
			...first,
			id: current.id,
			isNew: 1 === created.length,
		};

		if ( current.image ) {
			if ( 'item' === first.type ) {
				created[ 0 ].image = current.image;
			} else {
				created.unshift( createItemRow( { image: current.image } ) );
			}
		}

		const nextRows = [
			...rows.slice( 0, fromIndex ).map( clearIsNew ),
			...created,
			...rows.slice( fromIndex + 1 ).map( clearIsNew ),
		];

		pendingScrollRowIdRef.current = created[ created.length - 1 ].id;
		pushHistory();
		commitRows( nextRows );
	};

	const handleMoveRow = ( rowId, direction ) => {
		const fromIndex = rows.findIndex( row => row.id === rowId );
		if ( fromIndex < 0 ) {
			return;
		}

		const toIndex = fromIndex + direction;
		const nextRows = moveRow( rows, fromIndex, toIndex );
		if ( nextRows === rows ) {
			return;
		}

		pendingFlipRef.current = captureRowTops( listRef.current );
		pendingScrollRowIdRef.current = rowId;
		pushHistory();
		commitRows( nextRows );
	};

	const handleConvertRow = rowId => {
		pushHistory();
		commitRows(
			rows.map( row => {
				if ( row.id !== rowId ) {
					return clearIsNew( row );
				}

				const nextType = 'group' === row.type ? 'item' : 'group';
				return {
					...row,
					type: nextType,
					level: row.level || DEFAULT_GROUP_LEVEL,
					image: 'group' === nextType ? null : row.image,
					isNew: true,
				};
			} )
		);
	};

	const handleAddImage = rowId => {
		if ( ! isInstructions ) {
			return;
		}

		openImageMediaLibrary( {
			onSelect: attachment => {
				const image = getMediaImage( attachment, [
					'large',
					'full',
					'medium',
				] );
				if ( ! image?.url ) {
					return;
				}

				const nextRows = rowsRef.current.map( row => {
					if ( row.id !== rowId ) {
						return clearIsNew( row );
					}

					return {
						...row,
						image,
						isNew: false,
					};
				} );
				pushHistory();
				commitRows( nextRows );
			},
		} );
	};

	const handleRemoveImage = rowId => {
		pushHistory();
		const nextRows = rows.map( row => {
			if ( row.id !== rowId ) {
				return clearIsNew( row );
			}

			return {
				...row,
				image: null,
				isNew: false,
			};
		} );
		commitRows( nextRows );
	};

	const handleDeleteRow = rowId => {
		pushHistory();
		let nextRows = rows
			.filter( row => row.id !== rowId )
			.map( clearIsNew );

		const hasItemRow = nextRows.some( row => 'item' === row.type );
		if ( ! hasItemRow ) {
			nextRows = [ ...nextRows, createItemRow() ];
		}

		commitRows( nextRows );
	};

	return {
		handleValueChange,
		handleAddItem,
		handleAddSectionTitle,
		handleSplitRow,
		handlePaste,
		handleMoveRow,
		handleConvertRow,
		handleAddImage,
		handleRemoveImage,
		handleDeleteRow,
	};
};
