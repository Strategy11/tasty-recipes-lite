(function(){
	document.querySelectorAll('[data-tr-ingredient-checkbox]').forEach(function(el) {
		var input = el.querySelector('.tr-ingredient-checkbox-container input[type="checkbox"]');
		if ( ! input ) {
			return;
		}
		if (input.checked) {
			el.dataset.trIngredientCheckbox = 'checked';
		}
		el.addEventListener('click', function(event) {
			if ( 'A' === event.target.nodeName
				|| 'INPUT' === event.target.nodeName
				|| 'LABEL' === event.target.nodeName ) {
				return;
			}
			input.click();
		});
		input.addEventListener('change', function() {
			el.dataset.trIngredientCheckbox = input.checked ? 'checked' : '';
		});
	});
}());
