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

export const ensureRowVisible = rowId => {
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

const ALLOWED_INLINE_TAGS = new Set( [
	'b',
	'strong',
	'i',
	'em',
	'u',
	'a',
] );

const UNSUPPORTED_ROW_CONTENT_SELECTOR = [
	'img',
	'iframe',
	'video',
	'audio',
	'object',
	'embed',
	'figure',
	'picture',
	'source',
	'table',
	'svg',
	'canvas',
].join( ',' );

export const hasUnsupportedRowContent = html => {
	if ( ! html || ! String( html ).trim() ) {
		return false;
	}

	const value = String( html );
	const root = document.createElement( 'div' );
	root.innerHTML = value;

	if ( root.querySelector( UNSUPPORTED_ROW_CONTENT_SELECTOR ) ) {
		return true;
	}

	// Shortcodes / embeds that are not plain list markup.
	if ( /\[[^\]]*(video|embed|playlist|audio|caption)[^\]]*\]/i.test( value ) ) {
		return true;
	}

	return false;
};

const escapeHtml = value =>
	String( value )
		.replaceAll( '&', '&amp;' )
		.replaceAll( '<', '&lt;' )
		.replaceAll( '>', '&gt;' )
		.replaceAll( '"', '&quot;' );

export const normalizeRowText = value =>
	String( value || '' )
		.replaceAll( '\u00A0', ' ' )
		.replaceAll( /\s+/g, ' ' )
		.trim();

export const stripHtml = value => {
	const root = document.createElement( 'div' );
	root.innerHTML = String( value || '' );
	return normalizeRowText( root.textContent );
};

export const sanitizeInlineHtml = html => {
	const root = document.createElement( 'div' );
	root.innerHTML = String( html || '' );

	const wrapWith = ( tagName, content ) => {
		const el = document.createElement( tagName );
		el.append( content );
		return el;
	};

	const sanitize = parent => {
		Array.from( parent.childNodes ).forEach( child => {
			if ( Node.TEXT_NODE === child.nodeType ) {
				return;
			}

			if ( Node.ELEMENT_NODE !== child.nodeType ) {
				child.remove();
				return;
			}

			sanitize( child );

			const tag = child.tagName.toLowerCase();

			if ( 'br' === tag ) {
				child.replaceWith( document.createTextNode( ' ' ) );
				return;
			}

			if ( ALLOWED_INLINE_TAGS.has( tag ) ) {
				if ( 'a' === tag ) {
					const href = child.getAttribute( 'href' ) || '';
					const safe = /^https?:\/\//i.test( href ) ? href : '';
					while ( child.attributes.length ) {
						child.removeAttribute( child.attributes[ 0 ].name );
					}
					if ( ! safe ) {
						const fragment = document.createDocumentFragment();
						while ( child.firstChild ) {
							fragment.append( child.firstChild );
						}
						child.replaceWith( fragment );
						return;
					}
					child.setAttribute( 'href', safe );
					child.setAttribute( 'target', '_blank' );
					child.setAttribute( 'rel', 'noopener noreferrer' );
					return;
				}

				while ( child.attributes.length ) {
					child.removeAttribute( child.attributes[ 0 ].name );
				}
				return;
			}

			const style = child.getAttribute( 'style' ) || '';
			const needsBold = /font-weight:\s*(bold|[6-9]00)/i.test( style );
			const needsItalic = /font-style:\s*italic/i.test( style );
			const needsUnderline = /text-decoration:\s*[^;]*underline/i.test(
				style
			);

			const fragment = document.createDocumentFragment();
			while ( child.firstChild ) {
				fragment.append( child.firstChild );
			}

			let replacement = fragment;
			if ( needsUnderline ) {
				replacement = wrapWith( 'u', replacement );
			}
			if ( needsItalic ) {
				replacement = wrapWith( 'i', replacement );
			}
			if ( needsBold ) {
				replacement = wrapWith( 'b', replacement );
			}

			child.replaceWith( replacement );
		} );
	};

	sanitize( root );
	return root.innerHTML;
};

const rowHasContent = html => !! stripHtml( html );

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
			const text = escapeHtml( stripHtml( row.value ) );
			if ( ! text ) {
				return;
			}
			flushItems();
			parts.push( `<h4>${ text }</h4>` );
			return;
		}

		const html = sanitizeInlineHtml( row.value );
		if ( ! rowHasContent( html ) ) {
			return;
		}

		pendingItems.push( html );
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
		// Detached element is more reliable for HTML fragments than DOMParser
		// with a wrapper (lists/paragraphs from wpautop / TinyMCE).
		const root = document.createElement( 'div' );
		root.innerHTML = String( html );

		const rows = [];
		const pushItemHtml = valueHtml => {
			const cleaned = sanitizeInlineHtml( valueHtml );
			if ( rowHasContent( cleaned ) ) {
				rows.push( createItemRow( { value: cleaned } ) );
			}
		};
		const pushItemText = value => {
			const text = normalizeRowText( value );
			if ( text ) {
				rows.push( createItemRow( { value: escapeHtml( text ) } ) );
			}
		};
		const pushGroup = value => {
			const text = normalizeRowText( value );
			if ( text ) {
				rows.push( createGroupRow( { value: text } ) );
			}
		};

		const walk = node => {
			if ( ! node ) {
				return;
			}

			if ( Node.TEXT_NODE === node.nodeType ) {
				pushItemText( node.textContent );
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
				pushItemHtml( node.innerHTML );
				return;
			}

			if ( 'p' === tag || 'div' === tag ) {
				// Standalone blocks (legacy TinyMCE). Skip empty wrappers.
				if (
					! node.querySelector( 'ul,ol,li,h1,h2,h3,h4,h5,h6,p' )
				) {
					pushItemHtml( node.innerHTML );
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
