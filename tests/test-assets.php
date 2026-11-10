<?php
/**
 * Test case for the Tasty_Recipes\Assets controller class.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Assets;
use Tasty_Recipes\Shortcodes;

/**
 * Test case for the Tasty_Recipes\Assets controller class.
 */
class Test_Assets extends Tasty_Recipes_Testcase {

	/**
	 * Block editor data captured from the tasty_recipes_block_editor_data filter.
	 *
	 * @var array|null
	 */
	private $block_editor_data;

	/**
	 * Restores the request, global post and current user after each test.
	 */
	public function tearDown(): void {
		unset( $_GET['post'] );
		$GLOBALS['post'] = null;
		wp_set_current_user( 0 );
		$this->block_editor_data = null;
		parent::tearDown();
	}

	/**
	 * Ensures default value is returned for non-TR editor instance.
	 */
	public function test_filter_teeny_mce_before_init_other_editor() {
		$orig_init = array(
			'foo'     => 'bar',
			'plugins' => '',
		);
		$this->assertEquals( $orig_init, Assets::filter_teeny_mce_before_init( $orig_init, 'editor' ) );
	}

	/**
	 * Ensures expected value when 'external_plugins' isn't set.
	 */
	public function test_filter_teeny_mce_before_init_unset_external_plugins() {
		$orig_init  = array(
			'foo'     => 'bar',
			'plugins' => '',
		);
		$final_init = Assets::filter_teeny_mce_before_init( $orig_init, 'tasty-recipes-editor' );
		$this->assertStringContainsString( ',tr_heading,tr_image', $final_init['plugins'] );
		$this->assertArrayHasKey( 'external_plugins', $final_init );
		$external_plugins = json_decode( $final_init['external_plugins'], true );
		$this->assertArrayHasKey( 'tr_heading', $external_plugins );
		$this->assertArrayHasKey( 'tr_image', $external_plugins );
		$this->assertArrayHasKey( 'tr_video', $external_plugins );
		$this->assertCount( 3, $external_plugins );
	}

	/**
	 * Ensures foreign external plugins inherited from first_init are stripped.
	 *
	 * @dataProvider dataprovider_external_plugins_variations
	 *
	 * @param array|string $external_plugins Variation of 'external_plugins' formatting.
	 */
	public function test_filter_teeny_mce_before_init_external_plugins_variations( $external_plugins ) {
		$orig_init  = array(
			'foo'              => 'bar',
			'plugins'          => '',
			'external_plugins' => $external_plugins,
		);
		$final_init = Assets::filter_teeny_mce_before_init( $orig_init, 'tasty-recipes-editor' );
		$this->assertStringContainsString( ',tr_heading,tr_image', $final_init['plugins'] );
		$this->assertArrayHasKey( 'external_plugins', $final_init );
		$decoded_plugins = json_decode( $final_init['external_plugins'], true );
		$this->assertArrayHasKey( 'tr_heading', $decoded_plugins );
		$this->assertArrayHasKey( 'tr_image', $decoded_plugins );
		$this->assertArrayHasKey( 'tr_video', $decoded_plugins );
		$this->assertArrayNotHasKey( 'plugin1', $decoded_plugins );
		$this->assertArrayNotHasKey( 'plugin2', $decoded_plugins );
		$this->assertArrayNotHasKey( 'wp_recipe_maker', $decoded_plugins );
		$this->assertArrayNotHasKey( 'wprecipemaker', $decoded_plugins );
		$this->assertCount( 3, $decoded_plugins );
	}

	/**
	 * Provides variations of external plugins.
	 *
	 * @return array
	 */
	public function dataprovider_external_plugins_variations() {
		return array(
			array(
				'',
			),
			array(
				array(
					'plugin1'         => 'https://example.com/plugin1.js',
					'plugin2'         => 'https://example.com/plugin2.js',
					'wp_recipe_maker' => 'https://example.com/wprm.js',
					'wprecipemaker'   => 'https://example.com/wprm-preview.js',
				),
			),
			array(
				'{"plugin1":"https://example.com/plugin1.js","wp_recipe_maker":"https://example.com/wprm.js"}',
			),
		);
	}

