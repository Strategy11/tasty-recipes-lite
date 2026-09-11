import { parseFragment } from './html';
import { serializeRowImage } from './images';
import {
	createGroupRow,
	createItemRow,
	extractItemContent,
	getGroupLevel,
} from './rows';
import { rowHasContent, sanitizeInlineHtml } from './sanitize';
import { isMarkedSoftBreak, toMarkedEditorBreaks } from './soft-breaks';

const BULK_IMAGE_CLASS = 'tasty-recipes-bulk-image';
const BULK_IMAGE_REMOVE_CLASS = 'tasty-recipes-row-image__remove';

const isHeadingElement = node =>
	!! node &&
	Node.ELEMENT_NODE === node.nodeType &&
	/^H[1-6]$/.test( node.tagName );

const isBlockLineElement = node => {
	if ( ! node || Node.ELEMENT_NODE !== node.nodeType ) {
		return false;
	}

	return [ 'DIV', 'P', 'LI' ].includes( node.tagName );
};

const lineHasMedia = html => {
	const root = parseFragment( html );

	return !! root.querySelector( 'img, figure, picture' );
};

const splitBulkLines = value => {
	const root = parseFragment( value );
	const lines = [];
	const doc = root.ownerDocument;
	let current = doc.createElement( 'div' );

	const flush = () => {
		const html = current.innerHTML;

		if ( rowHasContent( html ) || lineHasMedia( html ) ) {
			lines.push( { type: 'item', html } );
		}

		current = doc.createElement( 'div' );
	};

	const pushHeading = node => {
		const html = node.innerHTML;

		if ( ! rowHasContent( html ) ) {
			return;
		}

		lines.push( {
			type: 'heading',
			html,
			level: Number( node.tagName.slice( 1 ) ),
		} );
	};

	const appendNode = node => {
		if ( Node.TEXT_NODE === node.nodeType ) {
			const parts = String( node.textContent || '' ).split( /\r?\n/ );
			parts.forEach( ( part, index ) => {
				if ( index > 0 ) {
					flush();
				}

				if ( part ) {
					current.append( doc.createTextNode( part ) );
				}
			} );
			return;
		}

		current.append( node.cloneNode( true ) );
	};

	const walk = parent => {
		Array.from( parent.childNodes ).forEach( child => {
			if (
				Node.ELEMENT_NODE === child.nodeType &&
				'BR' === child.tagName
			) {
				if ( isMarkedSoftBreak( child ) ) {
					appendNode( child );
					return;
				}

				flush();
				return;
			}

			if ( isHeadingElement( child ) ) {
				if ( current.childNodes.length ) {
					flush();
				}

				pushHeading( child );
				return;
			}

			if ( isBlockLineElement( child ) ) {
				if ( current.childNodes.length ) {
					flush();
				}

				walk( child );
				flush();
				return;
			}

			appendNode( child );
		} );
	};

	walk( root );

	if ( current.childNodes.length ) {
		flush();
	}

	return lines;
};

export const serializeBulkText = rows => {
	const parts = [];

	rows.forEach( row => {
		if ( 'group' === row.type ) {
			const html = sanitizeInlineHtml( row.value );
			if ( ! rowHasContent( html ) ) {
				return;
			}

			const level = getGroupLevel( row.level );
			parts.push( {
				html: `<h${ level }>${ html }</h${ level }>`,
				isHeading: true,
			} );
			return;
		}

		const html = sanitizeInlineHtml( row.value );
		const imageHtml = serializeRowImage( row.image );

		if ( ! rowHasContent( html ) && ! imageHtml ) {
			return;
		}

		let itemHtml = toMarkedEditorBreaks( html );

		if ( rowHasContent( html ) && imageHtml ) {
			itemHtml = `${ itemHtml }${ imageHtml }`;
		} else if ( ! rowHasContent( html ) ) {
			itemHtml = imageHtml;
		}

		parts.push( {
			html: itemHtml,
			isHeading: false,
		} );
	} );

	return parts.reduce( ( output, part, index ) => {
		if ( ! index ) {
			return part.html;
		}

		const previous = parts[ index - 1 ];

		if ( part.isHeading || previous.isHeading ) {
			return `${ output }${ part.html }`;
		}

		return `${ output }<br>${ part.html }`;
	}, '' );
};

const unwrapElement = node => {
	const fragment = node.ownerDocument.createDocumentFragment();

	while ( node.firstChild ) {
		fragment.append( node.firstChild );
	}

	node.replaceWith( fragment );
};

export const stripBulkImageChrome = html => {
	const root = parseFragment( html );

	root.querySelectorAll( `.${ BULK_IMAGE_REMOVE_CLASS }` ).forEach( button =>
		button.remove()
	);

	root.querySelectorAll( `.${ BULK_IMAGE_CLASS }` ).forEach( wrap => {
		if ( 'FIGURE' === wrap.tagName ) {
			wrap.removeAttribute( 'contenteditable' );
			wrap.classList.remove( BULK_IMAGE_CLASS );

			if ( ! wrap.getAttribute( 'class' ) ) {
				wrap.removeAttribute( 'class' );
			}

			return;
		}

		unwrapElement( wrap );
	} );

	return root.innerHTML;
};

export const decorateBulkImages = ( root, removeLabel ) => {
	if ( ! root ) {
		return;
	}

	Array.from( root.querySelectorAll( 'img' ) ).forEach( img => {
		let wrap = img.closest( `.${ BULK_IMAGE_CLASS }` );

		if ( ! wrap ) {
			wrap = img.closest( 'figure' );
			if ( wrap && root.contains( wrap ) ) {
				wrap.classList.add( BULK_IMAGE_CLASS );
			} else {
				wrap = root.ownerDocument.createElement( 'span' );
				wrap.className = BULK_IMAGE_CLASS;
				img.replaceWith( wrap );
				wrap.append( img );
			}
		}

		wrap.setAttribute( 'contenteditable', 'false' );

		if ( wrap.querySelector( `.${ BULK_IMAGE_REMOVE_CLASS }` ) ) {
			return;
		}

		const button = root.ownerDocument.createElement( 'button' );
		button.type = 'button';
		button.className = BULK_IMAGE_REMOVE_CLASS;
		button.setAttribute( 'aria-label', removeLabel );
		button.tabIndex = 0;
		button.innerHTML =
			'<svg viewBox="0 0 10 10" width="10" height="10" fill="none" aria-hidden="true"><path d="M9 1L1 9M1 1L9 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
		wrap.append( button );
	} );
};

export const applyBulkText = text => {
	const nextRows = [];

	splitBulkLines( stripBulkImageChrome( text ) ).forEach( line => {
		if ( 'heading' === line.type ) {
			const value = sanitizeInlineHtml( line.html );
			if ( ! rowHasContent( value ) ) {
				return;
			}

			nextRows.push(
				createGroupRow( {
					value,
					level: line.level,
				} )
			);
			return;
		}

		const { value, image, extraRows } = extractItemContent( line.html );

		if ( rowHasContent( value ) || image ) {
			nextRows.push( createItemRow( { value, image } ) );
		}

		nextRows.push( ...extraRows );
	} );

	const hasItem = nextRows.some( row => 'item' === row.type );

	return hasItem ? nextRows : [ ...nextRows, createItemRow() ];
};
