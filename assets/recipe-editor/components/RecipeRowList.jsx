import {
	createPortal,
	useEffect,
	useLayoutEffect,
	useRef,
	useState,
} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import classNames from 'classnames';
import {
	DragGripIcon,
	MoveDownIcon,
	MoveUpIcon,
	ParagraphIcon,
	RowTrashIcon,
	SectionTitleIcon,
} from './icons';

const REORDER_DURATION_MS = 220;
const REORDER_EASING = 'cubic-bezier(0.34, 1.32, 0.64, 1)';
const TOOLTIP_SHOW_DELAY_MS = 400;
const EMPTY_SHORTCUT = [];
const ROW_VISIBILITY_PADDING_PX = 16;

const ensureRowVisible = ( rowId ) => {
	const rowEl = document.querySelector(
		`.tasty-recipes-item-row[data-row-id="${ rowId }"]`
	);
	const scrollBody = document.querySelector( '.tasty-recipes-frame-content' );
	if ( ! rowEl || ! scrollBody ) {
		return;
	}

	const savedTransform = rowEl.style.transform;
	rowEl.style.transform = 'none';
	const rowRect = rowEl.getBoundingClientRect();
	rowEl.style.transform = savedTransform;

	const bodyRect = scrollBody.getBoundingClientRect();
	const topLimit = bodyRect.top + ROW_VISIBILITY_PADDING_PX;
	const bottomLimit = bodyRect.bottom - ROW_VISIBILITY_PADDING_PX;

	if ( rowRect.top < topLimit ) {
		scrollBody.scrollTop -= topLimit - rowRect.top;
		return;
	}

	if ( rowRect.bottom > bottomLimit ) {
		scrollBody.scrollTop += rowRect.bottom - bottomLimit;
	}
};

const getOptionKeySymbol = () => {
	const platform = navigator.platform || '';
	const userAgent = navigator.userAgent || '';
	const isApple =
		/Mac|iPhone|iPod|iPad/.test( platform ) ||
		/Mac OS X/.test( userAgent );

	return isApple ? '⌥' : 'Alt';
};

let rowIdCounter = 0;

const createRowId = () => {
	rowIdCounter += 1;
	return `tasty-recipe-row-${ rowIdCounter }`;
};

