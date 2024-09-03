<?php
/**
 * Test case for the WP Ultimate Recipe converter.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Objects\Recipe;

/**
 * Test case for the WP Ultimate Recipe converter.
 */
class Test_WP_Ultimate_Recipe_Converter extends Tasty_Recipes_Converter_Testcase {

	/**
	 * Seed SQL file to use with tests.
	 *
	 * @var string
	 */
	protected $sql_source = 'wpur.sql';

	/**
	 * Conversion class to use with tests.
	 *
	 * @var string
	 */
	protected static $conversion_class = 'Tasty_Recipes\Converters\WP_Ultimate_Recipe';

	/**
	 * Overrides base test method to add some further mocked data.
	 *
	 * @dataProvider recipe_samples
	 *
	 * @param string $source           String to use as the source post_content value.
	 * @param array  $expected         Expected result for processed recipe.
	 * @param string $expected_image   Expected image file name.
	 * @param string $expected_content Expected post content value, when relevant.
	 */
	public function test_recipe_conversion( $source, $expected, $expected_image, $expected_content = '' ) { // phpcs:ignore VariableAnalysis
		if ( false !== stripos( $source, 'id="33652"' ) ) {
			$file          = __DIR__ . '/data/canola.jpg';
			$attachment_id = $this->create_upload_object( $file );
			set_post_thumbnail( 33652, $attachment_id );
			$user_id = $this->factory->user->create( array( 'display_name' => 'Tasty Author' ) );
			wp_update_post(
				array(
					'ID'          => 33652,
					'post_author' => $user_id,
				)
			);
		} elseif ( false !== stripos( $source, 'id="33659"' ) ) {
			$file          = __DIR__ . '/data/Caramel-Cups-4-Square.jpg';
			$attachment_id = $this->create_upload_object( $file );
			update_post_meta( 33659, 'recipe_alternate_image', $attachment_id );
		}
		parent::test_recipe_conversion( $source, $expected, $expected_image );
		$this->assertStringNotContainsString( '[ultimate-recipe', get_post_field( 'post_content', $this->post_id ) );
		$this->assertStringNotContainsString( '<!-- wp:wp-ultimate-recipe/recipe', get_post_field( 'post_content', $this->post_id ) );
		$this->assertStringNotContainsString( '<!-- wp:shortcode', get_post_field( 'post_content', $this->post_id ) );
	}

