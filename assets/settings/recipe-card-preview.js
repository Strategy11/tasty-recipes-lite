const recipeCard = document.querySelector( '.tasty-recipes' );

const onWindowResize = function () {
	if ( ! recipeCard ) {
		return;
	}

	const styles = window.getComputedStyle( recipeCard );
	const height =
		recipeCard.offsetHeight +
		parseInt( styles.marginTop ) +
		parseInt( styles.marginBottom );

	const finalHeight = height + 50;

	window.parent.postMessage(
		JSON.stringify( {
			event: 'setRecipeCardSize',
			height: finalHeight,
		} ),
		'*'
	);
};

const resizeAfterIframeLoad = function () {
	if ( ! recipeCard ) {
		return;
	}

	const iframes = recipeCard.getElementsByTagName( 'iframe' );

	if ( 0 === iframes.length ) {
		return;
	}

	Array.from( iframes ).forEach( ( iframe ) => {
		iframe.onload = onWindowResize;
	} );
};

window.addEventListener( 'DOMContentLoaded', onWindowResize );
onWindowResize();
resizeAfterIframeLoad();

window.addEventListener( 'message', ( event ) => {
	if (
		window.location.origin !== event.origin ||
		typeof event.data !== 'string'
	) {
		return;
	}
	const payload = JSON.parse( event.data );
	switch ( payload.event ) {
		case 'updateCustomization':
			const settings = payload.data;

			if ( ! settings.star_color ) {
				settings.star_color = settings.detail_value_color
					? settings.detail_value_color
					: '#F2B955';
			}

			document
				.querySelectorAll( '[data-tasty-recipes-customization]' )
				.forEach( ( el ) => {
					const customizations = el
						.getAttribute( 'data-tasty-recipes-customization' )
						.split( ' ' );
					customizations.forEach( ( customization ) => {
						if ( -1 === customization.indexOf( '.' ) ) {
							return;
						}
						const bits = customization.split( '.' );
						const settingsName = bits[ 0 ].replace( /-/g, '_' );
						if ( typeof settings[ settingsName ] === 'undefined' ) {
							return;
						}
						if ( 'innerText' === bits[ 1 ] ) {
							el.innerText = settings[ settingsName ];
							return;
						}
						if ( 'innerHTML' === bits[ 1 ] ) {
							el.innerHTML = settings[ settingsName ];
							return;
						}

						if ( settings[ settingsName ].length ) {
							el.style.setProperty(
								bits[ 1 ],
								settings[ settingsName ]
							);
						} else {
							el.style.removeProperty( bits[ 1 ] );
						}
					} );
				} );
			onWindowResize();
			break;
	}
} );
