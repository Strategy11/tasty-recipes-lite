import { extractClipboardBlocks } from '../../recipe-row-list/lib/paste';

const NUMBERED_PATTERN = /^(\d{1,3}[.)]\s+|step\s*\d{1,3}\b)/i;
const BULLET_PATTERN = /^[-*+•›·◦▪●]\s+/;
const MARKDOWN_HEADING_PATTERN = /^#{1,6}\s+/;

// PDF page numbers. Plain text only: in HTML a bare number is usually a
// value laid out next to its label ("Servings" / "4").
const PAGE_NUMBER_PATTERN = /^(page\s*)?\d{1,3}(\s*(of|\/)\s*\d{1,3})?$/i;

// Buttons copied along with web recipe cards.
const NOISE_PATTERNS = [
	/^(jump to recipe|print( recipe)?|pin( recipe| it)?|save( recipe)?|share|email|rate (this|the) recipe|leave a (review|comment))$/i,
];

const isAllCapsHeading = text =>
	text.length <= 40 &&
	! /^[\d½⅓⅔¼¾⅛⅜⅝⅞]/.test( text ) &&
	/[A-Z]/.test( text ) &&
	text === text.toUpperCase();

const toLine = ( text, extra = {} ) => {
	let value = String( text || '' ).trim();
	let isHeading = !! extra.isHeading;
	let headingLevel = extra.headingLevel || 0;
	const markdown = value.match( MARKDOWN_HEADING_PATTERN );

	if ( markdown ) {
		value = value.slice( markdown[ 0 ].length );
		isHeading = true;
		headingLevel = markdown[ 0 ].trim().length;
	}

	return {
		text: value,
		isHeading: isHeading || isAllCapsHeading( value ),
		headingLevel,
		isListItem: !! extra.isListItem,
		isOrdered: !! extra.isOrdered,
		isNumbered: !! extra.isOrdered || NUMBERED_PATTERN.test( value ),
		isBullet:
			( !! extra.isListItem && ! extra.isOrdered ) ||
			BULLET_PATTERN.test( value ),
		startsParagraph: !! extra.startsParagraph,
	};
};

const isNoise = line =>
	! line.text || NOISE_PATTERNS.some( pattern => pattern.test( line.text ) );

export const getPastedLines = ( { html = '', text = '' } ) => {
	const blocks = extractClipboardBlocks( html, { splitLineBreaks: true } );

	if ( blocks && blocks.length > 1 ) {
		return {
			lines: blocks
				.map( block => toLine( block.text, block ) )
				.filter( line => ! isNoise( line ) ),
			isPlainText: false,
		};
	}

	const lines = [];
	let afterBlank = true;

	String( text || '' )
		.replaceAll( ' ', ' ' )
		.split( /\r?\n/ )
		.forEach( raw => {
			const value = raw.replaceAll( /\s+/g, ' ' ).trim();

			if ( ! value ) {
				afterBlank = true;
				return;
			}

			const line = toLine( value, { startsParagraph: afterBlank } );
			afterBlank = false;

			if ( ! isNoise( line ) && ! PAGE_NUMBER_PATTERN.test( value ) ) {
				lines.push( line );
			}
		} );

	return { lines, isPlainText: true };
};

export const detectPasteSource = html => {
	if ( ! html ) {
		return 'plain_text';
	}

	if ( html.includes( 'docs-internal-guid' ) ) {
		return 'google_docs';
	}

	if (
		html.includes( 'urn:schemas-microsoft-com:office' ) ||
		/class="?Mso/i.test( html )
	) {
		return 'word';
	}

	return 'web';
};
