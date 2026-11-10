<?php
/**
 * Test case for the Tasty_Recipes\Ratings class.
 *
 * @package Tasty_Recipes
 */

namespace Tasty_Recipes_Pro {

	if ( ! class_exists( 'Tasty_Recipes_Pro\Ratings' ) ) {
		/**
		 * Stands in for Pro so quick ratings without a comment can be stored.
		 *
		 * Lite requires a comment for every quick rating, so `create_rating()` only runs with Pro.
		 */
		class Ratings {

			/**
			 * Minimum rating without a comment. 6 matches Lite.
			 *
			 * @var int
			 */
			public static $minimum = 6;

			/**
			 * Get the minimum rating without a comment.
			 *
			 * @return int
			 */
			public static function get_minimum_rating_without_comment() {
				return self::$minimum;
			}
		}
	}
}

namespace {

	use Tasty_Recipes\Meta_Keys;
	use Tasty_Recipes\Objects\Recipe;
	use Tasty_Recipes\Ratings;
	use Tasty_Recipes\Shortcodes;

	/**
	 * Test case for the Tasty_Recipes\Ratings class.
	 */
	class Test_Ratings extends Tasty_Recipes_Testcase {

		/**
		 * Rater cookie name.
		 *
		 * @var string
		 */
		const COOKIE = 'tasty_recipes_rater';

		/**
		 * Rater A's cookie token.
		 *
		 * @var string
		 */
		const TOKEN_A = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';

		/**
		 * Rater B's cookie token.
		 *
		 * @var string
		 */
		const TOKEN_B = 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb';

		/**
		 * Recipe embedded in the test post.
		 *
		 * @var Recipe
		 */
		private $recipe;

		/**
		 * Original $_SERVER values.
		 *
		 * @var array
		 */
		private $server = array();

		/**
		 * Number of comments posted, to keep comment text unique.
		 *
		 * @var int
		 */
		private $comment_count = 0;

		/**
		 * Creates a published post with one recipe, and a shared IP and User-Agent.
		 *
		 * @return void
		 */
		public function setUp(): void {
			parent::setUp();

			$this->server              = $_SERVER;
			$_SERVER['REMOTE_ADDR']     = '203.0.113.7';
			$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Shared Browser)';

			$this->recipe  = $this->create_mock_recipe();
			$this->post_id = $this->factory->post->create(
				array(
					'post_status'    => 'publish',
					'post_author'    => 1,
					'comment_status' => 'open',
					'post_content'   => Shortcodes::get_shortcode_for_recipe( $this->recipe ),
				)
			);

			add_filter( 'wp_is_comment_flood', '__return_false', 99 );
			$this->new_request();
		}

		/**
		 * Restores globals and filters.
		 *
		 * @return void
		 */
		public function tearDown(): void {
			$_SERVER = $this->server;
			unset( $_COOKIE[ self::COOKIE ] );
			$_POST = array();
			$this->new_request();
			remove_filter( 'wp_is_comment_flood', '__return_false', 99 );
			remove_action( 'comment_post', array( Ratings::class, 'delete_replaced_comment' ), 10 );
			\Tasty_Recipes_Pro\Ratings::$minimum = 6;

			parent::tearDown();
		}

		/**
		 * Start a new request from a browser with the given rater token.
		 *
		 * @param string $token Rater cookie token. Empty for no cookie.
		 *
		 * @return void
		 */
		private function new_request( $token = '' ) {
			foreach ( array( 'rater_token' => '', 'replaced_comment_id' => 0 ) as $property => $value ) {
				if ( property_exists( Ratings::class, $property ) ) {
					$reflection = new ReflectionProperty( Ratings::class, $property );
					$reflection->setAccessible( true );
					$reflection->setValue( null, $value );
				}
			}

			if ( $token ) {
				$_COOKIE[ self::COOKIE ] = $token;
			} else {
				unset( $_COOKIE[ self::COOKIE ] );
			}
		}

