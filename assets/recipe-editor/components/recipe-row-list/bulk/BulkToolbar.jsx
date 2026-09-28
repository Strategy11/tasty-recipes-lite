import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import classNames from 'classnames';
import {
	AddImageIcon,
	BulletListIcon,
	NumberedListIcon,
	SectionTitleIcon,
} from '../../icons';
import { KbdTooltip } from '../../kbd-tooltip';
import { openLinkSettingsModal } from '../../link-settings';
import { getMediaImage, openImageMediaLibrary } from '../../media-library';
import { getModifierKeySymbol } from '../../platform';
import { DEFAULT_GROUP_LEVEL } from '../lib/constants';
import {
	applyAnchorAttributes,
	createEditorImageHtml,
	getAnchorFromRange,
	getClosestAnchor,
	getLinkAttributes,
	hasRelToken,
	isAbsoluteHttpUrl,
	isListElement,
	isRangeInsideNode,
	isSafeUrl,
	liftListItem,
	placeCaret,
} from '../lib/utils';
import { trackUsage } from '../../../track';
import { filterEditorActiveCommands, LinkPopoverFields } from '../toolbar';

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

	return (
		isElement( node ) &&
		( 'BR' === node.tagName ||
			BLOCK_TAGS.has( node.tagName ) ||
			isListElement( node ) )
	);
};

const isTopLevelListItem = ( editable, node ) =>
	isElement( node ) &&
	'LI' === node.tagName &&
	isListElement( node.parentElement ) &&
	node.parentElement.parentElement === editable;

