<?php
/**
 * Tests our base Recipe model.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Objects\Recipe;

/**
 * Tests our base Recipe model.
 */
class Test_Recipe_Object extends Tasty_Recipes_Testcase {

	/**
	 * Ensures expected behavior of Recipe->to_json() method.
	 */
	public function test_to_json() {
		$recipe = $this->create_mock_recipe_with_image();

		$recipe_json = $recipe->to_json();
		$image_sizes = array(
			'thumbnail'    => array(
				'width'  => 150,
				'height' => 150,
			),
			'medium'       => array(
				'width'  => 300,
				'height' => 225,
			),
			'medium_large' => array(
				'width'  => 640,
				'height' => 480,
			),
			'large'        => array(
				'width'  => 640,
				'height' => 480,
			),
			'full'         => array(
				'width'  => 640,
				'height' => 480,
			),
		);
		// WordPress 4.4 introduced 'medium_large' and 'large' image sizes.
		if ( version_compare( $GLOBALS['wp_version'], '4.4', '<' ) ) {
			unset( $image_sizes['medium_large'] ); // doesn't exist.
			unset( $image_sizes['large'], $recipe_json['image_sizes']['large'] ); // different size.
		}
		foreach ( $image_sizes as $image_size => $data ) {
			$this->assertEquals( $data['width'], $recipe_json['image_sizes'][ $image_size ]['width'] );
			$this->assertEquals( $data['height'], $recipe_json['image_sizes'][ $image_size ]['height'] );
		}
		unset( $recipe_json['image_sizes'] );
		$this->assertEquals(
			array(
				'id'                          => $recipe->get_id(),
				'title'                       => 'West Indian Beans & Coconut Rice',
				'title_rendered'              => 'West Indian Beans &#038; Coconut Rice',
				'author_name'                 => 'Moosewood Restaurant Simple Suppers',
				'image_id'                    => $this->attachment_id,
				'description'                 => "It's so tasty",
				'description_rendered'        => '<p>It&#8217;s so tasty</p>' . PHP_EOL,
				'ingredients'                 => $recipe->get_ingredients(),
				'ingredients_rendered'        => apply_filters( 'tasty_recipes_the_content', $recipe->get_ingredients(), 'ingredients' ),
				'instructions'                => $recipe->get_instructions(),
				'instructions_rendered'       => apply_filters( 'tasty_recipes_the_content', $recipe->get_instructions(), 'instructions' ),
				'notes'                       => 'Make sure to eat it all!',
				'notes_rendered'              => '<p>Make sure to eat it all!</p>' . PHP_EOL,
				'average_rating'              => '',
				'total_reviews'               => '0',
				'prep_time'                   => '15 minutes',
				'cook_time'                   => '1 hour 20 minutes',
				'total_time'                  => '1 hour 35 minutes',
				'yield'                       => '2 servings',
				'category'                    => 'Main',
				'cuisine'                     => 'Caribbean',
				'method'                      => 'Cooking',
				'serving_size'                => '2 cups',
				'calories'                    => '581 calories',
				'sugar'                       => '0 g',
				'sodium'                      => '1863 mg',
				'fat'                         => '10 g',
				'saturated_fat'               => '2 g',
				'unsaturated_fat'             => '0 g',
				'trans_fat'                   => '1 g',
				'carbohydrates'               => '102 g',
				'fiber'                       => '12 g',
				'protein'                     => '18 g',
				'cholesterol'                 => '120 mg',
				'keywords'                    => 'apples, pie',
				'nutrifox_id'                 => '',
				'video_url'                   => '',
				'total_time_raw'              => '',
				'diet'                        => 'Low Fat',
				'description_video_settings'  => '',
				'ingredients_video_settings'  => '',
				'instructions_video_settings' => '',
				'additional_time_label'       => '',
				'additional_time_value'       => '',
				'notes_video_settings'        => '',
			),
			$recipe_json
		);
		$this->assertStringContainsString( 'Rinse and drain the rice.', $recipe->get_instructions() );
		$this->assertStringContainsString( 'For The Red Beans:', $recipe->get_ingredients() );
	}

