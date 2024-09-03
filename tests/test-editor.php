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
}
