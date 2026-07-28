export const postDismissAction = ( action, nonce ) => {
	if ( ! nonce || ! window.ajaxurl ) {
		return;
	}

	const formData = new FormData();
	formData.append( 'action', action );
	formData.append( 'nonce', nonce );

	// eslint-disable-next-line compat/compat
	window.fetch( window.ajaxurl, {
		method: 'POST',
		body: formData,
	} );
};
