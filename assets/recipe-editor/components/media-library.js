import { __ } from '@wordpress/i18n';

let imageMediaFrame = null;
let selectCallback = () => {};

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

export const uploadImageFile = async file => {
	const nonce =
		window._wpPluploadSettings?.defaults?.multipart_params?._wpnonce;
	if ( ! nonce || ! window.ajaxurl || ! file?.type?.startsWith( 'image/' ) ) {
		return null;
	}

	const formData = new FormData();
	formData.append( 'name', file.name );
	formData.append( 'action', 'upload-attachment' );
	formData.append( '_wpnonce', nonce );
	formData.append( 'async-upload', file );

	try {
		// eslint-disable-next-line compat/compat
		const response = await window.fetch( window.ajaxurl, {
			method: 'POST',
			body: formData,
			credentials: 'same-origin',
		} );
		const json = await response.json();
		if ( ! json?.success || ! json.data ) {
			return null;
		}

		return json.data;
	} catch {
		return null;
	}
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

	selectCallback = onSelect;

	if ( ! imageMediaFrame ) {
		imageMediaFrame = window.wp.media( {
			title,
			multiple: false,
			library: {
				type: 'image',
			},
			button: {
				text: buttonText,
			},
		} );

		imageMediaFrame.on( 'select', () => {
			const [ attachment ] = imageMediaFrame
				.state()
				.get( 'selection' )
				.toJSON();
			if ( attachment ) {
				selectCallback( attachment );
			}
		} );
	}

	imageMediaFrame.off( 'open.tastyRecipes' );
	imageMediaFrame.on( 'open.tastyRecipes', () => {
		const selection = imageMediaFrame.state().get( 'selection' );
		selection.reset();
		if ( selectedId ) {
			const attachment =
				window.wp.media.model.Attachment.get( selectedId );
			attachment.fetch();
			selection.add( attachment );
		}
	} );

	imageMediaFrame.open();
	return true;
};
