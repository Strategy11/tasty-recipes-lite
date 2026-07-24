import { ROW_VISIBILITY_PADDING_PX } from './constants';

let rowIdCounter = 0;

export const createRowId = () => {
	rowIdCounter += 1;
	return `tasty-recipe-row-${ rowIdCounter }`;
};

export const createItemRow = ( { isNew = false, value = '' } = {} ) => ( {
	id: createRowId(),
	type: 'item',
	value,
	isNew,
} );

export const createGroupRow = ( { isNew = false, value = '' } = {} ) => ( {
	id: createRowId(),
	type: 'group',
	value,
	isNew,
} );

export const ensureRowVisible = ( rowId ) => {
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

export const getOptionKeySymbol = () => {
	const platform = navigator.platform || '';
	const userAgent = navigator.userAgent || '';
	const isApple =
		/Mac|iPhone|iPod|iPad/.test( platform ) ||
		/Mac OS X/.test( userAgent );

	return isApple ? '⌥' : 'Alt';
};

const escapeHtml = ( value ) =>
	String( value )
		.replace( /&/g, '&amp;' )
		.replace( /</g, '&lt;' )
		.replace( />/g, '&gt;' )
		.replace( /"/g, '&quot;' );

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

export const parseRowsFromHtml = ( html ) => {
	const empty = () => [ createItemRow() ];

	if ( ! html || ! String( html ).trim() ) {
		return empty();
	}

	try {
		// Detached element is more reliable for HTML fragments than DOMParser
		// with a wrapper (lists/paragraphs from wpautop / TinyMCE).
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
	return next.map( ( row ) => ( {
		...row,
		isNew: false,
	} ) );
};
