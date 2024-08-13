(function(){
	var buttonClass = 'tasty-recipes-convert-button',
		buttonActiveClass = 'tasty-recipes-convert-button-active',
		buttons = document.querySelectorAll('.tasty-recipes-convert-button');
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
			var unitType = button.dataset.unitType;
			var dataset = 'nf' + unitType.charAt(0).toUpperCase() + unitType.slice(1);
			var convertables = recipe.querySelectorAll('span[data-nf-original]');
			convertables.forEach(function(convertable){
				if (typeof convertable.dataset.amountOriginalType === 'undefined'
					&& 'usc' === convertable.dataset.nfOriginal) {
					if (-1 !== convertable.innerText.indexOf('/')) {
						convertable.dataset.amountOriginalType = 'frac';
					}
					if (-1 !== convertable.innerText.indexOf('.')) {
						convertable.dataset.amountOriginalType = 'number';
					}
					Object.keys(window.tastyRecipesVulgarFractions).forEach(function(vulgar) {
						if (-1 !== convertable.innerText.indexOf(vulgar)) {
							convertable.dataset.amountOriginalType = 'vulgar';
						}
					});
				}
				convertable.innerText = convertable.dataset[dataset];
				if (typeof convertable.dataset.unit !== 'undefined') {
					convertable.dataset.unit = convertable.dataset[dataset + 'Unit'];
				}
				if (typeof convertable.dataset.amount !== 'undefined') {
					convertable.dataset.amount = convertable.dataset[dataset];
					if ('metric' === unitType) {
						convertable.dataset.amountShouldRound = parseInt(convertable.dataset.amount) >= 10 ? 'integer' : 'number';
					} else if (typeof convertable.dataset.amountOriginalType !== 'undefined') {
						convertable.dataset.amountShouldRound = convertable.dataset.amountOriginalType;
					} else {
						convertable.dataset.amountShouldRound = false;
					}
					convertable.innerText = window.tastyRecipesFormatAmount(convertable.dataset[dataset], convertable);
				}
				if (convertable.classList.contains('nutrifox-unit')) {
					if ('gram' === convertable.dataset[dataset]) {
						convertable.innerText = 'grams';
					}
				}
			});
			/* Trigger the correct amount if scaling is active */
			document.querySelectorAll('.tasty-recipes-scale-button-active').forEach(function(scaleButton){
				scaleButton.click();
			});

			window.tastyRecipesUpdatePrintLink();
		});
	});
}());
