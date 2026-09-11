import {
	Fragment,
	useEffect,
	useLayoutEffect,
	useMemo,
	useRef,
	useState,
} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { getRecipeEditorScrollContainer } from '../dom';
import { BulkEditIcon, PlusIcon, SectionTitleIcon } from '../icons';
import { getMediaImage, openImageMediaLibrary } from '../media-library';
import {
	DEFAULT_GROUP_LEVEL,
	REORDER_DURATION_MS,
	REORDER_EASING,
} from './constants';
import {
	markRowListUndoActive,
	registerRowListUndo,
} from './history';
import { BulkToolbar } from './BulkToolbar';
import { parseClipboardParts } from './paste';
import { RecipeRow } from './RecipeRow';
import { SelectionToolbar } from './SelectionToolbar';
import {
	applyBulkText,
	createGroupRow,
	createItemRow,
	decorateBulkImages,
	deleteSoftBreakBeforeCaret,
	ensureRowVisible,
	escapeHtml,
	insertSoftBreak,
	moveRow,
	parseRowsFromHtml,
	sanitizeInlineHtml,
	serializeBulkText,
	serializeRows,
	stripBulkImageChrome,
} from './utils';

const clearIsNew = row => ( { ...row, isNew: false } );

const RecipeRowList = ( {
	name,
	label,
	placeholder = '',
	mode = 'ingredients',
	initialValue = '',
	onChange = () => {},
	addItemLabel: addItemLabelProp,
} ) => {
	const [ rows, setRows ] = useState( () =>
		parseRowsFromHtml( initialValue )
	);
	const [ draggingId, setDraggingId ] = useState( null );
	const [ hasTextSelection, setHasTextSelection ] = useState( false );
	const [ isBulkEditing, setIsBulkEditing ] = useState( false );
	const [ bulkText, setBulkText ] = useState( '' );
	const listRef = useRef( null );
	const bulkRef = useRef( null );
	const rowsRef = useRef( rows );
	const dragRef = useRef( null );
	const endDragRef = useRef( null );
	const pendingFlipRef = useRef( null );
	const pendingScrollRowIdRef = useRef( null );
	const pastRef = useRef( [] );
	const futureRef = useRef( [] );
	const lastChangeRef = useRef( 'text' );
	const undoApiRef = useRef( null );
	const groupPlaceholder = __( 'For the …', 'tasty-recipes-lite' );
	const isInstructions = 'instructions' === mode;
	const addItemLabel =
		addItemLabelProp ||
		( isInstructions
			? __( 'Add Step', 'tasty-recipes-lite' )
			: __( 'Add Ingredient', 'tasty-recipes-lite' ) );

	rowsRef.current = rows;

	const serializedValue = useMemo(
		() => serializeRows( rows, mode ),
		[ rows, mode ]
	);
	const formValue = isBulkEditing
		? serializeRows( applyBulkText( bulkText ), mode )
		: serializedValue;

	useLayoutEffect( () => {
		const firstTops = pendingFlipRef.current;
		const list = listRef.current;
		const scrollRowId = pendingScrollRowIdRef.current;
		pendingScrollRowIdRef.current = null;

		if ( scrollRowId ) {
			ensureRowVisible( list, scrollRowId );
			window.requestAnimationFrame( () => {
				ensureRowVisible( list, scrollRowId );
			} );
		}

		if ( ! firstTops || ! list ) {
			return;
		}

		pendingFlipRef.current = null;

		const items = Array.from(
			list.querySelectorAll( '.tasty-recipes-item-row' )
		);

		items.forEach( el => {
			const prevTop = firstTops.get( el.dataset.rowId );
			if ( undefined === prevTop ) {
				return;
			}

			const dy = prevTop - el.getBoundingClientRect().top;
			if ( ! dy ) {
				return;
			}

			el.style.transition = 'none';
			el.style.transform = `translateY(${ dy }px)`;
			// Force layout so the invert is applied before animating back.
			el.getBoundingClientRect();
			el.style.transition = `transform ${ REORDER_DURATION_MS }ms ${ REORDER_EASING }`;
			el.style.transform = '';

			const clearStyles = () => {
				el.style.transition = '';
				el.style.transform = '';
			};
			el.addEventListener( 'transitionend', clearStyles, { once: true } );
			window.setTimeout( clearStyles, REORDER_DURATION_MS + 80 );
		} );

		if ( scrollRowId ) {
			window.setTimeout( () => {
				ensureRowVisible( list, scrollRowId );
			}, REORDER_DURATION_MS + 20 );
		}
	}, [ rows ] );

	useEffect( () => () => endDragRef.current?.(), [] );

	useLayoutEffect( () => {
		if ( ! isBulkEditing ) {
			return;
		}

		const el = bulkRef.current;
		if ( ! el ) {
			return;
		}

		const isFocused = el === el.ownerDocument.activeElement;
		if ( ! isFocused && stripBulkImageChrome( el.innerHTML ) !== bulkText ) {
			el.innerHTML = bulkText;
		}

		decorateBulkImages(
			el,
			__( 'Remove image', 'tasty-recipes-lite' )
		);

		if ( ! isFocused ) {
			el.focus();
		}
	}, [ isBulkEditing, bulkText ] );

	const cloneRows = source =>
		source.map( row => ( {
			...row,
			image: row.image ? { ...row.image } : null,
			isNew: false,
		} ) );

	const captureFlip = () => {
		const list = listRef.current;
		if ( ! list ) {
			return;
		}

		pendingFlipRef.current = new Map(
			Array.from(
				list.querySelectorAll( '.tasty-recipes-item-row' )
			).map( el => [
				el.dataset.rowId,
				el.getBoundingClientRect().top,
			] )
		);
	};

	const pushHistory = () => {
		pastRef.current = [
			...pastRef.current.slice( -49 ),
			cloneRows( rowsRef.current ),
		];
		futureRef.current = [];
		lastChangeRef.current = 'structure';
		markRowListUndoActive( undoApiRef.current );
	};

	if ( ! undoApiRef.current ) {
		undoApiRef.current = {
			canUndo: () => pastRef.current.length > 0,
			canRedo: () => futureRef.current.length > 0,
			shouldHandleUndo: () => 'structure' === lastChangeRef.current,
			undo: () => {
				if ( ! pastRef.current.length ) {
					return;
				}

				futureRef.current = [
					...futureRef.current,
					cloneRows( rowsRef.current ),
				];
				const prev = pastRef.current[ pastRef.current.length - 1 ];
				pastRef.current = pastRef.current.slice( 0, -1 );
				lastChangeRef.current = pastRef.current.length
					? 'structure'
					: 'text';
				captureFlip();
				commitRows( prev );
			},
			redo: () => {
				if ( ! futureRef.current.length ) {
					return;
				}

				pastRef.current = [
					...pastRef.current,
					cloneRows( rowsRef.current ),
				];
				const next = futureRef.current[ futureRef.current.length - 1 ];
				futureRef.current = futureRef.current.slice( 0, -1 );
				lastChangeRef.current = 'structure';
				captureFlip();
				commitRows( next );
			},
		};
	}

	useEffect( () => registerRowListUndo( undoApiRef.current ), [] );

	const commitRows = nextRows => {
		setRows( nextRows );
		onChange( serializeRows( nextRows.map( clearIsNew ), mode ) );
	};

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
		const existing = ( current.value || '' ).trim();

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

			const selection = window.getSelection();
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

		const list = listRef.current;
		if ( list ) {
			pendingFlipRef.current = new Map(
				Array.from(
					list.querySelectorAll( '.tasty-recipes-item-row' )
				).map( el => [
					el.dataset.rowId,
					el.getBoundingClientRect().top,
				] )
			);
		}

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

	const handleGripPointerDown = ( event, rowId ) => {
		if ( 0 !== event.button || dragRef.current ) {
			return;
		}

		const list = listRef.current;
		if ( ! list || rowsRef.current.length < 2 ) {
			return;
		}

		event.preventDefault();

		const startIndex = rowsRef.current.findIndex(
			row => row.id === rowId
		);
		if ( startIndex < 0 ) {
			return;
		}

		dragRef.current = {
			rowId,
			index: startIndex,
			startY: event.clientY,
			lastY: event.clientY,
			raf: null,
		};
		setDraggingId( rowId );
		document.body.classList.add( 'tasty-recipes-is-dragging' );

		try {
			event.currentTarget.setPointerCapture( event.pointerId );
		} catch ( error ) {
			// Pointer capture is best-effort.
		}

		const getRowElement = id =>
			list.querySelector( `[data-row-id="${ id }"]` );

		const tickDrag = () => {
			const drag = dragRef.current;
			if ( ! drag ) {
				return;
			}

			const rowEl = getRowElement( drag.rowId );
			if ( ! rowEl ) {
				return;
			}

			rowEl.style.transform = `translate3d(0, ${
				drag.lastY - drag.startY
			}px, 0)`;

			const others = Array.from(
				list.querySelectorAll( '.tasty-recipes-item-row' )
			).filter( el => el.dataset.rowId !== drag.rowId );
			const listTop = list.getBoundingClientRect().top;
			let targetIndex = others.length;

			for ( let i = 0; i < others.length; i++ ) {
				const mid =
					listTop +
					others[ i ].offsetTop +
					others[ i ].offsetHeight / 2;
				if ( drag.lastY < mid ) {
					targetIndex = i;
					break;
				}
			}

			if ( targetIndex !== drag.index ) {
				if ( ! drag.didMove ) {
					pushHistory();
					drag.didMove = true;
				}

				const before = rowEl.offsetTop;
				const nextRows = moveRow(
					rowsRef.current,
					drag.index,
					targetIndex
				);
				drag.index = targetIndex;
				commitRows( nextRows );

				window.requestAnimationFrame( () => {
					if ( ! dragRef.current ) {
						return;
					}
					const after = rowEl.offsetTop;
					dragRef.current.startY += after - before;
					rowEl.style.transform = `translate3d(0, ${
						dragRef.current.lastY - dragRef.current.startY
					}px, 0)`;
				} );
			}

			const scrollBody = getRecipeEditorScrollContainer();
			if ( scrollBody ) {
				const bounds = scrollBody.getBoundingClientRect();
				if ( drag.lastY < bounds.top + 48 ) {
					scrollBody.scrollTop -= 10;
				} else if ( drag.lastY > bounds.bottom - 48 ) {
					scrollBody.scrollTop += 10;
				}
			}
		};

		const onPointerMove = moveEvent => {
			const drag = dragRef.current;
			if ( ! drag ) {
				return;
			}

			moveEvent.preventDefault();
			drag.lastY = moveEvent.clientY;
			if ( ! drag.raf ) {
				drag.raf = window.requestAnimationFrame( () => {
					if ( dragRef.current ) {
						dragRef.current.raf = null;
					}
					tickDrag();
				} );
			}
		};

		const endDrag = () => {
			const drag = dragRef.current;
			if ( ! drag ) {
				return;
			}

			const rowEl = getRowElement( drag.rowId );
			if ( rowEl ) {
				rowEl.style.transition =
					'transform 0.22s cubic-bezier(0.34, 1.32, 0.64, 1)';
				rowEl.style.transform = '';
				const clearDragStyles = () => {
					rowEl.style.transition = '';
				};
				rowEl.addEventListener( 'transitionend', clearDragStyles, {
					once: true,
				} );
				window.setTimeout( clearDragStyles, 300 );
			}

			if ( drag.raf ) {
				window.cancelAnimationFrame( drag.raf );
			}

			dragRef.current = null;
			endDragRef.current = null;
			setDraggingId( null );
			document.body.classList.remove( 'tasty-recipes-is-dragging' );
			window.removeEventListener( 'pointermove', onPointerMove );
			window.removeEventListener( 'pointerup', onPointerUp );
			window.removeEventListener( 'pointercancel', onPointerUp );
			window.removeEventListener( 'keydown', onKeyDown );
		};

		endDragRef.current = endDrag;

		const onPointerUp = () => endDrag();
		const onKeyDown = keyEvent => {
			if ( 'Escape' === keyEvent.key ) {
				endDrag();
			}
		};

		window.addEventListener( 'pointermove', onPointerMove, {
			passive: false,
		} );
		window.addEventListener( 'pointerup', onPointerUp );
		window.addEventListener( 'pointercancel', onPointerUp );
		window.addEventListener( 'keydown', onKeyDown );
	};

	const handleOpenBulkEdit = () => {
		setBulkText( serializeBulkText( rows ) );
		setIsBulkEditing( true );
	};

	const handleApplyBulkEdit = () => {
		const nextRows = applyBulkText( bulkText );
		if ( serializeRows( nextRows, mode ) !== serializedValue ) {
			pushHistory();
			commitRows( nextRows );
		}
		setIsBulkEditing( false );
	};

	const syncBulkText = () => {
		const el = bulkRef.current;
		if ( ! el ) {
			setBulkText( '' );
			return;
		}

		decorateBulkImages(
			el,
			__( 'Remove image', 'tasty-recipes-lite' )
		);
		setBulkText( stripBulkImageChrome( el.innerHTML || '' ) );
	};

	const handleBulkImageClick = event => {
		const button = event.target.closest( '.tasty-recipes-row-image__remove' );
		if ( ! button || ! event.currentTarget.contains( button ) ) {
			return;
		}

		event.preventDefault();
		event.stopPropagation();
		button.closest( '.tasty-recipes-bulk-image' )?.remove();
		syncBulkText();
	};

	const handleCancelBulkEdit = () => {
		setIsBulkEditing( false );
		setBulkText( '' );
	};

	const handleBulkInput = event => {
		const el = event.currentTarget;
		if ( '<br>' === el.innerHTML || '<br/>' === el.innerHTML ) {
			el.innerHTML = '';
		}
		syncBulkText();
	};

	const handleBulkKeyDown = event => {
		if (
			'Backspace' === event.key &&
			! event.altKey &&
			! event.metaKey &&
			! event.ctrlKey &&
			deleteSoftBreakBeforeCaret( event.currentTarget )
		) {
			event.preventDefault();
			return;
		}

		if (
			'Enter' !== event.key ||
			event.altKey ||
			event.metaKey ||
			event.ctrlKey
		) {
			return;
		}

		event.preventDefault();

		if ( event.shiftKey ) {
			insertSoftBreak( event.currentTarget, { marked: true } );
			return;
		}

		const selection = window.getSelection();
		const range = selection?.rangeCount ? selection.getRangeAt( 0 ) : null;
		const node = range?.commonAncestorContainer;
		const el =
			node && Node.ELEMENT_NODE === node.nodeType
				? node
				: node?.parentElement;
		const heading = el?.closest( 'h1,h2,h3,h4,h5,h6' );

		if ( heading && event.currentTarget.contains( heading ) ) {
			const doc = heading.ownerDocument;
			const br = doc.createElement( 'br' );
			heading.after( br );
			const after = doc.createRange();
			after.setStartAfter( br );
			after.collapse( true );
			selection.removeAllRanges();
			selection.addRange( after );
			syncBulkText();
			return;
		}

		if (
			'function' === typeof document.queryCommandSupported &&
			document.queryCommandSupported( 'insertText' )
		) {
			document.execCommand( 'insertText', false, '\n' );
		} else {
			document.execCommand( 'insertHTML', false, '<br>' );
		}
		syncBulkText();
	};

	const handleBulkPaste = event => {
		const html = event.clipboardData?.getData( 'text/html' ) || '';
		const text = event.clipboardData?.getData( 'text' ) || '';
		if ( ! text && ! html ) {
			return;
		}

		event.preventDefault();

		if ( html ) {
			document.execCommand(
				'insertHTML',
				false,
				serializeBulkText( parseRowsFromHtml( html ) )
			);
			syncBulkText();
			return;
		}

		if (
			'function' === typeof document.queryCommandSupported &&
			document.queryCommandSupported( 'insertText' )
		) {
			document.execCommand( 'insertText', false, text );
		} else {
			document.execCommand(
				'insertHTML',
				false,
				escapeHtml( text ).replaceAll( /\r?\n/g, '<br>' )
			);
		}
		syncBulkText();
	};

	const itemCount = rows.filter( entry => 'item' === entry.type ).length;

	return (
		<>
			<fieldset className="tasty-recipes-row-list-field">
				<label htmlFor={ isBulkEditing ? `${ name }-bulk` : undefined }>
					{ label }
				</label>
				<input type="hidden" name={ name } value={ formValue } />
				{ isBulkEditing ? (
					<Fragment key="bulk-edit">
						<div className="tasty-recipes-row-list-bulk-editor">
							<BulkToolbar
								editorRef={ bulkRef }
								showImage={ isInstructions }
								onChange={ syncBulkText }
							/>
							<div className="tasty-recipes-row-list-bulk-body">
								<div
									id={ `${ name }-bulk` }
									ref={ bulkRef }
									className="tasty-recipes-row-list-bulk"
									data-tasty-recipes-autolink=""
									contentEditable
									suppressContentEditableWarning
									role="textbox"
									tabIndex={ 0 }
									aria-multiline="true"
									onInput={ handleBulkInput }
									onKeyDown={ handleBulkKeyDown }
									onPaste={ handleBulkPaste }
									onMouseDown={ handleBulkImageClick }
									onClick={ handleBulkImageClick }
								/>
							</div>
						</div>
						<div className="tasty-recipes-row-list-bulk-actions">
							<button
								type="button"
								className="tasty-button"
								onClick={ handleApplyBulkEdit }
							>
								{ __( 'Done', 'tasty-recipes-lite' ) }
							</button>
							<button
								type="button"
								className="tasty-secondary-button"
								onClick={ handleCancelBulkEdit }
							>
								{ __( 'Cancel', 'tasty-recipes-lite' ) }
							</button>
						</div>
					</Fragment>
				) : (
					<Fragment key="row-list">
						<div
							className={
								isInstructions && hasTextSelection
									? 'tasty-recipes-row-list tasty-recipes-row-list--has-selection'
									: 'tasty-recipes-row-list'
							}
							ref={ listRef }
							data-mode={ mode }
						>
							{ rows.map( ( row, index ) => (
								<RecipeRow
									key={ row.id }
									row={ row }
									mode={ mode }
									placeholder={ placeholder }
									groupPlaceholder={ groupPlaceholder }
									canMoveUp={ index > 0 }
									canMoveDown={ index < rows.length - 1 }
									canDelete={
										'group' === row.type || itemCount > 1
									}
									isDragging={ draggingId === row.id }
									onValueChange={ handleValueChange }
									onMoveUp={ () =>
										handleMoveRow( row.id, -1 )
									}
									onMoveDown={ () =>
										handleMoveRow( row.id, 1 )
									}
									onConvert={ () =>
										handleConvertRow( row.id )
									}
									onSplit={ handleSplitRow }
									onAddImage={
										isInstructions
											? handleAddImage
											: null
									}
									onRemoveImage={ handleRemoveImage }
									onDelete={ () =>
										handleDeleteRow( row.id )
									}
									onPaste={ handlePaste }
									onGripPointerDown={
										handleGripPointerDown
									}
								/>
							) ) }
						</div>
						<div className="tasty-recipes-rich-textarea-actions">
							<div className="tasty-recipes-rich-textarea-actions-start">
								<button
									type="button"
									className="tasty-recipes-rich-textarea-action tasty-recipes-rich-textarea-action--add"
									onClick={ handleAddItem }
								>
									<span
										className="tasty-recipes-rich-textarea-action-icon"
										aria-hidden="true"
									>
										{ PlusIcon }
									</span>
									{ addItemLabel }
								</button>
								<button
									type="button"
									className="tasty-recipes-rich-textarea-action tasty-recipes-rich-textarea-action--section"
									onClick={ handleAddSectionTitle }
								>
									<span
										className="tasty-recipes-rich-textarea-action-icon"
										aria-hidden="true"
									>
										{ SectionTitleIcon }
									</span>
									{ __(
										'Add Section Title',
										'tasty-recipes-lite'
									) }
								</button>
							</div>
							<button
								type="button"
								className="tasty-recipes-rich-textarea-action tasty-recipes-rich-textarea-action--bulk"
								onClick={ handleOpenBulkEdit }
							>
								<span
									className="tasty-recipes-rich-textarea-action-icon"
									aria-hidden="true"
								>
									{ BulkEditIcon }
								</span>
								{ __( 'Bulk Edit', 'tasty-recipes-lite' ) }
							</button>
						</div>
					</Fragment>
				) }
			</fieldset>
			{ ! isBulkEditing && (
				<SelectionToolbar
					containerRef={ listRef }
					onHasSelectionChange={
						isInstructions ? setHasTextSelection : null
					}
				/>
			) }
		</>
	);
};

export const IngredientsList = props => (
	<RecipeRowList mode="ingredients" { ...props } />
);

export const InstructionsList = props => (
	<RecipeRowList mode="instructions" { ...props } />
);