const escapeHtml = ( value ) =>
	String( value )
		.replace( /&/g, '&amp;' )
		.replace( /</g, '&lt;' )
		.replace( />/g, '&gt;' )
		.replace( /"/g, '&quot;' );

const serializeRows = ( rows, mode = 'ingredients' ) => {
	const listTag = 'instructions' === mode ? 'ol' : 'ul';
	const parts = [];
	let pendingItems = [];

	const flushItems = () => {
		if ( ! pendingItems.length ) {
			return;
		}

		parts.push(
			`<${ listTag }>${ pendingItems
				.map( ( text ) => `<li>${ text }</li>` )
				.join( '' ) }</${ listTag }>`
		);
		pendingItems = [];
	};

	rows.forEach( ( row ) => {
		const text = escapeHtml( ( row.value || '' ).trim() );
		if ( ! text ) {
			return;
		}

		if ( 'group' === row.type ) {
			flushItems();
			parts.push( `<h4>${ text }</h4>` );
			return;
		}

		pendingItems.push( text );
	} );

	flushItems();
	return parts.join( '' );
};

const normalizeRowText = ( value ) =>
	String( value || '' )
		.replace( /\u00a0/g, ' ' )
		.replace( /\s+/g, ' ' )
		.trim();

const parseRowsFromHtml = ( html ) => {
	const empty = () => [ createItemRow() ];

	if ( ! html || ! String( html ).trim() ) {
		return empty();
	}

	try {
		// Use a detached element — more reliable for HTML fragments than
		// DOMParser with a wrapper (lists/paragraphs from wpautop / TinyMCE).
		const root = document.createElement( 'div' );
		root.innerHTML = String( html );

		const rows = [];
		const pushItem = ( value ) => {
			const text = normalizeRowText( value );
			if ( text ) {
				rows.push( createItemRow( { value: text } ) );
			}
		};
		const pushGroup = ( value ) => {
			const text = normalizeRowText( value );
			if ( text ) {
				rows.push( createGroupRow( { value: text } ) );
			}
		};

		const walk = ( node ) => {
			if ( ! node ) {
				return;
			}

			if ( Node.TEXT_NODE === node.nodeType ) {
				const text = normalizeRowText( node.textContent );
				if ( text ) {
					pushItem( text );
				}
				return;
			}

			if ( Node.ELEMENT_NODE !== node.nodeType ) {
				return;
			}

			const tag = node.tagName.toLowerCase();

			if ( /^h[1-6]$/.test( tag ) ) {
				pushGroup( node.textContent );
				return;
			}

			if ( 'li' === tag ) {
				pushItem( node.textContent );
				return;
			}

			if ( 'p' === tag || 'div' === tag ) {
				// Standalone blocks (legacy TinyMCE). Skip empty wrappers.
				if (
					! node.querySelector( 'ul,ol,li,h1,h2,h3,h4,h5,h6,p' )
				) {
					pushItem( node.textContent );
					return;
				}
			}

			if ( 'br' === tag ) {
				return;
			}

			if ( 'ul' === tag || 'ol' === tag ) {
				Array.from( node.children ).forEach( ( child ) => {
					if ( 'LI' === child.tagName ) {
						pushItem( child.textContent );
					} else {
						walk( child );
					}
				} );
				return;
			}

			Array.from( node.childNodes ).forEach( walk );
		};

		Array.from( root.childNodes ).forEach( walk );

		if ( ! rows.length ) {
			const fallback = String( root.innerHTML || '' )
				.replace( /<br\s*\/?>/gi, '\n' )
				.replace( /<\/p>/gi, '\n' )
				.replace( /<[^>]+>/g, '' );
			fallback
				.split( /\r?\n/ )
				.map( normalizeRowText )
				.filter( Boolean )
				.forEach( ( line ) => pushItem( line ) );
		}

		return rows.length ? rows : empty();
	} catch ( error ) {
		return empty();
	}
};

const createItemRow = ( { isNew = false, value = '' } = {} ) => ( {
	id: createRowId(),
	type: 'item',
	value,
	isNew,
} );

const createGroupRow = ( { isNew = false, value = '' } = {} ) => ( {
	id: createRowId(),
	type: 'group',
	value,
	isNew,
} );

const stripItemPrefix = ( value ) =>
	String( value || '' )
		.trim()
		.replace( /^\d{1,3}\s*[.):\-–—]\s+/, '' )
		.replace( /^[\-\*\+•›]\s+/, '' )
		.replace( /^step\s*\d{1,3}\s*[.:\-–—]?\s*/i, '' )
		.trim();

const parsePasteLines = ( text ) => {
	if ( ! text ) {
		return [];
	}

	return text
		.split( /\r?\n/ )
		.map( stripItemPrefix )
		.filter( Boolean );
};

const parsePasteHtml = ( html ) => {
	if ( ! html ) {
		return null;
	}

	try {
		const doc = new window.DOMParser().parseFromString( html, 'text/html' );
		const selector = 'h1,h2,h3,h4,h5,h6,p,li,div,td,th';
		let blocks = Array.from( doc.body.querySelectorAll( selector ) );

		// Keep leaf-most blocks so a wrapper div does not duplicate nested <p>s.
		blocks = blocks.filter(
			( el ) => ! blocks.some( ( other ) => other !== el && el.contains( other ) )
		);

		if ( ! blocks.length ) {
			return null;
		}

		const isBoldElement = ( el ) => {
			if ( ! el ) {
				return false;
			}

			if ( el.matches( 'b,strong' ) ) {
				return true;
			}

			return /font-weight:\s*(bold|[6-9]00)/i.test(
				el.getAttribute( 'style' ) || ''
			);
		};

		const parts = [];

		blocks.forEach( ( el ) => {
			const text = el.textContent.replace( /\s+/g, ' ' ).trim();
			if ( ! text ) {
				return;
			}

			let isHeading = /^H[1-6]$/.test( el.tagName );
			if ( ! isHeading && text.length <= 60 ) {
				const bold = Array.from(
					el.querySelectorAll( 'b,strong,[style*="font-weight"]' )
				).find( isBoldElement );

				if (
					bold &&
					bold.textContent.replace( /\s+/g, ' ' ).trim() === text
				) {
					isHeading = true;
				}
			}

			parts.push(
				isHeading
					? {
							text: text.replace( /:\s*$/, '' ),
							type: 'heading',
					  }
					: {
							text: stripItemPrefix( text ),
							type: 'item',
					  }
			);
		} );

		const filtered = parts.filter( ( part ) => part.text );
		return filtered.length ? filtered : null;
	} catch ( error ) {
		return null;
	}
};

