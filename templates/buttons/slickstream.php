<?php
/**
 * Template for the Slickstream button.
 *
 * @package Tasty_Recipes
 *
 * @var object $recipe        Recipe object.
 * @var string $customization Customization options.
 */

?>

<a class="button tasty-recipes-slickstream-button tasty-recipes-no-print" href="javascript:void(0)" data-tasty-recipes-customization="<?php echo esc_attr( $customization ); ?>">
	<span class="tasty-recipes-not-saved">
		<svg viewBox="0 0 24 24" class="svg-heart-regular" aria-hidden="true"><use xlink:href="#tasty-recipes-icon-heart-regular"></use></svg>
		<?php esc_html_e( 'Save Recipe', 'tasty-recipes' ); ?>
	</span>
	<span class="tasty-recipes-saved" style="display: none;">
		<svg viewBox="0 0 24 24" class="svg-heart-solid" aria-hidden="true"><use xlink:href="#tasty-recipes-icon-heart-solid"></use></svg>
		<?php esc_html_e( 'Recipe Saved', 'tasty-recipes' ); ?>
	</span>
</a>
<script>
(function(){
	function ensureSlickstream() {
		return new Promise((resolve, reject) => {
			if (window.slickstream) {
				resolve(window.slickstream.v1);
			} else {
				document.addEventListener('slickstream-ready', () => {
					resolve(window.slickstream.v1);
				});
			}
		});
	}
	function updateFavoriteButtonState() {
		ensureSlickstream().then(function(slickstream) {
			var isFavorite = slickstream.favorites.getState();
			document.querySelectorAll('.tasty-recipes-slickstream-button').forEach(function(el) {
				el.querySelector('.tasty-recipes-saved').style.display = isFavorite ? null : 'none';
				el.querySelector('.tasty-recipes-not-saved').style.display = isFavorite ? 'none' : null;
			});
		});
	}
	document.querySelectorAll('.tasty-recipes-slickstream-button').forEach(function(el) {
		el.addEventListener('click', function() {
			ensureSlickstream().then(function(slickstream) {
				var state = slickstream.favorites.getState();
				slickstream.favorites.setState(!state);
			});
		});
	});
	document.addEventListener('slickstream-favorite-change', () => {
		updateFavoriteButtonState();
	});
	updateFavoriteButtonState();
}())
</script>
