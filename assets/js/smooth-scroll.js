window.TastyRecipes = window.TastyRecipes || {};

window.TastyRecipes.smoothScroll = {
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
		});
	},
	goToSelector( selector ) {
		const element = document.querySelector( selector );
		if ( ! element ) {
			return;
		}
		element.scrollIntoView( { behavior: 'smooth' } );
	}
};

document.addEventListener(
	'DOMContentLoaded',
	() => window.TastyRecipes.smoothScroll.init()
);
