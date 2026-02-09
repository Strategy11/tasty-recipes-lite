window.TastyRecipes = window.TastyRecipes || {};

window.TastyRecipes.smoothScroll = {
	scrollDuration: 800,
	animationId: null,

	init() {
		document.addEventListener( 'click', ( e ) => {
			let anchor = e.target;
			if ( anchor.tagName !== 'A' ) {
				anchor = anchor.closest( 'a.tasty-recipes-scrollto' );
			}

			if ( ! anchor || ! anchor.classList.contains( 'tasty-recipes-scrollto' ) ) {
				return;
			}

			const elementHref = anchor.getAttribute( 'href' );
			if ( ! elementHref ) {
				return;
			}

			e.preventDefault();
			this.goToSelector( elementHref );
		} );
	},

	easeOutCubic( t ) {
		return 1 - Math.pow( 1 - t, 3 );
	},

	cancelScroll() {
		if ( this.animationId ) {
			cancelAnimationFrame( this.animationId );
			this.animationId = null;
		}
	},

	goToSelector( selector ) {
		const element = document.querySelector( selector );
		if ( ! element ) {
			return;
		}
		this.scrollToElement( element );
	},

	// Uses requestAnimationFrame to recalculate target position each frame,
	// adapting to layout shifts from dynamically loaded content like ads.
	scrollToElement( element ) {
		this.cancelScroll();

		const startY = window.scrollY;
		const startTime = performance.now();

		const cancelEvents = [ 'wheel', 'touchstart' ];
		const onUserScroll = () => {
			this.cancelScroll();
			cancelEvents.forEach( ( evt ) =>
				window.removeEventListener( evt, onUserScroll )
			);
		};
		cancelEvents.forEach( ( evt ) =>
			window.addEventListener( evt, onUserScroll, { passive: true, once: true } )
		);

		const step = ( currentTime ) => {
			const elapsed = currentTime - startTime;
			const progress = Math.min( elapsed / this.scrollDuration, 1 );
			const eased = this.easeOutCubic( progress );

			const targetY = window.scrollY + element.getBoundingClientRect().top;
			const newY = startY + ( targetY - startY ) * eased;

			window.scrollTo( 0, newY );

			if ( progress < 1 ) {
				this.animationId = requestAnimationFrame( step );
			} else {
				this.animationId = null;

				const finalOffset = element.getBoundingClientRect().top;
				if ( Math.abs( finalOffset ) > 1 ) {
					window.scrollTo( 0, window.scrollY + finalOffset );
				}

				cancelEvents.forEach( ( evt ) =>
					window.removeEventListener( evt, onUserScroll )
				);
			}
		};

		this.animationId = requestAnimationFrame( step );
	}
};

document.addEventListener(
	'DOMContentLoaded',
	() => window.TastyRecipes.smoothScroll.init()
);
