import { applyFilters } from '@wordpress/hooks';

export const getDefaultColors = template => {
	const isSnap = template === 'snap';

	const defaultColors = {
		primary_color: isSnap ? '#000000' : '#FFFFFF',
		secondary_color: '#FFFFFF',
		icon_color: '#000000',
		button_color: isSnap ? '#000000' : '#F9F9F9',
		button_text_color: isSnap ? '#FFFFFF' : '#AAAAAA',
		detail_label_color: '#000000',
		detail_value_color: '#000000',
		h2_color: '#000000',
		h3_color: '#000000',
		body_color: '#000000',
		star_color: '#F2B955',
	};

	return applyFilters(
		'tasty_recipes_default_template_colors',
		defaultColors,
		template
	);
};
