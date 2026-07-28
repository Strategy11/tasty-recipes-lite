import { getRecipeEditorScrollContainer } from '../dom';
import { DEFAULT_GROUP_LEVEL, ROW_VISIBILITY_PADDING_PX } from './constants';

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

const ALLOWED_INLINE_TAGS = new Set( [
	'a',
	'b',
	'br',
	'code',
	'del',
	'em',
	'i',
	's',
	'strong',
	'sub',
	'sup',
	'u',
] );

const ALLOWED_LINK_ATTRIBUTES = new Set( [
	'class',
	'href',
	'id',
	'name',
	'rel',
	'rev',
	'target',
	'title',
] );

const DANGEROUS_URL_SCHEME = /^(?:javascript|data|vbscript):/i;

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

// Bare provider URLs are auto-embedded on the front end and carry video
// settings the row list cannot expose, so they stay on the classic editor.
const EMBEDDABLE_URL_PATTERN = /(?:youtube\.com|youtu\.be|vimeo\.com)/i;

// DOMParser documents have no browsing context, so image and iframe sources
// are never fetched and error handlers in stored markup cannot run.
const parseFragment = html => {
	const doc = new window.DOMParser().parseFromString(
		String( html || '' ),
		'text/html'
	);
	return doc.body;
};

export const isSafeUrl = value => {
	const compact = String( value || '' ).replaceAll(
		/[\s\u0000-\u001F\u007F]/g,
		''
	);
	return ! DANGEROUS_URL_SCHEME.test( compact );
};

export const hasUnsupportedRowContent = html => {
	if ( ! html || ! String( html ).trim() ) {
		return false;
	}

	const value = String( html );
	const root = parseFragment( value );

	if ( root.querySelector( UNSUPPORTED_ROW_CONTENT_SELECTOR ) ) {
		return true;
	}

	// Shortcodes / embeds that are not plain list markup.
	if ( /\[[^\]]*(video|embed|playlist|audio|caption)[^\]]*\]/i.test( value ) ) {
		return true;
	}

	return EMBEDDABLE_URL_PATTERN.test( value );
};

export const escapeHtml = value =>
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
	const root = parseFragment( value );
	root.querySelectorAll( 'br' ).forEach( br =>
		br.replaceWith( root.ownerDocument.createTextNode( ' ' ) )
	);
	return normalizeRowText( root.textContent );
};

export const sanitizeInlineHtml = html => {
	const root = parseFragment( html );
	const doc = root.ownerDocument;

	const wrapWith = ( tagName, content ) => {
		const el = doc.createElement( tagName );
		el.append( content );
		return el;
	};

	const unwrap = child => {
		const fragment = doc.createDocumentFragment();
		while ( child.firstChild ) {
			fragment.append( child.firstChild );
		}
		child.replaceWith( fragment );
	};

	// Links keep their original href (relative, anchor, mailto, tel) so legacy
	// content survives a round trip; only dangerous schemes are unwrapped.
	const sanitizeLink = child => {
		const href = child.getAttribute( 'href' );

		Array.from( child.attributes ).forEach( attribute => {
			const name = attribute.name.toLowerCase();
			const isAllowed =
				ALLOWED_LINK_ATTRIBUTES.has( name ) ||
				name.startsWith( 'data-' ) ||
				name.startsWith( 'aria-' );

			if ( ! isAllowed ) {
				child.removeAttribute( attribute.name );
			}
		} );

		if ( href && ! isSafeUrl( href ) ) {
			unwrap( child );
		}
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

			if ( ALLOWED_INLINE_TAGS.has( tag ) ) {
				if ( 'a' === tag ) {
					sanitizeLink( child );
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

			const fragment = doc.createDocumentFragment();
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
	const figure = img.closest( 'figure' );
	const captionEl =
		figure && 1 === figure.querySelectorAll( 'img' ).length
			? figure.querySelector( 'figcaption' )
			: null;

	return {
		id: idMatch ? Number( idMatch[ 1 ] ) : 0,
		url,
		alt: img.getAttribute( 'alt' ) || '',
		width: widthAttr ? Number( widthAttr ) : undefined,
		height: heightAttr ? Number( heightAttr ) : undefined,
		caption: captionEl ? normalizeRowText( captionEl.textContent ) : '',
	};
};

export const extractItemContent = html => {
	const root = parseFragment( html );
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

	// Drop leftover empty wrappers and trailing breaks left by image removal.
	Array.from( root.querySelectorAll( 'br' ) )
		.reverse()
		.forEach( br => {
			if ( ! br.nextSibling ) {
				br.remove();
			}
		} );

	// A row holds a single image, so the rest become image-only rows.
	images.slice( 1 ).forEach( image => {
		extraRows.push( createItemRow( { image } ) );
	} );

	return {
		value: sanitizeInlineHtml( root.innerHTML ),
		image: images[ 0 ] || null,
		extraRows,
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

	const tag = `<img ${ attrs.join( ' ' ) } />`;
	const caption = normalizeRowText( image.caption || '' );
	if ( ! caption ) {
		return tag;
	}

	return `<figure>${ tag }<figcaption>${ escapeHtml(
		caption
	) }</figcaption></figure>`;
};

const getGroupLevel = level => {
	const parsed = Number( level );
	if ( ! parsed || parsed < 1 || parsed > 6 ) {
		return DEFAULT_GROUP_LEVEL;
	}

	return Math.trunc( parsed );
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
		const root = parseFragment( html );

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
				pushGroup( node.innerHTML, Number( tag.slice( 1 ) ) );
				return;
			}

			if ( 'li' === tag ) {
				pushItemHtml( node.innerHTML );
				return;
			}

			// Standalone media outside a list still deserves its own row.
			if ( 'img' === tag || 'figure' === tag || 'picture' === tag ) {
				pushItemHtml( node.outerHTML );
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

// Splits the caret position of a contenteditable row into leading/trailing
// HTML so Enter can start a new row without losing inline formatting.
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
