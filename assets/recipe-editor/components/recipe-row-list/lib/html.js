import { SOFT_BREAK_CLASS } from './constants';

export const ALLOWED_LINK_ATTRIBUTES = new Set( [
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

const EMBEDDABLE_URL_PATTERN = /(?:youtube\.com|youtu\.be|vimeo\.com)/i;

export const parseFragment = html => {
	const doc = new window.DOMParser().parseFromString(
		String( html || '' ),
		'text/html'
	);

	return doc.body;
};

export const isBr = node =>
	!! node &&
	Node.ELEMENT_NODE === node.nodeType &&
	'BR' === node.tagName;

export const isSoftBreakSpan = node =>
	!! node &&
	Node.ELEMENT_NODE === node.nodeType &&
	'SPAN' === node.tagName &&
	node.classList.contains( SOFT_BREAK_CLASS );

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
