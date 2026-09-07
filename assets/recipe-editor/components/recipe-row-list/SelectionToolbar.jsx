import {
	createPortal,
	useEffect,
	useLayoutEffect,
	useRef,
	useState,
} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import classNames from 'classnames';
import { getRecipeEditorScrollContainer } from '../dom';
import { KbdTooltip } from '../kbd-tooltip';
import { getModifierKeySymbol } from '../platform';
import { isSafeUrl } from './utils';

// Not translatable: a translated URL would render an invalid example.
const LINK_PLACEHOLDER_URL = 'https://example.com';

const LINK_ICON = (
	<svg viewBox="0 0 16 16" fill="none" aria-hidden="true">
		<path
			d="M7 9a3 3 0 004.24 0l2-2a3 3 0 00-4.24-4.24l-1 1M9 7a3 3 0 00-4.24 0l-2 2a3 3 0 004.24 4.24l1-1"
			stroke="currentColor"
			strokeWidth="1.4"
			strokeLinecap="round"
		/>
	</svg>
);

const getSelection = node => {
	const view = node?.ownerDocument?.defaultView || document.defaultView;
	return view?.getSelection() || null;
};

const getRange = selection => {
	if ( ! selection || ! selection.rangeCount ) {
		return null;
	}

	return selection.getRangeAt( 0 );
};

const DEFAULT_SURFACE_SELECTOR =
	'.tasty-recipes-row-input[contenteditable="true"]';

const isInsideSurface = ( node, container, surfaceSelector ) => {
	if ( ! node || ! container ) {
		return false;
	}

	const el =
		Node.ELEMENT_NODE === node.nodeType ? node : node.parentElement;
	if ( ! el ) {
		return false;
	}

	const editable = el.closest( surfaceSelector );
	return !! editable && container.contains( editable );
};

const getEditableFromRange = ( range, surfaceSelector ) => {
	if ( ! range ) {
		return null;
	}

	const node = range.commonAncestorContainer;
	const el =
		Node.ELEMENT_NODE === node.nodeType ? node : node.parentElement;
	return el?.closest( surfaceSelector ) || null;
};

const getTinyMceEditor = editable => {
	if ( ! editable?.id || ! window.tinymce?.get ) {
		return null;
	}

	return window.tinymce.get( editable.id ) || null;
};

const getAnchorFromSelection = container => {
	const selection = getSelection( container );
	if ( ! selection || ! selection.rangeCount ) {
		return null;
	}

	const node = selection.anchorNode;
	const el =
		node && Node.ELEMENT_NODE === node.nodeType
			? node
			: node?.parentElement;
	return el?.closest( 'a' ) || null;
};

const isAbsoluteHttpUrl = value => /^https?:\/\//i.test( value );

const normalizeUrl = raw => {
	const value = String( raw || '' ).trim();
	if ( ! value || ! isSafeUrl( value ) ) {
		return '';
	}

	// Keep explicit schemes (mailto:, tel:, https:), anchors, queries and
	// site-relative paths exactly as typed.
	if (
		/^[a-z][a-z0-9+.-]*:/i.test( value ) ||
		value.startsWith( '//' ) ||
		value.startsWith( '/' ) ||
		value.startsWith( '#' ) ||
		value.startsWith( '?' )
	) {
		return value;
	}

	// A bare domain gets a scheme; anything else stays a relative path.
	return /^[^\s/]+\.[^\s/]/.test( value ) ? `https://${ value }` : value;
};

const notifyEditable = ( range, surfaceSelector ) => {
	const editable = getEditableFromRange( range, surfaceSelector );
	editable?.dispatchEvent( new Event( 'input', { bubbles: true } ) );

	const editor = getTinyMceEditor( editable );
	if ( editor ) {
		editor.fire( 'change' );
		editor.nodeChanged();
	}
};

