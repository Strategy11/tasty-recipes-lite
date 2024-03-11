<?php
/**
 * Manages Frontend events.
 *
 * @package Tasty_Recipes
 */

namespace Tasty\Recipes\Lite;

use Tasty\Framework\Abstracts\Subscriber;
use Tasty\Recipes\Lite\Objects\Recipe;
use WP_Post;

/**
 * Frontend class.
 */
class Frontend extends Subscriber {

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
			$recipe_rating_label .= Ratings::get_rating_label( $total_reviews, $average_rating );
			$recipe_rating_label .= '</span>';
			$recipe_rating_icons  = Ratings::get_rendered_rating( $average_rating );
			$rating_link_class    = 'tasty-recipes-has-ratings';
		} else {
			$recipe_rating_label = esc_html__( 'Leave a Review', 'tasty-recipes' );
			$recipe_rating_icons = '';
			if ( 'buttons' === get_option( Tasty_Recipes::QUICK_LINKS_STYLE ) ) {
				$rating_link_class = 'button';
			}
		}
		$links['rating'] = '<a class="tasty-recipes-rating-link tasty-recipes-scrollto ' . $rating_link_class . '" href="#respond">' . $recipe_rating_icons . $recipe_rating_label . '</a>';

		return $links;
	}
}
