import { ROW_VISIBILITY_PADDING_PX } from './constants';

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

const isApplePlatform = () => {
	const platform = navigator.platform || '';
	const userAgent = navigator.userAgent || '';

	return (
		/Mac|iPhone|iPod|iPad/.test( platform ) || /Mac OS X/.test( userAgent )
	);
};

export const getOptionKeySymbol = () =>
	isApplePlatform() ? '⌥' : 'Alt';

export const getModifierKeySymbol = () =>
	isApplePlatform() ? '⌘' : 'Ctrl';

const ALLOWED_INLINE_TAGS = new Set( [
	'b',
	'strong',
	'i',
	'em',
	'u',
	'a',
] );

const UNSUPPORTED_ROW_CONTENT_SELECTOR = [
	'iframe',
	'video',
	'audio',
	'object',
	'embed',
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

const parseImageFromElement = img => {
	if ( ! img ) {
		return null;
	}

	const url = img.getAttribute( 'src' ) || '';
	if ( ! url ) {
		return null;
	}

	const className = img.getAttribute( 'class' ) || '';
	const idMatch = className.match( /wp-image-(\d+)/ );
	const widthAttr = img.getAttribute( 'width' );
	const heightAttr = img.getAttribute( 'height' );

	return {
		id: idMatch ? Number( idMatch[ 1 ] ) : 0,
		url,
		alt: img.getAttribute( 'alt' ) || '',
		width: widthAttr ? Number( widthAttr ) : undefined,
		height: heightAttr ? Number( heightAttr ) : undefined,
	};
};

export const extractItemContent = html => {
	const root = document.createElement( 'div' );
	root.innerHTML = String( html || '' );

	const img =
		root.querySelector( 'img' ) ||
		root.querySelector( 'figure img' ) ||
		null;
	const image = parseImageFromElement( img );

	if ( img ) {
		const figure = img.closest( 'figure' );
		( figure || img ).remove();
	}

	root.querySelectorAll( 'picture, source' ).forEach( node => node.remove() );

	// Drop leftover empty wrappers and trailing breaks left by image removal.
	Array.from( root.querySelectorAll( 'br' ) )
		.reverse()
		.forEach( br => {
			if ( ! br.nextSibling ) {
				br.remove();
			}
		} );

	return {
		value: sanitizeInlineHtml( root.innerHTML ),
		image,
	};
};

const serializeRowImage = image => {
	if ( ! image?.url ) {
		return '';
	}

	const attrs = [
		`src="${ escapeHtml( image.url ) }"`,
		`alt="${ escapeHtml( image.alt || '' ) }"`,
	];

	if ( image.width ) {
		attrs.push( `width="${ Number( image.width ) }"` );
	}
	if ( image.height ) {
		attrs.push( `height="${ Number( image.height ) }"` );
	}

	const classes = [ 'alignnone', 'size-full' ];
	if ( image.id ) {
		classes.push( `wp-image-${ Number( image.id ) }` );
	}
	attrs.push( `class="${ classes.join( ' ' ) }"` );

	return `<img ${ attrs.join( ' ' ) } />`;
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
			const text = escapeHtml( stripHtml( row.value ) );
			if ( ! text ) {
				return;
			}
			flushItems();
			parts.push( `<h4>${ text }</h4>` );
			return;
		}

		const html = sanitizeInlineHtml( row.value );
		const imageHtml = serializeRowImage( row.image );
		if ( ! rowHasContent( html ) && ! imageHtml ) {
			return;
		}

		if ( rowHasContent( html ) && imageHtml ) {
			pendingItems.push( `${ html }<br />${ imageHtml }` );
			return;
		}

		pendingItems.push( html || imageHtml );
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
			const { value, image } = extractItemContent( valueHtml );
			if ( rowHasContent( value ) || image ) {
				rows.push( createItemRow( { value, image } ) );
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