		/**
		 * Call a private static Ratings method.
		 *
		 * @param string $method Method name.
		 * @param array  $args   Arguments.
		 *
		 * @return mixed
		 */
		private function call_private( $method, $args = array() ) {
			$reflection = new ReflectionMethod( Ratings::class, $method );
			$reflection->setAccessible( true );
			return $reflection->invokeArgs( null, $args );
		}

		/**
		 * Post a comment through the comment form with a rating.
		 *
		 * @param mixed          $rating   Submitted rating.
		 * @param int|string     $approved Approval status for the new comment.
		 * @param array          $args     Extra comment data.
		 *
		 * @return int|WP_Error Comment ID.
		 */
		private function post_comment_form( $rating, $approved = 1, $args = array() ) {
			++$this->comment_count;
			$date = gmdate( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS + $this->comment_count * MINUTE_IN_SECONDS );
			$_POST['tasty-recipes-rating'] = $rating;

			$set_approved = function () use ( $approved ) {
				return $approved;
			};
			add_filter( 'pre_comment_approved', $set_approved, 99 );

			$comment_id = wp_new_comment(
				array_merge(
					array(
						'comment_post_ID'      => $this->post_id,
						'comment_author'       => 'Alice',
						'comment_author_email' => 'alice@example.com',
						'comment_author_url'   => '',
						'comment_content'      => 'Lovely recipe number ' . $this->comment_count,
						'comment_type'         => 'comment',
						// Each comment a minute after the last, like separate visits.
						'comment_date'         => $date,
						'comment_date_gmt'     => $date,
					),
					$args
				),
				true
			);

			remove_filter( 'pre_comment_approved', $set_approved, 99 );
			unset( $_POST['tasty-recipes-rating'] );

			return $comment_id;
		}

		/**
		 * Send a quick rating AJAX request and return the decoded JSON response.
		 *
		 * @param mixed $rating Submitted rating.
		 *
		 * @return array
		 */
		private function send_quick_rating( $rating ) {
			$_POST = array(
				'nonce'     => wp_create_nonce( 'tasty-recipes-save-rating' ),
				'rating'    => $rating,
				'post_id'   => $this->post_id,
				'recipe_id' => $this->recipe->get_id(),
			);
			// check_ajax_referer() reads the nonce from $_REQUEST.
			$_REQUEST['nonce'] = $_POST['nonce'];

			add_filter( 'wp_doing_ajax', '__return_true' );
			add_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler' ) );

			ob_start();
			try {
				Ratings::save_rating();
			} catch ( WPDieException $e ) {
				// Expected: wp_send_json_*() ends the request.
				unset( $e );
			}
			$output = ob_get_clean();

			remove_filter( 'wp_doing_ajax', '__return_true' );
			remove_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler' ) );
			$_POST = array();
			unset( $_REQUEST['nonce'] );

			return json_decode( $output, true );
		}

		/**
		 * Get the stored rating of a comment.
		 *
		 * @param int $comment_id Comment ID.
		 *
		 * @return string
		 */
		private function get_rating( $comment_id ) {
			return get_comment_meta( $comment_id, Ratings::COMMENT_META_KEY, true );
		}

		/**
		 * Get the post's rated comment IDs that are not in the trash.
		 *
		 * @return int[]
		 */
		private function get_rated_comment_ids() {
			return array_map(
				'intval',
				get_comments(
					array(
						'post_id'  => $this->post_id,
						'status'   => 'all',
						'fields'   => 'ids',
						'meta_key' => Ratings::COMMENT_META_KEY, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					)
				)
			);
		}

		/**
		 * Whether a comment is still counted (exists and not trashed or spam).
		 *
		 * @param int $comment_id Comment ID.
		 *
		 * @return bool
		 */
		private function comment_exists( $comment_id ) {
			return in_array( wp_get_comment_status( $comment_id ), array( 'approved', 'unapproved' ), true );
		}

