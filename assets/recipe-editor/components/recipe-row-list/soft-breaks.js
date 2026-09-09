import {
	SOFT_BREAK_ATTR,
	SOFT_BREAK_CLASS,
	ZWSP,
} from './constants';
import { isBr, isSoftBreakSpan, parseFragment } from './html';
import { sanitizeInlineHtml } from './sanitize';

const SOFT_BREAK_PLACEHOLDER = `<span class="${ SOFT_BREAK_CLASS }"></span>`;

export const protectSoftBreaks = html =>
	String( html || '' ).replaceAll( /<br\s*\/?>/gi, SOFT_BREAK_PLACEHOLDER );

const stripZwsp = value => String( value || '' ).replaceAll( ZWSP, '' );

const isWhitespaceText = node =>
	!! node &&
	Node.TEXT_NODE === node.nodeType &&
	! stripZwsp( node.textContent ).replaceAll( /\s/g, '' );

export const lastMeaningfulChild = parent => {
	let node = parent?.lastChild;

	while ( node && isWhitespaceText( node ) ) {
		node = node.previousSibling;
	}

	return node;
};

export const stripInsignificantBreakWhitespace = root => {
	Array.from( root.childNodes ).forEach( node => {
		if ( Node.ELEMENT_NODE === node.nodeType ) {
			stripInsignificantBreakWhitespace( node );
			return;
		}

		if ( Node.TEXT_NODE !== node.nodeType ) {
			return;
		}

		const prev = node.previousSibling;
		const next = node.nextSibling;
		const prevIsBreak = isBr( prev ) || isSoftBreakSpan( prev );
		const nextIsBreak = isBr( next ) || isSoftBreakSpan( next );

		if ( prevIsBreak ) {
			node.textContent = String( node.textContent || '' ).replace(
				/^[\n\r]+/,
				''
			);
		}

		if ( nextIsBreak ) {
			node.textContent = String( node.textContent || '' ).replace(
				/[\n\r]+$/,
				''
			);
		}

		if ( ( prevIsBreak || nextIsBreak ) && isWhitespaceText( node ) ) {
			node.remove();
		}
	} );
};

export const collapseSoftBreaks = root => {
	Array.from( root.querySelectorAll( `span.${ SOFT_BREAK_CLASS }` ) )
		.filter(
			span =>
				! span.parentElement?.closest( `span.${ SOFT_BREAK_CLASS }` )
		)
		.forEach( span => {
			const clean = root.ownerDocument.createElement( 'span' );
			clean.className = SOFT_BREAK_CLASS;

			span.replaceWith( clean );
		} );
};

const stripZwspFromRoot = root => {
	Array.from( root.childNodes ).forEach( node => {
		if ( Node.TEXT_NODE === node.nodeType ) {
			const next = stripZwsp( node.textContent );
			if ( next ) {
				node.textContent = next;
				return;
			}
			node.remove();
			return;
		}

		if ( Node.ELEMENT_NODE === node.nodeType ) {
			stripZwspFromRoot( node );
		}
	} );
};

export const encodeSoftBreaks = html => {
	const root = parseFragment( html );
	stripZwspFromRoot( root );
	collapseSoftBreaks( root );
	stripInsignificantBreakWhitespace( root );
	const doc = root.ownerDocument;

	root.querySelectorAll( 'br' ).forEach( br => {
		const span = doc.createElement( 'span' );
		span.className = SOFT_BREAK_CLASS;
		br.replaceWith( span );
	} );

	return root.innerHTML;
};

export const fromEditorHtml = html =>
	encodeSoftBreaks( sanitizeInlineHtml( stripZwsp( html ) ) );

export const toEditorHtml = html => {
	const root = parseFragment( sanitizeInlineHtml( html ) );
	collapseSoftBreaks( root );
	const doc = root.ownerDocument;

	root.querySelectorAll( `span.${ SOFT_BREAK_CLASS }` ).forEach( span => {
		span.replaceWith( doc.createElement( 'br' ) );
	} );

	stripInsignificantBreakWhitespace( root );

	if ( isBr( lastMeaningfulChild( root ) ) ) {
		root.append( doc.createTextNode( ZWSP ) );
	}

	return root.innerHTML;
};

export const toMarkedEditorBreaks = html => {
	const root = parseFragment( sanitizeInlineHtml( html ) );
	collapseSoftBreaks( root );
	const doc = root.ownerDocument;

	root.querySelectorAll( `span.${ SOFT_BREAK_CLASS }` ).forEach( span => {
		const br = doc.createElement( 'br' );
		br.setAttribute( SOFT_BREAK_ATTR, '' );
		span.replaceWith( br );
	} );

	return root.innerHTML;
};

