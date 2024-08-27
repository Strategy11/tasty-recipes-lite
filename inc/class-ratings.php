<?php
/**
 * Manages ratings integration with comments.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes;

use Tasty\Framework\Utils\Vars;
use Tasty_Recipes;
use Tasty_Recipes\Objects\Recipe;
use Tasty_Recipes_Pro;

/**
 * Manages ratings integration with comments.
 */
class Ratings {

	/**
	 * Meta key where ratings are stored.
	 *
	 * Defaults to 'ERRating' for historical compatibility with EasyRecipe.
	 *
	 * @var string
	 */
	const COMMENT_META_KEY = 'ERRating';

	/**
	 * Meta key where Cookbook ratings are stored.
	 *
	 * @var string
	 */
	const CB_COMMENT_META_KEY = 'cookbook_comment_rating';

	/**
	 * Meta key where Simple Recipe Pro ratings are stored.
	 *
	 * @var string
	 */
	const SRP_COMMENT_META_KEY = 'recipe_rating';

	/**
	 * Meta key where WP Recipe Maker ratings are stored.
	 *
	 * @var string
	 */
	const WPRM_COMMENT_META_KEY = 'wprm-comment-rating';

	/**
	 * Meta key where ZipList ratings are stored.
	 *
	 * @var string
	 */
	const ZRP_COMMENT_META_KEY = 'zrdn_post_recipe_rating';

	/**
	 * Card rating hash meta key.
	 *
	 * @var string
	 */
	const RATING_COMMENT_HASH_META_KEY = 'tasty-recipes-comment-hash';

	/**
	 * Get rating needed styles.
	 *
	 * @since 3.11.1
	 *
	 * @return string
	 */
	public static function get_styles() {
		_deprecated_function( __METHOD__, 'x.x', 'Tasty_Recipes_Pro\Ratings::get_styles' );
		return Tasty_Recipes_Pro\Ratings::get_styles();
	}

	/**
	 * Add ratings CSS inline on the page.
	 *
	 * @since 3.12.3
	 *
	 * @return void
	 */
	public static function add_inline_style() {
		_deprecated_function( __METHOD__, 'x.x', 'Tasty_Recipes_Pro\Ratings::add_inline_style' );
		Tasty_Recipes_Pro\Ratings::add_inline_style();
	}

	/**
	 * Get the HTML for a rendered rating.
	 *
	 * @param float|int $rating        Rating value.
	 * @param string    $customization Integration with card customization.
	 * @param string    $style         Style to use.
	 *
	 * @return string
	 */
	public static function get_rendered_rating( $rating, $customization = '', $style = null ) {
		_deprecated_function( __METHOD__, 'x.x', 'Tasty_Recipes_Pro\Ratings::get_rendered_rating' );
		return Tasty_Recipes_Pro\Ratings::get_rendered_rating( $rating, $customization, $style );
	}

	/**
	 * Filter customization settings to set the default value of stars color.
	 *
	 * @since 3.9
	 *
	 * @param array $settings Array of customization settings.
	 *
	 * @return array
	 */
	public static function apply_default_rating_stars_color( $settings = array() ) {
		_deprecated_function( __METHOD__, 'x.x', 'Tasty_Recipes_Pro\Ratings::apply_default_rating_stars_color' );
		return Tasty_Recipes_Pro\Ratings::apply_default_rating_stars_color( $settings );
	}

	/**
	 * Get rating stars HTML.
	 *
	 * @param bool $no_ratings Show no rating template without review yet.
	 * @param int  $average    The rating valie to show.
	 *
	 * @return string
	 */
	public static function get_rating_stars_html( $no_ratings = false, $average = 0 ) {
		_deprecated_function( __METHOD__, 'x.x', 'Tasty_Recipes_Pro\Ratings::get_rating_stars_html' );
		return Tasty_Recipes_Pro\Ratings::get_rating_stars_html( $no_ratings, $average );
	}

	/**
	 * Get the filled percentage for the stars.
	 *
	 * @since 3.12
	 *
	 * @param array $atts {
	 *     Attributes to be used.
	 *
	 *     @type int $i       Rating value.
	 *     @type int $average Default rating value.
	 * }
	 *
	 * @return int
	 */
	public static function get_fill_percent( $atts ) {
		_deprecated_function( __METHOD__, 'x.x', 'Tasty_Recipes_Pro\Ratings::get_fill_percent' );
		return Tasty_Recipes_Pro\Ratings::get_fill_percent( $atts );
	}

	/**
	 * Allow empty comment when rating is more than 3.
	 *
	 * @param bool  $allow        Whether to allow empty comments. Default false.
	 * @param array $comment_data Array of comment data to be sent to wp_insert_comment().
	 *
	 * @return bool
	 */
	public static function filter_allow_rating_empty_comment( $allow, $comment_data ) {
		_deprecated_function( __METHOD__, 'x.x', 'Tasty_Recipes_Pro\Ratings::filter_allow_rating_empty_comment' );
		return Tasty_Recipes_Pro\Ratings::filter_allow_rating_empty_comment( $allow, $comment_data );
	}

	/**
	 * Get minimum rating value without requiring comment (filtered).
	 *
	 * @param array $comment_data Comment data if found.
	 *
	 * @return int
	 */
	public static function get_minimum_rating_without_comment( $comment_data = array() ) {
		_deprecated_function( __METHOD__, 'x.x', 'Tasty_Recipes_Pro\Ratings::get_minimum_rating_without_comment' );
		return Tasty_Recipes_Pro\Ratings::get_minimum_rating_without_comment( $comment_data );
	}

	/**
	 * Get rating label HTML.
	 *
	 * @param int   $total_reviews  Reviews count.
	 * @param float $average_rating Average rating.
	 *
	 * @return string
	 */
	public static function get_rating_label( $total_reviews, $average_rating ) {
		_deprecated_function( __METHOD__, 'x.x', 'Tasty_Recipes_Pro\Ratings::get_rating_label' );
		return Tasty_Recipes_Pro\Ratings::get_rating_label( $total_reviews, $average_rating );
	}
}
