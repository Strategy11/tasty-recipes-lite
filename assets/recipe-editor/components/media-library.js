import { __ } from '@wordpress/i18n';

export const getMediaImage = ( attachment, sizeNames = [] ) => {
	const size = sizeNames
		.map( sizeName => attachment.sizes?.[ sizeName ] )
		.find( Boolean );

	return {
		id: attachment.id || 0,
		url: size?.url || attachment.url || '',
		alt: attachment.alt || '',
		width: size?.width || attachment.width || undefined,
		height: size?.height || attachment.height || undefined,
	};
};

export const openImageMediaLibrary = ( {
	title = __( 'Media Library', 'tasty-recipes-lite' ),
	buttonText = __( 'Select Image', 'tasty-recipes-lite' ),
	selectedId = 0,
	onSelect = () => {},
} = {} ) => {
	if ( ! window.wp?.media ) {
		return false;
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

	if ( selectedId ) {
		frame.on( 'open', () => {
			const attachment = window.wp.media.model.Attachment.get( selectedId );
			attachment.fetch();
			frame.state().get( 'selection' ).add( attachment );
		} );
	}

	frame.on( 'select', () => {
		const [ attachment ] = frame.state().get( 'selection' ).toJSON();
		if ( attachment ) {
			onSelect( attachment );
		}
	} );

	frame.open();
	return true;
};
