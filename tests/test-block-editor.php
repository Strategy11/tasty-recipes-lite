<?php
/**
 * Tests behavior of the Block_Editor class.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Block_Editor;

/**
 * Tests behavior of the Block_Editor class.
 */
class Test_Block_Editor extends Tasty_Recipes_Testcase {

	/**
	 * Ensures expected behavior of test_get_block_for_recipe().
	 */
	public function test_get_block_for_recipe() {
		$this->skip_if_less_than_wp_version( '5.0' );
		$recipe = $this->create_mock_recipe();
		$block  = Block_Editor::get_block_for_recipe( $recipe );
		$this->assertEquals( array( $recipe->get_id() ), Tasty_Recipes::get_recipe_ids_from_content( $block ) );
	}
}
