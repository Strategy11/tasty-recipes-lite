<?php
/**
 * Tests behavior of the Test_Editor class.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Editor;

/**
 * Tests behavior of the Test_Editor class.
 */
class Test_Editor extends Tasty_Recipes_Testcase {

	/**
	 * Ensures expected message is generated for a detected Zip Recipe.
	 */
	public function test_get_converter_messages_zip_recipe() {
		$this->prime_basic_test_post();
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_content' => '[amd-zlrecipe-recipe:1]',
			)
		);
		$messages = Editor::get_converter_messages( $this->post_id );
		$this->assertCount( 1, $messages );
		$this->assertEquals( 'ZipList (or Zip Recipes) recipe detected. Would you like to convert it to Tasty Recipes?', $messages[0]['content'] );
		$this->assertCount( 2, $messages[0]['actions'] );
		$this->assertEquals( 'Convert', $messages[0]['actions'][0]['label'] );
		$this->assertStringContainsString( 'action=tasty_recipes_convert_recipe', $messages[0]['actions'][0]['url'] );
		$this->assertStringContainsString( 'type=ziplist', $messages[0]['actions'][0]['url'] );
		$this->assertStringNotContainsString( 'is_block_editor=0', $messages[0]['actions'][0]['url'] );
	}

	/**
	 * Ensures expected message is generated when converter is successful but the post still has a recipe.
	 */
	public function test_get_converter_messages_zip_recipe_success() {
		$this->prime_basic_test_post();
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_content' => '[amd-zlrecipe-recipe:1]',
			)
		);
		$_GET['tasty_recipes_message'] = 'converted_recipe_success';
		$messages                      = Editor::get_converter_messages( $this->post_id );
		$this->assertCount( 2, $messages );
		$this->assertEquals( 'ZipList (or Zip Recipes) recipe detected. Would you like to convert it to Tasty Recipes?', $messages[0]['content'] );
		$this->assertEquals( 'Successfully migrated the recipe to Tasty Recipes. Enjoy!', $messages[1]['content'] );
	}

	/**
	 * Ensures expected message is generated for a detected Zip Recipe block.
	 */
	public function test_get_converter_messages_zip_recipe_block() {
		$this->skip_if_less_than_wp_version( '5.0' );
		$this->prime_basic_test_post();
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_content' => '<!-- wp:zip-recipes/recipe-block {"id":"1"} /-->',
			)
		);
		$messages = Editor::get_converter_messages( $this->post_id );
		$this->assertCount( 1, $messages );
		$this->assertEquals( 'ZipList (or Zip Recipes) recipe detected. Would you like to convert it to Tasty Recipes?', $messages[0]['content'] );
		$this->assertCount( 2, $messages[0]['actions'] );
		$this->assertEquals( 'Convert', $messages[0]['actions'][0]['label'] );
		$this->assertStringContainsString( 'action=tasty_recipes_convert_recipe', $messages[0]['actions'][0]['url'] );
		$this->assertStringContainsString( 'type=ziplist', $messages[0]['actions'][0]['url'] );
		$this->assertStringContainsString( 'is_block_editor=1', $messages[0]['actions'][0]['url'] );
	}

	/**
	 * Ensures expected message is generated for a detected Zip Recipe with revisions disabled.
	 */
	public function test_get_converter_messages_zip_recipe_disabled_revisions() {
		$this->prime_basic_test_post();
		remove_post_type_support( get_post_type( $this->post_id ), 'revisions' );
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_content' => '[amd-zlrecipe-recipe:1]',
			)
		);
		$messages = Editor::get_converter_messages( $this->post_id );
		$this->assertCount( 2, $messages );
		$this->assertEquals( 'ZipList (or Zip Recipes) recipe detected. Would you like to convert it to Tasty Recipes?', $messages[0]['content'] );
		$this->assertCount( 2, $messages[0]['actions'] );
		$this->assertEquals( 'Convert', $messages[0]['actions'][0]['label'] );
		$this->assertEquals( 'Do not ask again for ZipList (or Zip Recipes) recipes', $messages[0]['actions'][1]['label'] );
		$this->assertEquals( 'Learn more', $messages[1]['actions'][0]['label'] );
	}

	/**
	 * Contributors cannot create, rename, or delete terms outside recipe taxonomies.
	 *
	 * @since x.x
	 */
	public function test_contributor_cannot_mutate_non_recipe_taxonomies() {
		$current_user   = get_current_user_id();
		$contributor_id = $this->factory->user->create( array( 'role' => 'contributor' ) );
		wp_set_current_user( $contributor_id );

		$category_id = $this->factory->term->create(
			array(
				'taxonomy' => 'category',
				'name'     => 'TR Victim Cat',
			)
		);
		$menu_id     = wp_create_nav_menu( 'TR Victim Menu' );
		$this->assertNotWPError( $menu_id );

		$create = $this->dispatch_taxonomy_terms_request(
			'POST',
			array(
				'taxonomy' => 'category',
				'name'     => 'TR Injected Cat',
			)
		);
		$this->assertSame( 400, $create->get_status() );
		$this->assertNull( term_exists( 'TR Injected Cat', 'category' ) );

		$update = $this->dispatch_taxonomy_terms_request(
			'PUT',
			array(
				'taxonomy' => 'category',
				'id'       => $category_id,
				'name'     => 'TR Renamed Cat',
			)
		);
		$this->assertSame( 400, $update->get_status() );
		$category = get_term( $category_id, 'category' );
		$this->assertSame( 'TR Victim Cat', $category->name );

		$delete = $this->dispatch_taxonomy_terms_request(
			'DELETE',
			array(
				'taxonomy' => 'category',
				'id'       => $category_id,
			)
		);
		$this->assertSame( 400, $delete->get_status() );
		$this->assertInstanceOf( WP_Term::class, get_term( $category_id, 'category' ) );

		$menu_delete = $this->dispatch_taxonomy_terms_request(
			'DELETE',
			array(
				'taxonomy' => 'nav_menu',
				'id'       => $menu_id,
			)
		);
		$this->assertSame( 400, $menu_delete->get_status() );
		$this->assertInstanceOf( WP_Term::class, get_term( $menu_id, 'nav_menu' ) );

		wp_set_current_user( $current_user );
	}

	/**
	 * Contributors can create terms in recipe taxonomies.
	 *
	 * @since x.x
	 */
	public function test_contributor_can_create_recipe_taxonomy_term() {
		$current_user   = get_current_user_id();
		$contributor_id = $this->factory->user->create( array( 'role' => 'contributor' ) );
		wp_set_current_user( $contributor_id );

		$response = $this->dispatch_taxonomy_terms_request(
			'POST',
			array(
				'taxonomy' => 'tasty_recipe_cuisine',
				'name'     => 'TR Contrib Cuisine',
			)
		);

		$this->assertSame( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertSame( 'TR Contrib Cuisine', $data['value'] );
		$this->assertFalse( $data['canEdit'] );
		$this->assertFalse( $data['canDelete'] );
		$this->assertNotNull( term_exists( 'TR Contrib Cuisine', 'tasty_recipe_cuisine' ) );

		wp_set_current_user( $current_user );
	}

	/**
	 * Contributors cannot rename or delete recipe taxonomy terms.
	 *
	 * @since x.x
	 */
	public function test_contributor_cannot_update_or_delete_recipe_taxonomy_term() {
		$current_user   = get_current_user_id();
		$contributor_id = $this->factory->user->create( array( 'role' => 'contributor' ) );
		$term_id        = $this->factory->term->create(
			array(
				'taxonomy' => 'tasty_recipe_cuisine',
				'name'     => 'TR Custom Cuisine',
			)
		);
		wp_set_current_user( $contributor_id );

		$update = $this->dispatch_taxonomy_terms_request(
			'PUT',
			array(
				'taxonomy' => 'tasty_recipe_cuisine',
				'id'       => $term_id,
				'name'     => 'TR Renamed Cuisine',
			)
		);
		$this->assertSame( 403, $update->get_status() );
		$term = get_term( $term_id, 'tasty_recipe_cuisine' );
		$this->assertSame( 'TR Custom Cuisine', $term->name );

		$delete = $this->dispatch_taxonomy_terms_request(
			'DELETE',
			array(
				'taxonomy' => 'tasty_recipe_cuisine',
				'id'       => $term_id,
			)
		);
		$this->assertSame( 403, $delete->get_status() );
		$this->assertInstanceOf( WP_Term::class, get_term( $term_id, 'tasty_recipe_cuisine' ) );

		wp_set_current_user( $current_user );
	}

	/**
	 * Editors can rename and delete custom recipe taxonomy terms.
	 *
	 * @since x.x
	 */
	public function test_editor_can_update_and_delete_recipe_taxonomy_term() {
		$current_user = get_current_user_id();
		$editor_id    = $this->factory->user->create( array( 'role' => 'editor' ) );
		$term_id      = $this->factory->term->create(
			array(
				'taxonomy' => 'tasty_recipe_cuisine',
				'name'     => 'TR Custom Cuisine',
			)
		);
		wp_set_current_user( $editor_id );

		$update = $this->dispatch_taxonomy_terms_request(
			'PUT',
			array(
				'taxonomy' => 'tasty_recipe_cuisine',
				'id'       => $term_id,
				'name'     => 'TR Custom Cuisine Renamed',
			)
		);
		$this->assertSame( 200, $update->get_status() );
		$term = get_term( $term_id, 'tasty_recipe_cuisine' );
		$this->assertSame( 'TR Custom Cuisine Renamed', $term->name );

		$delete = $this->dispatch_taxonomy_terms_request(
			'DELETE',
			array(
				'taxonomy' => 'tasty_recipe_cuisine',
				'id'       => $term_id,
			)
		);
		$this->assertSame( 200, $delete->get_status() );
		$this->assertNull( get_term( $term_id, 'tasty_recipe_cuisine' ) );

		wp_set_current_user( $current_user );
	}

	/**
	 * Administrators still cannot delete default recipe terms.
	 *
	 * @since x.x
	 */
	public function test_admin_cannot_delete_default_recipe_term() {
		$current_user = get_current_user_id();
		$admin_id     = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$term = get_term_by( 'name', 'Italian', 'tasty_recipe_cuisine' );
		if ( ! $term instanceof WP_Term ) {
			$inserted = wp_insert_term( 'Italian', 'tasty_recipe_cuisine' );
			$this->assertNotWPError( $inserted );
			$term = get_term( $inserted['term_id'], 'tasty_recipe_cuisine' );
		}

		$response = $this->dispatch_taxonomy_terms_request(
			'DELETE',
			array(
				'taxonomy' => 'tasty_recipe_cuisine',
				'id'       => $term->term_id,
			)
		);

		$this->assertSame( 403, $response->get_status() );
		$data = $response->get_data();
		$this->assertSame( 'default_term', $data['code'] );
		$this->assertInstanceOf( WP_Term::class, get_term( $term->term_id, 'tasty_recipe_cuisine' ) );

		wp_set_current_user( $current_user );
	}

	/**
	 * Dispatch a taxonomy-terms REST request.
	 *
	 * @since x.x
	 *
	 * @param string $method Request method.
	 * @param array  $params Request parameters.
	 *
	 * @return \WP_REST_Response
	 */
	private function dispatch_taxonomy_terms_request( $method, $params ) {
		$request = new WP_REST_Request( $method, '/tasty-recipes/v1/taxonomy-terms' );

		foreach ( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}

		return rest_do_request( $request );
	}
}
