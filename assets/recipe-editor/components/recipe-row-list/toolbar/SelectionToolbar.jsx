import {
	createPortal,
	useEffect,
	useLayoutEffect,
	useRef,
	useState,
} from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import classNames from 'classnames';
import { getRecipeEditorScrollContainer } from '../../dom';
import { KbdTooltip } from '../../kbd-tooltip';
import { getModifierKeySymbol } from '../../platform';
import {
	applyAnchorAttributes,
	getClosestAnchor,
	getLinkAttributes,
	hasRelToken,
	isRangeInsideNode,
	isSafeUrl,
} from '../lib/utils';
import { LinkPopoverFields } from './LinkPopoverFields';
import { filterEditorActiveCommands } from './toolbar-state';

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

const isSelectionToolbarVisible = ( range, container ) => {
	/**
	 * Filters whether the floating selection toolbar should show.
	 *
	 * @since x.x
	 *
	 * @param {boolean} visible           Default true.
	 * @param {Object}  context           Selection context.
	 * @param {Range}   context.range     Active selection range.
	 * @param {Element} context.container Toolbar container element.
	 */
	return applyFilters( 'tasty_recipes_selection_toolbar_visible', true, {
		range,
		container,
	} );
};

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

const getVisibleRect = ( range, anchor ) => {
	const rangeRect = range?.getBoundingClientRect?.();
	if ( rangeRect && ( rangeRect.width || rangeRect.height ) ) {
		return rangeRect;
	}

	const anchorRect = anchor?.getBoundingClientRect?.();
	if ( anchorRect && ( anchorRect.width || anchorRect.height ) ) {
		return anchorRect;
	}

	return rangeRect || anchorRect || null;
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
	const [ linkSponsored, setLinkSponsored ] = useState( false );
	const [ linkNofollow, setLinkNofollow ] = useState( false );
	const existingRelRef = useRef( '' );
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
			setActiveCommands(
				filterEditorActiveCommands(
					{
						bold: !! editor.queryCommandState( 'Bold' ),
						italic: !! editor.queryCommandState( 'Italic' ),
						underline: !! editor.queryCommandState( 'Underline' ),
						link: !! editor.dom.getParent(
							editor.selection.getNode(),
							'a'
						),
					},
					range,
					container
				)
			);
			return;
		}

		setActiveCommands(
			filterEditorActiveCommands(
				{
					bold: document.queryCommandState( 'bold' ),
					italic: document.queryCommandState( 'italic' ),
					underline: document.queryCommandState( 'underline' ),
					link: !! getAnchorFromSelection( container ),
				},
				range,
				container
			)
		);
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

		if ( ! isSelectionToolbarVisible( range, container ) ) {
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

	const openLinkPopover = ( explicitAnchor = null ) => {
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
		const clickedAnchor =
			explicitAnchor && Node.ELEMENT_NODE === explicitAnchor.nodeType
				? explicitAnchor
				: null;
		const anchor =
			clickedAnchor ||
			( editor
				? editor.dom.getParent( editor.selection.getNode(), 'a' )
				: getAnchorFromSelection( container ) );
		const rect = getVisibleRect( range, anchor );
		if ( ! rect ) {
			return;
		}

		const existingRel = anchor ? anchor.getAttribute( 'rel' ) || '' : '';
		existingRelRef.current = existingRel;
		setLinkHasAnchor( !! anchor );
		// Shown verbatim so editing an existing link cannot rewrite its scheme.
		setLinkUrl( anchor ? anchor.getAttribute( 'href' ) || '' : '' );
		setLinkSponsored( hasRelToken( existingRel, 'sponsored' ) );
		setLinkNofollow( hasRelToken( existingRel, 'nofollow' ) );
		const center = rect.left + ( rect.width / 2 );
		setPosition( {
			top: Math.max( 8, rect.top - 40 ),
			left: center,
		} );
		setLinkPosition( {
			top: rect.bottom + 12,
			left: center,
		} );
		setLinkOpen( true );
		setIsVisible( true );
	};

	const selectAnchor = anchor => {
		const editable = anchor.closest( surfaceSelector );
		const editor = getTinyMceEditor( editable );
		if ( editor ) {
			editor.selection.select( anchor );
			return editor.selection.getRng();
		}

		const range = anchor.ownerDocument.createRange();
		range.selectNodeContents( anchor );
		const selection = getSelection( containerRef?.current );
		if ( ! selection ) {
			return range;
		}

		selection.removeAllRanges();
		selection.addRange( range );
		return range;
	};

	useEffect( () => {
		const onClick = event => {
			const container = containerRef?.current;
			const anchor = getClosestAnchor( event.target );
			if (
				! container ||
				! anchor ||
				! container.contains( anchor ) ||
				! anchor.closest( surfaceSelector )
			) {
				return;
			}

			event.preventDefault();

			if ( event.shiftKey ) {
				return;
			}

			const selection = getSelection( container );
			const range = getRange( selection );
			if (
				selection &&
				! selection.isCollapsed &&
				range &&
				! isRangeInsideNode( range, anchor )
			) {
				return;
			}

			event.stopPropagation();
			selectAnchor( anchor );
			openLinkPopover( anchor );
		};

		document.addEventListener( 'click', onClick, true );
		return () => document.removeEventListener( 'click', onClick, true );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ containerRef, surfaceSelector ] );

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

			if ( ! isSelectionToolbarVisible( range, container ) ) {
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
		const clampCenter = ( left, width ) => {
			const half = width / 2;
			return Math.max(
				8 + half,
				Math.min( left, window.innerWidth - 8 - half )
			);
		};

		if ( isVisible && toolbarRef.current ) {
			const tipRect = toolbarRef.current.getBoundingClientRect();
			const clamped = clampCenter( position.left, tipRect.width );
			if ( Math.abs( clamped - position.left ) > 0.5 ) {
				setPosition( prev => ( { ...prev, left: clamped } ) );
			}
		}

		if ( ! linkOpen || ! linkPopoverRef.current ) {
			return;
		}

		const selRect = getVisibleRect(
			savedRangeRef.current,
			getClosestAnchor( savedRangeRef.current?.commonAncestorContainer )
		);
		const nextTop = selRect ? selRect.bottom + 12 : linkPosition.top;
		const nextLeft = selRect
			? selRect.left + ( selRect.width / 2 )
			: linkPosition.left;
		const popRect = linkPopoverRef.current.getBoundingClientRect();
		const clampedLeft = clampCenter( nextLeft, popRect.width );

		if (
			Math.abs( clampedLeft - linkPosition.left ) > 0.5 ||
			Math.abs( nextTop - linkPosition.top ) > 0.5
		) {
			setLinkPosition( {
				top: nextTop,
				left: clampedLeft,
			} );
		}
	}, [ isVisible, position.left, position.top, linkOpen, linkPosition ] );

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
		const attrs = getLinkAttributes( {
			url,
			existingRel: existingRelRef.current,
			sponsored: linkSponsored,
			nofollow: linkNofollow,
		} );
		const editor = getTinyMceEditor( editable );
		if ( editor ) {
			editor.execCommand( 'mceInsertLink', false, {
				href: attrs.href,
				target: attrs.target,
				rel: attrs.rel,
			} );
		} else {
			document.execCommand( 'createLink', false, url );
			applyAnchorAttributes(
				getAnchorFromSelection( containerRef?.current ),
				attrs
			);
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
							onClick={ () => openLinkPopover() }
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
						if (
							event.target.closest(
								'input[type="text"], input[type="url"]'
							)
						) {
							return;
						}
						event.preventDefault();
					} }
				>
					<LinkPopoverFields
						inputRef={ linkInputRef }
						url={ linkUrl }
						onUrlChange={ setLinkUrl }
						onSubmit={ applyLink }
						onCancel={ () => setLinkOpen( false ) }
						onRemove={ removeLink }
						hasAnchor={ linkHasAnchor }
						sponsored={ linkSponsored }
						nofollow={ linkNofollow }
						onSponsoredChange={ setLinkSponsored }
						onNofollowChange={ setLinkNofollow }
					/>
				</div>
			) }
		</>,
		document.body
	);
};
