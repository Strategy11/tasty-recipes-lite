<?php
/**
 * AI Scraper Prevention.
 *
 * Blocks AI training/scraping bots via robots.txt rules and injects
 * noai/noimageai meta tags on posts that contain recipes.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes;

use Tasty_Recipes;
use Tasty_Recipes\Integrations\Yoast;
use Tasty_Recipes\Integrations\Rank_Math;
use Tasty_Recipes\Integrations\AIOSEO;

/**
 * AI Scraper Prevention.
 *
 * @since x.x
 */
class AI_Scraper_Prevention {

	/**
	 * AI training/scraping bot user-agents to block.
	 *
	 * @since x.x
	 *
	 * @var string[]
	 */
	const AI_BOTS = array(
		'ClaudeBot',
		'claudebot',
		'GPTBot',
		'Google-Extended',
		'PerplexityBot',
		'Meta-ExternalAgent',
		'Applebot-Extended',
		'CCBot',
		'cohere-ai',
		'Bytespider',
		'Amazonbot',
		'ai2bot',
		'Diffbot',
		'Omgilibot',
	);

	/**
	 * Map of Yoast Premium option keys to bot user-agent strings.
	 *
	 * When Yoast Premium is active and one of these options is false,
	 * the user has intentionally left that bot allowed.
	 *
	 * @since x.x
	 *
	 * @var array<string, string>
	 */
	const YOAST_PREMIUM_BOT_OPTIONS = array(
		'deny_ccbot_crawling'           => 'CCBot',
		'deny_gptbot_crawling'          => 'GPTBot',
		'deny_google_extended_crawling' => 'Google-Extended',
	);

	/**
	 * Maybe load hooks if the feature is enabled.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function maybe_load_hooks() {
		if ( get_option( Tasty_Recipes::AI_SCRAPER_PREVENTION_OPTION ) !== '1' ) {
			return;
		}

		// phpcs:ignore WordPressVIPMinimum.Hooks.RestrictedHooks.robots_txt
		add_filter( 'robots_txt', array( __CLASS__, 'filter_robots_txt' ), 500, 2 );

		self::register_meta_tag_hooks();
	}

	/**
	 * Register the noai/noimageai meta tag hooks for the active SEO plugin,
	 * or fall back to a standalone wp_head output.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function register_meta_tag_hooks() {
		if ( defined( 'WPSEO_VERSION' ) ) {
			Yoast::load_ai_scraper_hooks();
			return;
		}

		if ( class_exists( 'RankMath' ) ) {
			Rank_Math::load_ai_scraper_hooks();
			return;
		}

		if ( function_exists( 'aioseo' ) ) {
			AIOSEO::load_ai_scraper_hooks();
			return;
		}

		add_action( 'wp_head', array( __CLASS__, 'action_wp_head' ), 7 );
	}

	/**
	 * Get the list of AI bot user-agents to block.
	 *
	 * @since x.x
	 *
	 * @return string[] Array of user-agent strings to block.
	 */
	public static function get_blocked_bots() {
		/**
		 * Filters the list of AI training/scraping bot user-agents to block.
		 *
		 * @since x.x
		 *
		 * @param string[] $bots Array of user-agent strings to block.
		 */
		return apply_filters( 'tasty_recipes_ai_scraper_bots', self::AI_BOTS );
	}

	/**
	 * Append AI bot disallow rules to robots.txt output.
	 *
	 * Skips bots that already have a User-agent entry in the output and
	 * bots that Yoast Premium has explicitly left allowed.
	 *
	 * @since x.x
	 *
	 * @param string $output    Robots.txt content.
	 * @param bool   $is_public Whether the site is public.
	 *
	 * @return string
	 */
	public static function filter_robots_txt( $output, $is_public ) {
		if ( ! $is_public ) {
			return $output;
		}

		$existing_agents = self::parse_existing_user_agents( $output );
		$seo_allowed     = self::get_seo_allowed_bots();
		$bots            = self::get_blocked_bots();
		$additions       = '';

		foreach ( $bots as $bot ) {
			if ( in_array( strtolower( $bot ), $existing_agents, true ) ) {
				continue;
			}

			if ( in_array( $bot, $seo_allowed, true ) ) {
				continue;
			}

			$additions .= "\nUser-agent: " . $bot . "\nDisallow: /\n";
		}

		if ( ! in_array( '*', $existing_agents, true ) ) {
			$additions .= "\nUser-agent: *\nAllow: /\n";
		}

		return $output . $additions;
	}

	/**
	 * Output noai/noimageai meta tag on recipe posts (standalone, no SEO plugin).
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function action_wp_head() {
		if ( ! is_singular() ) {
			return;
		}

		$post_id = get_queried_object_id();

		if ( ! $post_id || ! Tasty_Recipes::has_recipe( $post_id ) ) {
			return;
		}

		echo '<meta name="robots" content="noai, noimageai">' . PHP_EOL;
	}

	/**
	 * Parse existing User-agent values from a robots.txt string.
	 *
	 * @since x.x
	 *
	 * @param string $output Robots.txt content.
	 *
	 * @return string[] Lowercased user-agent values.
	 */
	private static function parse_existing_user_agents( $output ) {
		$agents = array();

		if ( preg_match_all( '/^User-agent:\s*(.+)$/mi', $output, $matches ) ) {
			foreach ( $matches[1] as $agent ) {
				$agents[] = strtolower( trim( $agent ) );
			}
		}

		return array_unique( $agents );
	}

	/**
	 * Get bots that active SEO plugins have explicitly allowed.
	 *
	 * @since x.x
	 *
	 * @return string[] Bot user-agent strings to skip.
	 */
	private static function get_seo_allowed_bots() {
		$allowed = array();

		if ( ! class_exists( 'Yoast\WP\SEO\Premium\Integrations\Front_End\Robots_Txt_Integration' ) ) {
			return $allowed;
		}

		foreach ( self::YOAST_PREMIUM_BOT_OPTIONS as $option_key => $bot ) {
			if ( ! \WPSEO_Options::get( $option_key ) ) {
				$allowed[] = $bot;
			}
		}

		return $allowed;
	}
}
