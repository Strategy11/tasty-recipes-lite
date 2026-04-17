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

/**
 * AI Scraper Prevention.
 *
 * @since x.x
 */
class AI_Scraper_Prevention {

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
		add_filter( 'robots_txt', array( __CLASS__, 'filter_robots_txt' ), 100000, 2 );

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
			add_filter( 'wpseo_robots_array', array( __CLASS__, 'filter_listed_robots' ) );
			return;
		}

		if ( class_exists( 'RankMath' ) ) {
			add_filter( 'rank_math/frontend/robots', array( __CLASS__, 'filter_keyed_robots' ) );
			return;
		}

		if ( function_exists( 'aioseo' ) ) {
			add_filter( 'aioseo_robots_meta', array( __CLASS__, 'filter_keyed_robots' ) );
			return;
		}

		add_action( 'wp_head', array( __CLASS__, 'action_wp_head' ), 7 );
	}

	/**
	 * Append AI bot disallow rules to robots.txt output.
	 *
	 * Runs at priority 100000 (after all known SEO plugins) so that
	 * parse_existing_user_agents() naturally deduplicates against any
	 * bots already added by Yoast Premium, AIOSEO, Rank Math, etc.
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
		$bots            = self::get_blocked_bots();
		$rules           = '';

		foreach ( $bots as $bot ) {
			if ( in_array( strtolower( $bot ), $existing_agents, true ) ) {
				continue;
			}

			$rules .= "\nUser-agent: " . $bot . "\nDisallow: /\n";
		}

		if ( ! in_array( '*', $existing_agents, true ) ) {
			$rules .= "\nUser-agent: *\nAllow: /\n";
		}

		if ( '' === $rules ) {
			return $output;
		}

		$trimmed   = rtrim( $output );
		$separator = "\n\n";

		return $trimmed . $separator . "# START TASTY RECIPES BLOCK\n# ---------------------------" . $rules . "# ---------------------------\n# END TASTY RECIPES BLOCK\n";
	}

	/**
	 * Get the list of AI bot user-agents to block.
	 *
	 * @since x.x
	 *
	 * @return string[] Array of user-agent strings to block.
	 */
	public static function get_blocked_bots() {
		$bots = array(
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
		 * Filters the list of AI training/scraping bot user-agents to block.
		 *
		 * @since x.x
		 *
		 * @param string[] $bots Array of user-agent strings to block.
		 */
		return apply_filters( 'tasty_recipes_ai_scraper_bots', $bots );
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
	 * Add AI directives to a list-based robots array (Yoast SEO).
	 *
	 * @since x.x
	 *
	 * @param array $robots Existing robots directive tokens.
	 *
	 * @return array
	 */
	public static function filter_listed_robots( $robots ) {
		if ( ! self::current_post_has_recipe() ) {
			return $robots;
		}

		foreach ( self::get_ai_directives() as $directive ) {
			if ( ! in_array( $directive, $robots, true ) ) {
				$robots[] = $directive;
			}
		}

		return $robots;
	}

	/**
	 * Add AI directives to a keyed robots array (Rank Math, AIOSEO).
	 *
	 * @since x.x
	 *
	 * @param array $robots Existing robots directives, keyed by token.
	 *
	 * @return array
	 */
	public static function filter_keyed_robots( $robots ) {
		if ( ! self::current_post_has_recipe() ) {
			return $robots;
		}

		foreach ( self::get_ai_directives() as $directive ) {
			if ( ! isset( $robots[ $directive ] ) ) {
				$robots[ $directive ] = $directive;
			}
		}

		return $robots;
	}

	/**
	 * Output noai/noimageai meta tag on recipe posts (standalone, no SEO plugin).
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function action_wp_head() {
		if ( ! self::current_post_has_recipe() ) {
			return;
		}

		echo '<meta name="robots" content="' . esc_attr( implode( ', ', self::get_ai_directives() ) ) . '">' . PHP_EOL;
	}

	/**
	 * Get the robots meta directives that opt out of AI training and image scraping.
	 *
	 * @since x.x
	 *
	 * @return string[] Array of robots meta directive tokens.
	 */
	public static function get_ai_directives() {
		/**
		 * Filters the robots meta directives used to opt out of AI training and image scraping.
		 *
		 * @since x.x
		 *
		 * @param string[] $directives Array of robots meta directive tokens.
		 */
		return apply_filters( 'tasty_recipes_ai_directives', array( 'noai', 'noimageai' ) );
	}

	/**
	 * Whether the current singular request is a post that contains a recipe.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function current_post_has_recipe() {
		if ( ! is_singular() ) {
			return false;
		}

		$post_id = get_queried_object_id();

		if ( ! $post_id ) {
			return false;
		}

		return Tasty_Recipes::has_recipe( $post_id );
	}
}