	/**
	 * Recipe sample data to use for tests.
	 *
	 * @return array
	 */
	public function recipe_samples() {
		$recipe_1_ingredients  = <<<EOT
<ul>
<li>20 oz water (cold, sparkling)</li>
<li>1 slice lemon</li>
</ul>
EOT;
		$recipe_1_instructions = <<<EOT
<ol>
<li>Squeeze the lemon into the cold sparkling water.</li>
<li>Watch the bubbles rise to the surface for 3 hours.</li>
<li>Drink the now warm, non-sparkling lemon water.</li>
</ol>
EOT;
		$recipe_1_notes        = <<<EOT
This is a recipe note. It has <em>italic text</em>, <strong>bold text</strong>, and a <a href="http://google.com">link</a>.
<ul>
<li>It also has a bullet point!</li>
</ul>
<ol>
<li>And a numbered list.</li>
<li>With two items!</li>
</ol>
EOT;
		$recipe_2_ingredients  = <<<EOT
<h4>For the burgers</h4>
<ul>
<li>5 cups wild rice (cooked)</li>
<li>1 cup mushrooms (finely diced)</li>
<li>4 eggs</li>
<li>1 1/2 cups mayonnaise</li>
<li>1 cup panko breadcrumbs</li>
<li>4 tsp cumin</li>
<li>1 tsp salt</li>
<li>1 tsp pepper</li>
<li>1 tsp garlic powder</li>
<li>1/2 tsp cayenne (optional, to taste)</li>
<li>olive oil (for the pan)</li>
</ul>
<h4>For serving</h4>
<ul>
<li>6 burger buns</li>
<li>lettuce</li>
<li>6 slices cheddar cheese</li>
<li>barbecue sauce</li>
</ul>
EOT;
		$recipe_2_instructions = <<<EOT
<ol>
<li>Mix all ingredients.</li>
<li>Using a 1-cup measuring cup, scoop up rice mixture and form into patties. If the mixture is too wet, just add another 1/2 cup of panko or pop it in the fridge for a few minutes to help it stick together.</li>
<li>Heat a little bit of oil over medium high heat. Add the patties and pan fry until nicely browned on both sides.</li>
<li>Top with slices of Muenster cheese. Serve on a bun (or not!) with barbecue sauce, hot sauce, magic green sauce, ALL THE SAUCE. Also arugula, avocado, and beyond. You’ve only just begun.</li>
</ol>
<h4>Build the burgers</h4>
<ol>
<li>Put lettuce, cheese, and BBQ sauce on the burgers and eat.</li>
</ol>
EOT;
		$recipe_2_notes        = <<<EOT
<ul>
 	<li>I found that 1 1/2 cups raw wild rice was about the right amount to yield 5 cups cooked wild rice.</li>
 	<li>This was served to us with a side of roasted cauliflower which was just total perfection. They went together beautifully.</li>
</ul>
<em>Italic text.</em> <strong>Bold Text.</strong>
<p style="text-align: center;">Centered text.</p>
<p style="text-align: left;"><a href="http://google.com" rel="false">Linked text.</a></p>

<blockquote>
<p style="text-align: left;">Quoted text.</p>
</blockquote>
<ol>
 	<li>To get your cheese nice and ooey-gooey, add it to the burgers for the last few minutes of pan frying and cover with a lid.</li>
 	<li>To make ahead or gift to someone, just freeze the burgers on a sheet pan until individually frozen solid and then store together in a freezer bag.</li>
 	<li>Worth noting that I have tried reducing the mayo but it never turns out as good. I do, however, add more mushrooms and as long as the ratios of all the other ingredients are as-written, it comes out great.</li>
</ol>
EOT;
		$recipe_3_ingredients  = <<<EOT
<ul>
<li>1 cup olive oil</li>
<li>1 apple</li>
</ul>
EOT;
		$recipe_3_instructions = <<<EOT
<ol>
<li>Do something.</li>
</ol>
EOT;
		return array(
			array(
				"<!-- wp:shortcode -->\n\n[ultimate-recipe id=\"33652\" template=\"default\"]\n<!-- /wp:shortcode -->",
				array(
					'title'                       => 'Water with Lemon - Recipe Insertion Test',
					'author_name'                 => 'Tasty Author',
					'description'                 => 'This is the best water with lemon ever. Note: featured image selected in Recipe post, but not alternate image selected in recipe post, and no featured image selected in regular post that this is inserted into. ',
					'ingredients'                 => $recipe_1_ingredients,
					'instructions'                => $recipe_1_instructions,
					'notes'                       => $recipe_1_notes,
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '2 minutes',
					'cook_time'                   => '3 hours',
					'total_time'                  => '3 hours 2 minutes',
					'yield'                       => '1 glass',
					'category'                    => 'Main Course, Soup',
					'method'                      => '',
					'cuisine'                     => 'American, Chinese',
					'serving_size'                => '',
					'calories'                    => '',
					'sugar'                       => '',
					'sodium'                      => '',
					'fat'                         => '',
					'saturated_fat'               => '',
					'unsaturated_fat'             => '',
					'trans_fat'                   => '',
					'carbohydrates'               => '',
					'fiber'                       => '',
					'protein'                     => '',
					'cholesterol'                 => '',
					'keywords'                    => '',
					'nutrifox_id'                 => '',
					'video_url'                   => '',
					'total_time_raw'              => '',
					'diet'                        => '',
					'description_video_settings'  => '',
					'ingredients_video_settings'  => '',
					'instructions_video_settings' => '',
					'notes_video_settings'        => '',
					'additional_time_label'       => '',
					'additional_time_value'       => '',
					'image_sizes'                 => array(),
				),
				'canola',
			),
			array(
				'<!-- wp:wp-ultimate-recipe/recipe {"id": "33642"} /-->',
				array(
					'title'                       => 'the ultimate wild rice burgers - Recipe Title',
					'author_name'                 => '',
					'description'                 => 'These are THE ULTIMATE wild rice burgers and I might even say the best vegetarian burgers I’ve ever had. The recipe comes from the Duluth Grill Cookbook.',
					'ingredients'                 => $recipe_2_ingredients,
					'instructions'                => $recipe_2_instructions,
					'notes'                       => $recipe_2_notes,
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '15 mins',
					'cook_time'                   => '15 mins',
					'total_time'                  => '30 minutes',
					'yield'                       => '6 burgers',
					'category'                    => 'Main Dish',
					'method'                      => '',
					'cuisine'                     => 'American',
					'serving_size'                => '',
					'calories'                    => '',
					'sugar'                       => '',
					'sodium'                      => '',
					'fat'                         => '',
					'saturated_fat'               => '',
					'unsaturated_fat'             => '',
					'trans_fat'                   => '',
					'carbohydrates'               => '',
					'fiber'                       => '',
					'protein'                     => '',
					'cholesterol'                 => '',
					'keywords'                    => '',
					'nutrifox_id'                 => '',
					'video_url'                   => '',
					'total_time_raw'              => '',
					'diet'                        => '',
					'description_video_settings'  => '',
					'ingredients_video_settings'  => '',
					'instructions_video_settings' => '',
					'notes_video_settings'        => '',
					'additional_time_label'       => '',
					'additional_time_value'       => '',
					'image_sizes'                 => array(),
				),
				'',
			),
			array(
				"<!-- wp:wp-ultimate-recipe/recipe {\"id\":\"33659\"} -->\n[ultimate-recipe id=\"33659\" template=\"default\"]\n<!-- /wp:wp-ultimate-recipe/recipe -->",
				array(
					'title'                       => 'Recipe insertion test - with content in body',
					'author_name'                 => '',
					'description'                 => '',
					'ingredients'                 => $recipe_3_ingredients,
					'instructions'                => $recipe_3_instructions,
					'notes'                       => 'Recipe note',
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '',
					'cook_time'                   => '',
					'total_time'                  => '',
					'yield'                       => '',
					'category'                    => 'Breakfast',
					'method'                      => '',
					'cuisine'                     => 'French',
					'serving_size'                => '',
					'calories'                    => '',
					'sugar'                       => '',
					'sodium'                      => '',
					'fat'                         => '',
					'saturated_fat'               => '',
					'unsaturated_fat'             => '',
					'trans_fat'                   => '',
					'carbohydrates'               => '',
					'fiber'                       => '',
					'protein'                     => '',
					'cholesterol'                 => '',
					'keywords'                    => '',
					'nutrifox_id'                 => '',
					'video_url'                   => '',
					'total_time_raw'              => '',
					'diet'                        => '',
					'description_video_settings'  => '',
					'ingredients_video_settings'  => '',
					'instructions_video_settings' => '',
					'notes_video_settings'        => '',
					'additional_time_label'       => '',
					'additional_time_value'       => '',
					'image_sizes'                 => array(),
				),
				'Caramel-Cups-4-Square',
			),
		);
	}
}
