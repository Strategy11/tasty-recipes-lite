<?php
/**
 * Base test class to use for all tests.
 *
 * @package Tasty_Recipes
 */

/**
 * Base test class to use for all converter tests.
 */
class Tasty_Recipes_Converter_Testcase extends Tasty_Recipes_Testcase {

	/**
	 * Seed SQL file to use with tests.
	 *
	 * @var mixed
	 */
	protected $sql_source = false;

	/**
	 * Conversion class to use with tests.
	 *
	 * @var string
	 */
	protected static $conversion_class;

	/**
	 * Imports SQL seed data for test when present.
	 */
	public function setUp(): void {
		if ( $this->sql_source ) {
			$sql = file_get_contents( __DIR__ . '/data/' . $this->sql_source );
			foreach ( explode( ';' . PHP_EOL, $sql ) as $query ) {
				$query = trim( $query );
				if ( empty( $query ) ) {
					continue;
				}
				$GLOBALS['wpdb']->query( $query );
			}
		}
		parent::setUp();
		$this->prime_basic_test_post();
	}

	/**
	 * Runs the recipe conversion tests against each sample data.
	 *
	 * @dataProvider recipe_samples
	 *
	 * @param string $source           String to use as the source post_content value.
	 * @param array  $expected         Expected result for processed recipe.
	 * @param string $expected_image   Expected image file name.
	 * @param string $expected_content Expected post content value, when relevant.
	 */
	public function test_recipe_conversion( $source, $expected, $expected_image, $expected_content = '' ) { // phpcs:ignore VariableAnalysis
		global $wpdb;

		static::pre_recipe_conversion( $source, $expected );

		$wpdb->update( $wpdb->posts, array( 'post_content' => $source ), array( 'ID' => $this->post_id ) );
		clean_post_cache( $this->post_id );
		$conversion_class = static::$conversion_class;
		if ( ! in_array( $conversion_class, array( 'Tasty_Recipes\Converters\Mediavine_Create', 'Tasty_Recipes\Converters\Simple_Recipe_Pro', 'Tasty_Recipes\Converters\WP_Recipe_Maker', 'Tasty_Recipes\Converters\WPCom' ), true ) ) {
			$this->assertEquals( array( $this->post_id ), $conversion_class::get_post_ids() );
			$this->assertEquals( 1, $conversion_class::get_count() );
		}
		$recipe = $conversion_class::convert_post( $this->post_id );
		$this->assertExpectedRecipe( $expected, $expected_image, $recipe );
	}

	/**
	 * Allows for behavior override before recipe conversion.
	 *
	 * @param string $source   Source post_content value.
	 * @param array  $expected Expected results of recipe conversion.
	 */
	protected function pre_recipe_conversion( $source, $expected ) {
		/** Override in subclass as necessary */
		$source   = $source;
		$expected = $expected;
	}

	/**
	 * Asserts expected recipe format.
	 *
	 * @param array  $expected       Expected recipe data.
	 * @param string $expected_image Expected recipe image file name (if present).
	 * @param Recipe $recipe         Recipe object for converted recipe.
	 */
	protected function assertExpectedRecipe( $expected, $expected_image, $recipe ) {
		$recipe_json = $recipe->to_json();
		unset( $recipe_json['id'] );
		foreach ( $recipe_json as $k => $v ) {
			if ( false !== strpos( $k, '_rendered' ) ) {
				unset( $recipe_json[ $k ] );
			}
		}
		$image_url = wp_get_attachment_url( $recipe_json['image_id'] );
		if ( ! $image_url ) {
			$image_url = '';
		}
		if ( ! empty( $expected_image ) ) {
			$this->assertStringContainsString( $expected_image, $image_url );
		} else {
			$this->assertEmpty( $image_url );
		}
		unset( $expected['image_sizes'], $recipe_json['image_sizes'], $recipe_json['image_id'] );
		$this->assertEquals( $expected, $recipe_json );
	}

}
