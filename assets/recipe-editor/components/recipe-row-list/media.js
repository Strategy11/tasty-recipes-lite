import { __ } from '@wordpress/i18n';

export const openImageMediaLibrary = ( {
	title = __( 'Media Library', 'tasty-recipes-lite' ),
	buttonText = __( 'Select Image', 'tasty-recipes-lite' ),
	onSelect = () => {},
} = {} ) => {
	if ( ! window.wp?.media ) {
		return;
	}

	const frame = window.wp.media( {
		title,
		multiple: false,
		library: {
			type: 'image',
		},
		button: {
			text: buttonText,
		},
	} );

	frame.on( 'select', () => {
		const attachments = frame.state().get( 'selection' ).toJSON();
		if ( ! attachments.length ) {
			return;
		}

		const attachment = attachments[ 0 ];
		const size =
			attachment.sizes?.large ||
			attachment.sizes?.full ||
			attachment.sizes?.medium ||
			null;

		onSelect( {
			id: attachment.id || 0,
			url: size?.url || attachment.url || '',
			alt: attachment.alt || '',
			width: size?.width || attachment.width || undefined,
			height: size?.height || attachment.height || undefined,
		} );
	} );

	frame.open();
};
