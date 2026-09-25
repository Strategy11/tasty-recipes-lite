import {
	ALLOWED_LINK_ATTRIBUTES,
	escapeHtml,
	isSafeUrl,
	normalizeRowText,
	parseFragment,
} from './html';
import { parseRelTokens } from './link';

const getImageWrappingLink = img => {
	const link = img?.closest( 'a' );
	if ( ! link ) {
		return null;
	}

	const linkContent = link.cloneNode( true );
	linkContent
		.querySelectorAll( 'figcaption, button' )
		.forEach( node => node.remove() );

	if ( normalizeRowText( linkContent.textContent ) ) {
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

export const toImageDetailsMetadata = image => ( {
	attachment_id: image.id || false,
	url: image.url,
	alt: image.alt || '',
	caption: image.caption || '',
	width: image.width,
	height: image.height,
	customWidth: image.width,
	customHeight: image.height,
	align: 'none',
	linkUrl: image.link?.href || '',
	linkTargetBlank: '_blank' === image.link?.target,
	linkRel: image.link?.rel || '',
	linkClassName: image.link?.class || '',
} );

const getImageDetailsLink = ( imageData, previousLink = {} ) => {
	const href = imageData.linkUrl || '';

	if ( 'none' === imageData.link || ! href || ! isSafeUrl( href ) ) {
		return null;
	}

	const link = Object.fromEntries(
		Object.entries( previousLink ).filter(
			( [ name ] ) => ! [ 'href', 'target', 'rel', 'class' ].includes( name )
		)
	);
	const relTokens = parseRelTokens( imageData.linkRel );

	link.href = href;

	if ( imageData.linkTargetBlank ) {
		link.target = '_blank';

		if ( ! relTokens.includes( 'noopener' ) ) {
			relTokens.push( 'noopener' );
		}
	}

	if ( relTokens.length ) {
		link.rel = relTokens.join( ' ' );
	}

	if ( imageData.linkClassName ) {
		link.class = imageData.linkClassName;
	}

	return link;
};

export const fromImageDetailsData = ( imageData, previous = {} ) => {
	const isCustomSize = 'custom' === imageData.size;
	const width = Number(
		isCustomSize ? imageData.customWidth : imageData.width
	);
	const height = Number(
		isCustomSize ? imageData.customHeight : imageData.height
	);
	const link = getImageDetailsLink( imageData, previous.link );

	return {
		id: Number( imageData.attachment_id ) || 0,
		url: imageData.url || previous.url || '',
		alt: imageData.alt || '',
		width: width || undefined,
		height: height || undefined,
		caption: normalizeRowText(
			parseFragment( imageData.caption ).textContent
		),
		...( link ? { link } : {} ),
	};
};
