<?php
/**
 * Manage Tasty Recipes from WP-CLI.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes;

use Tasty_Recipes;
use Tasty_Recipes\Utils;
use WP_CLI;
use WP_Comment_Query;

/**
 * Manage Tasty Recipes from WP-CLI.
 */
class CLI {

	/**
	 * Provide a list of recipes attached to specified posts.
	 *
	 * ## OPTIONS
	 *
	 * [<id>...]
	 * : Run against specific post ids.
	 *
	 * [--format=<format>]
	 * : Output format for the results.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 *   - count
	 * ---
	 *
	 * @subcommand get-post-recipes
	 *
	 * @param array $args       Unnamed positional arguments.
	 * @param array $assoc_args Arguments passed to the command.
	 *
	 * @return void
	 */
	public function get_post_recipes( $args, $assoc_args ) {

		if ( isset( $args ) ) {
			$posts = array_map( 'get_post', $args );
		} else {
			WP_CLI::error( 'Please specify a post ID.' );
		}

		$results = array();

		foreach ( $posts as $post ) {
			$recipes = Tasty_Recipes::get_recipes_for_post( $post->ID );

			foreach ( $recipes as $recipe ) {
				$results[] = array(
					'post_id'             => $post->ID,
					'post_title'          => $post->post_title,
					'tasty_recipes_id'    => $recipe->get_id(),
					'tasty_recipes_title' => $recipe->get_title(),
				);
			}
		}

		$headers = array();
		if ( ! empty( $results ) ) {
			$headers = array_keys( $results[0] );
		}

		WP_CLI\Utils\format_items( $assoc_args['format'], $results, $headers );
	}

	/**
	 * Prepares a CSV of video URLs from legacy WPRM data.
	 *
	 * ## OPTIONS
	 *
	 * [--database=<database>]
	 * : Name of the database to connect to, if needed.
	 *
	 * [--format=<format>]
	 * : Output format for the results.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 *   - count
	 * ---
	 *
	 * @subcommand find-legacy-wprm-video-urls
	 *
	 * @param array $_          Unnamed positional arguments.
	 * @param array $assoc_args Arguments passed to the command.
	 *
	 * @return void
	 */
	public function find_legacy_wprm_video_urls( $_, $assoc_args ) { // phpcs:ignore SlevomatCodingStandard

		if ( ! empty( $assoc_args['database'] ) ) {
			$db = new \wpdb( DB_USER, DB_PASSWORD, $assoc_args['database'], DB_HOST );
			$db->set_prefix( 'wp_' );
		} else {
			$db = $GLOBALS['wpdb'];
		}

		$recipes = $db->get_results(
			"SELECT ID,post_title FROM {$db->posts} WHERE post_type='tasty_recipe'"
		);

		$results = array();
		foreach ( $recipes as $recipe ) {
			// Assume empty title isn't a valid recipe.
			if ( empty( $recipe->post_title ) ) {
				continue;
			}
			$existing = $db->get_var(
				$db->prepare(
					"SELECT meta_value FROM {$db->postmeta} WHERE meta_key='video_url' AND post_id=%d",
					$recipe->ID
				)
			);
			if ( $existing ) {
				continue;
			}

			$post_id           = null;
			$post_url          = null;
			$wprm_recipe_id    = null;
			$wprm_recipe_title = null;
			$match             = null;
			$video_url         = null;

			// Find the original post.
			$maybe_posts = $db->get_results(
				$db->prepare(
					"SELECT ID, post_title FROM {$db->posts} WHERE post_type='post' AND post_content LIKE %s",
					'%' . $recipe->ID . '%'
				)
			);
			if ( count( $maybe_posts ) === 1 ) {
				$post_id  = $maybe_posts[0]->ID;
				$post_url = sprintf(
					'https://www.fooddolls.com/?p=%d',
					$post_id
				);
			}

			// Join based on the recipe title.
			$maybe_wprms = $db->get_results(
				$db->prepare(
					"SELECT ID, post_title FROM {$db->posts} WHERE post_title=%s AND post_type='wprm_recipe'",
					$recipe->post_title
				)
			);
			if ( count( $maybe_wprms ) === 1 ) {
				$wprm_recipe_id    = $maybe_wprms[0]->ID;
				$wprm_recipe_title = $maybe_wprms[0]->post_title;
				$match             = 'title';
			}

			// Join based on a shared thumbnail.
			if ( ! $wprm_recipe_id ) {
				$thumbnail_id = $db->get_var(
					$db->prepare(
						"SELECT meta_value FROM {$db->postmeta} WHERE meta_key='_thumbnail_id' AND post_id=%d",
						$recipe->ID
					)
				);
				if ( $thumbnail_id ) {
					$maybe_wprms = $db->get_results(
						$db->prepare(
							"SELECT p.ID, p.post_title FROM {$db->posts} as p
							LEFT JOIN {$db->postmeta} as pm ON p.ID=pm.post_id
							WHERE p.post_type='wprm_recipe' AND pm.meta_key='_thumbnail_id' AND pm.meta_value=%s",
							$thumbnail_id
						)
					);
					if ( count( $maybe_wprms ) === 1 ) {
						$wprm_recipe_id    = $maybe_wprms[0]->ID;
						$wprm_recipe_title = $maybe_wprms[0]->post_title;
						$match             = 'thumbnail';
					}
				}
			}

			if ( $wprm_recipe_id ) {
				$video_url = $db->get_var(
					$db->prepare(
						"SELECT meta_value FROM {$db->postmeta} WHERE meta_key='wprm_video_embed' AND post_id=%d",
						$wprm_recipe_id
					)
				);
				if ( false !== stripos( $video_url, '<iframe' ) ) {
					$src = Utils::get_element_attribute( $video_url, 'iframe', 'src' );
					if ( $src ) {
						$youtube_id = Utils::get_youtube_id( $src );
						if ( $youtube_id ) {
							$video_url = sprintf(
								'https://www.youtube.com/watch?v=%s',
								$youtube_id
							);
						} else {
							$video_url = $src;
						}
					}
				}
			}

			$results[] = array(
				'post_id'             => $post_id,
				'post_url'            => $post_url,
				'tasty_recipes_id'    => $recipe->ID,
				'tasty_recipes_title' => $recipe->post_title,
				'match'               => $match,
				'wprm_recipe_id'      => $wprm_recipe_id,
				'wprm_recipe_title'   => $wprm_recipe_title,
				'video_url'           => $video_url,
			);
		}

		$headers = array();
		if ( ! empty( $results ) ) {
			$headers = array_keys( $results[0] );
		}
		WP_CLI\Utils\format_items( $assoc_args['format'], $results, $headers );
	}
}
