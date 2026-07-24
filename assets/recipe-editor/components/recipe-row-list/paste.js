const stripItemPrefix = ( value ) =>
	String( value || '' )
		.trim()
		.replace( /^\d{1,3}\s*[.):\-–—]\s+/, '' )
		.replace( /^[\-\*\+•›]\s+/, '' )
		.replace( /^step\s*\d{1,3}\s*[.:\-–—]?\s*/i, '' )
		.trim();

const parsePasteLines = ( text ) => {
	if ( ! text ) {
		return [];
	}

	return text
		.split( /\r?\n/ )
		.map( stripItemPrefix )
		.filter( Boolean );
};

const parsePasteHtml = ( html ) => {
	if ( ! html ) {
		return null;
	}

	try {
		const doc = new window.DOMParser().parseFromString( html, 'text/html' );
		const selector = 'h1,h2,h3,h4,h5,h6,p,li,div,td,th';
		let blocks = Array.from( doc.body.querySelectorAll( selector ) );

		// Keep leaf-most blocks so a wrapper div does not duplicate nested <p>s.
		blocks = blocks.filter(
			( el ) =>
				! blocks.some( ( other ) => other !== el && el.contains( other ) )
		);

		if ( ! blocks.length ) {
			return null;
		}

		const isBoldElement = ( el ) => {
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

		const parts = [];

		blocks.forEach( ( el ) => {
			const text = el.textContent.replace( /\s+/g, ' ' ).trim();
			if ( ! text ) {
				return;
			}

			let isHeading = /^H[1-6]$/.test( el.tagName );
			if ( ! isHeading && text.length <= 60 ) {
				const bold = Array.from(
					el.querySelectorAll( 'b,strong,[style*="font-weight"]' )
				).find( isBoldElement );

				if (
					bold &&
					bold.textContent.replace( /\s+/g, ' ' ).trim() === text
				) {
					isHeading = true;
				}
			}

			parts.push(
				isHeading
					? {
							text: text.replace( /:\s*$/, '' ),
							type: 'heading',
					  }
					: {
							text: stripItemPrefix( text ),
							type: 'item',
					  }
			);
		} );

		const filtered = parts.filter( ( part ) => part.text );
		return filtered.length ? filtered : null;
	} catch ( error ) {
		return null;
	}
};

export const parseClipboardParts = ( text, html ) => {
	const textParts = parsePasteLines( text ).map( ( line ) => ( {
		text: line,
		type: 'item',
	} ) );
	const htmlParts = parsePasteHtml( html );

	// Prefer HTML when it found real structure; otherwise use newline split.
	if ( ! htmlParts || ( htmlParts.length <= 1 && textParts.length > 1 ) ) {
		return textParts;
	}

	return htmlParts;
};