	/**
	 * Ensures print assets only load for a password-protected post once the password is entered.
	 *
	 * @since x.x
	 */
	public function test_print_assets_password_protected_post() {
		$recipe = $this->prime_password_protected_recipe_post();
		$this->go_to( tasty_recipes_get_print_url( $this->post_id, $recipe->get_id() ) );

		Assets::action_wp_print_styles();
		$this->assertFalse( wp_style_is( 'tasty-recipes-print', 'enqueued' ) );
		$this->assertFalse( wp_script_is( 'tasty-recipes-print-controls', 'enqueued' ) );

		$this->enter_post_password();
		Assets::action_wp_print_styles();
		$this->assertTrue( wp_style_is( 'tasty-recipes-print', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'tasty-recipes-print-controls', 'enqueued' ) );

		wp_dequeue_style( 'tasty-recipes-print' );
		wp_dequeue_script( 'tasty-recipes-print-controls' );
	}

	/**
	 * A contributor can't load another author's recipe through the post query arg.
	 *
	 * @since x.x
	 */
	public function test_block_editor_data_ignores_post_arg_contributor_cannot_edit() {
		$admin_id       = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$contributor_id = $this->factory->user->create( array( 'role' => 'contributor' ) );
		$recipe         = $this->create_post_with_recipe( $admin_id );

		wp_set_current_user( $contributor_id );
		$data = $this->get_block_editor_data();

		$this->assertArrayNotHasKey( $recipe->get_id(), $data['recipeDataStore'] );
		$this->assertEmpty( $data['recipeDataStore'] );
	}

	/**
	 * An admin gets the recipe for the post in the query arg.
	 *
	 * @since x.x
	 */
	public function test_block_editor_data_uses_post_arg_admin() {
		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$author   = $this->factory->user->create( array( 'role' => 'author' ) );
		$recipe   = $this->create_post_with_recipe( $author );

		wp_set_current_user( $admin_id );
		$data = $this->get_block_editor_data();

		$this->assertArrayHasKey( $recipe->get_id(), $data['recipeDataStore'] );
		$this->assertSame( $recipe->get_title(), $data['recipeDataStore'][ $recipe->get_id() ]['title'] );
	}

	/**
	 * A post query arg for a post that doesn't exist falls back without errors.
	 *
	 * @since x.x
	 */
	public function test_block_editor_data_ignores_missing_post_arg() {
		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$_GET['post'] = (string) PHP_INT_MAX;
		$data         = $this->get_block_editor_data();

		$this->assertSame( array(), $data['recipeDataStore'] );
		$this->assertSame( array(), $data['editorNotices'] );
	}

	/**
	 * A contributor gets the recipe for their own post in the query arg.
	 *
	 * @since x.x
	 */
	public function test_block_editor_data_uses_own_post_arg_contributor() {
		$contributor_id = $this->factory->user->create( array( 'role' => 'contributor' ) );
		$recipe         = $this->create_post_with_recipe( $contributor_id );

		wp_set_current_user( $contributor_id );
		$data = $this->get_block_editor_data();

		$this->assertArrayHasKey( $recipe->get_id(), $data['recipeDataStore'] );
	}

	/**
	 * Creates a draft post with an embedded recipe and points the post query arg at it.
	 *
	 * @param int $author_id Post author.
	 *
	 * @return Tasty_Recipes\Objects\Recipe
	 */
	private function create_post_with_recipe( $author_id ) {
		$recipe       = $this->create_mock_recipe();
		$post_id      = $this->factory->post->create(
			array(
				'post_status'  => 'draft',
				'post_author'  => $author_id,
				'post_content' => Shortcodes::get_shortcode_for_recipe( $recipe ),
			)
		);
		$_GET['post'] = (string) $post_id;
		return $recipe;
	}

	/**
	 * Runs the block editor enqueue and returns the data it localizes.
	 *
	 * @return array
	 */
	private function get_block_editor_data() {
		$GLOBALS['post'] = null;
		$capture         = function ( $data ) {
			$this->block_editor_data = $data;
			return $data;
		};
		add_filter( 'tasty_recipes_block_editor_data', $capture, PHP_INT_MAX );
		Assets::action_enqueue_block_editor_assets();
		remove_filter( 'tasty_recipes_block_editor_data', $capture, PHP_INT_MAX );

		$this->assertIsArray( $this->block_editor_data );
		return $this->block_editor_data;
	}
}
