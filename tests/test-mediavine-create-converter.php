<?php
/**
 * Test case for the Mediavine Create recipe converter.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Objects\Recipe;

/**
 * Test case for the Mediavine Create recipe converter.
 */
class Test_Mediavine_Create_Converter extends Tasty_Recipes_Converter_Testcase {

	/**
	 * Seed SQL file to use with tests.
	 *
	 * @var string
	 */
	protected $sql_source = 'create.sql';

	/**
	 * Conversion class to use with tests.
	 *
	 * @var string
	 */
	protected static $conversion_class = 'Tasty_Recipes\Converters\Mediavine_Create';

	/**
	 * Overloads default recipe conversion test to assert no fallback content remains.
	 *
	 * @dataProvider recipe_samples
	 *
	 * @param string $source           String to use as the source post_content value.
	 * @param array  $expected         Expected result for processed recipe.
	 * @param string $expected_image   Expected image file name.
	 * @param string $expected_content Expected post content value, when relevant.
	 */
	public function test_recipe_conversion( $source, $expected, $expected_image, $expected_content = '' ) {
		parent::test_recipe_conversion( $source, $expected, $expected_image, $expected_content );
		$post_content = get_post( $this->post_id )->post_content;
		$this->assertStringNotContainsString( 'wp:mv/recipe', $post_content );
		$this->assertStringNotContainsString( '[mv_create]', $post_content );
	}