const isBreakNode = node => isBr( node ) || isSoftBreakSpan( node );

export const deleteSoftBreakBeforeCaret = el => {
	if ( ! el ) {
		return false;
	}

	const doc = el.ownerDocument;
	const selection = doc.defaultView?.getSelection();

	if ( ! selection?.rangeCount ) {
		return false;
	}

	const range = selection.getRangeAt( 0 );

	if (
		! range.collapsed ||
		( ! el.contains( range.commonAncestorContainer ) &&
			range.commonAncestorContainer !== el )
	) {
		return false;
	}

	const placeCaret = node => {
		const next = doc.createRange();

		if ( node && el.contains( node ) ) {
			next.setStartAfter( node );
		} else {
			next.selectNodeContents( el );
			next.collapse( false );
		}

		next.collapse( true );
		selection.removeAllRanges();
		selection.addRange( next );
	};

	const removeBreak = node => {
		const before = node.previousSibling;
		const after = node.nextSibling;

		if (
			after &&
			Node.TEXT_NODE === after.nodeType &&
			! stripZwsp( after.textContent )
		) {
			after.remove();
		}

		node.remove();
		placeCaret( before );
		el.dispatchEvent( new Event( 'input', { bubbles: true } ) );

		return true;
	};

	const container = range.startContainer;
	const offset = range.startOffset;

	if ( Node.TEXT_NODE === container.nodeType ) {
		const text = String( container.textContent || '' );

		if ( offset > 0 && text[ offset - 1 ] === ZWSP ) {
			container.deleteData( offset - 1, 1 );
			const prev = container.previousSibling;
			if ( isBreakNode( prev ) ) {
				return removeBreak( prev );
			}
			el.dispatchEvent( new Event( 'input', { bubbles: true } ) );
			return true;
		}

		if ( 0 === offset && isBreakNode( container.previousSibling ) ) {
			return removeBreak( container.previousSibling );
		}

		return false;
	}

	if ( offset > 0 ) {
		const prev = container.childNodes[ offset - 1 ];

		if (
			prev &&
			Node.TEXT_NODE === prev.nodeType &&
			! stripZwsp( prev.textContent )
		) {
			const before = prev.previousSibling;
			prev.remove();
			if ( isBreakNode( before ) ) {
				return removeBreak( before );
			}
			el.dispatchEvent( new Event( 'input', { bubbles: true } ) );
			return true;
		}

		if ( isBreakNode( prev ) ) {
			return removeBreak( prev );
		}
	}

	return false;
};

export const insertSoftBreak = ( el, { marked = false } = {} ) => {
	if ( ! el ) {
		return false;
	}

	const doc = el.ownerDocument;
	const selection = doc.defaultView?.getSelection();

	if ( ! selection ) {
		return false;
	}

	el.focus();

	let range = selection.rangeCount ? selection.getRangeAt( 0 ) : null;
	if (
		! range ||
		( ! el.contains( range.commonAncestorContainer ) &&
			range.commonAncestorContainer !== el )
	) {
		range = doc.createRange();
		range.selectNodeContents( el );
		range.collapse( false );
	}

	range.deleteContents();

	const br = doc.createElement( 'br' );
	if ( marked ) {
		br.setAttribute( SOFT_BREAK_ATTR, '' );
	}

	range.insertNode( br );

	while (
		br.nextSibling &&
		Node.TEXT_NODE === br.nextSibling.nodeType &&
		! String( br.nextSibling.textContent || '' )
	) {
		br.nextSibling.remove();
	}

	if ( ! br.nextSibling ) {
		br.after( doc.createTextNode( ZWSP ) );
	}

	const next = doc.createRange();

	if ( br.nextSibling && Node.TEXT_NODE === br.nextSibling.nodeType ) {
		next.setStart( br.nextSibling, 0 );
	} else {
		next.setStartAfter( br );
	}

	next.collapse( true );
	selection.removeAllRanges();
	selection.addRange( next );

	el.dispatchEvent( new Event( 'input', { bubbles: true } ) );

	return true;
};

export const isMarkedSoftBreak = node =>
	!! node &&
	Node.ELEMENT_NODE === node.nodeType &&
	'BR' === node.tagName &&
	node.hasAttribute( SOFT_BREAK_ATTR );
