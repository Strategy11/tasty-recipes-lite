export const stripItemPrefix = value =>
	String( value || '' )
		.trim()
		.replace( /^\d{1,3}[.)]\s+/, '' )
		// Dash and colon markers only when no number follows, so quantity
		// ranges such as "1 - 2 tsp chili flakes" keep their lower bound.
		.replace( /^\d{1,3}\s*[:\-–—]\s+(?!\d)/, '' )
		.replace( /^[-*+•›·◦▪●]\s+/, '' )
		.replace( /^step\s*\d{1,3}\s*[.:\-–—]?\s*/i, '' )
		.trim();

const parsePasteLines = text => {
	if ( ! text ) {
		return [];
	}

	const parts = [];

	text.split( /\r?\n/ ).forEach( line => {
		const trimmed = String( line || '' ).trim();

		if ( ! trimmed ) {
			return;
		}

		const item = stripItemPrefix( trimmed );

		if ( item ) {
			parts.push( {
				text: item,
				type: 'item',
			} );
		}
	} );

	return parts;
};

const isBoldElement = el => {
	if ( ! el ) {
		return false;
	}

	if ( el.matches( 'b,strong' ) ) {
		return true;
	}

	return /font-weight:\s*(bold|[6-9]00)/i.test(
		el.getAttribute( 'style' ) || ''
	);
};

const collapseWhitespace = value =>
	String( value || '' ).replaceAll( /\s+/g, ' ' ).trim();

export const extractClipboardBlocks = ( html, { splitLineBreaks = false } = {} ) => {
	if ( ! html ) {
		return null;
	}

	try {
		const doc = new window.DOMParser().parseFromString( html, 'text/html' );
		const selector = 'h1,h2,h3,h4,h5,h6,p,li,div,td,th';

		let elements = Array.from( doc.body.querySelectorAll( selector ) );

		// Keep leaf-most blocks so a wrapper div does not duplicate nested <p>s.
		elements = elements.filter(
			el =>
				! elements.some(
					other => other !== el && el.contains( other )
				)
		);

		if ( ! elements.length ) {
			return null;
		}

		const blocks = [];

		elements.forEach( el => {
			const headingLevel = /^H[1-6]$/.test( el.tagName )
				? Number( el.tagName.slice( 1 ) )
				: 0;
			const boldTexts = Array.from(
				el.querySelectorAll( 'b,strong,[style*="font-weight"]' )
			)
				.filter( isBoldElement )
				.map( bold => collapseWhitespace( bold.textContent ) );
			const list = el.closest( 'ul,ol' );
			let parts = [ el.textContent ];

			if ( splitLineBreaks && el.querySelector( 'br' ) ) {
				const copy = el.cloneNode( true );
				copy.querySelectorAll( 'br' ).forEach( br =>
					br.replaceWith( '\n' )
				);
				parts = copy.textContent.split( '\n' );
			}

			parts.forEach( part => {
				const text = collapseWhitespace( part );

				if ( ! text ) {
					return;
				}

				blocks.push( {
					text,
					isHeading:
						headingLevel > 0 ||
						( text.length <= 60 && boldTexts.includes( text ) ),
					headingLevel,
					isListItem: !! el.closest( 'li' ),
					isOrdered: !! list && 'OL' === list.tagName,
				} );
			} );
		} );

		return blocks.length ? blocks : null;
	} catch ( error ) {
		return null;
	}
};

const parsePasteHtml = html => {
	const blocks = extractClipboardBlocks( html );

	if ( ! blocks ) {
		return null;
	}

	const filtered = blocks
		.map( block =>
			block.isHeading
				? {
					text: block.text.replace( /:\s*$/, '' ),
					type: 'heading',
				}
				: {
					text: stripItemPrefix( block.text ),
					type: 'item',
				}
		)
		.filter( part => part.text );

	return filtered.length ? filtered : null;
};

export const parseClipboardParts = ( text, html ) => {
	const textParts = parsePasteLines( text );
	const htmlParts = parsePasteHtml( html );

	// Prefer HTML when it found real structure; otherwise use newline split.
	if ( ! htmlParts || ( htmlParts.length <= 1 && textParts.length > 1 ) ) {
		return textParts;
	}

	return htmlParts;
};
