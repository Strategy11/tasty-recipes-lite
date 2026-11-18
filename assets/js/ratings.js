/* global tastyRecipesRating */

window.TastyRecipes = window.TastyRecipes || {};

window.TastyRecipes.staticTooltip = {
	element: null,
	tooltipElement: null,
	deleting: false,
	init( element ) {
		if ( this.deleting ) {
			return;
		}
		this.element = element;
		this.buildElements();
	},
	destroy() {
		if ( ! this.tooltipElement || this.deleting ) {
			return;
		}

		this.deleting = true;
		this.tooltipElement.classList.remove( 'opened' );

		setTimeout( () => {
			this.tooltipElement.remove();
			this.deleting = false;
		}, 500 );
	},
	buildElements() {
		const tooltipElement = document.createElement( 'div' );
		tooltipElement.classList.add( 'tasty-recipes-static-tooltip');
		tooltipElement.setAttribute( 'id', 'tasty-recipes-tooltip' );

		const currentTooltipElement = document.getElementById( 'tasty-recipes-tooltip' );
		if ( currentTooltipElement ) {
			document.body.replaceChild( tooltipElement, currentTooltipElement );
		} else {
			document.body.appendChild( tooltipElement );
		}

		this.tooltipElement = document.getElementById( 'tasty-recipes-tooltip' );
	},
	show() {
		if ( ! this.tooltipElement ) {
			return;
		}

		const tooltipTop = this.element.getBoundingClientRect().top
			+ window.scrollY
			- 10 // 10px offset.
			- this.tooltipElement.getBoundingClientRect().height;
		const tooltipLeft = this.element.getBoundingClientRect().left
			- ( this.tooltipElement.getBoundingClientRect().width / 2 )
			+ ( this.element.getBoundingClientRect().width / 2 ) - 1;
		const posLeft = Math.max( 10, tooltipLeft );
		this.maybeRemoveTail( posLeft !== tooltipLeft );

		this.tooltipElement.setAttribute( 'style', 'top:' + tooltipTop + 'px;left:' + posLeft + 'px;' );
		this.tooltipElement.classList.add( 'opened' );

	},
	maybeRemoveTail( removeTail ) {
		if ( removeTail ) {
			this.tooltipElement.classList.add( 'tr-hide-tail' );
		} else {
			this.tooltipElement.classList.remove( 'tr-hide-tail' );
		}
	},
	changeMessage( message ) {
		if ( ! this.tooltipElement ) {
			return;
		}
		this.tooltipElement.innerHTML = message;
	}
};

window.TastyRecipes.ajax = {
	sendPostRequest( url, data, success, failure ) {
		const xhr = new XMLHttpRequest();
		xhr.open( 'POST', url, true );
		xhr.send( this.preparePostData( data ) );

		xhr.onreadystatechange = () => {
			if ( 4 !== xhr.readyState ) {
				return;
			}
			if ( xhr.status === 200 ) {
				success( JSON.parse( xhr.responseText ) );
				return;
			}

			failure( xhr );
		};

		xhr.onerror = () => {
			failure( xhr );
		};
	},
	preparePostData( data ) {
		const formData = new FormData();

		for ( const key in data ) {
			formData.append( key, data[key] );
		}
		return formData;
	},
};

