<?php
/**
 * Test case for the Tasty_Recipes\Assets controller class.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Assets;

/**
 * Test case for the Tasty_Recipes\Assets controller class.
 */
class Test_Assets extends Tasty_Recipes_Testcase {

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
}
