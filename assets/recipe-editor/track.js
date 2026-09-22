export const trackUsage = ( event, properties = {} ) => {
	if ( 'function' !== typeof window.tastyRecipesTrackEvent ) {
		return;
	}

	window.tastyRecipesTrackEvent( event, properties );
};
