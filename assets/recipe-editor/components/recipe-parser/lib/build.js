import { stripItemPrefix } from '../../recipe-row-list/lib/paste';
import {
	createGroupRow,
	createItemRow,
	escapeHtml,
	serializeRows,
} from '../../recipe-row-list/lib/utils';
import { isSubheading } from './sections';

const BARE_STEP_PATTERN = /^step\s*\d{1,3}\s*[:.]?$/i;
const SENTENCE_END_PATTERN = /[.!?:;)"”]$/;
const INGREDIENT_CONTINUES_PATTERN =
	/([,(/–-]|\b(and|or|of|to|with|into|plus))$/i;
const QUANTITY_START_PATTERN = /^[\d½⅓⅔¼¾⅛⅜⅝⅞]/;

const startsLowercase = text => /^[a-z]/.test( text );

const continuesPrevious = ( format, prev, line, context ) => {
	if ( ! prev || prev.isHeading || line.isBullet ) {
		return false;
	}

	if ( 'instructions' === format ) {
		if ( context.hasNumbered ) {
			return ! line.isNumbered && ! line.startsParagraph;
		}

		return (
			! SENTENCE_END_PATTERN.test( prev.text ) &&
			startsLowercase( line.text )
		);
	}

	if ( 'ingredients' === format ) {
		return (
			! line.isNumbered &&
			! QUANTITY_START_PATTERN.test( line.text ) &&
			INGREDIENT_CONTINUES_PATTERN.test( prev.text )
		);
	}

	if ( prev.isBullet || line.startsParagraph ) {
		return false;
	}

	return context.hasBlankLines || ! SENTENCE_END_PATTERN.test( prev.text );
};

export const mergeSectionLines = ( format, lines, isPlainText ) => {
	const isList = 'ingredients' === format || 'instructions' === format;
	const context = {
		hasNumbered: lines.some( line => line.isNumbered ),
		hasBlankLines: lines.slice( 1 ).some( line => line.startsParagraph ),
	};
	const items = [];
	let forceNewItem = false;

	lines.forEach( line => {
		if ( 'instructions' === format && BARE_STEP_PATTERN.test( line.text ) ) {
			forceNewItem = true;
			return;
		}

		if ( isSubheading( line ) ) {
			items.push( {
				text: line.text.replace( /:\s*$/, '' ),
				isHeading: true,
			} );
			return;
		}

		const text = isList || line.isBullet || line.isOrdered
			? stripItemPrefix( line.text )
			: line.text;

		if ( ! text ) {
			return;
		}

		const prev = items[ items.length - 1 ];

		if (
			isPlainText &&
			! forceNewItem &&
			continuesPrevious( format, prev, line, context )
		) {
			prev.text = `${ prev.text } ${ text }`;
			return;
		}

		forceNewItem = false;
		items.push( {
			text,
			isHeading: false,
			isBullet: line.isBullet && ! line.isOrdered,
			isOrdered: line.isOrdered,
		} );
	} );

	return items;
};

const buildRichHtml = items => {
	const parts = [];
	let list = null;

	const flushList = () => {
		if ( list ) {
			parts.push(
				`<${ list.tag }>${ list.items
					.map( text => `<li>${ text }</li>` )
					.join( '' ) }</${ list.tag }>`
			);
			list = null;
		}
	};

	items.forEach( item => {
		const text = escapeHtml( item.text );

		if ( item.isBullet || item.isOrdered ) {
			const tag = item.isOrdered ? 'ol' : 'ul';
			if ( list?.tag !== tag ) {
				flushList();
				list = { tag, items: [] };
			}
			list.items.push( text );
			return;
		}

		flushList();
		parts.push(
			item.isHeading ? `<p><strong>${ text }</strong></p>` : `<p>${ text }</p>`
		);
	} );

	flushList();

	return parts.join( '' );
};

export const buildFieldHtml = ( format, items ) => {
	if ( 'ingredients' === format || 'instructions' === format ) {
		const rows = items.map( item =>
			item.isHeading
				? createGroupRow( { value: escapeHtml( item.text ) } )
				: createItemRow( { value: escapeHtml( item.text ) } )
		);

		return serializeRows( rows, format );
	}

	return buildRichHtml( items );
};
