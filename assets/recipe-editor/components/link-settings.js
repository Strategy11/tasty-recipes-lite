const LINK_SETTINGS_TEXTAREA_ID = 'tasty-recipes-link-settings-target';

export const openLinkSettingsModal = ( {
	url = '',
	text = '',
	openInNewTab = false,
	nofollow = false,
	isUpdate = false,
	onSubmit = () => {},
} ) => {
	const { wpLink, jQuery: $ } = window;
	if ( ! wpLink || ! $ || ! document.getElementById( 'wp-link-wrap' ) ) {
		return false;
	}

	document.getElementById( LINK_SETTINGS_TEXTAREA_ID )?.remove();

	const textarea = document.createElement( 'textarea' );
	textarea.id = LINK_SETTINGS_TEXTAREA_ID;
	textarea.hidden = true;
	document.body.append( textarea );

	const previousEditorId = window.wpActiveEditor;
	const cleanup = () => {
		$( document ).off( 'wplink-close.tastyRecipes' );
		$( textarea ).off( 'change.tastyRecipes' );
		textarea.remove();
		window.wpActiveEditor = previousEditorId;
	};

	// The nofollow checkbox only exists when Pro is active.
	const $nofollow = $( '#wp-link-nofollow' );

	// wpLink writes the link into the textarea, then fires a jQuery change.
	$( textarea ).on( 'change.tastyRecipes', () => {
		const { href, target } = wpLink.getAttrs();
		onSubmit( {
			url: href,
			text: $( '#wp-link-text' ).val() || '',
			openInNewTab: '_blank' === target,
			nofollow: $nofollow.length ? $nofollow.prop( 'checked' ) : null,
		} );
		cleanup();
	} );
	$( document ).on( 'wplink-close.tastyRecipes', () =>
		window.setTimeout( cleanup, 0 )
	);

	wpLink.open( LINK_SETTINGS_TEXTAREA_ID, url, text );

	$( '#wp-link-url' ).val( url );
	$( '#wp-link-text' ).val( text );
	$( '#wp-link-target' ).prop( 'checked', openInNewTab );
	$nofollow.prop( 'checked', nofollow );
	if ( isUpdate && window.wpLinkL10n?.update ) {
		$( '#wp-link-submit' ).val( window.wpLinkL10n.update );
	}

	return true;
};