const parseClipboardParts = ( text, html ) => {
	const textParts = parsePasteLines( text ).map( ( line ) => ( {
		text: line,
		type: 'item',
	} ) );
	const htmlParts = parsePasteHtml( html );

	// Prefer HTML when it found real structure; otherwise use newline split.
	if (
		! htmlParts ||
		( htmlParts.length <= 1 && textParts.length > 1 )
	) {
		return textParts;
	}

	return htmlParts;
};

const moveRow = ( rows, fromIndex, toIndex ) => {
	if (
		fromIndex === toIndex ||
		fromIndex < 0 ||
		toIndex < 0 ||
		fromIndex >= rows.length ||
		toIndex >= rows.length
	) {
		return rows;
	}

	const next = [ ...rows ];
	const [ moved ] = next.splice( fromIndex, 1 );
	next.splice( toIndex, 0, moved );
	return next.map( ( row ) => ( {
		...row,
		isNew: false,
	} ) );
};

const RowIconButton = ( {
	label,
	shortcut = EMPTY_SHORTCUT,
	disabled = false,
	danger = false,
	onClick = () => {},
	children,
} ) => {
	const buttonRef = useRef( null );
	const tipRef = useRef( null );
	const showTimerRef = useRef( null );
	const [ isVisible, setIsVisible ] = useState( false );
	const [ position, setPosition ] = useState( { top: 0, left: 0 } );
	const shortcutKey = shortcut.join( '\0' );

	const clearShowTimer = () => {
		if ( showTimerRef.current ) {
			window.clearTimeout( showTimerRef.current );
			showTimerRef.current = null;
		}
	};

	const hideTooltip = () => {
		clearShowTimer();
		setIsVisible( false );
	};

	const showTooltip = () => {
		if ( disabled ) {
			return;
		}

		clearShowTimer();
		showTimerRef.current = window.setTimeout( () => {
			setIsVisible( true );
		}, TOOLTIP_SHOW_DELAY_MS );
	};

	useLayoutEffect( () => {
		if ( ! isVisible || ! buttonRef.current || ! tipRef.current ) {
			return;
		}

		const buttonRect = buttonRef.current.getBoundingClientRect();
		const tipRect = tipRef.current.getBoundingClientRect();
		const nextLeft = Math.max(
			8,
			Math.min(
				buttonRect.left + buttonRect.width / 2 - tipRect.width / 2,
				window.innerWidth - tipRect.width - 8
			)
		);
		const nextTop = Math.max( 8, buttonRect.top - tipRect.height - 6 );

		setPosition( ( prev ) => {
			if ( prev.top === nextTop && prev.left === nextLeft ) {
				return prev;
			}

			return { top: nextTop, left: nextLeft };
		} );
	}, [ isVisible, label, shortcutKey ] );

	useEffect( () => {
		if ( ! isVisible ) {
			return undefined;
		}

		const onScrollOrResize = () => hideTooltip();
		window.addEventListener( 'scroll', onScrollOrResize, true );
		window.addEventListener( 'resize', onScrollOrResize );

		return () => {
			window.removeEventListener( 'scroll', onScrollOrResize, true );
			window.removeEventListener( 'resize', onScrollOrResize );
		};
	}, [ isVisible ] );

	useEffect( () => {
		return () => clearShowTimer();
	}, [] );

	return (
		<>
			<button
				ref={ buttonRef }
				type="button"
				className={ classNames( 'tasty-recipes-row-icon', {
					'tasty-recipes-row-icon--danger': danger,
				} ) }
				aria-label={ label }
				disabled={ disabled }
				onClick={ ( event ) => {
					hideTooltip();
					onClick( event );
				} }
				onMouseEnter={ showTooltip }
				onMouseLeave={ hideTooltip }
				onFocus={ showTooltip }
				onBlur={ hideTooltip }
				onPointerDown={ hideTooltip }
			>
				{ children }
			</button>
			{ isVisible &&
				createPortal(
					<div
						ref={ tipRef }
						className="tasty-recipes-kbd-tooltip is-visible"
						style={ {
							top: `${ position.top }px`,
							left: `${ position.left }px`,
						} }
						role="tooltip"
					>
						<span className="tasty-recipes-kbd-tooltip__label">
							{ label }
						</span>
						{ !! shortcut.length && (
							<span className="tasty-recipes-kbd-tooltip__keys">
								{ shortcut.map( ( key ) => (
									<kbd
										key={ key }
										className="tasty-recipes-kbd-tooltip__key"
									>
										{ key }
									</kbd>
								) ) }
							</span>
						) }
					</div>,
					document.body
				) }
		</>
	);
};

