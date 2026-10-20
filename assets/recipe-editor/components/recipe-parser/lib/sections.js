import { __ } from '@wordpress/i18n';

export const getDefaultSections = () => [
	{
		name: 'description',
		label: __( 'Description', 'tasty-recipes-lite' ),
		format: 'rich',
		patterns: [
			/^((short|recipe) )?(description|summary)$/,
			/^(introduction|intro|about( this| the)? recipe)$/,
		],
	},
	{
		name: 'ingredients',
		label: __( 'Ingredients', 'tasty-recipes-lite' ),
		format: 'ingredients',
		patterns: [
			/^ingredients?\b.{0,30}$/,
			/^(what you('|’)?ll need|you('|’)?ll need|you will need|shopping list)$/,
		],
	},
	{
		name: 'instructions',
		label: __( 'Instructions', 'tasty-recipes-lite' ),
		format: 'instructions',
		patterns: [
			/^(instructions?|directions?)\b.{0,30}$/,
			/^(method|steps|preparation|how to make( it| this)?|to make)$/,
		],
	},
	{
		name: 'notes',
		label: __( 'Notes', 'tasty-recipes-lite' ),
		format: 'rich',
		patterns: [
			/^((recipe|cook('|’)?s|chef('|’)?s|kitchen)\s+)?(notes?|tips?)$/,
			/^(tips (and|&) tricks|variations?|substitutions?|storage( (and|&) reheating)?|make[- ]ahead.{0,20}|to store|serving suggestions?)$/,
		],
	},
];

// Nutrition is not a field of its own: its lines feed the nutrition details.
const NUTRITION_PATTERN =
	/^nutrition(al)?( facts| information| info)?( \(?per serving\)?)?$/;

const normalizeHeading = text =>
	String( text || '' )
		.toLowerCase()
		.replace( /^#+\s*/, '' )
		.replace( /[:.]\s*$/, '' )
		.replaceAll( /\s+/g, ' ' )
		.trim();

export const matchSectionHeading = ( sections, text ) => {
	if ( text.length > 50 ) {
		return null;
	}

	const heading = normalizeHeading( text );

	if ( NUTRITION_PATTERN.test( heading ) ) {
		return 'nutrition';
	}

	const section = sections.find( item =>
		( item.patterns || [] ).some( pattern => pattern.test( heading ) )
	);

	return section ? section.name : null;
};

export const isSubheading = line => {
	if ( line.isNumbered || line.text.length > 60 ) {
		return false;
	}

	return (
		line.isHeading ||
		/:\s*$/.test( line.text ) ||
		/^for the\b[^.]{0,35}$/i.test( line.text )
	);
};
