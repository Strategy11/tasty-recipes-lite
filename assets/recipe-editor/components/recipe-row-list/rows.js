import { getRecipeEditorScrollContainer } from '../dom';
import { DEFAULT_GROUP_LEVEL, ROW_VISIBILITY_PADDING_PX } from './constants';
import {
	escapeHtml,
	isBr,
	isSoftBreakSpan,
	normalizeRowText,
	parseFragment,
} from './html';
import { parseImageFromElement, serializeRowImage } from './images';
import { rowHasContent, sanitizeInlineHtml } from './sanitize';
import {
	collapseSoftBreaks,
	encodeSoftBreaks,
	lastMeaningfulChild,
	protectSoftBreaks,
	stripInsignificantBreakWhitespace,
} from './soft-breaks';

let rowIdCounter = 0;

export const createRowId = () => {
	rowIdCounter += 1;

	return `tasty-recipe-row-${ rowIdCounter }`;
};

export const createItemRow = ( {
	isNew = false,
	value = '',
	image = null,
} = {} ) => ( {
	id: createRowId(),
	type: 'item',
	value,
	image,
	isNew,
} );

export const createGroupRow = ( {
	isNew = false,
	value = '',
	level = DEFAULT_GROUP_LEVEL,
} = {} ) => ( {
	id: createRowId(),
	type: 'group',
	value,
	level,
	isNew,
} );

