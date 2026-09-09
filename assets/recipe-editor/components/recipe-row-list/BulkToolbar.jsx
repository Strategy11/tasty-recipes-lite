import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import classNames from 'classnames';
import { AddImageIcon } from '../icons';
import { KbdTooltip } from '../kbd-tooltip';
import { getMediaImage, openImageMediaLibrary } from '../media-library';
import { getModifierKeySymbol } from '../platform';
import { DEFAULT_GROUP_LEVEL } from './constants';
import { createEditorImageHtml, isSafeUrl } from './utils';

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

const BLOCK_TAGS = new Set( [
	'DIV',
	'P',
	'LI',
	'H1',
	'H2',
	'H3',
	'H4',
	'H5',
	'H6',
] );

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

const isElement = node =>
	!! node && Node.ELEMENT_NODE === node.nodeType;

const isHeadingTag = node =>
	isElement( node ) && /^H[1-6]$/.test( node.tagName );

const isLineBoundary = node => {
	if ( ! node ) {
		return true;
	}

	return isElement( node ) && ( 'BR' === node.tagName || BLOCK_TAGS.has( node.tagName ) );
};

const getBlockAncestor = ( editable, node ) => {
	let current = isElement( node ) ? node : node?.parentElement;
	while ( current && current !== editable ) {
		if ( isHeadingTag( current ) && editable.contains( current ) ) {
			return current;
		}

		if (
			BLOCK_TAGS.has( current.tagName ) &&
			current.parentElement === editable
		) {
			return current;
		}

		current = current.parentElement;
	}

	return null;
};

const collectLineNodes = ( editable, range ) => {
	const block = getBlockAncestor( editable, range.commonAncestorContainer );
	if ( block ) {
		return { block, nodes: [ block ] };
	}

	let caretNode = range.startContainer;
	if ( caretNode === editable ) {
		caretNode =
			editable.childNodes[ range.startOffset ] ||
			editable.childNodes[ range.startOffset - 1 ];
	}

	while ( caretNode && caretNode.parentNode !== editable ) {
		caretNode = caretNode.parentNode;
	}

	if ( ! caretNode ) {
		return { block: null, nodes: [] };
	}

	let start = caretNode;
	while (
		start.previousSibling &&
		! isLineBoundary( start.previousSibling )
	) {
		start = start.previousSibling;
	}

	let end = caretNode;
	while ( end.nextSibling && ! isLineBoundary( end.nextSibling ) ) {
		end = end.nextSibling;
	}

	const nodes = [];
	for ( let node = start; node; node = node.nextSibling ) {
		nodes.push( node );
		if ( node === end ) {
			break;
		}
	}

	return { block: null, nodes };
};

const collectSelectedLines = ( editable, range ) => {
	const startRange = range.cloneRange();
	startRange.collapse( true );
	const start = collectLineNodes( editable, startRange );

	if ( range.collapsed ) {
		return [ start ];
	}

	const endRange = range.cloneRange();
	endRange.collapse( false );
	const end = collectLineNodes( editable, endRange );

	const first = start.nodes[ 0 ] || start.block;
	const last = end.nodes[ end.nodes.length - 1 ] || end.block;
	if ( ! first || ! last || first === last ) {
		return [ start ];
	}

	const lines = [];
	let current = [];
	let node = first;
	const stop = last.nextSibling;

	while ( node && node !== stop ) {
		if ( isElement( node ) && 'BR' === node.tagName ) {
			if ( current.length ) {
				lines.push( { block: null, nodes: current } );
				current = [];
			}
		} else if (
			isElement( node ) &&
			BLOCK_TAGS.has( node.tagName ) &&
			node.parentNode === editable
		) {
			if ( current.length ) {
				lines.push( { block: null, nodes: current } );
				current = [];
			}
			lines.push( { block: node, nodes: [ node ] } );
		} else {
			current.push( node );
		}

		node = node.nextSibling;
	}

	if ( current.length ) {
		lines.push( { block: null, nodes: current } );
	}

	return lines.length ? lines : [ start ];
};

const unwrapElement = el => {
	const fragment = el.ownerDocument.createDocumentFragment();
	while ( el.firstChild ) {
		fragment.append( el.firstChild );
	}
	el.replaceWith( fragment );
};

const wrapNodesInHeading = ( nodes, level ) => {
	if ( ! nodes.length ) {
		return;
	}

	const heading = nodes[ 0 ].ownerDocument.createElement( `h${ level }` );
	nodes[ 0 ].parentNode.insertBefore( heading, nodes[ 0 ] );
	nodes.forEach( node => heading.append( node ) );
};

const lineIsHeading = line => {
	const el = line.block || ( 1 === line.nodes.length ? line.nodes[ 0 ] : null );
	return isHeadingTag( el );
};