window.TastyRecipes.ratings = {
	defaultRating: 0,
	currentRatingPercentage: 100,
	savingRating: false,
	init( minRating ) {
		this.minRating = minRating;

		this.formWatchRating();
		this.closeTooltipWhenClickOutside();
		this.addBodyClassBasedOnSelectedRating();
		this.backwardCompFormRatingPosition();
	},
	formWatchRating() {
		const ratings = document.querySelectorAll('.tasty-recipes-no-ratings-buttons [data-rating]');
		if ( ratings.length <= 0 ) {
			return;
		}
		for ( const rating of ratings ) {
			rating.addEventListener( 'click', event => {
				event.preventDefault();
				this.defaultRating = event.target.closest( '.checked' ).dataset.rating;
				this.setCheckedStar( event.target );
				this.maybeSendRating( this.defaultRating, event.target );
				this.setRatingInForm( this.defaultRating );
			} );
		}
	},
	closeTooltipWhenClickOutside() {
		window.addEventListener( 'click', e => {
			// Bailout (don't remove the tooltip) when the clicked element is a rating star, or it's the tooltip itself.
			if ( e.target.closest( '.tasty-recipes-rating' ) || e.target.classList.contains( 'tasty-recipes-static-tooltip' ) ) {
				return;
			}

			window.TastyRecipes.staticTooltip.destroy();
		} );
	},
 	setRatingInForm( rating ) {
		const ratingInput = document.querySelector( '#respond .tasty-recipes-rating[value="' + rating + '"]' );
		if ( ! ratingInput ) {
			return;
		}
		ratingInput.click();
	},
	addBodyClassBasedOnSelectedRating() {
		const ratingInputs = document.querySelectorAll( 'input.tasty-recipes-rating' );
		if ( ! ratingInputs ) {
			return;
		}
		for ( const ratingInput of ratingInputs ) {
			ratingInput.addEventListener( 'click', currentEvent => {
				const selectedRating = currentEvent.target.getAttribute( 'value' );
				this.handleBodyClassByRating( selectedRating );
				this.toggleCommentTextareaRequired( selectedRating );
			} );
		}
	},
	handleBodyClassByRating( rating ) {
		if ( rating < this.minRating ) {
			document.body.classList.remove( 'tasty-recipes-selected-minimum-rating' );
			return;
		}
		document.body.classList.add( 'tasty-recipes-selected-minimum-rating' );
	},
	toggleCommentTextareaRequired( rating ) {
		const commentTextarea = document.getElementById( 'comment' );
		if ( ! commentTextarea ) {
			return;
		}

		if ( rating < this.minRating ) {
			commentTextarea.setAttribute( 'required', '' );
			return;
		}

		commentTextarea.removeAttribute( 'required' );
	},
	maybeSendRating( rating, element ) {
		if ( this.savingRating === rating ) {
			return;
		}

		this.savingRating = rating;
		window.TastyRecipes.staticTooltip.init( element );

		const recipeCardElement = element.closest( '.tasty-recipes' );
		if ( ! recipeCardElement ) {
			window.TastyRecipes.staticTooltip.destroy();
			return;
		}

		window.TastyRecipes.ajax.sendPostRequest(
			window.trCommon.ajaxurl,
			{
				action: 'tasty_recipes_save_rating',
				rating,
				nonce: window.trCommon.ratingNonce,
				post_id: window.trCommon.postId,
				recipe_id: recipeCardElement.dataset.trId,
			},
			( response ) => {
				window.TastyRecipes.staticTooltip.changeMessage( response.data.message );
				window.TastyRecipes.staticTooltip.show();
				this.updateAverageText( response.data, recipeCardElement );
				this.maybeFillCommentForm( response.data );

				// Hide the tooltip after 5 seconds.
				setTimeout( () => {
					this.maybeResetTooltip( recipeCardElement, response.data, rating );
				}, 5000 );
			},
			() => {
				this.resetTooltip( recipeCardElement );
			}
		);
	},
	updateAverageText( data, recipeCardElement ) {
		if ( ! data.average ) {
			return;
		}
		this.setRatingPercent( data );

		if ( ! data.count ) {
			return;
		}

		const quickLink = document.querySelector( '.tasty-recipes-rating-link' );
		if ( quickLink ) {
			this.setTextInContainer( quickLink, data );
			this.setPartialStar( quickLink );
		}

		const cardStars = recipeCardElement.querySelector( '.tasty-recipes-ratings-buttons' );
		cardStars.dataset.trDefaultRating = data.average;
		this.setTextInContainer( recipeCardElement.querySelector( '.tasty-recipes-rating' ), data );
	},
	setTextInContainer( container, data ) {
		if ( ! container ) {
			return;
		}

		if ( data.label ) {
			const ratingLabelElement = container.querySelector( '.rating-label' );
			if ( ratingLabelElement ) {
				ratingLabelElement.innerHTML = data.label;
			}
			return;
		}

		const averageElement = container.querySelector( '.average' );
		if ( averageElement ) {
			averageElement.textContent = data.average;
		}

		const countElement = container.querySelector( '.count' );
		if ( countElement ) {
			countElement.textContent = data.count;
		}
	},
	setPartialStar( container ) {
		const highestStar = container.querySelector( '[data-rating="' + Math.ceil( this.defaultRating ) + '"]' );
		if ( highestStar ) {
			highestStar.dataset.trClip = this.currentRatingPercentage;
		}
	},
	setRatingPercent( data ) {
		this.defaultRating = data.average.toFixed( 1 );
		const parts = data.average.toFixed( 2 ).toString().split( '.' );
		this.currentRatingPercentage = parts[1] ? parts[1] : 100;
		if ( this.currentRatingPercentage === '00' ) {
			this.currentRatingPercentage = 100;
		}
	},
	setCheckedStar( target ) {
		const cardRatingContainer = target.closest( '.tasty-recipes-ratings-buttons' );
		const selectedRatingElement = cardRatingContainer.querySelector( '[data-tr-checked]' );
		if ( selectedRatingElement ) {
			delete selectedRatingElement.dataset.trChecked;
		}

		const thisStar = target.closest( '.tasty-recipes-rating' );
		thisStar.dataset.trChecked = 1;
		thisStar.querySelector( '[data-tr-clip]' ).dataset.trClip = 100;
	},
	maybeFillCommentForm( data ) {
		if ( ! data.comment || ! data.comment.content ) {
			return;
		}

		const commentForm = document.querySelector( '#commentform' );
		if ( ! commentForm ) {
			return;
		}

		const commentBox = commentForm.querySelector( '[name=comment]' );
		if ( ! commentBox || commentBox.value ) {
			return;
		}

		// Add comment details for editing.
		commentBox.innerHTML = data.comment.content;
	},
	maybeResetTooltip( recipeCardElement, data, rating ) {
		if ( this.savingRating === rating ) {
			this.resetTooltip( recipeCardElement, data );
		}
	},
	resetTooltip( recipeCardElement, data ) {
		window.TastyRecipes.staticTooltip.destroy();
		this.savingRating = false;

		// Reset the default rating.
		const cardRatingContainer = recipeCardElement.querySelector( '.tasty-recipes-ratings-buttons' );
		if ( cardRatingContainer ) {
			this.defaultRating = ( data && data.average ) ? data.average.toFixed(1) : cardRatingContainer.dataset.trDefaultRating;
			cardRatingContainer.dataset.trDefaultRating = this.defaultRating;

			this.resetSelectedStar( cardRatingContainer, data );
		}
	},
	resetSelectedStar( cardRatingContainer ) {
		const selectedRatingElement = cardRatingContainer.querySelector( '[data-rating="' + Math.ceil( this.defaultRating ) + '"]' );
		if ( selectedRatingElement ) {
			selectedRatingElement.querySelector( '[data-tr-clip]' ).dataset.trClip = this.currentRatingPercentage;
			selectedRatingElement.parentNode.dataset.trChecked = 1;
		}

		const previousSelectedElement= cardRatingContainer.querySelector( '[data-tr-checked]' );
		if ( previousSelectedElement ) {
			const currentSelectedRating = previousSelectedElement.querySelector('[data-rating]');
			if ( currentSelectedRating !== selectedRatingElement ) {
				delete previousSelectedElement.dataset.trChecked;
			}
		}
	},
	backwardCompFormRatingPosition() {
		const ratingsButtons = document.querySelector( '#respond .tasty-recipes-ratings-buttons, #tasty-recipes-comment-rating .tasty-recipes-ratings-buttons' );
		if ( ! ratingsButtons ) {
			return;
		}
		const ratingsButtonsStyles = window.getComputedStyle(ratingsButtons);
		if ( ! ratingsButtonsStyles.display.includes( 'flex' ) ) {
			ratingsButtons.style.direction = 'rtl';
		}

		if ( typeof tastyRecipesRating !== 'undefined' ) {
			// Select the rating that was previously selected in admin.
			ratingsButtons.querySelector( '.tasty-recipes-rating[value="' + tastyRecipesRating + '"]' ).checked = true;
		}

		const ratingSpans = ratingsButtons.querySelectorAll( '.tasty-recipes-rating' );
		for (const ratingSpan of ratingSpans) {
			ratingSpan.addEventListener( 'click', event => {
				if ( ratingSpan === event.target ) {
					return;
				}
				ratingSpan.previousElementSibling.click();
			} );
		}
	}
};

(function(callback) {
	if (document.readyState !== "loading") {
		callback();
	} else {
		window.addEventListener( 'load', callback );
	}
})(() => {
	window.TastyRecipes.ratings.init( window.trCommon ? window.trCommon.minRating : 6 );
});
