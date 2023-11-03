<?php
/**
 * Manages Frontend events.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite;

use Tasty\Recipes\Lite\Objects\Recipe;
use WP_Post;

/**
 * Frontend class.
 */
class Frontend {
	/**
	 * Singleton instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Frontend
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Frontend
	 */
	public static function get_instance() {
		$called_class = get_called_class();
		if ( null !== self::$instance && $called_class === get_class( self::$instance ) ) {
			return self::$instance;
		}

		self::$instance = new static();
		self::$instance->add_hooks();
		return self::$instance;
	}

	/**
	 * Subscribe hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_hooks() {
		add_filter( 'tasty_recipes_quick_links', array( $this, 'insert_rating_quick_link' ), 10, 4 );

	}

	/**
	 * Insert rating quick link into quick links array.
	 *
	 * @since 1.0.0
	 *
	 * @param array   $links Current list of available quick links.
	 * @param WP_Post $post Current post object.
	 * @param int     $recipe_id Current recipe ID.
	 * @param array   $should_prepend_jump_to Array of selected quick links.
	 *
	 * @return array
	 *
	 * @TODO Update this method to extract the html outside it after merging PR#2351
	 */
	public function insert_rating_quick_link( $links, $post, $recipe_id, $should_prepend_jump_to ) {
		if ( ! in_array( 'rating', $should_prepend_jump_to, true ) || ! Ratings::is_enabled() ) {
			return $links;
		}

		$recipe            = Recipe::get_by_id( $recipe_id );
		$total_reviews     = $recipe->get_total_reviews();
		$rating_link_class = '';

		if ( $total_reviews ) {
			$average_rating       = round( (float) $recipe->get_average_rating(), 1 );
			$recipe_rating_label  = '<span data-tasty-recipes-customization="detail-label-color.color" class="rating-label">';
			$recipe_rating_label .= sprintf(
			// translators: Ratings from number of reviews.
				_n( '%1$s from %2$s review', '%1$s from %2$s reviews', (int) $total_reviews, 'tasty-recipes-lite' ),
				'<span class="average">' . $average_rating . '</span>',
				'<span class="count">' . (int) $total_reviews . '</span>'
			);
			$recipe_rating_label .= '</span>';
			$recipe_rating_icons  = Ratings::get_rendered_rating( $average_rating );
			$rating_link_class    = 'tasty-recipes-has-ratings';
		} else {
			$recipe_rating_label = esc_html__( 'Leave a review', 'tasty-recipes-lite' );
			$recipe_rating_icons = '';
		}
		$links['rating'] = '<a class="tasty-recipes-rating-link ' . $rating_link_class . '" href="#respond">' . $recipe_rating_icons . $recipe_rating_label . '</a>';

		return $links;
	}
}
