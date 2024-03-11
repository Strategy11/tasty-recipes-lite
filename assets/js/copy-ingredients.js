(function() {

	document.querySelectorAll('.tasty-recipes-copy-button').forEach(function(copyButton) {
		if (copyButton.getAttribute('data-tasty-recipes-click-event')) {
			return;
		}
		copyButton.setAttribute('data-tasty-recipes-click-event', true);

		copyButton.addEventListener('click', function(event) {
			event.preventDefault();
			var copyContainer = copyButton.closest('.tasty-recipes-ingredients-clipboard-container');

			var messageText = copyButton.attributes.getNamedItem('data-success').nodeValue;
			var copySuccessMessage = document.createElement('div');
			var copySuccessParagraph = document.createElement('p');
			copySuccessParagraph.innerText = messageText;
			copySuccessMessage.appendChild(copySuccessParagraph);
			copySuccessMessage.classList.add('tasty-recipes-flash-message');
			var messageDuration = 3000;

			var ingredients = [];
			var ingredientsContainer = copyButton.closest('.tasty-recipes-ingredients') || copyButton.closest('.tasty-recipe-ingredients');
			var foundIngredients = false;
			var findIngredients = function(div) {
				if ( div.querySelectorAll('li').length ) {
					div.querySelectorAll('li').forEach(function(li) {
						ingredients.push(li.innerText);
					});
					foundIngredients = true;
				} else if ( div.querySelectorAll('p').length ) {
					div.querySelectorAll('p').forEach(function(p) {
						ingredients.push(p.innerText);
					});
					foundIngredients = true;
				}
			};
			if (ingredientsContainer.querySelector('.tasty-recipes-ingredients-body')) {
				findIngredients(ingredientsContainer.querySelector('.tasty-recipes-ingredients-body'));
			} else {
				ingredientsContainer.querySelectorAll('div').forEach(function(div) {
					if ( foundIngredients ) {
						return;
					}
					if ( div.classList.contains('tasty-recipes-ingredients-header')
						|| div.parentElement.classList.contains('tasty-recipes-ingredients-header')
						|| div.classList.contains('tasty-recipes-ingredients-header') ) {
						return;
					}
					findIngredients(div);
				});
			}

			var readableIngredients = '';
			ingredients.forEach(function(ingredient) {
				readableIngredients += ingredient + '\n';
			});
			readableIngredients = readableIngredients.trim();

			var fakeElem = document.createElement("textarea");
			fakeElem.style.fontSize = "12pt";
			fakeElem.style.border = "0";
			fakeElem.style.padding = "0";
			fakeElem.style.margin = "0";
			fakeElem.style.position = "absolute";
			fakeElem.style.left = "-9999px";

			var yPosition = window.pageYOffset || document.documentElement.scrollTop;
			fakeElem.style.top = yPosition + 'px';

			fakeElem.setAttribute("readonly", "");
			fakeElem.value = readableIngredients;

			document.body.appendChild(fakeElem);

			if (navigator.userAgent.match(/ipad|iphone/i)) {
				var range = document.createRange();
				/* eslint-disable-next-line @wordpress/no-global-get-selection */
				var selection = window.getSelection();
				range.selectNodeContents(fakeElem);
				selection.removeAllRanges();
				selection.addRange(range);
				fakeElem.setSelectionRange(0, 999999);
			} else {
				fakeElem.select();
			}

			document.execCommand("copy");

			document.body.removeChild(fakeElem);
			fakeElem = null;

			copyContainer.appendChild(copySuccessMessage);

			setTimeout(function() {
				copyContainer.removeChild(copySuccessMessage);
			}, messageDuration);
		});
	});

}());
