<?php
/**
 * Base test class to use for all tests.
 *
 * @package Tasty_Recipes
 */

use Masterminds\HTML5;
use Tasty_Recipes\Objects\Recipe;
use Tasty_Recipes\Ratings;
use Tasty_Recipes\Shortcodes;

/**
 * Base test class to use for all tests.
 */
class Tasty_Recipes_Testcase extends WP_UnitTestCase {

	/**
	 * Reference to an image imported during tests.
	 *
	 * @var int
	 */
	protected $imported_image_id = 0;

	/**
	 * Actual API requests that were made.
	 *
	 * @var array
	 */
	protected $actual_api_requests = array();

	/**
	 * Creates base data and registers filters for every test.
	 */
	public function setUp(): void {
		parent::setUp();

		if ( class_exists( '\Yoast\WP\SEO\Memoizers\Meta_Tags_Context_Memoizer' ) ) {
			// Clear Yoast SEO meta.
			// phpcs:ignore PHPCompatibility.Constants.NewMagicClassConstant.Found
			$memoizer = YoastSEO()->classes->get( \Yoast\WP\SEO\Memoizers\Meta_Tags_Context_Memoizer::class );
			$memoizer->clear( 'current_page' );
		}

		Tasty_Recipes\Content_Model::action_init_register_rewrite_rules();

		add_filter( 'pre_http_request', array( $this, 'filter_pre_http_request' ), 10, 3 );
		add_action( 'tasty_recipes_imported_image', array( $this, 'action_tasty_recipes_imported_image' ) );

		// Reset the expected API request variables.
		$this->reset_api_tracking_vars();
	}

	/**
	 * Gets a dom object for a HTML string.
	 *
	 * @param string $content Existing HTML string.
	 *
	 * @return object
	 */
	protected function get_dom( $content ) {
		$html5 = new HTML5();
		return $html5->loadHTML( $content );
	}

	/**
	 * Primes a basic post to test against.
	 */
	protected function prime_basic_test_post() {
		$this->post_id = $this->factory->post->create(
			array(
				'post_status' => 'publish',
				'post_author' => 1,
			)
		);
		wp_update_user(
			array(
				'ID'           => 1,
				'display_name' => 'The Administrator',
			)
		);
		$this->go_to( get_permalink( $this->post_id ) );
	}

	/**
	 * Primes a password-protected post with an embedded recipe.
	 *
	 * @since x.x
	 *
	 * @param string $password Post password.
	 *
	 * @return Recipe
	 */
	protected function prime_password_protected_recipe_post( $password = 'secret' ) {
		$recipe        = $this->create_mock_recipe();
		$this->post_id = $this->factory->post->create(
			array(
				'post_status'   => 'publish',
				'post_author'   => 1,
				'post_password' => $password,
				'post_content'  => Shortcodes::get_shortcode_for_recipe( $recipe ),
			)
		);
		$this->go_to( get_permalink( $this->post_id ) );
		return $recipe;
	}

	/**
	 * Sets the cookie WordPress uses to remember an entered post password.
	 *
	 * @since x.x
	 *
	 * @param string $password Post password.
	 *
	 * @return void
	 */
	protected function enter_post_password( $password = 'secret' ) {
		require_once ABSPATH . WPINC . '/class-phpass.php';
		$hasher = new PasswordHash( 8, true );

		$_COOKIE[ 'wp-postpass_' . COOKIEHASH ] = $hasher->HashPassword( $password );
	}

	/**
	 * Adds a featured image to the test post.
	 */
	protected function add_featured_image_to_post() {
		$file          = __DIR__ . '/data/canola.jpg';
		$attachment_id = $this->create_upload_object( $file );
		set_post_thumbnail( $this->post_id, $attachment_id );
	}

