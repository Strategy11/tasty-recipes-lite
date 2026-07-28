import {
	createPortal,
	useEffect,
	useLayoutEffect,
	useRef,
	useState,
} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import classNames from 'classnames';

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

const isInsideSurface = ( node, container ) => {
	if ( ! node || ! container ) {
		return false;
	}

	const el =
		Node.ELEMENT_NODE === node.nodeType ? node : node.parentElement;
	if ( ! el ) {
		return false;
	}

	const editable = el.closest(
		'.tasty-recipes-row-input[contenteditable="true"]'
	);
	return !! editable && container.contains( editable );
};

const getEditableFromRange = range => {
	if ( ! range ) {
		return null;
	}

	const node = range.commonAncestorContainer;
	const el =
		Node.ELEMENT_NODE === node.nodeType ? node : node.parentElement;
	return (
		el?.closest( '.tasty-recipes-row-input[contenteditable="true"]' ) ||
		null
	);
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
	if ( ! value ) {
		return '';
	}

	if ( /^https?:\/\//i.test( value ) ) {
		return value;
	}

	return `https://${ value.replace( /^\/\//, '' ) }`;
};

const notifyEditable = range => {
	const editable = getEditableFromRange( range );
	editable?.dispatchEvent( new Event( 'input', { bubbles: true } ) );
};

export const SelectionToolbar = ( { containerRef } ) => {
	const toolbarRef = useRef( null );
	const linkPopoverRef = useRef( null );
	const linkInputRef = useRef( null );
	const savedRangeRef = useRef( null );
	const linkOpenRef = useRef( false );
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

	const updateActiveState = () => {
		const container = containerRef?.current;
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

		if ( ! isInsideSurface( range.commonAncestorContainer, container ) ) {
			hide();
			return;
		}

		const rect = range.getBoundingClientRect();
		if ( ! rect.width && ! rect.height ) {
			hide();
			return;
		}

		const scrollBody = document.querySelector(
			'.tasty-recipes-frame-content'
		);
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

		if ( ! isInsideSurface( range.commonAncestorContainer, container ) ) {
			return;
		}

		savedRangeRef.current = range.cloneRange();
		const anchor = getAnchorFromSelection( container );
		const rect = range.getBoundingClientRect();
		setLinkHasAnchor( !! anchor );
		setLinkUrl(
			anchor
				? anchor
					.getAttribute( 'href' )
					?.replace( /^https?:\/\//i, '' ) || ''
				: ''
		);
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

		const scrollBody = document.querySelector(
			'.tasty-recipes-frame-content'
		);
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
	}, [ containerRef ] );

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
				! isInsideSurface( range.commonAncestorContainer, container )
			) {
				return;
			}

			event.preventDefault();
			openLinkPopover();
		};

		document.addEventListener( 'keydown', onKeyDown );
		return () => document.removeEventListener( 'keydown', onKeyDown );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ containerRef ] );

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

		document.execCommand( command, false, null );
		const range = getRange( getSelection( containerRef?.current ) );
		if ( range ) {
			savedRangeRef.current = range.cloneRange();
		}
		notifyEditable( savedRangeRef.current );
		updateActiveState();
	};

	const applyLink = () => {
		const url = normalizeUrl( linkUrl );
		if ( ! url || ! restoreSelection() ) {
			setLinkOpen( false );
			return;
		}

		document.execCommand( 'createLink', false, url );
		const anchor = getAnchorFromSelection( containerRef?.current );
		if ( anchor ) {
			anchor.setAttribute( 'target', '_blank' );
			anchor.setAttribute( 'rel', 'noopener noreferrer' );
		}
		notifyEditable( savedRangeRef.current );
		setLinkOpen( false );
		syncFromSelection();
	};

	const removeLink = () => {
		if ( ! restoreSelection() ) {
			setLinkOpen( false );
			return;
		}

		document.execCommand( 'unlink', false, null );
		notifyEditable( savedRangeRef.current );
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
					<button
						type="button"
						className={ classNames( {
							'is-active': activeCommands.bold,
						} ) }
						aria-label={ __( 'Bold', 'tasty-recipes-lite' ) }
						onClick={ () => runCommand( 'bold' ) }
					>
						<b>B</b>
					</button>
					<button
						type="button"
						className={ classNames( {
							'is-active': activeCommands.italic,
						} ) }
						aria-label={ __( 'Italic', 'tasty-recipes-lite' ) }
						onClick={ () => runCommand( 'italic' ) }
					>
						<i>I</i>
					</button>
					<button
						type="button"
						className={ classNames( {
							'is-active': activeCommands.underline,
						} ) }
						aria-label={ __( 'Underline', 'tasty-recipes-lite' ) }
						onClick={ () => runCommand( 'underline' ) }
					>
						<span className="tasty-recipes-selection-toolbar__underline">
							U
						</span>
					</button>
					<span
						className="tasty-recipes-selection-toolbar__sep"
						aria-hidden="true"
					/>
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
						placeholder={ __(
							'https://example.com',
							'tasty-recipes-lite'
						) }
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