export const ensureRowVisible = ( list, rowId ) => {
	const rowEl = list?.querySelector(
		`.tasty-recipes-item-row[data-row-id="${ rowId }"]`
	);
	const scrollBody = getRecipeEditorScrollContainer();

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

export const getGroupLevel = level => {
	const parsed = Number( level );

	if ( ! parsed || parsed < 1 || parsed > 6 ) {
		return DEFAULT_GROUP_LEVEL;
	}

	return Math.trunc( parsed );
};

export const extractItemContent = html => {
	const root = parseFragment( protectSoftBreaks( html ) );
	collapseSoftBreaks( root );
	stripInsignificantBreakWhitespace( root );

	const extraRows = [];

	// Sub-lists cannot be nested in the row list, so every nested item becomes
	// its own row instead of being flattened into the parent row.
	Array.from( root.querySelectorAll( 'ul, ol' ) )
		.filter( list => ! list.parentElement?.closest( 'ul, ol' ) )
		.forEach( list => {
			Array.from( list.children ).forEach( child => {
				if ( 'LI' !== child.tagName ) {
					return;
				}

				const nested = extractItemContent( child.innerHTML );

				if ( rowHasContent( nested.value ) || nested.image ) {
					extraRows.push(
						createItemRow( {
							value: nested.value,
							image: nested.image,
						} )
					);
				}
				extraRows.push( ...nested.extraRows );
			} );

			list.remove();
		} );

	const images = Array.from( root.querySelectorAll( 'img' ) )
		.map( parseImageFromElement )
		.filter( Boolean );

	Array.from( root.querySelectorAll( 'img' ) ).forEach( img => {
		const figure = img.closest( 'figure' );
		( figure || img ).remove();
	} );

	root.querySelectorAll( 'picture, source' ).forEach( node => node.remove() );

	// Drop leftover empty wrappers left by image removal.
	Array.from( root.querySelectorAll( 'a' ) )
		.reverse()
		.forEach( link => {
			if (
				normalizeRowText( link.textContent ) ||
				link.querySelector( 'img, figure, picture, br' ) ||
				! link.getAttribute( 'href' )
			) {
				return;
			}

			link.remove();
		} );

	if ( images.length ) {
		const last = lastMeaningfulChild( root );
		if ( isBr( last ) || isSoftBreakSpan( last ) ) {
			last.remove();
		}
	}

	images.slice( 1 ).forEach( image => {
		extraRows.push( createItemRow( { image } ) );
	} );

	return {
		value: sanitizeInlineHtml( root.innerHTML ),
		image: images[ 0 ] || null,
		extraRows,
	};
};

export const serializeRows = ( rows, mode = 'ingredients' ) => {
	const listTag = 'instructions' === mode ? 'ol' : 'ul';
	const parts = [];
	let pendingItems = [];

	const flushItems = () => {
		if ( ! pendingItems.length ) {
			return;
		}

		parts.push(
			`<${ listTag }>${ pendingItems
				.map( html => `<li>${ html }</li>` )
				.join( '' ) }</${ listTag }>`
		);
		pendingItems = [];
	};

	rows.forEach( row => {
		if ( 'group' === row.type ) {
			const heading = sanitizeInlineHtml( row.value );
			if ( ! rowHasContent( heading ) ) {
				return;
			}

			flushItems();

			const level = getGroupLevel( row.level );
			parts.push( `<h${ level }>${ heading }</h${ level }>` );

			return;
		}

		const html = encodeSoftBreaks( sanitizeInlineHtml( row.value ) );
		const imageHtml = serializeRowImage( row.image );
		const hasText = rowHasContent( html );

		if ( ! hasText && ! imageHtml ) {
			return;
		}

		if ( hasText && imageHtml ) {
			pendingItems.push( `${ html }<br />${ imageHtml }` );
			return;
		}

		pendingItems.push( hasText ? html : imageHtml );
	} );

	flushItems();

	return parts.join( '' );
};

export const parseRowsFromHtml = html => {
	const empty = () => [ createItemRow() ];

	if ( ! html || ! String( html ).trim() ) {
		return empty();
	}

	try {
		const root = parseFragment( protectSoftBreaks( html ) );

		const rows = [];

		const pushItemHtml = valueHtml => {
			const { value, image, extraRows } = extractItemContent( valueHtml );

			if ( rowHasContent( value ) || image ) {
				rows.push( createItemRow( { value, image } ) );
			}

			rows.push( ...extraRows );
		};

		const pushItemText = value => {
			const text = normalizeRowText( value );

			if ( text ) {
				rows.push( createItemRow( { value: escapeHtml( text ) } ) );
			}
		};

		const pushGroup = ( valueHtml, level ) => {
			const value = sanitizeInlineHtml( valueHtml );

			if ( rowHasContent( value ) ) {
				rows.push( createGroupRow( { value, level } ) );
			}
		};

		const splitBlockIntoRows = node => {
			const holder = node.ownerDocument.createElement( 'div' );
			const flushHolder = () => {
				if (
					rowHasContent( holder.innerHTML ) ||
					holder.querySelector( 'img, figure, picture' )
				) {
					pushItemHtml( holder.innerHTML );
				}

				holder.replaceChildren();
			};

			Array.from( node.childNodes ).forEach( child => {
				if (
					Node.ELEMENT_NODE === child.nodeType &&
					'BR' === child.tagName
				) {
					flushHolder();
					return;
				}

				if ( Node.TEXT_NODE === child.nodeType ) {
					const lines = String( child.textContent || '' ).split(
						/\r?\n/
					);
					lines.forEach( ( line, index ) => {
						if ( index > 0 ) {
							flushHolder();
						}

						if ( line ) {
							holder.append(
								node.ownerDocument.createTextNode( line )
							);
						}
					} );
					return;
				}

				holder.append( child.cloneNode( true ) );
			} );

			flushHolder();
		};

		const walk = node => {
			if ( ! node ) {
				return;
			}

			if ( Node.TEXT_NODE === node.nodeType ) {
				String( node.textContent || '' )
					.split( /\r?\n/ )
					.forEach( line => pushItemText( line ) );
				return;
			}

			if ( Node.ELEMENT_NODE !== node.nodeType ) {
				return;
			}

			const tag = node.tagName.toLowerCase();

			if ( /^h[1-6]$/.test( tag ) ) {
				pushGroup( node.innerHTML, Number( tag.slice( 1 ) ) );
				return;
			}

			if ( 'li' === tag ) {
				pushItemHtml( node.innerHTML );
				return;
			}

			if ( 'img' === tag || 'figure' === tag || 'picture' === tag ) {
				pushItemHtml( node.outerHTML );
				return;
			}

			if (
				'a' === tag &&
				node.querySelector( 'img, figure, picture' ) &&
				! rowHasContent( node.innerHTML )
			) {
				pushItemHtml( node.outerHTML );
				return;
			}

			if ( 'p' === tag || 'div' === tag ) {
				if (
					! node.querySelector( 'ul,ol,li,h1,h2,h3,h4,h5,h6,p' )
				) {
					splitBlockIntoRows( node );
					return;
				}
			}

			if ( 'br' === tag ) {
				return;
			}

			if ( 'ul' === tag || 'ol' === tag ) {
				Array.from( node.children ).forEach( child => {
					if ( 'LI' === child.tagName ) {
						pushItemHtml( child.innerHTML );
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
				.replaceAll( /<br\s*\/?>/gi, '\n' )
				.replaceAll( /<\/p>/gi, '\n' )
				.replaceAll( /<[^>]+>/g, '' );
			fallback
				.split( /\r?\n/ )
				.map( normalizeRowText )
				.filter( Boolean )
				.forEach( line => pushItemText( line ) );
		}

		return rows.length ? rows : empty();
	} catch ( error ) {
		return empty();
	}
};

export const getCaretSplitHtml = el => {
	const view = el?.ownerDocument?.defaultView;
	const selection = view?.getSelection();

	if ( ! el || ! selection?.rangeCount ) {
		return null;
	}

	const range = selection.getRangeAt( 0 );

	if ( ! el.contains( range.commonAncestorContainer ) ) {
		return null;
	}

	const toHtml = fragmentRange => {
		const holder = el.ownerDocument.createElement( 'div' );
		holder.append( fragmentRange.cloneContents() );

		return sanitizeInlineHtml( holder.innerHTML );
	};

	const before = range.cloneRange();
	before.selectNodeContents( el );
	before.setEnd( range.startContainer, range.startOffset );

	const after = range.cloneRange();
	after.selectNodeContents( el );
	after.setStart( range.endContainer, range.endOffset );

	return {
		before: toHtml( before ),
		after: toHtml( after ),
	};
};

export const moveRow = ( rows, fromIndex, toIndex ) => {
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

	return next.map( row => ( {
		...row,
		isNew: false,
	} ) );
};