	/**
	 * Recipe sample data to use for tests.
	 *
	 * @return array
	 */
	public function recipe_samples() {

		$recipe_1              = <<<EOT
Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Nullam quis risus eget urna mollis ornare vel eu leo. Donec ullamcorper nulla non metus auctor fringilla. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Etiam porta sem malesuada magna mollis euismod.

<!-- wp:mv/recipe {"title":"Amazing Mushroom Bowls with Kale Pesto","id":"20","thumbnail_uri":"http://tastyrecipes.test/wp-content/uploads/2021/05/Mushroom-Bowls-1.jpeg","type":"recipe"} -->
<div class="wp-block-mv-recipe">[mv_create key="20" type="recipe" title="Amazing Mushroom Bowls with Kale Pesto" thumbnail="http://tastyrecipes.test/wp-content/uploads/2021/05/Mushroom-Bowls-1.jpeg"]</div>
<!-- /wp:mv/recipe -->

Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Nullam quis risus eget urna mollis ornare vel eu leo. Donec ullamcorper nulla non metus auctor fringilla. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Etiam porta sem malesuada magna mollis euismod.
EOT;
		$recipe_1_description  = <<<EOT
<p>Amazing Mushroom Bowls with Kale Pesto! Smoky, sweet, salt mushrooms (or tempeh!), caramelized pineapple, peppers, and dollop of kale pesto, all served over rice. SO GOOD.</p>
EOT;
		$recipe_1_ingredients  = <<<EOT
<ul>
<li>1/4 cup olive oil</li>
<li>1/4 cup maple syrup</li>
<li>1/4 cup soy sauce</li>
<li>1 teaspoon smoked paprika</li>
<li>1/2 teaspoon cumin</li>
<li>1/2 teaspoon Italian seasoning</li>
<li>1/2 teaspoon onion powder</li>
<li>1/2 teaspoon garlic powder</li>
<li>salt and black pepper (to taste)</li>
</ul>
<h3>Bowls</h3>
<ul>
<li>16 ounces fresh mushrooms, washed and cut into thick-ish slices</li>
<li>2 cups fresh pineapple chunks</li>
<li>2–3 bell peppers</li>
<li>rice or cauliflower rice</li>
<li><a href="https://pinchofyum.com/5-minute-vegan-kale-pesto" rel="nofollow" target="_blank">kale pesto, magic green sauce, or any other yummy sauce</a></li>
</ul>
EOT;
		$recipe_1_instructions = <<<EOT
<ol><li>Whisk or shake the marinate in a jar until combined.</li><li>Place mushrooms in a shallow bowl. Cover with enough marinade to get some flavor on each of the mushroom pieces (you might have some marinade left – that’s okay, you can use it later in this recipe, or save for next week’s bowls).</li><li>Preheat oven to 450 degrees. Let the mushrooms rest for 20-30 minutes while you prep other ingredients, including your sauce! Place peppers, pineapple, and mushrooms with marinade each on their own sheet pans. Bake for 20-or-so minutes, rotating pans as needed, until you get some nice browning on everything (peppers will roast a few minutes faster). You can use the broiler to help with the browning as needed.</li><li>Serve in a bowl over rice or cauli rice. Top with your sauce (yeah kale pesto)! You can also put all this yumminess in a wrap or on a salad. Poof! Look at healthy, delicious you.</li></ol>
EOT;
		$recipe_1_notes        = <<<EOT
<ul><li><strong>Tips for Roasting:</strong> don’t overcrowd the pans because the pineapple, peppers, and mushrooms might start to steam instead of roast / caramelize / brown. I would recommend draining off excess juice from your pineapple if it seems to have a lot of moisture. If your mushrooms look like they have a lot of liquid, that’s okay – keep roasting a bit longer and the sauce will kind of turn into a gooey crust and the mushrooms will get even deeper in color and flavor. YUM.</li></ul>
EOT;

		$recipe_2              = <<<EOT
Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Nullam quis risus eget urna mollis ornare vel eu leo. Donec ullamcorper nulla non metus auctor fringilla. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Etiam porta sem malesuada magna mollis euismod.

[mv_create key="13" type="recipe"]</div>

Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Nullam quis risus eget urna mollis ornare vel eu leo. Donec ullamcorper nulla non metus auctor fringilla. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Etiam porta sem malesuada magna mollis euismod.
EOT;
		$recipe_2_description  = <<<EOT
<p>Oatmilk <a href="https://www.amazon.com/gp/product/B07MQPXP7D/ref=ppx_yo_dt_b_asin_title_o02_s00?ie=UTF8&amp;psc=1">Honey</a> Latte – a delicious homemade morning treat! Steaming hot, rich, and dark coffee that’s satisfyingly creamy, barely sweet, and warm-spiced.</p>
EOT;
		$recipe_2_ingredients  = <<<EOT
<ul>
<li>3/4 cup oatmilk</li>
<li>1–2 tablespoons honey (depends how sweet you like it)</li>
<li>1 tiny tiny pinch of salt</li>
<li>1/2 cup of strongly brewed coffee (decaf, in my case!)</li>
<li>a dusting of cinnamon, optional</li>
</ul>
EOT;
		$recipe_2_instructions = <<<EOT
<ol><ol><li><strong>Pour the milk</strong>, <a href="https://www.amazon.com/gp/product/B07MQPXP7D/ref=ppx_yo_dt_b_asin_title_o02_s00?ie=UTF8&amp;psc=1">honey</a>, and salt into a <a href="https://amzn.to/2N1QlJ2">glass jar</a>. Microwave for about a minute, until very hot.</li><li><strong>Put a cap on the jar</strong><strong>:</strong> and wrap with a towel (the metal lid will get hot). Shake until foamy and delicious.<strong>Pour your milky mixture</strong></li><li> into a mug. Add coffee. Sprinkle with cinnamon. Your cozy morning awaits.</li></ol></ol>
EOT;
		$recipe_2_notes        = <<<EOT
<p>And this is a note. </p>
EOT;
		return array(
			array(
				$recipe_1,
				array(
					'title'                       => 'Amazing Mushroom Bowls with Kale Pesto',
					'author_name'                 => 'Ann Nguyen',
					'description'                 => $recipe_1_description,
					'ingredients'                 => $recipe_1_ingredients,
					'instructions'                => $recipe_1_instructions,
					'notes'                       => $recipe_1_notes,
					'average_rating'              => '4.6',
					'total_reviews'               => '64',
					'prep_time'                   => '20 minutes',
					'cook_time'                   => '30 minutes',
					'total_time'                  => '1 hour 5 minutes',
					'yield'                       => '4',
					'category'                    => 'Bowls',
					'method'                      => '',
					'cuisine'                     => 'american',
					'serving_size'                => '2 cups',
					'calories'                    => '450',
					'sugar'                       => '2',
					'sodium'                      => '1400',
					'fat'                         => '12',
					'saturated_fat'               => '2',
					'unsaturated_fat'             => '10',
					'trans_fat'                   => '0',
					'carbohydrates'               => '20',
					'fiber'                       => '5',
					'protein'                     => '18',
					'cholesterol'                 => '0',
					'keywords'                    => '',
					'nutrifox_id'                 => '',
					'video_url'                   => 'https://www.youtube.com/watch?v=A73lRQop_zA',
					'total_time_raw'              => '1 hour 5 minutes',
					'diet'                        => 'VeganDiet',
					'description_video_settings'  => '',
					'ingredients_video_settings'  => '',
					'instructions_video_settings' => '',
					'notes_video_settings'        => '',
					'additional_time_label'       => 'Custom Time',
					'additional_time_value'       => '15 minutes',
					'image_sizes'                 => array(),
				),
				'',
			),
			array(
				$recipe_2,
				array(
					'title'                       => 'Oatmilk Honey Latte',
					'author_name'                 => 'Katie Koteen',
					'description'                 => $recipe_2_description,
					'ingredients'                 => $recipe_2_ingredients,
					'instructions'                => $recipe_2_instructions,
					'notes'                       => $recipe_2_notes,
					'average_rating'              => '4.5',
					'total_reviews'               => '4',
					'prep_time'                   => '5 minutes',
					'cook_time'                   => '10 minutes',
					'total_time'                  => '15 minutes',
					'yield'                       => '1 serving',
					'category'                    => '',
					'method'                      => '',
					'cuisine'                     => 'american',
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
					'keywords'                    => 'coffee, drink, honey',
					'nutrifox_id'                 => '',
					'video_url'                   => 'https://www.youtube.com/watch?v=tjaY2r6XF5w',
					'total_time_raw'              => '15 minutes',
					'diet'                        => 'VegetarianDiet',
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
		);
	}
}