const RowActions = ( {
	type,
	canMoveUp = true,
	canMoveDown = true,
	canDelete = true,
	onMoveUp = () => {},
	onMoveDown = () => {},
	onConvert = () => {},
	onDelete = () => {},
	mode = 'ingredients',
} ) => {
	const isGroup = 'group' === type;
	const optionKey = getOptionKeySymbol();
	const itemNoun =
		'instructions' === mode
			? __( 'Convert to step', 'tasty-recipes-lite' )
			: __( 'Convert to ingredient', 'tasty-recipes-lite' );
	const convertLabel = isGroup
		? itemNoun
		: __( 'Convert to section title', 'tasty-recipes-lite' );

	return (
		<div className="tasty-recipes-row-actions">
			<RowIconButton
				label={ __( 'Move up', 'tasty-recipes-lite' ) }
				shortcut={ [ optionKey, '↑' ] }
				disabled={ ! canMoveUp }
				onClick={ onMoveUp }
			>
				{ MoveUpIcon }
			</RowIconButton>
			<RowIconButton
				label={ __( 'Move down', 'tasty-recipes-lite' ) }
				shortcut={ [ optionKey, '↓' ] }
				disabled={ ! canMoveDown }
				onClick={ onMoveDown }
			>
				{ MoveDownIcon }
			</RowIconButton>
			<RowIconButton label={ convertLabel } onClick={ onConvert }>
				{ isGroup ? ParagraphIcon : SectionTitleIcon }
			</RowIconButton>
			<RowIconButton
				label={ __( 'Delete', 'tasty-recipes-lite' ) }
				danger
				disabled={ ! canDelete }
				onClick={ onDelete }
			>
				{ RowTrashIcon }
			</RowIconButton>
		</div>
	);
};

const RecipeRow = ( {
	row,
	placeholder,
	groupPlaceholder,
	canMoveUp,
	canMoveDown,
	canDelete,
	isDragging,
	onValueChange,
	onMoveUp,
	onMoveDown,
	onConvert,
	onDelete,
	onPaste = () => {},
	onGripPointerDown,
	mode = 'ingredients',
} ) => {
	const inputRef = useRef( null );
	const isGroup = 'group' === row.type;

	

	const handleRowKeyDown = ( event ) => {
		if ( ! event.altKey ) {
			return;
		}

		if ( 'ArrowUp' === event.key ) {
			event.preventDefault();
			if ( canMoveUp ) {
				onMoveUp();
			}
			return;
		}

		if ( 'ArrowDown' === event.key ) {
			event.preventDefault();
			if ( canMoveDown ) {
				onMoveDown();
			}
		}
	};

	// Re-sync after every render. React clears contentEditable DOM text when
	// parent re-renders (no React children), which wiped hydrated values.
	useLayoutEffect( () => {
		const el = inputRef.current;
		if ( ! el || isGroup ) {
			return;
		}

		if ( ( el.textContent || '' ) !== ( row.value || '' ) ) {
			el.textContent = row.value || '';
		}
	} );

	useLayoutEffect( () => {
		const el = inputRef.current;
		if ( ! el || ! row.isNew ) {
			return;
		}

		el.focus();
		if ( isGroup && 'function' === typeof el.setSelectionRange ) {
			const end = el.value.length;
			el.setSelectionRange( end, end );
			return;
		}

		if ( ! isGroup ) {
			const selection = window.getSelection();
			const range = document.createRange();
			range.selectNodeContents( el );
			range.collapse( false );
			selection.removeAllRanges();
			selection.addRange( range );
		}
	}, [ row.isNew, row.id, isGroup ] );

	return (
		<div
			className={ classNames( 'tasty-recipes-item-row', {
				'tasty-recipes-item-row--group': isGroup,
				'tasty-recipes-item-row--new': row.isNew,
				'is-dragging': isDragging,
			} ) }
			data-row-id={ row.id }
		>
			<button
				type="button"
				className="tasty-recipes-drag-grip"
				tabIndex={ -1 }
				aria-label={ __( 'Drag to reorder', 'tasty-recipes-lite' ) }
				onPointerDown={ ( event ) => onGripPointerDown( event, row.id ) }
			>
				{ DragGripIcon }
			</button>

			{ isGroup ? (
				<input
					key={ `${ row.id }-group` }
					ref={ inputRef }
					type="text"
					className="tasty-recipes-row-input"
					placeholder={ groupPlaceholder }
					value={ row.value }
					onChange={ ( event ) =>
						onValueChange( row.id, event.target.value )
					}
					onKeyDown={ handleRowKeyDown }
				/>
			) : (
				<div
					key={ `${ row.id }-item` }
					ref={ inputRef }
					className={ classNames( 'tasty-recipes-row-input', {
						'is-empty': ! row.value,
					} ) }
					contentEditable
					suppressContentEditableWarning
					role="textbox"
					aria-multiline="false"
					data-placeholder={ placeholder }
					onKeyDown={ handleRowKeyDown }
					onPaste={ ( event ) => onPaste( event, row.id ) }
					onInput={ ( event ) => {
						const el = event.currentTarget;
						if (
							'<br>' === el.innerHTML ||
							'<br/>' === el.innerHTML
						) {
							el.innerHTML = '';
						}
						onValueChange( row.id, el.textContent || '' );
					} }
				/>
			) }

			<RowActions
				type={ row.type }
				mode={ mode }
				canMoveUp={ canMoveUp }
				canMoveDown={ canMoveDown }
				canDelete={ canDelete }
				onMoveUp={ onMoveUp }
				onMoveDown={ onMoveDown }
				onConvert={ onConvert }
				onDelete={ onDelete }
			/>
		</div>
	);
};