const toggleHeadingLines = ( editable, range, level ) => {
	const lines = collectSelectedLines( editable, range );
	const allHeadings = lines.length && lines.every( lineIsHeading );

	if ( allHeadings ) {
		lines.forEach( line => unwrapElement( line.block || line.nodes[ 0 ] ) );
		return;
	}

	const doc = editable.ownerDocument;
	lines.forEach( line => {
		if ( lineIsHeading( line ) ) {
			return;
		}

		if ( line.block && [ 'DIV', 'P', 'LI' ].includes( line.block.tagName ) ) {
			const heading = doc.createElement( `h${ level }` );
			heading.innerHTML = line.block.innerHTML;
			line.block.replaceWith( heading );
			return;
		}

		if ( line.nodes.length ) {
			wrapNodesInHeading( line.nodes, level );
		}
	} );
};

const getAnchorFromSelection = container => {
	const selection = getSelection( container );
	if ( ! selection || ! selection.rangeCount ) {
		return null;
	}

	const node = selection.anchorNode;
	const el = isElement( node ) ? node : node?.parentElement;
	return el?.closest( 'a' ) || null;
};

const isAbsoluteHttpUrl = value => /^https?:\/\//i.test( value );

const normalizeUrl = raw => {
	const value = String( raw || '' ).trim();
	if ( ! value || ! isSafeUrl( value ) ) {
		return '';
	}

	if (
		/^[a-z][a-z0-9+.-]*:/i.test( value ) ||
		value.startsWith( '//' ) ||
		value.startsWith( '/' ) ||
		value.startsWith( '#' ) ||
		value.startsWith( '?' )
	) {
		return value;
	}

	return /^[^\s/]+\.[^\s/]/.test( value ) ? `https://${ value }` : value;
};

const notifyEditable = editable => {
	editable?.dispatchEvent( new Event( 'input', { bubbles: true } ) );
};