const getBlockAncestor = ( editable, node ) => {
	let current = isElement( node ) ? node : node?.parentElement;
	while ( current && current !== editable ) {
		if ( isHeadingTag( current ) && editable.contains( current ) ) {
			return current;
		}

		if ( isTopLevelListItem( editable, current ) ) {
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

const getRangeAnchorNode = range => {
	const node = range.commonAncestorContainer;
	if ( ! isListElement( node ) ) {
		return node;
	}

	return (
		node.children[ range.startOffset ] || node.lastElementChild || node
	);
};

const collectLineNodes = ( editable, range ) => {
	const block = getBlockAncestor( editable, getRangeAnchorNode( range ) );
	if ( block ) {
		return { block, nodes: [ block ] };
	}

	let caretNode = range.startContainer;
	if ( caretNode === editable ) {
		const after = editable.childNodes[ range.startOffset ] || null;
		const before = editable.childNodes[ range.startOffset - 1 ] || null;

		if ( isLineBoundary( before ) && isLineBoundary( after ) ) {
			return { block: null, nodes: [], insertBefore: after };
		}

		caretNode = isLineBoundary( before ) ? after : before;
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

const getLineUnits = editable =>
	Array.from( editable.childNodes ).flatMap( node =>
		isListElement( node )
			? Array.from( node.children ).filter(
					child => 'LI' === child.tagName
			  )
			: [ node ]
	);

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

	const units = getLineUnits( editable );
	const from = units.indexOf( first );
	const to = units.indexOf( last );
	if ( from < 0 || to < from ) {
		return [ start ];
	}

	const lines = [];
	let current = [];
	const flushCurrent = () => {
		if ( current.length ) {
			lines.push( { block: null, nodes: current } );
			current = [];
		}
	};

	units.slice( from, to + 1 ).forEach( node => {
		if ( isElement( node ) && 'BR' === node.tagName ) {
			flushCurrent();
			return;
		}

		if (
			isTopLevelListItem( editable, node ) ||
			( isElement( node ) &&
				BLOCK_TAGS.has( node.tagName ) &&
				node.parentNode === editable )
		) {
			flushCurrent();
			lines.push( { block: node, nodes: [ node ] } );
			return;
		}

		current.push( node );
	} );

	flushCurrent();

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

		if ( isTopLevelListItem( editable, line.block ) ) {
			wrapNodesInHeading(
				liftListItem( line.block, { addBreaks: false } ),
				level
			);
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

const getLineListItem = ( editable, line ) =>
	isTopLevelListItem( editable, line.block ) ? line.block : null;

const renameList = ( list, tag ) => {
	const renamed = list.ownerDocument.createElement( tag );
	while ( list.firstChild ) {
		renamed.append( list.firstChild );
	}
	list.replaceWith( renamed );
};

const createLineListItem = ( editable, line ) => {
	const doc = editable.ownerDocument;
	const item = doc.createElement( 'li' );

	if ( line.block ) {
		line.block.before( item );
		while ( line.block.firstChild ) {
			item.append( line.block.firstChild );
		}
		line.block.remove();
	} else if ( line.nodes.length ) {
		line.nodes[ 0 ].before( item );
		item.append( ...line.nodes );
	} else {
		editable.insertBefore( item, line.insertBefore || null );
	}

	if ( ! item.childNodes.length ) {
		item.append( doc.createElement( 'br' ) );
	}

	return item;
};

const placeItemInList = ( item, tag ) => {
	[ item.previousSibling, item.nextSibling ].forEach( sibling => {
		if ( isElement( sibling ) && 'BR' === sibling.tagName ) {
			sibling.remove();
		}
	} );

	let list = item.previousSibling;
	if ( ! isListElement( list ) || list.tagName !== tag ) {
		list = item.ownerDocument.createElement( tag );
		item.before( list );
	}
	list.append( item );

	const following = list.nextSibling;
	if ( isListElement( following ) && following.tagName === tag ) {
		list.append( ...following.childNodes );
		following.remove();
	}
};

const toggleListLines = ( editable, range, tag ) => {
	const listTag = tag.toUpperCase();
	const lines = collectSelectedLines( editable, range ).filter(
		line => ! lineIsHeading( line )
	);
	const items = lines.map( line => getLineListItem( editable, line ) );

	if (
		items.length &&
		items.every( item => item?.parentElement.tagName === listTag )
	) {
		return items.flatMap( item => liftListItem( item ) );
	}

	return lines.map( ( line, index ) => {
		const existing = items[ index ];
		if ( existing ) {
			if ( existing.parentElement.tagName !== listTag ) {
				renameList( existing.parentElement, listTag );
			}
			return existing;
		}

		const item = createLineListItem( editable, line );
		placeItemInList( item, listTag );
		return item;
	} );
};

const selectNodes = ( editable, nodes ) => {
	const first = nodes[ 0 ];
	const last = nodes[ nodes.length - 1 ];
	const selection = getSelection( editable );
	if ( ! first || ! selection ) {
		return;
	}

	const range = editable.ownerDocument.createRange();
	if ( isElement( first ) && 'BR' !== first.tagName ) {
		range.setStart( first, 0 );
	} else {
		range.setStartBefore( first );
	}

	if ( isElement( last ) && 'BR' !== last.tagName ) {
		range.setEnd( last, last.childNodes.length );
	} else {
		range.setEndAfter( last );
	}

	selection.removeAllRanges();
	selection.addRange( range );
};

const getRangeBounds = range => ( {
	collapsed: range.collapsed,
	startContainer: range.startContainer,
	startOffset: range.startOffset,
	endContainer: range.endContainer,
	endOffset: range.endOffset,
} );

const isRestorableBoundary = ( editable, container, offset ) => {
	if ( container === editable || ! editable.contains( container ) ) {
		return false;
	}

	const length =
		Node.TEXT_NODE === container.nodeType
			? container.length
			: container.childNodes.length;

	return offset <= length;
};

const restoreRangeBounds = ( editable, bounds ) => {
	const selection = getSelection( editable );
	if (
		! selection ||
		! isRestorableBoundary( editable, bounds.startContainer, bounds.startOffset ) ||
		! isRestorableBoundary( editable, bounds.endContainer, bounds.endOffset )
	) {
		return false;
	}

	const range = editable.ownerDocument.createRange();
	range.setStart( bounds.startContainer, bounds.startOffset );
	range.setEnd( bounds.endContainer, bounds.endOffset );
	selection.removeAllRanges();
	selection.addRange( range );

	return true;
};

const getAnchorFromSelection = container => {
	const selection = getSelection( container );
	if ( ! selection || ! selection.rangeCount ) {
		return null;
	}

	return getAnchorFromRange( selection.getRangeAt( 0 ) );
};

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
	field = 'ingredients',
} ) => {
	const toolbarRef = useRef( null );
	const linkPopoverRef = useRef( null );
	const linkInputRef = useRef( null );
	const savedRangeRef = useRef( null );
	const [ activeCommands, setActiveCommands ] = useState( {} );
	const [ linkOpen, setLinkOpen ] = useState( false );
	const [ linkUrl, setLinkUrl ] = useState( '' );
	const [ linkHasAnchor, setLinkHasAnchor ] = useState( false );
	const [ linkSponsored, setLinkSponsored ] = useState( false );
	const [ linkNofollow, setLinkNofollow ] = useState( false );
	const existingRelRef = useRef( '' );

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
		const list = el?.closest( 'ul, ol' );
		const listTag = list && editable.contains( list ) ? list.tagName : '';

		setActiveCommands(
			filterEditorActiveCommands(
				{
					bold: document.queryCommandState( 'bold' ),
					italic: document.queryCommandState( 'italic' ),
					underline: document.queryCommandState( 'underline' ),
					link: !! el?.closest( 'a' ),
					heading: !! el?.closest( 'h1,h2,h3,h4,h5,h6' ),
					bulletList: 'UL' === listTag,
					numberedList: 'OL' === listTag,
				},
				range,
				editable
			)
		);
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
		if ( ! saveSelection() && ! restoreSelection() ) {
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
		if ( ! saveSelection() && ! restoreSelection() ) {
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

	const handleList = tag => {
		const editable = editorRef?.current;
		if ( ! saveSelection() && ! restoreSelection() ) {
			return;
		}

		restoreSelection();
		const range = getRange( getSelection( editable ) );
		if ( ! editable || ! range ) {
			return;
		}

		const bounds = getRangeBounds( range );
		const changedNodes = toggleListLines( editable, range, tag );
		if ( ! restoreRangeBounds( editable, bounds ) ) {
			if ( bounds.collapsed && 1 === changedNodes.length ) {
				placeCaret( changedNodes[ 0 ] );
			} else {
				selectNodes( editable, changedNodes );
			}
		}

		saveSelection();
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
		const existingRel = anchor ? anchor.getAttribute( 'rel' ) || '' : '';
		existingRelRef.current = existingRel;
		setLinkHasAnchor( !! anchor );
		setLinkUrl( anchor ? anchor.getAttribute( 'href' ) || '' : '' );
		setLinkSponsored( hasRelToken( existingRel, 'sponsored' ) );
		setLinkNofollow( hasRelToken( existingRel, 'nofollow' ) );
		setLinkOpen( true );
	};

	useEffect( () => {
		const onClick = event => {
			const editable = editorRef?.current;
			const anchor = getClosestAnchor( event.target );
			if (
				! editable ||
				! anchor ||
				! editable.contains( anchor ) ||
				event.target.closest( '.tasty-recipes-row-image__actions' )
			) {
				return;
			}

			event.preventDefault();

			if ( event.shiftKey ) {
				return;
			}

			const selection = getSelection( editable );
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
			const nextRange = editable.ownerDocument.createRange();
			nextRange.selectNodeContents( anchor );
			selection?.removeAllRanges();
			selection?.addRange( nextRange );
			openLinkPopover();
		};

		document.addEventListener( 'click', onClick, true );
		return () => document.removeEventListener( 'click', onClick, true );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ editorRef ] );

	const applyLink = ( {
		url: rawUrl = linkUrl,
		text = '',
		openInNewTab = null,
		nofollow = null,
	} = {} ) => {
		const url = normalizeUrl( rawUrl );
		if ( ! url || ! restoreSelection() ) {
			setLinkOpen( false );
			return;
		}

		document.execCommand( 'createLink', false, url );
		const anchor = getAnchorFromSelection( editorRef?.current );
		const attrs = getLinkAttributes( {
			url,
			existingRel: existingRelRef.current,
			sponsored: linkSponsored,
			nofollow: nofollow ?? linkNofollow,
		} );
		if ( null !== openInNewTab ) {
			attrs.target = openInNewTab ? '_blank' : '';
		}
		applyAnchorAttributes( anchor, attrs );
		if ( anchor && text && text !== anchor.textContent ) {
			anchor.textContent = text;
		}

		notifyEditable( editorRef?.current );
		onChange();
		setLinkOpen( false );
		updateActiveState();
	};

	const clearLink = () => {
		const range = savedRangeRef.current;
		const anchor = range && getAnchorFromRange( range );
		if ( anchor && restoreSelection() ) {
			const selection = getSelection( editorRef.current );
			const anchorRange = editorRef.current.ownerDocument.createRange();
			anchorRange.selectNodeContents( anchor );
			selection.removeAllRanges();
			selection.addRange( anchorRange );
			document.execCommand( 'unlink', false, null );
			saveSelection();
			notifyEditable( editorRef.current );
			onChange();
			updateActiveState();
		}

		existingRelRef.current = '';
		setLinkUrl( '' );
		setLinkHasAnchor( false );
		setLinkSponsored( false );
		setLinkNofollow( false );
		linkInputRef.current?.focus();
	};

	const openLinkSettings = () => {
		const range = savedRangeRef.current;
		if ( ! range ) {
			return;
		}

		const anchor = getAnchorFromRange( range );
		const url = normalizeUrl( linkUrl );
		const opened = openLinkSettingsModal( {
			url: url || linkUrl,
			text: range.toString(),
			openInNewTab: anchor
				? '_blank' === anchor.getAttribute( 'target' )
				: isAbsoluteHttpUrl( url ),
			nofollow: linkNofollow,
			isUpdate: linkHasAnchor,
			onSubmit: settings => {
				savedRangeRef.current = range;
				applyLink( settings );
			},
		} );

		if ( opened ) {
			setLinkOpen( false );
		}
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
					const isListItem = isTopLevelListItem( editable, block );
					if ( isHeadingTag( block ) || isListItem ) {
						const insertRange = editable.ownerDocument.createRange();
						insertRange.setStartAfter(
							isListItem ? block.parentElement : block
						);
						insertRange.collapse( true );
						const selection = getSelection( editable );
						selection.removeAllRanges();
						selection.addRange( insertRange );
						document.execCommand(
							'insertHTML',
							false,
							isListItem ? html : `<br>${ html }`
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
	const blockActions = [
		{
			id: 'heading',
			label: __( 'Heading', 'tasty-recipes-lite' ),
			active: activeCommands.heading,
			onClick: handleHeading,
			content: SectionTitleIcon,
		},
		{
			id: 'bullet-list',
			label: __( 'Bulleted list', 'tasty-recipes-lite' ),
			active: activeCommands.bulletList,
			onClick: () => handleList( 'ul' ),
			content: BulletListIcon,
		},
		{
			id: 'numbered-list',
			label: __( 'Numbered list', 'tasty-recipes-lite' ),
			active: activeCommands.numberedList,
			onClick: () => handleList( 'ol' ),
			content: NumberedListIcon,
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
			{ blockActions.map( action => (
				<KbdTooltip key={ action.id } label={ action.label }>
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
						onClear={ clearLink }
						onOpenSettings={ openLinkSettings }
						hasAnchor={ linkHasAnchor }
						sponsored={ linkSponsored }
						nofollow={ linkNofollow }
						onSponsoredChange={ enabled => {
							setLinkSponsored( enabled );
							trackUsage( 'recipe-modal-link-settings', {
								setting: 'sponsored',
								enabled,
								field,
								surface: 'bulk',
							} );
						} }
						onNofollowChange={ enabled => {
							setLinkNofollow( enabled );
							trackUsage( 'recipe-modal-link-settings', {
								setting: 'nofollow',
								enabled,
								field,
								surface: 'bulk',
							} );
						} }
					/>
				</div>
			) }
		</div>
	);
};
