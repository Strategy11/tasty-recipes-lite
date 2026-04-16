<?php
/**
 * Tests the AI_Scraper_Prevention class.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\AI_Scraper_Prevention;

/**
 * Tests the AI Scraper Prevention feature.
 */
class Test_AI_Scraper_Prevention extends Tasty_Recipes_Testcase {

	/**
	 * Tear down after each test.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		delete_option( Tasty_Recipes::AI_SCRAPER_PREVENTION_OPTION );
		remove_all_filters( 'tasty_recipes_ai_scraper_bots' );
		parent::tearDown();
	}

	/**
	 * Verifies robots.txt output includes all AI bots when site is public.
	 */
	public function test_filter_robots_txt_appends_all_bots() {
		$output = "User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\n";
		$result = AI_Scraper_Prevention::filter_robots_txt( $output, true );

		foreach ( AI_Scraper_Prevention::AI_BOTS as $bot ) {
			$this->assertStringContainsString( "User-agent: {$bot}", $result );
			$this->assertStringContainsString( 'Disallow: /', $result );
		}
	}

	/**
	 * Verifies robots.txt is unchanged when site is not public.
	 */
	public function test_filter_robots_txt_skips_private_site() {
		$output = "User-agent: *\nDisallow: /\n";
		$result = AI_Scraper_Prevention::filter_robots_txt( $output, false );

		$this->assertSame( $output, $result );
	}

	/**
	 * Verifies duplicate user-agents are not added when already present.
	 */
	public function test_filter_robots_txt_skips_existing_bots() {
		$output = "User-agent: GPTBot\nDisallow: /\n\nUser-agent: CCBot\nDisallow: /\n";
		$result = AI_Scraper_Prevention::filter_robots_txt( $output, true );

		$this->assertSame( 1, substr_count( $result, 'User-agent: GPTBot' ) );
		$this->assertSame( 1, substr_count( $result, 'User-agent: CCBot' ) );
	}

	/**
	 * Verifies case-insensitive deduplication of user-agents.
	 */
	public function test_filter_robots_txt_case_insensitive_dedup() {
		$output = "User-agent: gptbot\nDisallow: /\n";
		$result = AI_Scraper_Prevention::filter_robots_txt( $output, true );

		$this->assertSame( 1, substr_count( strtolower( $result ), 'user-agent: gptbot' ) );
	}

	/**
	 * Verifies User-agent: * / Allow: / is appended when not already present.
	 */
	public function test_filter_robots_txt_adds_wildcard_allow() {
		$output = "User-agent: Googlebot\nAllow: /\n";
		$result = AI_Scraper_Prevention::filter_robots_txt( $output, true );

		$this->assertStringContainsString( "User-agent: *\nAllow: /", $result );
	}

	/**
	 * Verifies User-agent: * is not duplicated when already present.
	 */
	public function test_filter_robots_txt_skips_wildcard_when_present() {
		$output = "User-agent: *\nDisallow: /wp-admin/\n";
		$result = AI_Scraper_Prevention::filter_robots_txt( $output, true );

		$this->assertSame( 1, substr_count( $result, 'User-agent: *' ) );
	}

	/**
	 * Verifies get_blocked_bots returns the default list.
	 */
	public function test_get_blocked_bots_returns_defaults() {
		$bots = AI_Scraper_Prevention::get_blocked_bots();

		$this->assertSame( AI_Scraper_Prevention::AI_BOTS, $bots );
	}

	/**
	 * Verifies the tasty_recipes_ai_scraper_bots filter modifies the bot list.
	 */
	public function test_get_blocked_bots_is_filterable() {
		add_filter(
			'tasty_recipes_ai_scraper_bots',
			function () {
				return array( 'CustomBot' );
			}
		);

		$bots = AI_Scraper_Prevention::get_blocked_bots();

		$this->assertSame( array( 'CustomBot' ), $bots );
	}

	/**
	 * Verifies filtered bot list is used in robots.txt output.
	 */
	public function test_filter_robots_txt_uses_filtered_bots() {
		add_filter(
			'tasty_recipes_ai_scraper_bots',
			function () {
				return array( 'OnlyThisBot' );
			}
		);

		$result = AI_Scraper_Prevention::filter_robots_txt( '', true );

		$this->assertStringContainsString( 'User-agent: OnlyThisBot', $result );
		$this->assertStringNotContainsString( 'User-agent: GPTBot', $result );
		$this->assertStringNotContainsString( 'User-agent: ClaudeBot', $result );
	}

	/**
	 * Verifies maybe_load_hooks does not register filters when option is off.
	 */
	public function test_maybe_load_hooks_skips_when_disabled() {
		delete_option( Tasty_Recipes::AI_SCRAPER_PREVENTION_OPTION );

		AI_Scraper_Prevention::maybe_load_hooks();

		$this->assertFalse( has_filter( 'robots_txt', array( AI_Scraper_Prevention::class, 'filter_robots_txt' ) ) );
	}

	/**
	 * Verifies maybe_load_hooks registers filters when option is enabled.
	 */
	public function test_maybe_load_hooks_registers_when_enabled() {
		update_option( Tasty_Recipes::AI_SCRAPER_PREVENTION_OPTION, '1' );

		AI_Scraper_Prevention::maybe_load_hooks();

		$this->assertSame( 100000, has_filter( 'robots_txt', array( AI_Scraper_Prevention::class, 'filter_robots_txt' ) ) );
	}

	/**
	 * Verifies meta tag output on a recipe post.
	 */
	public function test_meta_tag_output_on_recipe_post() {
		$post_id = $this->prime_basic_test_post();
		$this->create_mock_recipe( $post_id );
		$this->go_to( get_permalink( $post_id ) );

		ob_start();
		AI_Scraper_Prevention::action_wp_head();
		$output = ob_get_clean();

		$this->assertStringContainsString( '<meta name="robots" content="noai, noimageai">', $output );
	}

	/**
	 * Verifies meta tag is not output on a non-recipe post.
	 */
	public function test_meta_tag_skips_non_recipe_post() {
		$post_id = $this->factory->post->create( array( 'post_content' => 'No recipe here.' ) );
		$this->go_to( get_permalink( $post_id ) );

		ob_start();
		AI_Scraper_Prevention::action_wp_head();
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	/**
	 * Verifies no search engine crawlers are in the default bot list.
	 */
	public function test_no_search_crawlers_in_default_list() {
		$search_crawlers = array( 'Googlebot', 'Bingbot', 'Applebot', 'Yandex', 'DuckDuckBot', 'Slurp' );
		$blocked         = AI_Scraper_Prevention::get_blocked_bots();

		foreach ( $search_crawlers as $crawler ) {
			$this->assertNotContains( $crawler, $blocked, "{$crawler} should not be blocked." );
		}
	}
}