export const BulkToolbar = ( {
	editorRef,
	showImage = false,
	onChange = () => {},
} ) => {
	const toolbarRef = useRef( null );
	const linkPopoverRef = useRef( null );
	const linkInputRef = useRef( null );
	const savedRangeRef = useRef( null );
	const [ activeCommands, setActiveCommands ] = useState( {} );
	const [ linkOpen, setLinkOpen ] = useState( false );
	const [ linkUrl, setLinkUrl ] = useState( '' );
	const [ linkHasAnchor, setLinkHasAnchor ] = useState( false );

	const saveSelection = () => {
		const range = getRange( getSelection( editorRef?.current ) );
		if ( ! range || ! editorRef?.current ) {
			return false;
		}

		if (
			! editorRef.current.contains( range.commonAncestorContainer ) &&
			range.commonAncestorContainer !== editorRef.current
		) {
			return false;
		}

		savedRangeRef.current = range.cloneRange();
		return true;
	};

	const restoreSelection = () => {
		const selection = getSelection( editorRef?.current );
		const range = savedRangeRef.current;
		if ( ! selection || ! range || ! editorRef?.current ) {
			return false;
		}

		editorRef.current.focus();
		selection.removeAllRanges();
		selection.addRange( range );
		return true;
	};

	const updateActiveState = () => {
		const editable = editorRef?.current;
		const range = getRange( getSelection( editable ) );
		if (
			! editable ||
			! range ||
			( ! editable.contains( range.commonAncestorContainer ) &&
				range.commonAncestorContainer !== editable )
		) {
			setActiveCommands( {} );
			return;
		}

		const node = range.commonAncestorContainer;
		const el = isElement( node ) ? node : node.parentElement;

		setActiveCommands( {
			bold: document.queryCommandState( 'bold' ),
			italic: document.queryCommandState( 'italic' ),
			underline: document.queryCommandState( 'underline' ),
			link: !! el?.closest( 'a' ),
			heading: !! el?.closest( 'h1,h2,h3,h4,h5,h6' ),
		} );
	};

	useEffect( () => {
		const onSelectionChange = () => updateActiveState();
		document.addEventListener( 'selectionchange', onSelectionChange );
		return () =>
			document.removeEventListener( 'selectionchange', onSelectionChange );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ editorRef ] );

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
			if (
				toolbarRef.current?.contains( event.target ) ||
				linkPopoverRef.current?.contains( event.target )
			) {
				return;
			}

			setLinkOpen( false );
		};

		document.addEventListener( 'mousedown', onPointerDown );
		return () =>
			document.removeEventListener( 'mousedown', onPointerDown );
	}, [ linkOpen ] );

	useEffect( () => {
		const onKeyDown = event => {
			if ( ! ( event.metaKey || event.ctrlKey ) ) {
				return;
			}

			if ( 'k' !== event.key.toLowerCase() ) {
				return;
			}

			const editable = editorRef?.current;
			const range = getRange( getSelection( editable ) );
			if ( ! editable || ! range ) {
				return;
			}

			if (
				! editable.contains( range.commonAncestorContainer ) &&
				range.commonAncestorContainer !== editable
			) {
				return;
			}

			event.preventDefault();
			openLinkPopover();
		};

		document.addEventListener( 'keydown', onKeyDown );
		return () => document.removeEventListener( 'keydown', onKeyDown );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ editorRef ] );

	const runCommand = command => {
		if ( ! restoreSelection() && ! saveSelection() ) {
			return;
		}

		restoreSelection();
		document.execCommand( command, false, null );
		const range = getRange( getSelection( editorRef?.current ) );
		if ( range ) {
			savedRangeRef.current = range.cloneRange();
		}
		notifyEditable( editorRef?.current );
		onChange();
		updateActiveState();
	};

	const handleHeading = () => {
		const editable = editorRef?.current;
		if ( ! restoreSelection() && ! saveSelection() ) {
			return;
		}

		restoreSelection();
		const range = getRange( getSelection( editable ) );
		if ( ! editable || ! range ) {
			return;
		}

		toggleHeadingLines( editable, range, DEFAULT_GROUP_LEVEL );
		notifyEditable( editable );
		onChange();
		updateActiveState();
	};

	const openLinkPopover = () => {
		if ( ! saveSelection() ) {
			return;
		}

		const editable = editorRef?.current;
		const anchor = getAnchorFromSelection( editable );
		setLinkHasAnchor( !! anchor );
		setLinkUrl( anchor ? anchor.getAttribute( 'href' ) || '' : '' );
		setLinkOpen( true );
	};

	const applyLink = () => {
		const url = normalizeUrl( linkUrl );
		if ( ! url || ! restoreSelection() ) {
			setLinkOpen( false );
			return;
		}

		document.execCommand( 'createLink', false, url );
		const anchor = getAnchorFromSelection( editorRef?.current );
		if ( anchor && isAbsoluteHttpUrl( url ) ) {
			anchor.setAttribute( 'target', '_blank' );
			anchor.setAttribute( 'rel', 'noopener noreferrer' );
		}

		notifyEditable( editorRef?.current );
		onChange();
		setLinkOpen( false );
		updateActiveState();
	};

	const removeLink = () => {
		if ( ! restoreSelection() ) {
			setLinkOpen( false );
			return;
		}

		document.execCommand( 'unlink', false, null );
		notifyEditable( editorRef?.current );
		onChange();
		setLinkOpen( false );
		updateActiveState();
	};

	const handleImage = () => {
		saveSelection();
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

				restoreSelection();
				const editable = editorRef?.current;
				const range = getRange( getSelection( editable ) );
				const html = createEditorImageHtml( image );
				if ( ! editable || ! html ) {
					return;
				}

				if ( range ) {
					const { block } = collectLineNodes( editable, range );
					if ( isHeadingTag( block ) ) {
						const insertRange = editable.ownerDocument.createRange();
						insertRange.setStartAfter( block );
						insertRange.collapse( true );
						const selection = getSelection( editable );
						selection.removeAllRanges();
						selection.addRange( insertRange );
						document.execCommand(
							'insertHTML',
							false,
							`<br>${ html }`
						);
					} else {
						document.execCommand( 'insertHTML', false, html );
					}
				} else {
					document.execCommand( 'insertHTML', false, html );
				}

				notifyEditable( editable );
				onChange();
			},
		} );
	};

	const preventBlur = event => event.preventDefault();
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
		{
			id: 'link',
			label: __( 'Add link', 'tasty-recipes-lite' ),
			shortcut: [ mod, 'K' ],
			active: activeCommands.link || linkOpen,
			onClick: openLinkPopover,
			content: LINK_ICON,
		},
	];

	return (
		<div
			ref={ toolbarRef }
			className="tasty-recipes-bulk-toolbar"
			role="toolbar"
			aria-label={ __( 'Text formatting', 'tasty-recipes-lite' ) }
			onMouseDown={ preventBlur }
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
						aria-pressed={ !! action.active }
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
			<KbdTooltip label={ __( 'Heading', 'tasty-recipes-lite' ) }>
				<button
					type="button"
					className={ classNames( {
						'is-active': activeCommands.heading,
					} ) }
					aria-label={ __( 'Heading', 'tasty-recipes-lite' ) }
					aria-pressed={ !! activeCommands.heading }
					onClick={ handleHeading }
				>
					<b>H</b>
				</button>
			</KbdTooltip>
			{ showImage && (
				<KbdTooltip
					label={ __( 'Add image', 'tasty-recipes-lite' ) }
				>
					<button
						type="button"
						aria-label={ __( 'Add image', 'tasty-recipes-lite' ) }
						onClick={ handleImage }
					>
						{ AddImageIcon }
					</button>
				</KbdTooltip>
			) }
			{ linkOpen && (
				// eslint-disable-next-line jsx-a11y/no-noninteractive-element-interactions
				<div
					ref={ linkPopoverRef }
					className="tasty-recipes-selection-link-popover tasty-recipes-bulk-toolbar-link"
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
						onChange={ event => setLinkUrl( event.target.value ) }
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
		</div>
	);
};