	/**
	 * Ensure expected value for total time greater than two hours.
	 */
	public function test_total_time_greater_than_two_hours() {
		$recipe = $this->create_mock_recipe();
		$recipe->set_prep_time( '2 hours 6 minutes' );
		$recipe->set_cook_time( '15 minutes' );
		$this->assertEquals( '2 hours 21 minutes', $recipe->get_total_time() );
	}

	/**
	 * Ensure expected value for total time between one and two hours.
	 */
	public function test_total_time_between_one_and_two_hours() {
		$recipe = $this->create_mock_recipe();
		$recipe->set_prep_time( '1 hour' );
		$recipe->set_cook_time( '15 minutes' );
		$this->assertEquals( '1 hour 15 minutes', $recipe->get_total_time() );
	}

	/**
	 * Ensure expected time for total time equal to one hour.
	 */
	public function test_total_time_exact_hour() {
		$recipe = $this->create_mock_recipe();
		$recipe->set_prep_time( '30 minutes' );
		$recipe->set_cook_time( '30 minutes' );
		$this->assertEquals( '1 hour', $recipe->get_total_time() );
		$recipe->set_prep_time( '1 hour 30 minutes' );
		$recipe->set_cook_time( '30 minutes' );
		$this->assertEquals( '2 hours', $recipe->get_total_time() );
	}

	/**
	 * Ensure expected time for total time less than an hour.
	 */
	public function test_total_time_less_than_hour() {
		$recipe = $this->create_mock_recipe();
		$recipe->set_prep_time( '6 minutes' );
		$recipe->set_cook_time( '15 minutes' );
		$this->assertEquals( '21 minutes', $recipe->get_total_time() );
	}

	/**
	 * Ensure expected time for total time less than ten minutes.
	 */
	public function test_total_time_less_than_ten_minutes() {
		$recipe = $this->create_mock_recipe();
		$recipe->set_prep_time( '1 minute' );
		$recipe->set_cook_time( '3 minutes' );
		$this->assertEquals( '4 minutes', $recipe->get_total_time() );
	}

	/**
	 * Ensure expected time for total time with parens.
	 */
	public function test_total_time_parens() {
		$recipe = $this->create_mock_recipe();
		$recipe->set_prep_time( '15 minutes (plus something)' );
		$recipe->set_cook_time( '20 minutes' );
		$this->assertEquals( '35 minutes', $recipe->get_total_time() );
	}

	/**
	 * Ensure the total time includes the additional time.
	 */
	public function test_total_time_includes_additional_time() {
		$recipe = $this->create_mock_recipe();
		$recipe->set_prep_time( '20 minutes' );
		$recipe->set_additional_time_value( '5 minutes' );
		$recipe->set_cook_time( '20 minutes' );
		$this->assertEquals( '45 minutes', $recipe->get_total_time() );
	}

	/**
	 * Ensure expected values for a recipe with ratings.
	 */
	public function test_recipe_with_ratings() {
		$this->prime_basic_test_post();
		$recipe = $this->create_mock_recipe_with_ratings();
		$this->assertEquals( '5', $recipe->get_total_reviews() );
		$this->assertEquals( '2.8', $recipe->get_average_rating() );
	}

	/**
	 * Ensure expected values for times as integers.
	 */
	public function test_integer_times_as_minutes() {
		$recipe = $this->create_mock_recipe();
		$recipe->set_prep_time( '21' );
		$recipe->set_cook_time( '15' );
		$this->assertEquals( '36 minutes', $recipe->get_total_time() );
	}

	/**
	 * Ensure expected values for recipe with Nutrifox data.
	 */
	public function test_nutrifox_data() {
		$recipe = $this->create_mock_recipe_with_nutrifox_id();
		$this->assertEquals( 7500, $recipe->get_nutrifox_id() );
		$nutrifox_data = $recipe->get_nutrifox_response();
		$this->assertEquals( 'Spaghetti Squash Peanut Noodles', $nutrifox_data['name'] );
	}

	/**
	 * Ensure expected value for override of total time.
	 */
	public function test_total_time_override() {
		$recipe = $this->create_mock_recipe();
		$recipe->set_prep_time( '21' );
		$recipe->set_cook_time( '15' );
		$this->assertEquals( '36 minutes', $recipe->get_total_time() );
		$recipe->set_total_time( '40 minutes' );
		$this->assertEquals( '40 minutes', $recipe->get_total_time() );
	}
}
