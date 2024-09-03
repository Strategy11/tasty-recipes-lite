window.TastyRecipes = window.TastyRecipes || {};

window.TastyRecipes.grow = {
	init() {
		if (!window.growMe) {
			window.growMe = function (e) {
				window.growMe._.push(e);
			};
			window.growMe._ = [];
		}

		document.querySelectorAll('.tasty-recipes-mediavine-button').forEach(el => {
			el.addEventListener('click', e => {
				e.preventDefault();
				window.TastyRecipes.grow.onClickButton(el);
			});
		});

		window.growMe(() => {
			const isBookmarked = window.growMe.getIsBookmarked();
			window.growMe.on('isBookmarkedChanged', data => {
				window.TastyRecipes.grow.markAsSaved(false, data.isBookmarked);
			});

			window.TastyRecipes.grow.markAsSaved(false, isBookmarked);
		});
	},

	onClickButton(el) {
		if (!window.growMe.getIsBookmarked || window.growMe.getIsBookmarked()) {
			return;
		}

		const bookmarkData = { source: 'tr-save-btn', tooltipReferenceElement: el };
		const recipeId = parseInt(el.dataset.recipeId);
		window.growMe.addBookmark(bookmarkData)
			.then(() => window.TastyRecipes.grow.markAsSaved(recipeId, true))
			.catch(() => window.TastyRecipes.grow.markAsSaved(recipeId, true));
	},

	markAsSaved(recipeId, isBookmarked) {
		document.querySelectorAll('.tasty-recipes-mediavine-button').forEach(el => {
			if (isBookmarked || recipeId === parseInt(el.dataset.recipeId)) {
				el.querySelector('.tasty-recipes-saved').style.display = isBookmarked ? '' : 'none';
				el.querySelector('.tasty-recipes-not-saved').style.display = isBookmarked ? 'none' : '';
			}
		});
	}
};

document.addEventListener('DOMContentLoaded', () => window.TastyRecipes.grow.init());
