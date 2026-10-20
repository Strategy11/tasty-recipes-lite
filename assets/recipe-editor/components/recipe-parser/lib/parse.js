import {
	createDetailMatcher,
	getBareLabelField,
	getMaxValueLength,
	parseDetailLine,
} from './details';
import { getPastedLines } from './lines';
import { buildFieldHtml, mergeSectionLines } from './build';
import { isSubheading, matchSectionHeading } from './sections';

export const UNSORTED = 'unsorted';

const SENTENCE_END_PATTERN = /[.!?]$/;
const INLINE_SECTION_PATTERN = /^([^:]{2,40}):\s*(\S.*)$/;
const GENERIC_RICH_HEADING_PATTERN =
	/^((recipe|cook('|’)?s|chef('|’)?s|kitchen|short)\s+)?(notes?|tips?|description|summary|introduction|intro)\s*:?$/i;
const QUANTITY_PATTERN =
	/^([\d½⅓⅔¼¾⅛⅜⅝⅞]|(a|an|one|two|three|four|half|pinch|dash|handful|few)\b)/i;

const endsSection = ( chunk, line ) =>
	line.isHeading &&
	line.headingLevel > 0 &&
	chunk.headingLevel > 0 &&
	line.headingLevel <= chunk.headingLevel &&
	! /(:\s*$|^for the\b)/i.test( line.text );

// Dropdown fields only keep values they list, so map "baking" or "Baking"
// onto the option's key and drop anything that matches no option.
const matchOption = ( options, value ) => {
	const wanted = String( value ).trim().toLowerCase();

	return (
		Object.keys( options ).find(
			key =>
				key.toLowerCase() === wanted ||
				String( options[ key ] ).toLowerCase() === wanted
		) || ''
	);
};

const looksLikeTitle = line =>
	line.text.length <= 100 &&
	! SENTENCE_END_PATTERN.test( line.text ) &&
	! line.isNumbered &&
	! line.isBullet;

const isIngredientLike = line =>
	line.text.length <= 100 &&
	! SENTENCE_END_PATTERN.test( line.text ) &&
	( line.isBullet || QUANTITY_PATTERN.test( line.text ) );

const sortByShape = ( chunks, create ) => {
	const lines = chunks.flatMap( chunk => [
		...( chunk.heading
			? [ { text: chunk.heading, isHeading: true } ]
			: [] ),
		...chunk.lines,
	] );

	const start = lines.findIndex(
		( line, index ) =>
			isIngredientLike( line ) &&
			lines[ index + 1 ] &&
			isIngredientLike( lines[ index + 1 ] )
	);

	if ( start < 0 ) {
		return chunks;
	}

	let end = start;
	while (
		end < lines.length &&
		( isIngredientLike( lines[ end ] ) || isSubheading( lines[ end ] ) )
	) {
		end++;
	}

	return [
		create( 'description', lines.slice( 0, start ) ),
		create( 'ingredients', lines.slice( start, end ) ),
		create( 'instructions', lines.slice( end ) ),
	].filter( chunk => chunk.lines.length );
};

