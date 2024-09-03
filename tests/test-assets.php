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
	}

	/**
	 * Ensures expected value for variations of 'external_plugins' formatting.
	 *
	 * @dataProvider dataprovider_external_plugins_variations
	 *
	 * @param array $external_plugins Variation of 'external_plugins' formatting.
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
		$external_plugins = json_decode( $final_init['external_plugins'], true );
		$this->assertArrayHasKey( 'tr_heading', $external_plugins );
		$this->assertArrayHasKey( 'tr_image', $external_plugins );
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
					'plugin1' => '',
					'plugin2' => '',
				),
			),
			array(
				'{"plugin1":""}',
			),
		);
	}
}
