// English aliases on top of each field's own (translated) label.
const FIELD_ALIASES = {
	title: [ 'title', 'recipe title', 'recipe name' ],
	video_url: [ 'video url', 'video link', 'video' ],
	author_name: [ 'author', 'recipe by', 'recipe author', 'by' ],
	prep_time: [ 'prep time', 'preparation time', 'prep' ],
	cook_time: [
		'cook time',
		'cooking time',
		'bake time',
		'baking time',
		'cook',
	],
	total_time: [ 'total time', 'ready in', 'total' ],
	yield: [ 'yield', 'yields', 'servings', 'serves', 'makes' ],
	category: [ 'category', 'course' ],
	method: [ 'cooking method', 'method' ],
	cuisine: [ 'cuisine' ],
	diet: [ 'diet' ],
	keywords: [ 'keywords', 'keyword', 'tags' ],
	serving_size: [ 'serving size', 'portion size' ],
	calories: [ 'calories', 'energy', 'kcal' ],
	sugar: [ 'sugar', 'sugars', 'total sugars' ],
	sodium: [ 'sodium' ],
	fat: [ 'total fat', 'fat' ],
	saturated_fat: [ 'saturated fat', 'sat fat', 'saturated' ],
	unsaturated_fat: [ 'unsaturated fat' ],
	trans_fat: [ 'trans fat' ],
	carbohydrates: [
		'total carbohydrates',
		'carbohydrates',
		'carbohydrate',
		'carbs',
	],
	fiber: [ 'dietary fiber', 'fiber', 'fibre' ],
	protein: [ 'protein' ],
	cholesterol: [ 'cholesterol' ],
};

// Labels that only count with a colon, because the bare word also opens a
// section ("Method") or reads like prose ("By the way…", "Total").
const COLON_ONLY_ALIASES = new Set( [
	'by',
	'method',
	'total',
	'cook',
	'prep',
	'title',
] );

// Inside the instructions, "Cook: 10 minutes until golden" is a step, not a
// cook time. Only labels that can't start a step count there.
const isSafeInInstructions = alias =>
	alias.includes( ' ' ) ||
	[
		'video',
		'title',
		'author',
		'category',
		'course',
		'cuisine',
		'diet',
		'keywords',
		'keyword',
		'tags',
	].includes( alias );

// Longest value each field accepts; anything longer is prose, not a value.
const MAX_VALUE_LENGTH = {
	video_url: 500,
	title: 150,
	keywords: 300,
};

// Bare "Serves 4" / "Makes 12 cookies" lines without a colon.
const BARE_YIELD_PATTERN = /^(serves|makes|yields?)\s+(\d.*)$/i;

const escapeRegExp = value => value.replaceAll( /[.*+?^${}()|[\]\\]/g, String.raw`\$&` );

const normalizeLabel = value =>
	String( value || '' )
		.toLowerCase()
		.replace( /:\s*$/, '' )
		.trim();

export const createDetailMatcher = fields => {
	const aliases = [];
	const labels = {};

	Object.keys( fields ).forEach( name => {
		const label = fields[ name ]?.label;

		if ( ! label ) {
			return;
		}

		labels[ name ] = label;

		const names = new Set( [
			normalizeLabel( label ),
			...( FIELD_ALIASES[ name ] || [] ),
		] );

		names.forEach( alias => {
			if ( alias ) {
				aliases.push( { alias, name } );
			}
		} );
	} );

	aliases.sort( ( a, b ) => b.alias.length - a.alias.length );

	const pattern = aliases.length
		? new RegExp(
			`(^|[\\s|•·,;/])(${ aliases
				.map( item => escapeRegExp( item.alias ) )
				.join( '|' ) })\\s*:`,
			'gi'
		)
		: null;

	return { aliases, labels, pattern };
};

const findAlias = ( matcher, value ) => {
	const normalized = normalizeLabel( value );

	return matcher.aliases.find( item => item.alias === normalized );
};

export const getMaxValueLength = name => MAX_VALUE_LENGTH[ name ] || 80;

const cleanValue = value =>
	String( value || '' )
		.replace( /^[\s|•·,;/–—-]+/, '' )
		.replace( /[\s|•·,;/–—-]+$/, '' )
		.trim();

export const parseDetailLine = (
	matcher,
	text,
	{ allowBareValues = false, inInstructions = false } = {}
) => {
	if ( ! matcher.pattern || text.length > 600 ) {
		return null;
	}

	const matches = Array.from( text.matchAll( matcher.pattern ) ).map(
		match => ( {
			found: findAlias( matcher, match[ 2 ] ),
			start: match.index + match[ 1 ].length,
			end: match.index + match[ 0 ].length,
		} )
	);

	if ( matches.length && 0 === matches[ 0 ].start ) {
		// A short value with no sentence ending ("Method: Baking") is still a
		// value, wherever it sits.
		const firstValue = text.slice( matches[ 0 ].end ).trim();
		const isShortValue =
			firstValue.length <= 30 && ! /[.!?]$/.test( firstValue );

		if (
			inInstructions &&
			! isShortValue &&
			! isSafeInInstructions( matches[ 0 ].found?.alias || '' )
		) {
			return null;
		}

		const pairs = matches
			.map( ( match, index ) => ( {
				name: match.found?.name,
				value: cleanValue(
					text.slice( match.end, matches[ index + 1 ]?.start )
				),
			} ) )
			.filter(
				pair =>
					pair.name &&
					pair.value &&
					pair.value.length <= getMaxValueLength( pair.name )
			);

		return pairs.length ? pairs : null;
	}

	if ( inInstructions ) {
		return null;
	}

	const bareYield = text.match( BARE_YIELD_PATTERN );
	if ( bareYield && text.length <= 40 && matcher.labels.yield ) {
		return [ { name: 'yield', value: cleanValue( bareYield[ 2 ] ) } ];
	}

	if ( allowBareValues ) {
		const bare = matcher.aliases.find(
			item =>
				! COLON_ONLY_ALIASES.has( item.alias ) &&
				new RegExp(
					`^${ escapeRegExp( item.alias ) }\\s+([<~]?\\s?\\d.*)$`,
					'i'
				).test( text )
		);

		if ( bare ) {
			return [
				{
					name: bare.name,
					value: cleanValue( text.slice( bare.alias.length ) ),
				},
			];
		}
	}

	return null;
};

export const getBareLabelField = ( matcher, text ) => {
	if ( text.length > 30 ) {
		return null;
	}

	const found = findAlias( matcher, text );

	if ( ! found || COLON_ONLY_ALIASES.has( found.alias ) ) {
		return null;
	}

	return found.name;
};