export const parseRecipe = ( input, { sections, detailFields } ) => {
	const { lines, isPlainText } = getPastedLines( input );
	const matcher = createDetailMatcher( detailFields );
	const details = {};
	const chunks = [];
	let title = '';
	let current = null;
	let inNutrition = false;
	let pendingField = null;
	let nextId = 0;

	const createChunk = ( kind, chunkLines = [], heading = '', level = 0 ) => ( {
		id: `chunk-${ nextId++ }`,
		kind,
		heading,
		headingLevel: level,
		lines: chunkLines,
	} );

	const openChunk = ( kind, heading = '', level = 0 ) => {
		current = createChunk( kind, [], heading, level );
		chunks.push( current );
	};

	const setDetail = ( name, value ) => {
		const options = detailFields[ name ]?.options;
		const optionValue = options ? matchOption( options, value ) : value;

		if ( optionValue && ! ( name in details ) ) {
			details[ name ] = optionValue;
		}
	};

	lines.forEach( ( line, index ) => {
		const section = matchSectionHeading( sections, line.text );

		if ( pendingField ) {
			const field = pendingField;
			pendingField = null;

			if ( ! section ) {
				setDetail( field, line.text );
				return;
			}
		}

		if ( 'nutrition' === section ) {
			inNutrition = true;
			current = null;
			return;
		}

		if ( section ) {
			inNutrition = false;

			if ( current?.kind === section ) {
				current.lines.push( { ...line, isHeading: true } );
				return;
			}

			openChunk( section, line.text, line.headingLevel );
			return;
		}

		if ( current && endsSection( current, line ) ) {
			openChunk( UNSORTED, line.text, line.headingLevel );
			return;
		}

		const inInstructions = ! inNutrition && 'instructions' === current?.kind;
		const pairs = parseDetailLine( matcher, line.text, {
			allowBareValues: inNutrition,
			inInstructions,
		} );

		if ( pairs ) {
			pairs.forEach( pair => setDetail( pair.name, pair.value ) );
			return;
		}

		if ( ! inInstructions ) {
			const bareField = getBareLabelField( matcher, line.text );
			const next = lines[ index + 1 ]?.text || '';
			if (
				bareField &&
				next.length <= Math.max( 40, getMaxValueLength( bareField ) )
			) {
				pendingField = bareField;
				return;
			}
		}

		// "Notes: Mix them all together" opens the section and keeps its text.
		const inline = line.text.match( INLINE_SECTION_PATTERN );
		const inlineSection =
			inline && matchSectionHeading( sections, inline[ 1 ] );
		if ( inlineSection && 'nutrition' !== inlineSection ) {
			inNutrition = false;
			if ( current?.kind !== inlineSection ) {
				openChunk( inlineSection, inline[ 1 ] );
			}
			current.lines.push( { ...line, text: inline[ 2 ], isHeading: false } );
			return;
		}

		// Lines in a nutrition panel that are not values ("Amount per serving").
		if ( inNutrition ) {
			return;
		}

		if (
			! title &&
			! ( 'title' in details ) &&
			! chunks.length &&
			lines.length > 1 &&
			looksLikeTitle( line )
		) {
			title = line.text;
			return;
		}

		if ( ! current ) {
			openChunk( chunks.length ? UNSORTED : 'description' );
		}

		// A heading the parser does not recognize, outside the list sections,
		// starts a block the author routes by hand in the review step.
		if (
			line.isHeading &&
			[ 'description', UNSORTED ].includes( current.kind )
		) {
			openChunk( UNSORTED, line.text, line.headingLevel );
			return;
		}

		current.lines.push( line );
	} );

	const hasListSections = chunks.some( chunk =>
		[ 'ingredients', 'instructions' ].includes( chunk.kind )
	);
	const sortedChunks = hasListSections
		? chunks
		: sortByShape( chunks, createChunk );

	return {
		title,
		details,
		isPlainText,
		chunks: sortedChunks.filter(
			chunk => chunk.lines.length || chunk.heading
		),
	};
};

export const buildParsedRecipe = ( parsed, { sections } ) => {
	const values = { ...parsed.details };

	if ( parsed.title && ! values.title ) {
		values.title = parsed.title;
	}

	sections.forEach( section => {
		const chunkLines = parsed.chunks
			.filter( chunk => chunk.kind === section.name )
			.flatMap( chunk => getChunkLines( chunk, section ) );

		if ( ! chunkLines.length ) {
			return;
		}

		const items = mergeSectionLines(
			section.format,
			chunkLines,
			parsed.isPlainText
		);
		const html = buildFieldHtml( section.format, items );

		if ( html ) {
			values[ section.name ] = html;
		}
	} );

	return values;
};

export const getChunkLines = ( chunk, section ) => {
	const namesField =
		chunk.kind === section.name &&
		( 'rich' !== section.format ||
			GENERIC_RICH_HEADING_PATTERN.test( chunk.heading ) );

	if ( ! chunk.heading || namesField ) {
		return chunk.lines;
	}

	return [ { text: chunk.heading, isHeading: true }, ...chunk.lines ];
};
