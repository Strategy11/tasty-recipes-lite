(function(){
	var buttonClass = 'tasty-recipes-scale-button',
		buttonActiveClass = 'tasty-recipes-scale-button-active',
		buttons = document.querySelectorAll('.tasty-recipes-scale-button');
	if ( ! buttons ) {
		return;
	}

	buttons.forEach(function(button){
		button.addEventListener('click', function(event){
			event.preventDefault();
			var recipe = event.target.closest('.tasty-recipes');
			if ( ! recipe ) {
				return;
			}
			var otherButtons = recipe.querySelectorAll('.' + buttonClass);
			otherButtons.forEach(function(bt){
				bt.classList.remove(buttonActiveClass);
			});
			button.classList.add(buttonActiveClass);

			/* Scales all scalable amounts. */
			var scalables = recipe.querySelectorAll('span[data-amount]');
			var buttonAmount = parseFloat( button.dataset.amount );
			scalables.forEach(function(scalable){
				if (typeof scalable.dataset.amountOriginalType === 'undefined'
					&& typeof scalable.dataset.nfOriginal === 'undefined') {
					if (-1 !== scalable.innerText.indexOf('/')) {
						scalable.dataset.amountOriginalType = 'frac';
					}
					if (-1 !== scalable.innerText.indexOf('.')) {
						scalable.dataset.amountOriginalType = 'number';
					}
					Object.keys(window.tastyRecipesVulgarFractions).forEach(function(vulgar) {
						if (-1 !== scalable.innerText.indexOf(vulgar)) {
							scalable.dataset.amountOriginalType = 'vulgar';
						}
					});
					if (typeof scalable.dataset.amountOriginalType !== 'undefined') {
						scalable.dataset.amountShouldRound = scalable.dataset.amountOriginalType;
					}
				}
				var amount = parseFloat( scalable.dataset.amount ) * buttonAmount;
				amount = window.tastyRecipesFormatAmount(amount, scalable);
				if ( typeof scalable.dataset.unit !== 'undefined' ) {
					if ( ! scalable.classList.contains('nutrifox-quantity') ) {
						if ( ! scalable.classList.contains('nutrifox-second-quantity') ) {
							amount += ' ' + scalable.dataset.unit;
						}
					}
				}
				scalable.innerText = amount;
			});
			/* Appends " (x2)" indicator. */
			var nonNumerics = recipe.querySelectorAll('[data-has-non-numeric-amount]');
			nonNumerics.forEach(function(nonNumeric){
				var indicator = nonNumeric.querySelector('span[data-non-numeric-label]');
				if ( indicator ) {
					nonNumeric.removeChild(indicator);
				}
				if ( 1 !== buttonAmount ) {
					var indicator = document.createElement('span');
					indicator.setAttribute('data-non-numeric-label', true);
					var text = document.createTextNode(' (x' + buttonAmount + ')');
					indicator.appendChild(text);
					nonNumeric.appendChild(indicator);
				}
			});

			window.tastyRecipesUpdatePrintLink();
		});
	});
}());
