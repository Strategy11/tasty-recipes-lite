import {
	ALLOWED_LINK_ATTRIBUTES,
	escapeHtml,
	isSafeUrl,
	normalizeRowText,
} from './html';

const getImageWrappingLink = img => {
	const link = img?.closest( 'a' );
	if ( ! link ) {
		return null;
	}

	if ( normalizeRowText( link.textContent ) ) {
		return null;
	}

	const href = link.getAttribute( 'href' ) || '';

	if ( ! href || ! isSafeUrl( href ) ) {
		return null;
	}

	const attrs = {};
	Array.from( link.attributes ).forEach( attribute => {
		const name = attribute.name.toLowerCase();
		const isAllowed =
			ALLOWED_LINK_ATTRIBUTES.has( name ) ||
			name.startsWith( 'data-' ) ||
			name.startsWith( 'aria-' );

		if ( isAllowed && attribute.value ) {
			attrs[ name ] = attribute.value;
		}
	} );

	return attrs.href ? attrs : null;
};

export const parseImageFromElement = img => {
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

	const link = getImageWrappingLink( img );

	return {
		id: idMatch ? Number( idMatch[ 1 ] ) : 0,
		url,
		alt: img.getAttribute( 'alt' ) || '',
		width: widthAttr ? Number( widthAttr ) : undefined,
		height: heightAttr ? Number( heightAttr ) : undefined,
		caption: captionEl ? normalizeRowText( captionEl.textContent ) : '',
		...( link ? { link } : {} ),
	};
};

const serializeImageLink = ( image, innerHtml ) => {
	const href = image.link?.href;
	if ( ! href || ! isSafeUrl( href ) ) {
		return innerHtml;
	}

	const attrs = [];
	Object.entries( image.link ).forEach( ( [ name, value ] ) => {
		if ( ! value ) {
			return;
		}

		const isAllowed =
			ALLOWED_LINK_ATTRIBUTES.has( name ) ||
			name.startsWith( 'data-' ) ||
			name.startsWith( 'aria-' );

		if ( isAllowed ) {
			attrs.push( `${ name }="${ escapeHtml( value ) }"` );
		}
	} );

	if ( ! attrs.length ) {
		return innerHtml;
	}

	return `<a ${ attrs.join( ' ' ) }>${ innerHtml }</a>`;
};

export const serializeRowImage = image => {
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
	const captionHtml = caption
		? `<figcaption>${ escapeHtml( caption ) }</figcaption>`
		: '';
	const innerHtml = caption
		? `<figure>${ tag }${ captionHtml }</figure>`
		: tag;

	return serializeImageLink( image, innerHtml );
};

export const createEditorImageHtml = image => serializeRowImage( image );