		/**
		 * Reload the recipe so the stored rating is read again.
		 *
		 * @return Recipe
		 */
		private function get_recipe() {
			return Recipe::get_by_id( $this->recipe->get_id() );
		}

		/**
		 * Only whole ratings from 1 to 5 are valid.
		 *
		 * @return void
		 */
		public function test_is_valid_rating() {
			foreach ( array( 1, 2, 3, 4, 5, '5', '1', 5.0 ) as $rating ) {
				$this->assertTrue( $this->call_private( 'is_valid_rating', array( $rating ) ), var_export( $rating, true ) );
			}
			foreach ( array( 0, 6, 4.5, '4.5', 'abc', 50000, '50000', -1, '', null, '5abc' ) as $rating ) {
				$this->assertFalse( $this->call_private( 'is_valid_rating', array( $rating ) ), var_export( $rating, true ) );
			}
		}

		/**
		 * The comment form only stores ratings from 1 to 5, but still saves the comment.
		 *
		 * @return void
		 */
		public function test_comment_form_rejects_out_of_range_ratings() {
			$this->new_request( self::TOKEN_A );

			foreach ( array( 0, 6, '4.5', 'abc', 50000 ) as $rating ) {
				$comment_id = $this->post_comment_form( $rating );
				$this->assertIsInt( $comment_id, var_export( $rating, true ) );
				$this->assertSame( '', $this->get_rating( $comment_id ), var_export( $rating, true ) );
				$this->assertSame( '', get_comment_meta( $comment_id, Ratings::RATING_COMMENT_HASH_META_KEY, true ) );
			}

			$comment_id = $this->post_comment_form( '5' );
			$this->assertSame( '5', $this->get_rating( $comment_id ) );
		}

		/**
		 * The REST API only stores ratings from 1 to 5.
		 *
		 * @return void
		 */
		public function test_rest_rejects_out_of_range_ratings() {
			foreach ( array( 0, 6, 4.5, 'abc', 50000, 5 ) as $rating ) {
				$comment_id = $this->factory->comment->create( array( 'comment_post_ID' => $this->post_id ) );
				$request    = new WP_REST_Request( 'POST', '/wp/v2/comments' );
				$request->set_param( 'meta', array( 'tasty-recipes-rating' => $rating ) );

				Ratings::action_rest_insert_comment( get_comment( $comment_id ), $request );

				$expected = 5 === $rating ? '5' : '';
				$this->assertSame( $expected, $this->get_rating( $comment_id ), var_export( $rating, true ) );
			}
		}

		/**
		 * Quick ratings outside 1 to 5 are refused and nothing is stored.
		 *
		 * The request value is passed through intval(), so 4.5 is stored as 4 stars.
		 *
		 * @return void
		 */
		public function test_quick_rating_rejects_out_of_range_ratings() {
			\Tasty_Recipes_Pro\Ratings::$minimum = 1;
			$this->new_request( self::TOKEN_A );

			foreach ( array( 0, 6, 'abc', 50000 ) as $rating ) {
				$response = $this->send_quick_rating( $rating );
				$this->assertFalse( $response['success'], var_export( $rating, true ) );
				$this->assertSame( array(), $this->get_rated_comment_ids(), var_export( $rating, true ) );
			}

			$response = $this->send_quick_rating( '4.5' );
			$this->assertTrue( $response['success'] );
			$comment_ids = $this->get_rated_comment_ids();
			$this->assertCount( 1, $comment_ids );
			$this->assertSame( '4', $this->get_rating( $comment_ids[0] ) );

			$response = $this->send_quick_rating( 5 );
			$this->assertTrue( $response['success'] );
			$this->assertSame( $comment_ids, $this->get_rated_comment_ids() );
			$this->assertSame( '5', $this->get_rating( $comment_ids[0] ) );
		}

