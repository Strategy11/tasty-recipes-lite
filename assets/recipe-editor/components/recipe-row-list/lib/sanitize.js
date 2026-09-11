import { applyFilters } from '@wordpress/hooks';
import { SOFT_BREAK_CLASS, ZWSP } from './constants';
import {
	ALLOWED_LINK_ATTRIBUTES,
	isSafeUrl,
	isSoftBreakSpan,
	normalizeRowText,
	parseFragment,
} from './html';

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

const getInlineHtmlAllowlist = () => {
	/**
	 * Filters custom inline nodes kept by the recipe editor sanitizer.
	 *
	 * @since x.x
	 *
	 * @param {Object}   allowlist          Default allowlist.
	 * @param {Object[]} allowlist.elements Custom elements to keep.
	 * @param {string[]} allowlist.comments Comment node values to keep.
	 */
	const allowlist = applyFilters( 'tasty_recipes_inline_html_allowlist', {
		elements: [],
		comments: [],
	} );

	return {
		elements: Array.isArray( allowlist?.elements )
			? allowlist.elements
			: [],
		comments: Array.isArray( allowlist?.comments )
			? allowlist.comments
			: [],
	};
};

const getCustomElementRule = ( node, elements ) => {
	const classes = String( node.getAttribute( 'class' ) || '' ).split( /\s+/ );

	return elements.find( rule => {
		const ruleTag = String( rule?.tag || '' ).toUpperCase();

		if ( ruleTag && ruleTag !== node.tagName ) {
			return false;
		}

		return ! rule?.className || classes.includes( rule.className );
	} );
};

const sanitizeCustomElement = ( child, rule ) => {
	const allowed = new Set(
		( rule.attributes || [] ).map( name => String( name ).toLowerCase() )
	);

	Array.from( child.attributes ).forEach( attribute => {
		const name = attribute.name.toLowerCase();
		const isAllowed =
			allowed.has( name ) ||
			( true === rule.allowData && name.startsWith( 'data-' ) ) ||
			( true === rule.allowAria && name.startsWith( 'aria-' ) );

		if ( ! isAllowed ) {
			child.removeAttribute( attribute.name );
		}
	} );
};

export const sanitizeInlineHtml = html => {
	const root = parseFragment( html );
	const doc = root.ownerDocument;
	const allowlist = getInlineHtmlAllowlist();
	const allowedComments = new Set( allowlist.comments );

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

			if ( Node.COMMENT_NODE === child.nodeType ) {
				const value = String( child.nodeValue || '' ).trim();

				if ( ! allowedComments.has( value ) ) {
					child.remove();
				}
				return;
			}

			if ( Node.ELEMENT_NODE !== child.nodeType ) {
				child.remove();
				return;
			}

			sanitize( child );

			if ( isSoftBreakSpan( child ) ) {
				Array.from( child.attributes ).forEach( attribute => {
					if ( 'class' !== attribute.name.toLowerCase() ) {
						child.removeAttribute( attribute.name );
					}
				} );
				child.setAttribute( 'class', SOFT_BREAK_CLASS );

				while ( child.firstChild ) {
					child.firstChild.remove();
				}

				return;
			}

			const customRule = getCustomElementRule(
				child,
				allowlist.elements
			);
			if ( customRule ) {
				sanitizeCustomElement( child, customRule );
				return;
			}

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

export const stripHtml = value => {
	const root = parseFragment( value );

	root.querySelectorAll( `br, span.${ SOFT_BREAK_CLASS }` ).forEach( node =>
		node.replaceWith( root.ownerDocument.createTextNode( ' ' ) )
	);

	return normalizeRowText(
		String( root.textContent || '' ).replaceAll( ZWSP, '' )
	);
};

export const rowHasContent = html => !! stripHtml( html );
