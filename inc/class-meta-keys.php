<?php
/**
 * Registry of structural and integration meta keys used by Tasty Recipes Lite.
 *
 * Recipe field meta keys live on the Recipe attribute map; do not duplicate them here.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes;

/**
 * Non-recipe-field meta key constants.
 *
 * @since x.x
 */
final class Meta_Keys {

	/**
	 * Post meta linking a recipe to parent posts.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public const RECIPE_PARENTS = '_tasty_recipe_parents';

	/**
	 * Cached Nutrifox API response meta.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public const NUTRIFOX_RESPONSE = 'nutrifox_response';

	/**
	 * Nutrifox API error meta.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public const NUTRIFOX_ERROR = 'nutrifox_error';

	/**
	 * Cached video URL provider response meta.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public const VIDEO_URL_RESPONSE = 'video_url_response';

	/**
	 * Video URL provider error meta.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public const VIDEO_URL_ERROR = 'video_url_error';

	/**
	 * Mediavine Create recipe link meta.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public const CREATE_KEY = '_tasty_recipes_create_key';

	/**
	 * Prefix for per-post ignore-convert meta keys.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public const IGNORE_CONVERT_PREFIX = 'tasty_recipes_ignore_convert_';

	/**
	 * EasyRecipe-compatible comment rating meta.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public const COMMENT_RATING = 'ERRating';

	/**
	 * Cookbook comment rating meta.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public const COOKBOOK_COMMENT_RATING = 'cookbook_comment_rating';

	/**
	 * Simple Recipe Pro comment rating meta.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public const SRP_COMMENT_RATING = 'recipe_rating';

	/**
	 * WP Recipe Maker comment rating meta.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public const WPRM_COMMENT_RATING = 'wprm-comment-rating';

	/**
	 * ZipList comment rating meta.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public const ZRP_COMMENT_RATING = 'zrdn_post_recipe_rating';

	/**
	 * Card rating hash comment meta.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public const RATING_COMMENT_HASH = 'tasty-recipes-comment-hash';

	/**
	 * Imported Create ratings post meta.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public const CREATE_RATINGS = 'create_ratings';

	/**
	 * Imported Simple Recipe Pro ratings post meta.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public const SRP_RATINGS = 'srp_ratings';

	/**
	 * Imported WP Recipe Maker ratings post meta.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public const WPRM_RATINGS = 'wprm_ratings';

	/**
	 * Imported ZipList ratings post meta.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public const ZRP_RATINGS = 'zrp_ratings';

	/**
	 * Build a per-converter ignore-convert meta key.
	 *
	 * @since x.x
	 *
	 * @param string $converter_type Converter type slug.
	 *
	 * @return string
	 */
	public static function ignore_convert( string $converter_type ): string {
		return self::IGNORE_CONVERT_PREFIX . $converter_type;
	}
}