export const SelectionToolbar = ( {
	containerRef,
	surfaceSelector = DEFAULT_SURFACE_SELECTOR,
	onHasSelectionChange = null,
} ) => {
	const toolbarRef = useRef( null );
	const linkPopoverRef = useRef( null );
	const linkInputRef = useRef( null );
	const savedRangeRef = useRef( null );
	const linkOpenRef = useRef( false );
	const onHasSelectionChangeRef = useRef( onHasSelectionChange );
	const [ isVisible, setIsVisible ] = useState( false );
	const [ position, setPosition ] = useState( { top: 0, left: 0 } );
	const [ activeCommands, setActiveCommands ] = useState( {} );
	const [ linkOpen, setLinkOpen ] = useState( false );
	const [ linkUrl, setLinkUrl ] = useState( '' );
	const [ linkHasAnchor, setLinkHasAnchor ] = useState( false );
	const [ linkPosition, setLinkPosition ] = useState( {
		top: 0,
		left: 0,
	} );

	linkOpenRef.current = linkOpen;
	onHasSelectionChangeRef.current = onHasSelectionChange;

	const reportHasSelection = hasSelection => {
		onHasSelectionChangeRef.current?.( hasSelection );
	};

	const updateActiveState = () => {
		const container = containerRef?.current;
		const range = getRange( getSelection( container ) );
		const editable = getEditableFromRange( range, surfaceSelector );
		const editor = getTinyMceEditor( editable );

		if ( editor ) {
			setActiveCommands( {
				bold: !! editor.queryCommandState( 'Bold' ),
				italic: !! editor.queryCommandState( 'Italic' ),
				underline: !! editor.queryCommandState( 'Underline' ),
				link: !! editor.dom.getParent( editor.selection.getNode(), 'a' ),
			} );
			return;
		}

		setActiveCommands( {
			bold: document.queryCommandState( 'bold' ),
			italic: document.queryCommandState( 'italic' ),
			underline: document.queryCommandState( 'underline' ),
			link: !! getAnchorFromSelection( container ),
		} );
	};

	const hide = () => {
		setIsVisible( false );
		setLinkOpen( false );
		reportHasSelection( false );
	};

	const restoreSelection = () => {
		const selection = getSelection( containerRef?.current );
		const range = savedRangeRef.current;
		if ( ! selection || ! range ) {
			return false;
		}

		selection.removeAllRanges();
		selection.addRange( range );
		return true;
	};

	const syncFromSelection = () => {
		if ( linkOpenRef.current ) {
			return;
		}

		const container = containerRef?.current;
		const selection = getSelection( container );
		const range = getRange( selection );
		if ( ! container || ! selection || selection.isCollapsed || ! range ) {
			hide();
			return;
		}

		if (
			! isInsideSurface(
				range.commonAncestorContainer,
				container,
				surfaceSelector
			)
		) {
			hide();
			return;
		}

		const rect = range.getBoundingClientRect();
		if ( ! rect.width && ! rect.height ) {
			hide();
			return;
		}

		reportHasSelection( true );

		const scrollBody = getRecipeEditorScrollContainer();
		if ( scrollBody ) {
			const bounds = scrollBody.getBoundingClientRect();
			if ( rect.bottom < bounds.top || rect.top > bounds.bottom ) {
				setIsVisible( false );
				return;
			}
		}

		savedRangeRef.current = range.cloneRange();
		setIsVisible( true );
		updateActiveState();
		setPosition( {
			top: Math.max( 8, rect.top - 40 ),
			left: rect.left + ( rect.width / 2 ),
		} );
	};

	const openLinkPopover = () => {
		const container = containerRef?.current;
		const selection = getSelection( container );
		const range = getRange( selection );
		if ( ! selection || selection.isCollapsed || ! container || ! range ) {
			return;
		}

		if (
			! isInsideSurface(
				range.commonAncestorContainer,
				container,
				surfaceSelector
			)
		) {
			return;
		}

		savedRangeRef.current = range.cloneRange();
		const editable = getEditableFromRange( range, surfaceSelector );
		const editor = getTinyMceEditor( editable );
		const anchor = editor
			? editor.dom.getParent( editor.selection.getNode(), 'a' )
			: getAnchorFromSelection( container );
		const rect = range.getBoundingClientRect();
		setLinkHasAnchor( !! anchor );
		// Shown verbatim so editing an existing link cannot rewrite its scheme.
		setLinkUrl( anchor ? anchor.getAttribute( 'href' ) || '' : '' );
		setLinkPosition( {
			top: rect.bottom + 12,
			left: rect.left + ( rect.width / 2 ),
		} );
		setLinkOpen( true );
		setIsVisible( true );
	};

	useEffect( () => {
		const onSelectionChange = () => syncFromSelection();
		document.addEventListener( 'selectionchange', onSelectionChange );

		const scrollBody = getRecipeEditorScrollContainer();
		scrollBody?.addEventListener( 'scroll', onSelectionChange, {
			passive: true,
		} );
		window.addEventListener( 'scroll', onSelectionChange, true );
		window.addEventListener( 'resize', onSelectionChange );

		return () => {
			document.removeEventListener(
				'selectionchange',
				onSelectionChange
			);
			scrollBody?.removeEventListener( 'scroll', onSelectionChange );
			window.removeEventListener( 'scroll', onSelectionChange, true );
			window.removeEventListener( 'resize', onSelectionChange );
		};
		// Intentional: listeners close over latest sync via render; ref guards link popover.
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ containerRef, surfaceSelector ] );

	useEffect( () => {
		const onKeyDown = event => {
			if ( ! ( event.metaKey || event.ctrlKey ) ) {
				return;
			}

			if ( 'k' !== event.key.toLowerCase() ) {
				return;
			}

			const container = containerRef?.current;
			const selection = getSelection( container );
			const range = getRange( selection );
			if (
				! selection ||
				selection.isCollapsed ||
				! container ||
				! range
			) {
				return;
			}

			if (
				! isInsideSurface(
					range.commonAncestorContainer,
					container,
					surfaceSelector
				)
			) {
				return;
			}

			event.preventDefault();
			openLinkPopover();
		};

		document.addEventListener( 'keydown', onKeyDown );
		return () => document.removeEventListener( 'keydown', onKeyDown );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ containerRef, surfaceSelector ] );

	useLayoutEffect( () => {
		if ( ! isVisible || ! toolbarRef.current ) {
			return;
		}

		const tipRect = toolbarRef.current.getBoundingClientRect();
		const half = tipRect.width / 2;
		const minLeft = 8 + half;
		const maxLeft = window.innerWidth - 8 - half;
		const clamped = Math.max(
			minLeft,
			Math.min( position.left, maxLeft )
		);
		if ( Math.abs( clamped - position.left ) > 0.5 ) {
			setPosition( prev => ( { ...prev, left: clamped } ) );
		}

		if ( linkOpen && savedRangeRef.current ) {
			const selRect = savedRangeRef.current.getBoundingClientRect();
			setLinkPosition( {
				top: selRect.bottom + 12,
				left: clamped,
			} );
		}
	}, [ isVisible, position.left, position.top, linkOpen ] );

	useEffect( () => {
		if ( linkOpen && linkInputRef.current ) {
			linkInputRef.current.focus();
			linkInputRef.current.select();
		}
	}, [ linkOpen ] );

	useEffect( () => {
		if ( ! linkOpen ) {
			return;
		}

		const onPointerDown = event => {
			const target = event.target;
			if (
				toolbarRef.current?.contains( target ) ||
				linkPopoverRef.current?.contains( target )
			) {
				return;
			}

			setLinkOpen( false );
			window.setTimeout( () => syncFromSelection(), 0 );
		};

		document.addEventListener( 'mousedown', onPointerDown );
		return () =>
			document.removeEventListener( 'mousedown', onPointerDown );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ linkOpen ] );

	const runCommand = command => {
		if ( ! restoreSelection() ) {
			return;
		}

		const editable = getEditableFromRange(
			savedRangeRef.current,
			surfaceSelector
		);
		const editor = getTinyMceEditor( editable );
		if ( editor ) {
			editor.execCommand( command );
		} else {
			document.execCommand( command, false, null );
		}

		const range = getRange( getSelection( containerRef?.current ) );
		if ( range ) {
			savedRangeRef.current = range.cloneRange();
		}
		notifyEditable( savedRangeRef.current, surfaceSelector );
		updateActiveState();
	};

	const applyLink = () => {
		const url = normalizeUrl( linkUrl );
		if ( ! url || ! restoreSelection() ) {
			setLinkOpen( false );
			return;
		}

		const editable = getEditableFromRange(
			savedRangeRef.current,
			surfaceSelector
		);
		const editor = getTinyMceEditor( editable );
		if ( editor ) {
			const linkAttrs = { href: url };
			if ( isAbsoluteHttpUrl( url ) ) {
				linkAttrs.target = '_blank';
				linkAttrs.rel = 'noopener noreferrer';
			}
			editor.execCommand( 'mceInsertLink', false, linkAttrs );
		} else {
			document.execCommand( 'createLink', false, url );
			const anchor = getAnchorFromSelection( containerRef?.current );
			if ( anchor && isAbsoluteHttpUrl( url ) ) {
				anchor.setAttribute( 'target', '_blank' );
				anchor.setAttribute( 'rel', 'noopener noreferrer' );
			}
		}

		notifyEditable( savedRangeRef.current, surfaceSelector );
		setLinkOpen( false );
		syncFromSelection();
	};

	const removeLink = () => {
		if ( ! restoreSelection() ) {
			setLinkOpen( false );
			return;
		}

		const editable = getEditableFromRange(
			savedRangeRef.current,
			surfaceSelector
		);
		const editor = getTinyMceEditor( editable );
		if ( editor ) {
			editor.execCommand( 'unlink' );
		} else {
			document.execCommand( 'unlink', false, null );
		}

		notifyEditable( savedRangeRef.current, surfaceSelector );
		setLinkOpen( false );
		syncFromSelection();
	};

	const toolbarStyle = {
		top: `${ position.top }px`,
		left: `${ position.left }px`,
	};

	const linkStyle = {
		top: `${ linkPosition.top }px`,
		left: `${ linkPosition.left }px`,
	};

	const mod = getModifierKeySymbol();
	const formatActions = [
		{
			id: 'bold',
			label: __( 'Bold', 'tasty-recipes-lite' ),
			shortcut: [ mod, 'B' ],
			active: activeCommands.bold,
			onClick: () => runCommand( 'bold' ),
			content: <b>B</b>,
		},
		{
			id: 'italic',
			label: __( 'Italic', 'tasty-recipes-lite' ),
			shortcut: [ mod, 'I' ],
			active: activeCommands.italic,
			onClick: () => runCommand( 'italic' ),
			content: <i>I</i>,
		},
		{
			id: 'underline',
			label: __( 'Underline', 'tasty-recipes-lite' ),
			shortcut: [ mod, 'U' ],
			active: activeCommands.underline,
			onClick: () => runCommand( 'underline' ),
			content: (
				<span className="tasty-recipes-selection-toolbar__underline">
					U
				</span>
			),
		},
	];

	return createPortal(
		<>
			{ isVisible && (
				<div
					ref={ toolbarRef }
					className="tasty-recipes-selection-toolbar"
					style={ toolbarStyle }
					role="toolbar"
					aria-label={ __(
						'Text formatting',
						'tasty-recipes-lite'
					) }
					onMouseDown={ event => event.preventDefault() }
				>
					{ formatActions.map( action => (
						<KbdTooltip
							key={ action.id }
							label={ action.label }
							shortcut={ action.shortcut }
						>
							<button
								type="button"
								className={ classNames( {
									'is-active': action.active,
								} ) }
								aria-label={ action.label }
								onClick={ action.onClick }
							>
								{ action.content }
							</button>
						</KbdTooltip>
					) ) }
					<span
						className="tasty-recipes-toolbar-separator"
						aria-hidden="true"
					/>
					<KbdTooltip
						label={ __( 'Add link', 'tasty-recipes-lite' ) }
						shortcut={ [ mod, 'K' ] }
					>
						<button
							type="button"
							className={ classNames( {
								'is-active': activeCommands.link || linkOpen,
							} ) }
							aria-label={ __( 'Add link', 'tasty-recipes-lite' ) }
							aria-expanded={ linkOpen }
							onClick={ openLinkPopover }
						>
							{ LINK_ICON }
						</button>
					</KbdTooltip>
				</div>
			) }
			{ linkOpen && (
				// eslint-disable-next-line jsx-a11y/no-noninteractive-element-interactions
				<div
					ref={ linkPopoverRef }
					className="tasty-recipes-selection-link-popover"
					style={ linkStyle }
					role="dialog"
					aria-label={ __( 'Insert link', 'tasty-recipes-lite' ) }
					onMouseDown={ event => {
						if ( 'INPUT' !== event.target.tagName ) {
							event.preventDefault();
						}
					} }
				>
					<input
						ref={ linkInputRef }
						type="text"
						inputMode="url"
						autoComplete="off"
						value={ linkUrl }
						placeholder={ LINK_PLACEHOLDER_URL }
						onChange={ event =>
							setLinkUrl( event.target.value )
						}
						onKeyDown={ event => {
							if ( 'Enter' === event.key ) {
								event.preventDefault();
								applyLink();
							}
							if ( 'Escape' === event.key ) {
								event.preventDefault();
								setLinkOpen( false );
							}
						} }
					/>
					<button type="button" onClick={ applyLink }>
						{ linkHasAnchor
							? __( 'Update', 'tasty-recipes-lite' )
							: __( 'Add', 'tasty-recipes-lite' ) }
					</button>
					{ linkHasAnchor && (
						<button
							type="button"
							className="tasty-recipes-selection-link-popover__remove"
							onClick={ removeLink }
						>
							{ __( 'Remove', 'tasty-recipes-lite' ) }
						</button>
					) }
				</div>
			) }
		</>,
		document.body
	);
};