		/**
		 * In Lite every quick rating needs a comment, so none are stored.
		 *
		 * @return void
		 */
		public function test_lite_quick_rating_without_comment_is_not_stored() {
			$this->new_request( self::TOKEN_A );

			$response = $this->send_quick_rating( 5 );

			$this->assertTrue( $response['success'] );
			$this->assertSame( array(), $this->get_rated_comment_ids() );
		}

		/**
		 * Stored ratings outside 1 to 5 are left out of the average.
		 *
		 * @return void
		 */
		public function test_update_recipe_rating_ignores_out_of_range_values() {
			foreach ( array( 4, 5, 50000, 0, 6, -3, 'abc', 4.5 ) as $rating ) {
				$comment_id = $this->factory->comment->create(
					array(
						'comment_post_ID'  => $this->post_id,
						'comment_approved' => 1,
					)
				);
				update_comment_meta( $comment_id, Ratings::COMMENT_META_KEY, $rating );
			}

			Ratings::update_recipe_rating( $this->recipe, $this->post_id );

			$recipe = $this->get_recipe();
			$this->assertSame( 3, (int) $recipe->get_total_reviews() );
			$this->assertEquals( 4.5, $recipe->get_average_rating() );
		}

		/**
		 * A browser without a rater cookie has no hash, and an empty hash matches nothing.
		 *
		 * @return void
		 */
		public function test_no_cookie_has_no_hash() {
			$this->new_request( self::TOKEN_A );
			$comment_id = $this->post_comment_form( 4 );
			$this->assertNotEmpty( get_comment_meta( $comment_id, Ratings::RATING_COMMENT_HASH_META_KEY, true ) );

			$this->new_request();
			$this->assertSame( '', $this->call_private( 'generate_comment_hash', array( $this->post_id ) ) );
			$this->assertSame( 0, $this->call_private( 'get_comment_id_by_hash', array( '', $this->post_id ) ) );
			$this->assertSame( 0, $this->call_private( 'get_comment_id_by_hash', array( '' ) ) );
		}

		/**
		 * A malformed cookie is treated as no cookie.
		 *
		 * @return void
		 */
		public function test_invalid_cookie_has_no_hash() {
			$this->new_request( 'short' );
			$this->assertSame( '', $this->call_private( 'generate_comment_hash', array( $this->post_id ) ) );
		}

		/**
		 * Two logged-out raters on the same IP and browser can't find or replace each other's rating.
		 *
		 * @return void
		 */
		public function test_raters_with_same_ip_and_user_agent_are_kept_apart() {
			$this->new_request( self::TOKEN_A );
			$comment_a = $this->post_comment_form( 4 );
			$hash_a    = $this->call_private( 'generate_comment_hash', array( $this->post_id ) );

			$this->new_request( self::TOKEN_B );
			$hash_b = $this->call_private( 'generate_comment_hash', array( $this->post_id ) );
			$this->assertNotSame( $hash_a, $hash_b );
			$this->assertSame( array( $hash_b, 0 ), $this->call_private( 'get_previous_comment', array( $this->post_id ) ) );

			$comment_b = $this->post_comment_form(
				2,
				1,
				array(
					'comment_author'       => 'Bob',
					'comment_author_email' => 'bob@example.com',
				)
			);

			$this->assertTrue( $this->comment_exists( $comment_a ) );
			$this->assertTrue( $this->comment_exists( $comment_b ) );
			$this->assertSame( '4', $this->get_rating( $comment_a ) );
			$this->assertSame( '2', $this->get_rating( $comment_b ) );
			$this->assertSame( $hash_a, get_comment_meta( $comment_a, Ratings::RATING_COMMENT_HASH_META_KEY, true ) );
			$this->assertSame( $hash_b, get_comment_meta( $comment_b, Ratings::RATING_COMMENT_HASH_META_KEY, true ) );

			// Rater A still finds their own rating.
			$this->new_request( self::TOKEN_A );
			$this->assertSame( (int) $comment_a, $this->call_private( 'get_comment_id_by_hash', array( $hash_a, $this->post_id ) ) );
		}

