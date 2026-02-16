const recipeCard = document.querySelector( '.tasty-recipes' );

const onWindowResize = function() {
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

const resizeAfterMediaLoad = function() {
	if ( ! recipeCard ) {
		return;
	}

	const iframes = recipeCard.getElementsByTagName( 'iframe' );
	const images = recipeCard.getElementsByTagName( 'img' );

	if ( 0 === iframes.length && 0 === images.length ) {
		return;
	}

	Array.from( iframes ).forEach( iframe => {
		iframe.addEventListener( 'load', onWindowResize );
	} );

	Array.from( images ).forEach( image => {
		image.addEventListener( 'load', onWindowResize );
	} );
};

window.addEventListener( 'DOMContentLoaded', onWindowResize );
onWindowResize();

window.addEventListener( 'DOMContentLoaded', () => {
	resizeAfterMediaLoad();

	const proImage = document.querySelector(
		'.tasty-recipes-pro-template img'
	);

	if ( proImage ) {
		proImage.addEventListener( 'click', () => {
			window.parent.postMessage(
				JSON.stringify( {
					event: 'proImageClicked',
				} ),
				'*'
			);
		} );
	}
} );

window.addEventListener( 'message', event => {
	if (
		window.location.origin !== event.origin ||
		typeof event.data !== 'string'
	) {
		return;
	}
	const payload = JSON.parse( event.data );
	if ( payload.event === 'updateCustomization' ) {
		const settings = payload.data;

		if ( ! settings.star_color ) {
			settings.star_color =
				settings.detail_value_color || '#F2B955';
		}

		document
			.querySelectorAll( '[data-tasty-recipes-customization]' )
			.forEach( el => {
				const customizations = el.dataset.tastyRecipesCustomization
					.split( ' ' );
				customizations.forEach( customization => {
					if ( -1 === customization.indexOf( '.' ) ) {
						return;
					}
					const bits = customization.split( '.' );
					const settingsName = bits[ 0 ].replaceAll( '-', '_' );
					if ( settings[ settingsName ] === undefined ) {
						return;
					}
					if ( 'innerText' === bits[ 1 ] ) {
						el.textContent = settings[ settingsName ];
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
	}
} );
