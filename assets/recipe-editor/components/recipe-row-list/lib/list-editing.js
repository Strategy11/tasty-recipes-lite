import { ZWSP } from './constants';
import { isBr } from './html';
import { isListElement } from './lists';

const LINE_BLOCK_TAGS = new Set( [
	'DIV',
	'P',
	'LI',
	'UL',
	'OL',
	'FIGURE',
	'H1',
	'H2',
	'H3',
	'H4',
	'H5',
	'H6',
] );

const isLineBreakBoundary = node =>
	isBr( node ) ||
	( !! node &&
		Node.ELEMENT_NODE === node.nodeType &&
		LINE_BLOCK_TAGS.has( node.tagName ) );

const hasVisibleContent = node =>
	!! String( node.textContent || '' ).replaceAll( ZWSP, '' ).trim() ||
	!! node.querySelector?.( 'img, br' );

export const getClosestListItem = ( root, node ) => {
	const el = Node.ELEMENT_NODE === node?.nodeType ? node : node?.parentElement;
	const item = el?.closest( 'li' );

	return item && root?.contains( item ) && isListElement( item.parentElement )
		? item
		: null;
};

export const isListItemEmpty = item =>
	! String( item.textContent || '' ).replaceAll( ZWSP, '' ).trim() &&
	! item.querySelector( 'img' );

export const isCaretAtListItemStart = ( item, range ) => {
	if ( ! range?.collapsed ) {
		return false;
	}

	const before = range.cloneRange();
	before.selectNodeContents( item );
	before.setEnd( range.startContainer, range.startOffset );

	const holder = item.ownerDocument.createElement( 'div' );
	holder.append( before.cloneContents() );

	return ! hasVisibleContent( holder );
};

export const placeCaret = ( node, { before = false } = {} ) => {
	const doc = node.ownerDocument;
	const selection = doc.defaultView?.getSelection();
	if ( ! selection ) {
		return;
	}

	const range = doc.createRange();
	if ( before ) {
		range.setStartBefore( node );
	} else {
		range.setStart( node, 0 );
	}

	range.collapse( true );
	selection.removeAllRanges();
	selection.addRange( range );
};

const fillEmptyListItem = item => {
	if ( ! hasVisibleContent( item ) ) {
		item.append( item.ownerDocument.createElement( 'br' ) );
	}
};

export const splitListItemAtCaret = ( item, range ) => {
	range.deleteContents();

	const tail = range.cloneRange();
	tail.selectNodeContents( item );
	tail.setStart( range.startContainer, range.startOffset );

	const next = item.ownerDocument.createElement( 'li' );
	next.append( tail.extractContents() );
	item.after( next );

	fillEmptyListItem( item );
	fillEmptyListItem( next );
	placeCaret( next );

	return next;
};

export const liftListItem = ( item, { addBreaks = true } = {} ) => {
	const list = item.parentElement;
	const doc = item.ownerDocument;
	const tail = doc.createElement( list.tagName );

	while ( item.nextSibling ) {
		tail.append( item.nextSibling );
	}

	const nodes = Array.from( item.childNodes );
	const anchor = doc.createTextNode( '' );
	list.after( anchor );
	anchor.after( ...nodes );

	if ( tail.childNodes.length ) {
		( nodes[ nodes.length - 1 ] || anchor ).after( tail );
	}

	item.remove();
	anchor.remove();
	if ( ! list.children.length ) {
		list.remove();
	}

	if ( addBreaks && nodes.length ) {
		const first = nodes[ 0 ];
		const last = nodes[ nodes.length - 1 ];

		if (
			first.previousSibling &&
			! isLineBreakBoundary( first.previousSibling )
		) {
			first.before( doc.createElement( 'br' ) );
		}

		if (
			! isBr( last ) &&
			last.nextSibling &&
			! isLineBreakBoundary( last.nextSibling )
		) {
			last.after( doc.createElement( 'br' ) );
		}
	}

	return nodes;
};

export const exitListItem = item => {
	const doc = item.ownerDocument;
	item.replaceChildren( doc.createElement( 'br' ) );

	const [ lineBreak ] = liftListItem( item, { addBreaks: false } );

	if (
		lineBreak.previousSibling &&
		! isLineBreakBoundary( lineBreak.previousSibling )
	) {
		lineBreak.before( doc.createElement( 'br' ) );
	}

	return lineBreak;
};
