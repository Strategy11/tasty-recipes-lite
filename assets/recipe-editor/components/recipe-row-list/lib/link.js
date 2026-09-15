const SECURITY_REL_TOKENS = [ 'noopener', 'noreferrer' ];
const OPTION_REL_TOKENS = [ 'sponsored', 'nofollow' ];

export const isAbsoluteHttpUrl = value => /^https?:\/\//i.test( value );

export const parseRelTokens = rel =>
	String( rel || '' )
		.trim()
		.split( /\s+/ )
		.filter( Boolean );

export const hasRelToken = ( rel, token ) =>
	parseRelTokens( rel ).includes( token );

const uniqueTokens = tokens => {
	const seen = new Set();

	return tokens.filter( token => {
		if ( seen.has( token ) ) {
			return false;
		}

		seen.add( token );
		return true;
	} );
};

export const buildLinkRel = ( {
	existingRel = '',
	sponsored = false,
	nofollow = false,
	isAbsoluteHttp = false,
} = {} ) => {
	const managed = new Set( [ ...SECURITY_REL_TOKENS, ...OPTION_REL_TOKENS ] );
	const tokens = parseRelTokens( existingRel ).filter(
		token => ! managed.has( token )
	);

	if ( isAbsoluteHttp ) {
		tokens.push( ...SECURITY_REL_TOKENS );
	}

	if ( sponsored ) {
		tokens.push( 'sponsored' );
	}

	if ( nofollow ) {
		tokens.push( 'nofollow' );
	}

	return uniqueTokens( tokens ).join( ' ' );
};

export const getLinkAttributes = ( {
	url,
	existingRel = '',
	sponsored = false,
	nofollow = false,
} ) => {
	const isAbsoluteHttp = isAbsoluteHttpUrl( url );
	const rel = buildLinkRel( {
		existingRel,
		sponsored,
		nofollow,
		isAbsoluteHttp,
	} );

	return {
		href: url,
		target: isAbsoluteHttp ? '_blank' : '',
		rel,
	};
};

export const getClosestAnchor = node => {
	const el =
		node && Node.ELEMENT_NODE === node.nodeType
			? node
			: node?.parentElement;

	return el?.closest( 'a' ) || null;
};

export const getAnchorFromRange = range => {
	if ( ! range ) {
		return null;
	}

	const fromAncestor = getClosestAnchor( range.commonAncestorContainer );
	if ( fromAncestor ) {
		return fromAncestor;
	}

	const root = range.commonAncestorContainer;
	const container =
		Node.ELEMENT_NODE === root.nodeType ? root : root.parentElement;
	if ( ! container ) {
		return null;
	}

	const anchors = Array.from( container.querySelectorAll( 'a' ) ).filter(
		anchor => range.intersectsNode( anchor )
	);

	return 1 === anchors.length ? anchors[ 0 ] : null;
};

export const isRangeInsideNode = ( range, node ) => {
	if ( ! range || ! node ) {
		return false;
	}

	return (
		node === range.commonAncestorContainer ||
		node.contains( range.commonAncestorContainer )
	);
};

export const applyAnchorAttributes = ( anchor, attrs ) => {
	if ( ! anchor ) {
		return;
	}

	anchor.setAttribute( 'href', attrs.href );

	if ( attrs.target ) {
		anchor.setAttribute( 'target', attrs.target );
	} else {
		anchor.removeAttribute( 'target' );
	}

	if ( attrs.rel ) {
		anchor.setAttribute( 'rel', attrs.rel );
	} else {
		anchor.removeAttribute( 'rel' );
	}
};