		/**
		 * A quick rating never returns another rater's comment, name or email.
		 *
		 * @return void
		 */
		public function test_quick_rating_does_not_return_another_raters_comment() {
			$this->new_request( self::TOKEN_A );
			$this->post_comment_form( 4 );

			foreach ( array( self::TOKEN_B, '' ) as $token ) {
				$this->new_request( $token );
				$response = $this->send_quick_rating( 3 );

				$this->assertTrue( $response['success'] );
				$this->assertSame( array(), $response['data']['comment'] );
				$this->assertStringNotContainsString( 'alice@example.com', wp_json_encode( $response ) );
			}

			// Rater A gets their own comment text back, without name or email.
			$this->new_request( self::TOKEN_A );
			$response = $this->send_quick_rating( 3 );
			$this->assertSame( array( 'content' => 'Lovely recipe number 1' ), $response['data']['comment'] );
		}

		/**
		 * A quick rating from rater B creates their own rating instead of changing rater A's.
		 *
		 * @return void
		 */
		public function test_quick_rating_does_not_change_another_raters_rating() {
			\Tasty_Recipes_Pro\Ratings::$minimum = 1;

			$this->new_request( self::TOKEN_A );
			$comment_a = $this->post_comment_form( 4 );

			$this->new_request( self::TOKEN_B );
			$response = $this->send_quick_rating( 1 );

			$this->assertTrue( $response['success'] );
			$this->assertSame( '4', $this->get_rating( $comment_a ) );
			$this->assertCount( 2, $this->get_rated_comment_ids() );
		}

		/**
		 * Comment responses only include approved text, never the author's name or email.
		 *
		 * @return void
		 */
		public function test_prepare_comment_response_has_no_author_details() {
			$comment_id = $this->factory->comment->create(
				array(
					'comment_post_ID'      => $this->post_id,
					'comment_author'       => 'Alice',
					'comment_author_email' => 'alice@example.com',
					'comment_content'      => 'Approved text',
					'comment_approved'     => 1,
				)
			);

			$response = $this->call_private( 'prepare_comment_response', array( get_comment( $comment_id ) ) );
			$this->assertSame( array( 'content' => 'Approved text' ), $response );

			wp_set_comment_status( $comment_id, 'hold' );
			$response = $this->call_private( 'prepare_comment_response', array( get_comment( $comment_id ) ) );
			$this->assertSame( array(), $response );
			$this->assertSame( array(), $this->call_private( 'prepare_comment_response', array( false ) ) );
		}

		/**
		 * Logged-in raters keep the same hash as before.
		 *
		 * @return void
		 */
		public function test_logged_in_hash_is_unchanged() {
			$user_id = $this->factory->user->create();
			wp_set_current_user( $user_id );
			$this->new_request( self::TOKEN_A );

			$params = array( $this->post_id, '203.0.113.7', 'Mozilla/5.0 (Shared Browser)', $user_id );
			if ( defined( 'AUTH_SALT' ) && ! empty( AUTH_SALT ) ) {
				$params[] = AUTH_SALT;
			}

			$this->assertSame( md5( implode( '::', $params ) ), $this->call_private( 'generate_comment_hash', array( $this->post_id ) ) );
		}

		/**
		 * A new rated comment replaces the rater's earlier one once it is saved.
		 *
		 * @return void
		 */
		public function test_new_rating_replaces_earlier_one_after_save() {
			$this->new_request( self::TOKEN_A );
			$first = $this->post_comment_form( 2 );

			$this->new_request( self::TOKEN_A );
			$second = $this->post_comment_form( 4 );

			$this->assertIsInt( $second );
			$this->assertFalse( $this->comment_exists( $first ) );
			$this->assertTrue( $this->comment_exists( $second ) );
			$this->assertSame( array( $second ), $this->get_rated_comment_ids() );
			$this->assertEquals( 4, $this->get_recipe()->get_average_rating() );
		}