export const RecipeRowList = ( {
	name,
	label,
	placeholder = '',
	mode = 'ingredients',
	initialValue = '',
	onChange = () => {},
} ) => {
	const [ rows, setRows ] = useState( () =>
		parseRowsFromHtml( initialValue )
	);
	const [ draggingId, setDraggingId ] = useState( null );
	const listRef = useRef( null );
	const rowsRef = useRef( rows );
	const dragRef = useRef( null );
	const pendingFlipRef = useRef( null );
	const pendingScrollRowIdRef = useRef( null );
	const groupPlaceholder = __( 'For the …', 'tasty-recipes-lite' );
	const isInstructions = 'instructions' === mode;
	const addItemLabel = isInstructions
		? __( 'Add Step', 'tasty-recipes-lite' )
		: __( 'Add Ingredient', 'tasty-recipes-lite' );

	rowsRef.current = rows;

	

	useLayoutEffect( () => {
		const firstTops = pendingFlipRef.current;
		const list = listRef.current;
		const scrollRowId = pendingScrollRowIdRef.current;
		pendingScrollRowIdRef.current = null;

		if ( scrollRowId ) {
			ensureRowVisible( scrollRowId );
			window.requestAnimationFrame( () => {
				ensureRowVisible( scrollRowId );
			} );
		}

		if ( ! firstTops || ! list ) {
			return;
		}

		pendingFlipRef.current = null;

		const items = Array.from(
			list.querySelectorAll( '.tasty-recipes-item-row' )
		);

		items.forEach( ( el ) => {
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
				ensureRowVisible( scrollRowId );
			}, REORDER_DURATION_MS + 20 );
		}
	}, [ rows ] );

	const commitRows = ( nextRows ) => {
		setRows( nextRows );
		onChange(
			serializeRows(
				nextRows.map( ( row ) => ( {
					...row,
					isNew: false,
				} ) ),
				mode
			)
		);
	};

	const handleValueChange = ( id, value ) => {
		commitRows(
			rows.map( ( row ) =>
				row.id === id
					? {
							...row,
							value,
							isNew: false,
					  }
					: { ...row, isNew: false }
			)
		);
	};

	const handleAddItem = () => {
		commitRows( [
			...rows.map( ( row ) => ( { ...row, isNew: false } ) ),
			createItemRow( { isNew: true } ),
		] );
	};

	const handleAddSectionTitle = () => {
		commitRows( [
			...rows.map( ( row ) => ( { ...row, isNew: false } ) ),
			createGroupRow( { isNew: true } ),
		] );
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

		const fromIndex = rows.findIndex( ( row ) => row.id === rowId );
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
					handleValueChange( rowId, el.textContent || '' );
				}
			}
			return;
		}

		event.preventDefault();

		const parts = [ ...parsed ];
		if ( existing ) {
			parts.unshift( { text: existing, type: 'item' } );
		}

		const created = parts.map( ( part, index ) => {
			const isLast = index === parts.length - 1;
			if ( 'heading' === part.type ) {
				return createGroupRow( {
					value: part.text,
					isNew: isLast,
				} );
			}

			return createItemRow( {
				value: part.text,
				isNew: isLast,
			} );
		} );

		// Keep the original row id on the first replacement so React remounts less.
		created[ 0 ] = {
			...created[ 0 ],
			id: current.id,
			isNew: 1 === created.length,
		};

		const nextRows = [
			...rows.slice( 0, fromIndex ).map( ( row ) => ( {
				...row,
				isNew: false,
			} ) ),
			...created,
			...rows.slice( fromIndex + 1 ).map( ( row ) => ( {
				...row,
				isNew: false,
			} ) ),
		];

		pendingScrollRowIdRef.current = created[ created.length - 1 ].id;
		commitRows( nextRows );
	};

	

	const handleMoveRow = ( rowId, direction ) => {
		const fromIndex = rows.findIndex( ( row ) => row.id === rowId );
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
				).map( ( el ) => [
					el.dataset.rowId,
					el.getBoundingClientRect().top,
				] )
			);
		}

		pendingScrollRowIdRef.current = rowId;
		commitRows( nextRows );
	};

	

	const handleConvertRow = ( rowId ) => {
		commitRows(
			rows.map( ( row ) => {
				if ( row.id !== rowId ) {
					return { ...row, isNew: false };
				}

				return {
					...row,
					type: 'group' === row.type ? 'item' : 'group',
					isNew: true,
				};
			} )
		);
	};

	

	const handleDeleteRow = ( rowId ) => {
		let nextRows = rows
			.filter( ( row ) => row.id !== rowId )
			.map( ( row ) => ( {
				...row,
				isNew: false,
			} ) );

		const hasItemRow = nextRows.some( ( row ) => 'item' === row.type );
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
			( row ) => row.id === rowId
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

		const getRowElement = ( id ) =>
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
			).filter( ( el ) => el.dataset.rowId !== drag.rowId );
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

			const scrollBody = document.querySelector(
				'.tasty-recipes-frame-content'
			);
			if ( scrollBody ) {
				const bounds = scrollBody.getBoundingClientRect();
				if ( drag.lastY < bounds.top + 48 ) {
					scrollBody.scrollTop -= 10;
				} else if ( drag.lastY > bounds.bottom - 48 ) {
					scrollBody.scrollTop += 10;
				}
			}
		};

		const onPointerMove = ( moveEvent ) => {
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
			setDraggingId( null );
			document.body.classList.remove( 'tasty-recipes-is-dragging' );
			window.removeEventListener( 'pointermove', onPointerMove );
			window.removeEventListener( 'pointerup', onPointerUp );
			window.removeEventListener( 'pointercancel', onPointerUp );
			window.removeEventListener( 'keydown', onKeyDown );
		};

		const onPointerUp = () => endDrag();
		const onKeyDown = ( keyEvent ) => {
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

	const itemCount = rows.filter( ( entry ) => 'item' === entry.type ).length;

	return (
		<fieldset
			className={ classNames( 'tasty-recipes-row-list-field', {
				'tasty-recipes-row-list-field--ingredients': ! isInstructions,
				'tasty-recipes-row-list-field--instructions': isInstructions,
			} ) }
		>
			<label>{ label }</label>
			<input
				type="hidden"
				name={ name }
				value={ serializeRows( rows, mode ) }
			/>
			<div
				className="tasty-recipes-row-list"
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
						canDelete={ 'group' === row.type || itemCount > 1 }
						isDragging={ draggingId === row.id }
						onValueChange={ handleValueChange }
						onMoveUp={ () => handleMoveRow( row.id, -1 ) }
						onMoveDown={ () => handleMoveRow( row.id, 1 ) }
						onConvert={ () => handleConvertRow( row.id ) }
						onDelete={ () => handleDeleteRow( row.id ) }
						onPaste={ handlePaste }
						onGripPointerDown={ handleGripPointerDown }
					/>
				) ) }
			</div>
			<div className="tasty-recipes-rich-textarea-actions">
				<button
					type="button"
					className="tasty-recipes-rich-textarea-action tasty-recipes-rich-textarea-action--add"
					onClick={ handleAddItem }
				>
					<span
						className="tasty-recipes-rich-textarea-action-icon"
						aria-hidden="true"
					></span>
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
					></span>
					{ __( 'Add Section Title', 'tasty-recipes-lite' ) }
				</button>
			</div>
		</fieldset>
	);
};

export const IngredientsList = ( props ) => (
	<RecipeRowList mode="ingredients" { ...props } />
);

export const InstructionsList = ( props ) => (
	<RecipeRowList mode="instructions" { ...props } />
);
