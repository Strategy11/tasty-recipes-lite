import { parseFragment } from './html';
import { rowHasContent, sanitizeInlineHtml } from './sanitize';

const LIST_TAGS = new Set( [ 'UL', 'OL' ] );
const MEDIA_SELECTOR = 'img, figure, picture, source';

export const isListElement = node =>
	!! node &&
	Node.ELEMENT_NODE === node.nodeType &&
	LIST_TAGS.has( node.tagName );

export const getTopLevelLists = root =>
	Array.from( root.querySelectorAll( 'ul, ol' ) ).filter(
		list => ! list.parentElement?.closest( 'ul, ol' )
	);

const collectListItems = list =>
	Array.from( list.children ).flatMap( child => {
		if ( 'LI' !== child.tagName ) {
			return [];
		}

		const item = child.cloneNode( true );
		const nestedItems = getTopLevelLists( item ).flatMap( nested => {
			nested.remove();
			return collectListItems( nested );
		} );

		item.querySelectorAll( MEDIA_SELECTOR ).forEach( node =>
			node.remove()
		);

		return [ item.innerHTML, ...nestedItems ];
	} );

export const splitNestedList = html => {
	const root = parseFragment( html );
	const lists = getTopLevelLists( root );

	if ( ! lists.length ) {
		return { inline: root.innerHTML, list: null };
	}

	const tag = lists[ 0 ].tagName.toLowerCase();
	const items = lists.flatMap( list => {
		list.remove();
		return collectListItems( list );
	} );

	return {
		inline: root.innerHTML,
		list: items.length ? { tag, items } : null,
	};
};

export const joinNestedList = ( inline, list ) => {
	if ( ! list?.items?.length ) {
		return inline;
	}

	const items = list.items.map( item => `<li>${ item }</li>` ).join( '' );

	return `${ inline }<${ list.tag }>${ items }</${ list.tag }>`;
};

export const mapNestedList = ( html, transform ) => {
	const { inline, list } = splitNestedList( html );
	const items = ( list?.items || [] )
		.map( transform )
		.filter( rowHasContent );

	return joinNestedList(
		transform( inline ),
		items.length ? { tag: list.tag, items } : null
	);
};

export const sanitizeItemHtml = html =>
	mapNestedList( html, sanitizeInlineHtml );