		/**
		 * The earlier rating is kept when the new comment is not saved.
		 *
		 * @return void
		 */
		public function test_earlier_rating_kept_when_new_comment_fails() {
			$this->new_request( self::TOKEN_A );
			$first = $this->post_comment_form( 2 );

			$this->new_request( self::TOKEN_A );
			$second = $this->post_comment_form( 4, new WP_Error( 'comment_rejected', 'Rejected' ) );

			$this->assertWPError( $second );
			$this->assertTrue( $this->comment_exists( $first ) );
		}

		/**
		 * The earlier rating is kept when the new comment is spam or trash.
		 *
		 * @return void
		 */
		public function test_earlier_rating_kept_when_new_comment_is_spam_or_trash() {
			$this->new_request( self::TOKEN_A );
			$first = $this->post_comment_form( 2 );

			foreach ( array( 'spam', 'trash' ) as $status ) {
				$this->new_request( self::TOKEN_A );
				$this->post_comment_form( 4, $status );

				$this->assertTrue( $this->comment_exists( $first ), $status );
				$this->assertEquals( 2, $this->get_recipe()->get_average_rating(), $status );
			}
		}

		/**
		 * A held replacement keeps the earlier rating counted until it is approved.
		 *
		 * @return void
		 */
		public function test_held_replacement_only_replaces_once_approved() {
			$this->new_request( self::TOKEN_A );
			$first = $this->post_comment_form( 2 );

			$this->new_request( self::TOKEN_A );
			$second = $this->post_comment_form( 4, 0 );

			$this->assertTrue( $this->comment_exists( $first ) );
			$this->assertSame( 'approved', wp_get_comment_status( $first ) );
			$this->assertSame( 'unapproved', wp_get_comment_status( $second ) );
			$this->assertEquals( 2, $this->get_recipe()->get_average_rating() );
			$this->assertSame( 1, (int) $this->get_recipe()->get_total_reviews() );

			wp_set_comment_status( $second, 'approve' );

			$this->assertFalse( $this->comment_exists( $first ) );
			$this->assertSame( 'approved', wp_get_comment_status( $second ) );
			$this->assertSame( '', get_comment_meta( $second, Meta_Keys::RATING_REPLACES_COMMENT, true ) );
			$this->assertEquals( 4, $this->get_recipe()->get_average_rating() );
			$this->assertSame( 1, (int) $this->get_recipe()->get_total_reviews() );
		}

		/**
		 * A newer approved rating also removes the earlier one a held rating was waiting to replace.
		 *
		 * @return void
		 */
		public function test_approved_rating_replaces_held_rating_and_the_one_before() {
			$this->new_request( self::TOKEN_A );
			$first = $this->post_comment_form( 2 );

			$this->new_request( self::TOKEN_A );
			$held = $this->post_comment_form( 3, 0 );

			$this->new_request( self::TOKEN_A );
			$third = $this->post_comment_form( 5 );

			$this->assertFalse( $this->comment_exists( $first ) );
			$this->assertFalse( $this->comment_exists( $held ) );
			$this->assertSame( array( $third ), $this->get_rated_comment_ids() );
			$this->assertEquals( 5, $this->get_recipe()->get_average_rating() );
		}

		/**
		 * A held replacement that is spammed later does not remove the earlier rating.
		 *
		 * @return void
		 */
		public function test_held_replacement_marked_spam_keeps_earlier_rating() {
			$this->new_request( self::TOKEN_A );
			$first = $this->post_comment_form( 2 );

			$this->new_request( self::TOKEN_A );
			$second = $this->post_comment_form( 4, 0 );

			wp_spam_comment( $second );

			$this->assertTrue( $this->comment_exists( $first ) );
			$this->assertEquals( 2, $this->get_recipe()->get_average_rating() );
		}
	}
}