	/**
	 * Adds an inline image that's uploaded to the media library to the test post.
	 */
	protected function add_inline_image_to_post() {
		$file          = __DIR__ . '/data/canola.jpg';
		$attachment_id = $this->create_upload_object( $file );
		$post          = get_post( $this->post_id );
		$image_html    = get_image_send_to_editor( $attachment_id, '', '', 'none' );
		$post_content  = $post->post_content . PHP_EOL . PHP_EOL . $image_html;
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_content' => $post_content,
			)
		);
		// Refresh the main queried object.
		$this->go_to( get_permalink( $this->post_id ) );
	}

	/**
	 * Creates a standard mock recipe for use in tests.
	 *
	 * @return Recipe
	 */
	protected function create_mock_recipe() {
		$recipe = Recipe::create();
		$recipe->set_title( 'West Indian Beans & Coconut Rice' );
		$recipe->set_author_name( 'Moosewood Restaurant Simple Suppers' );
		$recipe->set_description( "It's so tasty" );
		$instructions = <<<EOT
<p></p>
<p>&nbsp;</p>
<p>&nbsp; </p>
<p> &nbsp;</p>
<p> &nbsp; </p>
<p>
&nbsp;
</p>

How to make the rice:

Rinse and drain the rice. In a saucepan on medium-high heat, sauté the rice, turmeric, and salt in the oil for a minute or two, stirring constantly. Add the water and coconut milk, bring to a boil, cover, and reduce the heat to low. Simmer until the rice is tender and the liquid absorbed, 15 to 20 minutes.

How to make the beans:

While the rice is cooking, in a saucepan on medium heat, sauté the onions in the oil for 3 or 4 minutes. Add the red pepper flakes, sprinkle lightly with salt, cover, and cook, stirring occasionally, until the onions are soft, 5 to 8 minutes. Add the thyme, allspice, tomatoes, and beans, cover, and simmer for 10 to 15 minutes, stirring occasionally.

&nbsp;
<p></p>
<p>&nbsp;</p>
<p>&nbsp; </p>
<p> &nbsp;</p>
<p> &nbsp; </p>
<p>
&nbsp;
</p>
&nbsp;
EOT;
		$recipe->set_instructions( $instructions );
		$ingredients = <<<EOT
<p></p>
<p>&nbsp;</p>
<p>&nbsp; </p>
<p> &nbsp;</p>
<p> &nbsp; </p>
<p>
&nbsp;
</p>



For The Red Beans:

1 1/2cups diced onions
1 tablespoon vegetable oil
1/2 teaspoon red pepper flakes or 1 fresh chile, minced
1/2 teaspoon dried thyme
Salt
1/4 teaspoon allspice
One 28-ounce can of diced tomatoes
One 28-ounce can of red kidney beans, drained
Chopped scallions and/or cilantro

For The Coconut Rice:
1 1/2 cups white rice
1/2 teaspoon turmeric
1/2 teaspoon salt
1 teaspoon vegetable oil
1 1/2 cups water
3/4 cup coconut milk

<p></p>
<p>&nbsp;</p>
<p>&nbsp; </p>
<p> &nbsp;</p>
<p> &nbsp; </p>
<p>
&nbsp;
</p>

EOT;
		$recipe->set_ingredients( $ingredients );
		$recipe->set_notes( 'Make sure to eat it all!' );
		$recipe->set_keywords( 'apples, pie' );
		$recipe->set_prep_time( '15 minutes' );
		$recipe->set_cook_time( '1 hour 20 minutes' );
		$recipe->set_yield( '2 servings' );
		$recipe->set_category( 'Main' );
		$recipe->set_cuisine( 'Caribbean' );
		$recipe->set_diet( 'Low Fat' );
		$recipe->set_method( 'Cooking' );
		$recipe->set_serving_size( '2 cups' );
		$recipe->set_calories( '581 calories' );
		$recipe->set_fat( '10 g' );
		$recipe->set_saturated_fat( '2 g' );
		$recipe->set_unsaturated_fat( '0 g' );
		$recipe->set_trans_fat( '1 g' );
		$recipe->set_carbohydrates( '102 g' );
		$recipe->set_protein( '18 g' );
		$recipe->set_sodium( '1863 mg' );
		$recipe->set_fiber( '12 g' );
		$recipe->set_sugar( '0 g' );
		$recipe->set_cholesterol( '120 mg' );
		return $recipe;
	}

	/**
	 * Adds an image to the standard mocked recipe.
	 *
	 * @return Recipe
	 */
	protected function create_mock_recipe_with_image() {
		$file                = __DIR__ . '/data/canola.jpg';
		$this->attachment_id = $this->create_upload_object( $file );
		$recipe              = $this->create_mock_recipe();
		$recipe->set_image_id( $this->attachment_id );
		return $recipe;
	}

	/**
	 * Adds ratings to the standard mocked recipe.
	 *
	 * @return Recipe
	 */
	protected function create_mock_recipe_with_ratings() {
		$recipe = $this->create_mock_recipe();
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_content' => Shortcodes::get_shortcode_for_recipe( $recipe ),
			)
		);
		$this->create_mock_ratings_for_post( $this->post_id );
		return $recipe;
	}

	/**
	 * Adds Nutrifox information to the standard mocked recipe.
	 *
	 * @return Recipe
	 */
	protected function create_mock_recipe_with_nutrifox_id() {
		$recipe = $this->create_mock_recipe();
		// Setting the id also fetches the remote data.
		$recipe->set_nutrifox_id( 7500 );
		return $recipe;
	}

	/**
	 * Adds Vimeo video URL to the standard mocked recipe.
	 *
	 * @return Recipe
	 */
	protected function create_mock_recipe_with_vimeo_url() {
		$recipe = $this->create_mock_recipe();
		// Setting the video URL also fetches the remote data.
		$recipe->set_video_url( 'https://vimeo.com/261212868' );
		return $recipe;
	}

	/**
	 * Adds MediaVine URL to the standard mocked recipe.
	 *
	 * @return Recipe
	 */
	protected function create_mock_recipe_with_mediavine_url() {
		$recipe = $this->create_mock_recipe();
		// Setting the video URL also fetches the remote data.
		$recipe->set_video_url( 'https://dashboard.mediavine.com/videos/yqm6njhxgp1xf9he5sjm/edit' );
		return $recipe;
	}

	/**
	 * Adds YouTube URL to the standard mocked recipe.
	 *
	 * @return Recipe
	 */
	protected function create_mock_recipe_with_youtube_url() {
		$recipe = $this->create_mock_recipe();
		// Setting the video URL also fetches the remote data.
		$recipe->set_video_url( 'https://www.youtube.com/watch?v=GRtWNZwgGn8' );
		return $recipe;
	}

	/**
	 * Adds AdThrive shortcode to the standard mocked recipe.
	 *
	 * @return Recipe
	 */
	protected function create_mock_recipe_with_adthrive_shortcode() {
		$recipe = $this->create_mock_recipe();
		$recipe->set_video_url( '[adthrive-in-post-video-player video-id="iVKGcLPq" upload-date="2017-02-24T20:40:06.000Z" name="Black Pepper Stir Fried Noodles" description="Black Pepper Stir Fried Noodles - this simple 30 minute stir fry is packed with AMAZING flavor!"]' );
		return $recipe;
	}

	/**
	 * Creates mocked ratings for a given post.
	 *
	 * @param int $post_id Post ID to create ratings for.
	 */
	protected function create_mock_ratings_for_post( $post_id ) {
		// Only four of the comments have valid ratings.
		$comment_id_1 = $this->factory->comment->create(
			array(
				'comment_approved' => 0,
				'comment_post_ID'  => $post_id,
				'comment_content'  => 'First comment',
				'comment_author'   => 'First author',
				'comment_date'     => '2017-01-01 12:00',
			)
		);
		update_comment_meta( $comment_id_1, Ratings::COMMENT_META_KEY, 1 );
		$comment_id_2 = $this->factory->comment->create(
			array(
				'comment_approved' => 0,
				'comment_post_ID'  => $post_id,
				'comment_content'  => 'Second comment',
				'comment_author'   => 'Second author',
				'comment_date'     => '2017-01-02 12:00',
			)
		);
		update_comment_meta( $comment_id_2, Ratings::COMMENT_META_KEY, 3 );
		$comment_id_3 = $this->factory->comment->create(
			array(
				'comment_approved' => 0,
				'comment_post_ID'  => $post_id,
				'comment_content'  => 'Third comment',
				'comment_author'   => 'Third author',
				'comment_date'     => '2017-01-03 12:00',
			)
		);
		update_comment_meta( $comment_id_3, Ratings::COMMENT_META_KEY, 4 );
		update_comment_meta( $comment_id_3, Ratings::CB_COMMENT_META_KEY, 4 );
		$comment_id_4 = $this->factory->comment->create(
			array(
				'comment_approved' => 0,
				'comment_post_ID'  => $post_id,
				'comment_content'  => 'Fourth comment',
				'comment_author'   => 'Fourth author',
				'comment_date'     => '2017-01-04 12:00',
			)
		);
		update_comment_meta( $comment_id_4, Ratings::COMMENT_META_KEY, 0 );
		$comment_id_5 = $this->factory->comment->create(
			array(
				'comment_approved' => 0,
				'comment_post_ID'  => $post_id,
				'comment_content'  => 'Fifth comment',
				'comment_author'   => 'Fifth author',
				'comment_date'     => '2017-01-05 12:00',
			)
		);
		update_comment_meta( $comment_id_5, Ratings::CB_COMMENT_META_KEY, 2 );
		$comment_id_6 = $this->factory->comment->create(
			array(
				'comment_approved' => 0,
				'comment_post_ID'  => $post_id,
				'comment_content'  => 'Sixth comment',
				'comment_author'   => 'Sixth author',
				'comment_date'     => '2017-01-06 12:00',
			)
		);
		$comment_id_7 = $this->factory->comment->create(
			array(
				'comment_approved' => 0,
				'comment_post_ID'  => $post_id,
				'comment_content'  => 'Seventh comment',
				'comment_author'   => 'Seventh author',
				'comment_date'     => '2017-01-07 12:00',
			)
		);
		update_comment_meta( $comment_id_7, Ratings::SRP_COMMENT_META_KEY, 4 );
		wp_set_comment_status( $comment_id_1, 'approve' );
		wp_set_comment_status( $comment_id_2, 'approve' );
		wp_set_comment_status( $comment_id_3, 'approve' );
		wp_set_comment_status( $comment_id_4, 'approve' );
		wp_set_comment_status( $comment_id_5, 'approve' );
		wp_set_comment_status( $comment_id_6, 'approve' );
		wp_set_comment_status( $comment_id_7, 'approve' );
	}

	/**
	 * Creates an attachment object from a file.
	 *
	 * @param string $file   Path to the file.
	 * @param int    $parent Parent post ID, if one needs to be set.
	 *
	 * @return int
	 */
	protected function create_upload_object( $file, $parent = 0 ) {
		$contents = file_get_contents( $file );
		$upload   = wp_upload_bits( basename( $file ), null, $contents );

		$type = '';
		if ( ! empty( $upload['type'] ) ) {
			$type = $upload['type'];
		} else {
			$mime = wp_check_filetype( $upload['file'] );
			if ( $mime ) {
				$type = $mime['type'];
			}
		}

		$attachment = array(
			'post_title'     => basename( $upload['file'] ),
			'post_content'   => '',
			'post_type'      => 'attachment',
			'post_parent'    => $parent,
			'post_mime_type' => $type,
			'guid'           => $upload['url'],
		);

		// Save the data.
		$id = wp_insert_attachment( $attachment, $upload['file'], $parent );
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );

		return $id;
	}

	/**
	 * Set up permalink structure.
	 */
	protected function setup_permalink_structure() {
		global $wp_rewrite;
		update_option( 'permalink_structure', 'blog/%postname%' );
		$structure = get_option( 'permalink_structure' );
		$wp_rewrite->init();
		$wp_rewrite->set_permalink_structure( $structure );
		create_initial_taxonomies();
		$wp_rewrite->flush_rules();
	}

	/**
	 * Shorthand for a test that requires a specific WP version.
	 *
	 * @param string $version WordPress version to check.
	 */
	protected function skip_if_less_than_wp_version( $version ) {
		if ( ! version_compare( str_replace( '-src', '', $GLOBALS['wp_version'] ), $version, '>=' ) ) {
			$this->markTestSkipped( 'Only runs on WP ' . $version . ' and higher' );
		}
	}

	/**
	 * Filter remote requests to avoid actual HTTP request in tests.
	 *
	 * @param mixed  $ret Existing filter value.
	 * @param array  $r   Request arguments.
	 * @param string $url Request URL.
	 *
	 * @return mixed
	 */
	public function filter_pre_http_request( $ret, $r, $url ) {

		$host = parse_url( $url, PHP_URL_HOST );

		$this->actual_api_requests[ $host ] = array(
			'method' => $r['method'],
			'path'   => parse_url( $url, PHP_URL_PATH ),
			'query'  => null,
			'body'   => null,
		);

		if ( ! empty( $r['body'] ) ) {
			$body = $r['body'];
			if ( is_string( $body ) ) {
				$json_body = json_decode( $body, true );
				if ( false !== $json_body ) {
					$body = $json_body;
				}
			}
			$this->actual_api_requests[ $host ]['body'] = $body;
		}
		$query_args = parse_url( $url, PHP_URL_QUERY );
		if ( $query_args ) {
			parse_str( $query_args, $this->actual_api_requests[ $host ]['query'] );
		}

		if ( empty( $r['filename'] ) ) {
			return $ret;
		}

		$file = __DIR__ . '/data/' . basename( $url );
		if ( file_exists( $file ) ) {
			copy( $file, $r['filename'] );
			// PHP bug, see https://wordpress.slack.com/archives/core/p1479883406003700.
			if ( version_compare( phpversion(), '5.4', '<' ) ) {
				clearstatcache();
			}
			return array(
				'body'     => '',
				'response' => array(
					'code'    => 200,
					'message' => 'OK',
				),
				'filename' => $r['filename'],
			);
		}
		return array(
			'body'     => '',
			'response' => array(
				'code'    => 404,
				'message' => 'OK',
			),
			'filename' => $r['filename'],
		);
	}

	/**
	 * Asserts that an API request was performed through the HTTP API.
	 *
	 * @param mixed $expected_host Expected host value.
	 * @param array $expected_args Expected request arguments.
	 */
	protected function assertExpectedAPIRequest( $expected_host, $expected_args = array() ) {
		$this->assertArrayHasKey( $expected_host, $this->actual_api_requests );
		$default_args  = array(
			'method' => null,
			'path'   => null,
			'query'  => null,
			'body'   => null,
		);
		$expected_args = array_merge(
			$default_args,
			$expected_args
		);
		$key_diff      = array_diff( array_keys( $expected_args ), array_keys( $default_args ) );
		$this->assertEquals( array(), $key_diff, 'Invalid $expected_args passed' );
		foreach ( array_keys( $default_args ) as $source ) {
			if ( is_null( $expected_args[ $source ] ) ) {
				continue;
			}
			if ( in_array( $source, array( 'method', 'path' ), true ) ) {
				$this->assertEquals( $expected_args[ $source ], $this->actual_api_requests[ $expected_host ][ $source ] );
			} else {
				$actual_args = array_intersect_key( $this->actual_api_requests[ $expected_host ][ $source ], $expected_args[ $source ] );
				$this->assertEqualSets( $expected_args[ $source ], $actual_args );
			}
		}
		// Reset for the next assertion.
		$this->reset_api_tracking_vars();
	}

	/**
	 * Asserts no API request was made through the HTTP API.
	 *
	 * @param string $expected_host Optionally define a specific host to inspect.
	 */
	protected function assertNotAPIRequest( $expected_host = null ) {
		if ( ! is_null( $expected_host ) ) {
			$this->assertArrayNotHasKey( $expected_host, $this->actual_api_requests );
		} else {
			$this->assertEmpty( $this->actual_api_requests );
		}
	}

	/**
	 * Resets API request tracking vars.
	 */
	protected function reset_api_tracking_vars() {
		$this->actual_api_requests = array();
	}

	/**
	 * Assigns class $imported_image_id reference when image is imported.
	 *
	 * @param int $image_id ID for the newly imported image.
	 */
	public function action_tasty_recipes_imported_image( $image_id ) {
		$this->imported_image_id = $image_id;
	}

	/**
	 * Removes filters when test is complete.
	 */
	public function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'filter_pre_http_request' ) );
		remove_action( 'tasty_recipes_imported_image', array( $this, 'action_tasty_recipes_imported_image' ) );
		unset( $_COOKIE[ 'wp-postpass_' . COOKIEHASH ] );
		parent::tearDown();
	}
}
