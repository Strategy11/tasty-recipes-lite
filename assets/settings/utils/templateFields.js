import { __ } from '@wordpress/i18n';

export const fieldLabels = {
	button_color: __( 'Button Color', 'tasty-recipes-lite' ),
	button_text_color: __( 'Button Text Color', 'tasty-recipes-lite' ),
	detail_label_color: __( 'Detail Label Color', 'tasty-recipes-lite' ),
	detail_value_color: __( 'Detail Value Color', 'tasty-recipes-lite' ),
	primary_color: __( 'Primary Color', 'tasty-recipes-lite' ),
	secondary_color: __( 'Secondary Color', 'tasty-recipes-lite' ),
	icon_color: __( 'Icon Color', 'tasty-recipes-lite' ),
};

const fullSettings = [
	'primary_color',
	'secondary_color',
	'icon_color',
	'button_color',
	'button_text_color',
	'detail_label_color',
	'detail_value_color',
];

const v2Settings = [
	'primary_color',
	'icon_color',
	'button_color',
	'button_text_color',
	'detail_label_color',
	'detail_value_color',
];

export const templateFields = {
	default: [
		'button_color',
		'button_text_color',
		'detail_label_color',
		'detail_value_color',
	],
	bold: fullSettings,
	'bold-2-0': v2Settings,
	snap: fullSettings,
	'snap-2-0': v2Settings,
	fresh: fullSettings,
	'fresh-2-0': v2Settings,
	'elegant-2-0': v2Settings,
	simple: [
		'primary_color',
		'secondary_color',
		'button_color',
		'button_text_color',
		'detail_label_color',
		'detail_value_color',
	],
	'modern-compact': [
		'primary_color',
		'secondary_color',
		'button_color',
		'button_text_color',
		'detail_label_color',
		'detail_value_color',
	],
	elegant: [
		'primary_color',
		'button_color',
		'button_text_color',
		'detail_label_color',
		'detail_value_color',
	],
};
