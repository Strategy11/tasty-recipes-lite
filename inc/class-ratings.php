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
	 * Whether the CSS has been loaded.
	 *
	 * @var bool
	 */
	private static $css_loaded = false;

	/**
	 * Get rating needed styles.
	 *
	 * @since 3.11.1
	 *
	 * @return string
	 */
	public static function get_styles() {
		$styles = self::get_style_content();
		if ( ! $styles ) {
			return '';
		}
		return '<style>' . $styles . '</style>' . PHP_EOL;
	}

	/**
	 * Add ratings CSS inline on the page.
	 *
	 * @since 3.12.3
	 *
	 * @return void
	 */
	public static function add_inline_style() {
		if ( self::$css_loaded ) {
			return;
		}

		$styles = self::get_style_content();
		if ( ! $styles ) {
			return;
		}

		self::$css_loaded = true;
		wp_add_inline_style( 'tasty-recipes-main', Utils::minify_css( $styles ) );
	}

	/**
	 * Get the CSS content for ratings.
	 *
	 * @since 3.12.3
	 *
	 * @return string
	 */
	private static function get_style_content() {
		return Utils::get_contents( dirname( __DIR__ ) . '/assets/dist/ratings.css' );
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
		if ( ! $rating ) {
			return '';
		}

		if ( is_null( $style ) ) {
			$settings = Tasty_Recipes::get_customization_settings();
			$style    = $settings['star_ratings_style'];
		}

		$icons = self::get_icons_by_type( $style );
		if ( empty( $icons ) ) {
			return '';
		}

		$ret = '';
		for ( $i = 1; $i <= 5; $i++ ) {
			if ( ceil( $rating ) >= $i || in_array( $style, array( 'outline', 'solid' ), true ) ) {
				$percentage = self::get_fill_percent(
					array(
						'i'       => $i,
						'average' => $rating,
					)
				);

				$ret .= '<span';
				if ( ! empty( $customization ) ) {
					$ret .= ' data-tasty-recipes-customization="' . esc_attr( $customization ) . '"';
				}
				$ret .= ' class="' . esc_attr( 'tasty-recipes-rating tasty-recipes-rating-' . $style ) . '"';
				$ret .= ' data-tr-clip="' . esc_attr( (string) $percentage ) . '"';
				$ret .= ' data-rating="' . intval( $i ) . '"';
				$ret .= '>';
				$ret .= $icons['checked'];
				$ret .= '</span>';
			}
		}
		return $ret;
	}

	/**
	 * Get the rating icon HTML.
	 *
	 * @since 3.12
	 *
	 * @param string $style Type of the icon. ie. solid or outline.
	 *
	 * @return array
	 */
	private static function get_icons_by_type( $style ) {
		if ( 'solid' === $style ) {
			$svg_name = 'star-rating-solid.svg';
		} elseif ( 'outline' === $style ) {
			$svg_name = 'star-rating-clip.svg';
		}

		if ( empty( $svg_name ) ) {
			return array();
		}

		$star_svg = Utils::get_contents( dirname( __DIR__ ) . '/assets/images/' . $svg_name );
		return array(
			'checked'   => $star_svg,
			'unchecked' => $star_svg,
		);
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
		if ( ! empty( $settings['star_color'] ) ) {
			return $settings;
		}

		$settings['star_color'] = ! empty( $settings['detail_value_color'] ) ? $settings['detail_value_color'] : '#F2B955';
		return $settings;
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
		if ( ! comments_open() && ! is_admin() ) {
			return '';
		}

		$settings     = Tasty_Recipes::get_customization_settings();
		$rating_style = $settings['star_ratings_style'];

		return Tasty_Recipes::get_template_part(
			'parts/ratings',
			array(
				'no_ratings'       => $no_ratings,
				'icons'            => self::get_icons_by_type( $rating_style ),
				'remove_new_lines' => true,
				'average'          => $average,
				'rating_style'     => $rating_style,
			)
		);
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
		$diff = round( $atts['average'], 1 ) - $atts['i'] + 1;
		if ( $diff < 1 && $diff > 0 ) {
			$percentage = $diff * 100;
		} elseif ( $diff >= 1 ) {
			$percentage = 100;
		} else {
			$percentage = 0;
		}

		return $percentage;
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
		$recipes = Tasty_Recipes::get_recipes_for_post( (int) $comment_data['comment_post_ID'] );
		if ( 1 !== count( $recipes ) ) {
			return $allow;
		}

		$minimum_rating_without_comment = self::get_minimum_rating_without_comment( $comment_data );
		$post_rating                    = (int) Utils::get_post_value( 'tasty-recipes-rating' );
		return ! empty( $post_rating ) && $post_rating >= $minimum_rating_without_comment;
	}

	/**
	 * Get minimum rating value without requiring comment (filtered).
	 *
	 * @param array $comment_data Comment data if found.
	 *
	 * @return int
	 */
	public static function get_minimum_rating_without_comment( $comment_data = array() ) {
		/**
		 * Control minimum rating number where comment is not mandatory.
		 *
		 * @param int   $min_rating   Rating minimum integer value, default is 4.
		 * @param array $comment_data Array of comment data.
		 */
		return (int) apply_filters( 'tasty_recipes_min_rating_without_comment', 6, $comment_data );
	}

	/**
	 * Check if the rating has a comment or not.
	 *
	 * @since 3.12
	 *
	 * @param int $comment_id Comment ID.
	 *
	 * @return bool|object
	 */
	private static function has_comment( $comment_id ) {
		$has_comment = false;
		if ( $comment_id ) {
			$comment     = get_comment( $comment_id );
			$has_comment = $comment && ! empty( $comment->comment_content );
		}
		return $has_comment ? $comment : false;
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
		return sprintf(
			// translators: Ratings from number of reviews.
			_n( '%1$s from %2$s review', '%1$s from %2$s reviews', (int) $total_reviews, 'tasty-recipes' ),
			'<span class="average">' . $average_rating . '</span>',
			'<span class="count">' . (int) $total_reviews . '</span>'
		);
	}
}
