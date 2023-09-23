window.TastyRecipes = window.TastyRecipes || {};
window.TastyRecipes.ratings = {
	init( min_rating ) {
		this.min_rating = min_rating;

		this.recipeRatingReflectInRespondSection();
		this.addBodyClassBasedOnSelectedRating();
		this.backwardCompRespondRatingPosition();
	},
	recipeRatingReflectInRespondSection() {
		const ratings = document.querySelectorAll('.tasty-recipes-no-ratings-buttons [data-rating]');
		if (ratings.length <= 0) {
			return;
		}
		for (const rating of ratings) {
			rating.addEventListener( 'click', event => {
				this.fireRatingInRespond( event.target.dataset.rating );
			} );
		}
	},
	fireRatingInRespond( rating ) {
		const ratingInput = document.querySelector('.tasty-recipes-rating[value="' + rating + '"]');
		if ( ! ratingInput ) {
			return;
		}
		ratingInput.click();
	},
	addBodyClassBasedOnSelectedRating() {
		const ratingInputs = document.querySelectorAll('input.tasty-recipes-rating');
		if ( ratingInputs.length <= 0 ) {
			return;
		}
		for ( const ratingInput of ratingInputs ) {
			ratingInput.addEventListener( 'click', event => {
				let selectedRating = event.target.getAttribute( 'value' );
				this.handleBodyClassByRating( selectedRating );
				this._toggle_comment_textarea_required( selectedRating );
			} );
		}
	},
	handleBodyClassByRating( rating ) {
		if ( rating < this.min_rating ) {
			document.body.classList.remove( 'tasty-recipes-selected-minimum-rating' );
			return;
		}
		document.body.classList.add( 'tasty-recipes-selected-minimum-rating' );
	},
	_toggle_comment_textarea_required( rating ) {
		const comment_textarea = document.getElementById( 'comment' );
		if ( 0 >= comment_textarea.length ) {
			return;
		}

		if ( rating < this.min_rating ) {
			comment_textarea.setAttribute( 'required', '' );
			return;
		}

		comment_textarea.removeAttribute('required');
	},
	backwardCompRespondRatingPosition() {
		const ratings_buttons = document.querySelector( '#respond .tasty-recipes-ratings-buttons' );
		if ( ratings_buttons.length <= 0 ) {
			return;
		}
		const ratings_buttons_styles = window.getComputedStyle(ratings_buttons);
		if ( 'flex' !== ratings_buttons_styles.display ) {
			ratings_buttons.style.direction = 'rtl';
		}

		const rating_spans = ratings_buttons.querySelectorAll( '.tasty-recipes-rating' );
		for (const rating_span of rating_spans) {
			rating_span.addEventListener( 'click', event => {
				if ( rating_span === event.target ) {
					return;
				}
				rating_span.previousElementSibling.click();
			} );
		}
	}
};

(function(callback) {
	if (document.readyState !== "loading") {
		callback();
	} else {
		document.addEventListener("DOMContentLoaded", callback);
	}
})(() => {
	window.TastyRecipes.ratings.init( window.tasty_recipes_min_rating );
});
