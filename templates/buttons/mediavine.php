<?php
/**
 * Template for the Grow button.
 *
 * @package Tasty_Recipes
 *
 * @var object $recipe        Recipe object.
 * @var string $customization Customization options.
 */

?>

<a class="button tasty-recipes-mediavine-button tasty-recipes-no-print" href="javascript:void(0)" data-tasty-recipes-customization="<?php echo esc_attr( $customization ); ?>">
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
	function initGrowMeSdk() {
		if (!window.growMe) {
			window.growMe = function (e) {
				window.growMe._.push(e);
			}
			window.growMe._ = [];
		}
	}
	initGrowMeSdk();
	window.growMe(function() {
		function updateFavoriteButtonState(isFavorite) {
			document.querySelectorAll('.tasty-recipes-mediavine-button').forEach(function(el) {
				el.querySelector('.tasty-recipes-saved').style.display = isFavorite ? null : 'none';
				el.querySelector('.tasty-recipes-not-saved').style.display = isFavorite ? 'none' : null;
			});
		}
		document.querySelectorAll('.tasty-recipes-mediavine-button').forEach(function(el) {
			el.addEventListener('click', function() {
				window.growMe.addBookmark();
			});
		});
		window.growMe.on('isBookmarkedChanged', function(data) {
			updateFavoriteButtonState(data.isBookmarked);
		});
		var isBookmarked = window.growMe.getIsBookmarked();
		updateFavoriteButtonState(isBookmarked);
	});
}())
</script>
