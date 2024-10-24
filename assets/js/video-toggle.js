(function() {
	var buttons = document.querySelectorAll('button[name="tasty-recipes-video-toggle"]');
	if ( ! buttons ) {
		return;
	}
	buttons.forEach(function(button){
		button.addEventListener('click', function(event) {
			var container = event.target.closest('.tasty-recipes-instructions');
			/* Backwards compat for our other template. */
			if ( ! container ) {
				container = event.target.closest('.tasty-recipe-instructions');
			}
			if ( ! container ) {
				return;
			}
			var wasChecked = 'true' === button.getAttribute('aria-checked');
			button.setAttribute('aria-checked', wasChecked ? 'false' : 'true');
			if ( wasChecked ) {
				container.classList.add('tasty-recipes-hide-video');
			} else {
				container.classList.remove('tasty-recipes-hide-video');
			}
		});
	});
}());
