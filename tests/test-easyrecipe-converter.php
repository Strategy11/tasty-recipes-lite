<?php
/**
 * Test case for the EasyRecipe converter class.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Converters\Converter;
use Tasty_Recipes\Converters\EasyRecipe;
use Tasty_Recipes\Objects\Recipe;

/**
 * Test case for the EasyRecipe converter class.
 */
class Test_EasyRecipe_Converter extends Tasty_Recipes_Converter_Testcase {

	/**
	 * ID for an image created during the tests.
	 *
	 * @var int
	 */
	protected $created_image_id;

	/**
	 * Ensure expected behavior when wrapper <div> is empty.
	 */
	public function test_found_recipes_count_no_matching_for_wrapper() {
		$test_content = <<<EOT
Testing one two three

<div class="easyrecipeWrapper mceNonEditable"></div>
EOT;
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_content' => $test_content,
			)
		);
		$this->assertEquals( 0, EasyRecipe::get_count() );
	}

	/**
	 * Ensure expected behavior when wrapper <div> indicates a recipe.
	 */
	public function test_found_recipes_count_matching_for_div() {
		$test_content = <<<EOT
Testing one two three

<div class="easyrecipe mceNonEditable" data-rating="0">
EOT;
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_content' => $test_content,
			)
		);
		$this->assertEquals( 1, EasyRecipe::get_count() );
	}

	/**
	 * Override base method to include EasyRecipe-specific assertions.
	 *
	 * @dataProvider recipe_samples
	 *
	 * @param string $source           String to use as the source post_content value.
	 * @param array  $expected         Expected result for processed recipe.
	 * @param string $expected_image   Expected image file name.
	 * @param string $expected_content Expected post content value, when relevant.
	 */
	public function test_recipe_conversion( $source, $expected, $expected_image, $expected_content = '' ) {
		global $wpdb;
		$images = array(
			'easy-recipe-1.txt' => 'http://pinchofyum.com/wp-content/uploads/Miracle-No-Knead-Bread-3-2-Square.jpg',
			'easy-recipe-2.txt' => 'http://pinchofyum.com/wp-content/uploads/Caramel-Cups-4-Square.jpg',
			'easy-recipe-3.txt' => 'http://pinchofyum.com/wp-content/uploads/Sweet-Potato-Pizza-1-4-Square.jpg',
		);
		update_option( 'uploads_use_yearmonth_folders', 0 );
		if ( isset( $images[ $source ] ) ) {
			Converter::media_sideload_image( $images[ $source ] );
		}
		$post_id = $this->factory->post->create();
		$content = file_get_contents( __DIR__ . '/data/' . $source );
		$wpdb->update( $wpdb->posts, array( 'post_content' => $content ), array( 'ID' => $post_id ) );
		clean_post_cache( $post_id );
		if ( 'easy-recipe-2.txt' === $source ) {
			$this->create_mock_ratings_for_post( $post_id );
			wp_insert_attachment(
				array(
					'post_title'     => 'Carmel Cups',
					'post_content'   => '',
					'post_status'    => 'inherit',
					'post_mime_type' => 'image/jpeg',
					'guid'           => 'http://pinchofyum.com/wp-content/uploads/Caramel-Cups-4-Square.jpg',
				),
				'Caramel-Cups-4-Square.jpg'
			);
		}
		$this->assertEquals( array( $post_id ), EasyRecipe::get_post_ids() );
		$this->assertEquals( 1, EasyRecipe::get_count() );
		$this->assertStringContainsString( '<div class="easyrecipe', get_post( $post_id )->post_content );
		$version = '3.5.3208';
		if ( 'easy-recipe-7.txt' === $source ) {
			$version = '3.1.09';
		}
		if ( 'easy-recipe-8.txt' === $source ) {
			$version = '';
		}
		if ( 'easy-recipe-9.txt' === $source ) {
			$version = '3.2.1226';
		}
		if ( 'easy-recipe-10.txt' === $source ) {
			$version = '3.2.2802';
		}
		$this->assertStringContainsString( $version . '</div>' . PHP_EOL . '</div>', get_post( $post_id )->post_content );
		$this->assertStringNotContainsString( '[tasty-recipe id', get_post( $post_id )->post_content );
		$recipe = EasyRecipe::convert_post( $post_id );
		$this->assertInstanceOf( 'Tasty_Recipes\Objects\Recipe', $recipe );
		// Check for the beginning of a recipe
		// Both in forms of <div class="easyrecipe someotherclass">
		// as well as <div class="easyrecipe">.
		$this->assertStringNotContainsString( '<div class="easyrecipe ', get_post( $post_id )->post_content );
		$this->assertStringNotContainsString( '<div class="easyrecipe"', get_post( $post_id )->post_content );
		$this->assertStringNotContainsString( $version . '</div>' . PHP_EOL . '</div>', get_post( $post_id )->post_content );
		$this->assertStringNotContainsString( '</div>', get_post( $post_id )->post_content );
		$this->assertStringContainsString( '[tasty-recipe id', get_post( $post_id )->post_content );
		$this->assertStringContainsString( $expected_content, get_post( $post_id )->post_content );
		$this->assertExpectedRecipe( $expected, $expected_image, $recipe );
	}

	/**
	 * Tests markdown conversion behavior.
	 */
	public function test_convert_easy_recipe_markdown() {
		$initial  = <<<EOT
[i]Testing[/i] a [b]darn[/b] [u]onote[/u][br][br][br][img src="http://pinchofyum.dev/wp-content/uploads/LOFT-Collarless-Chambray.png" width="786" height="786" class="alignnone size-full" title="LOFT Collarless Chambray"]
EOT;
		$expected = <<<EOT
<em>Testing</em> a <strong>darn</strong> <u>onote</u><br><br><br><img src="http://pinchofyum.dev/wp-content/uploads/LOFT-Collarless-Chambray.png" width="786" height="786" class="alignnone size-full" title="LOFT Collarless Chambray">
EOT;
		$this->assertEquals( $expected, EasyRecipe::transform_markup_to_html( $initial ) );
	}

	/**
	 * Recipe sample data to use for tests.
	 *
	 * @return array
	 */
	public function recipe_samples() {
		$recipe_1_ingredients   = <<<EOT
<ul>
 	<li>3 cups all purpose flour</li>
 	<li>1 1/2 teaspoons salt</li>
 	<li>1/2 teaspoon <strong>instant</strong> yeast</li>
 	<li>1 1/2 cups room temperature water</li>
</ul>
EOT;
		$recipe_1_instructions  = <<<EOT
<ol>
 	<li><strong>DOUGH PREP:</strong> In a large mixing bowl, whisk the flour, salt, and yeast together until mixed. Stir in the water until a chunky, thick dough forms. If it needs a little more water, add a few more tablespoons, just enough to get it barely wet throughout. It's gonna look scrappy and weird and you're going to question me on whether or not this will work, but it will. Cover the mixing bowl with plastic wrap and let it rest for <strong>12-18 hours</strong>. Overnight is ideal here, kids.</li>
 	<li><strong>PREP FOR BAKING:</strong> When you're ready to bake, preheat the oven to 450. Stick a 6 quart enamel coated cast iron Lodge Dutch Oven (or similar) in the oven for about 30 minutes to heat. At this point, the dough should be big and puffy and pretty loose, with little bubbles in it. Gently scrape the dough out onto a well-floured surface. (Remember: NO KNEAD.) Gently shape it into a ball with flour on the outside, set on a piece of parchment, and cover with plastic while your pan heats up.</li>
 	<li><strong>BAKE:</strong> Remove the plastic from the dough. Lift the dough and parchment together into the pan so the parchment lines the bottom of the hot pan (be careful not to touch the pan since it's very hot). Bake, covered, for 30 minutes. Remove the cover and bake another 10-15 minutes to get the exterior nice and golden brown and crispy. Voila! Done. Miracle no-knead bread, you boss you.</li>
</ol>
EOT;
		$recipe_2_ingredients   = <<<EOT
<ul>
 	<li>1½ cups <a href="http://bit.ly/28YsUOl" target="_blank" rel="nofollow">Quaker Rolled Oats</a></li>
 	<li>1½ cups pecan halves</li>
 	<li>1/2 cup pretzels</li>
 	<li>8 whole medjool dates, pitted</li>
 	<li>2/3 cup coconut oil, melted</li>
 	<li>a pinch of coarse sea salt</li>
 	<li>1 cup thick caramel (could be <a href="http://sallysbakingaddiction.com/homemade-salted-caramel-recipe/" target="_blank">homemade salted caramel</a>, <a href="http://heartbeetkitchen.com/2015/recipes/type/dessert/salty-chocolate-date-caramels/" target="_blank">date caramel</a>, or store bought caramel pieces melted into a sauce - but make sure it is a little bit salty!)</li>
 	<li>2 cups chocolate chips, melted</li>
 	<li>sea salt, optional, for topping</li>
</ul>
EOT;
		$recipe_2_instructions  = <<<EOT
<ol>
 	<li>CRUST: Pulse the oats, pecans, and pretzels in a food processor until broken down into small pieces. Add the dates, coconut oil, and sea salt and pulse until a soft dough forms. Refrigerate for about 15-20 minutes to help the mixture become easier to work with.</li>
 	<li>CARAMEL: Meanwhile, prepare the caramel and melt the chocolate.</li>
 	<li>ASSEMBLY: When the crust mixture is firm enough, press into a muffin tin, leaving a well in the center of each. Add a tablespoon or two of caramel into each cup and top with chocolate. Sprinkle with sea salt if you want. Refrigerate or freeze until hardened. Remove from muffin tins and serve (give them a little time if they've been frozen). Yummmmmy.</li>
</ol>
EOT;
		$recipe_3_ingredients   = <<<EOT
<ul>
 	<li><a href="http://pinchofyum.com/sweet-potato-pizza-crust" target="_blank">Sweet Potato Pizza Crust</a></li>
 	<li>1/2 cup barbecue sauce</li>
 	<li>1/2 cup shredded chicken</li>
 	<li>1/4 cup thinly sliced red onion</li>
 	<li>1 cup mozzarella cheese</li>
 	<li>1/2 cup chopped cilantro</li>
</ul>
EOT;
		$recipe_3_instructions  = <<<EOT
<ol>
 	<li>Prepare the pizza crust.</li>
 	<li>Top with sauce, chicken, red onion, and mozzarella cheese, in that order. Bake at 400 degrees for 10 minutes, or until the cheese is melted and pizza is hot.</li>
 	<li>Finish with cilantro. Cut and serve!</li>
</ol>
EOT;
		$recipe_4_ingredients   = <<<EOT
<ul>
 	<li>2 1/2 cups (about) canned vegetable broth</li>
 	<li>1 15- to 16-ounce can kidney beans, drained</li>
 	<li>1 cup canned unsweetened regular or light coconut milk</li>
 	<li>1 tablespoon minced seeded jalapeño chili</li>
 	<li>1 teaspoon dried thyme</li>
 	<li>1/4 teaspoon ground allspice</li>
 	<li>3/4 cup medium-grain white rice</li>
 	<li>1 cup thinly sliced green onions</li>
</ul>
EOT;
		$recipe_4_instructions  = <<<EOT
<ol>
 	<li>Combine 2 cups vegetable broth, kidney beans, coconut milk, minced jalapño chili, thyme and allspice in heavy large saucepan. Bring mixture to boil over medium-high heat. Stir in rice. Reduce heat to medium-low and simmer mixture uncovered until most of liquid is absorbed and rice is almost tender, stirring often, about 20 minutes.</li>
 	<li>Mix 3/4 cup green onions into rice. Continue to simmer until rice is very tender and mixture is creamy, adding more broth by 1/4 cupfuls if mixture seems dry, about 5 minutes longer. Season to taste with salt and pepper. Transfer to serving bowl. Sprinkle with remaining 1/4 cup green onions and serve.</li>
</ol>
EOT;
		$recipe_5_ingredients   = '';
		$recipe_5_instructions  = <<<EOT
<ol>
	<li>Combine spices. Heat in a pan until fragrant. Add 2 Tbsp olive oil, then the cabbage and cauliflower.</li>
	<li>Sautee until tender.</li>
	<li>Eat!</li>
</ol>
EOT;
		$recipe_6_ingredients   = <<<EOT
<h4><strong>Chili Sesame Sauce:</strong></h4>
<ul>
 	<li>1/2 cup peanut butter</li>
 	<li>1/3 cup sesame oil</li>
 	<li>1/3 cup light low sodium soy sauce</li>
 	<li>1/4 cup rice vinegar</li>
 	<li>2 tablespoons chili paste (like sambal oelek)</li>
 	<li>2 tablespoons sugar</li>
 	<li>1 clove garlic, minced</li>
 	<li>1 knob of fresh ginger, peeled and grated</li>
 	</ul>
<h4><strong>Zoodles and Tofu</strong></h4>
<ul>
 	<li>12 ounces extra firm tofu</li>
 	<li>4-6 zucchini</li>
 	<li>sesame seeds and scallions for topping</li>
</ul>
EOT;
		$recipe_6_instructions  = <<<EOT
<ol>
 	<li><strong>Chili Sesame Sauce:</strong> Shake all the sauce ingredients in a jar or toss them in the food processor and give them a whirl. If you're going to have this cold, refrigerate your sauce for a while.</li>
 	<li><strong>Tofu:</strong>Press excess moisture out of the tofu. Cut into bite sized pieces. Heat a tiny bit of oil in a nonstick pan. Add the tofu and stir fry until golden brown. Add about 1/2 cup sauce and simmer until the sauce starts to evaporate / absorb into the tofu and becomes browned in the pan. Keep gently flipping and scraping browned bits off the bottom - after a while, you should end up with nicely golden brown tofu with some little yummy browned bits from the sauce.</li>
 	<li><strong>All Together:</strong> Spiralize your zucchini and toss with about 1/4 cup sauce per serving. Top with tofu, sesame seeds, and scallions. Serve immediately.</li>
</ol>
EOT;
		$recipe_7_ingredients   = <<<EOT
<h4>Zucchini Fritters</h4>
<ul>
 	<li>1 medium zucchini, grated</li>
 	<li>1/2 tsp salt</li>
 	<li>zest of 1 lemon</li>
 	<li>a handful of parsley, chopped</li>
 	<li>chives, chopped</li>
 	<li>1 egg</li>
 	<li>pepper</li>
 	<li>1/2 cup flour</li>
 	<li>oil for frying</li>
 	</ul>
<h4>Goat Cheese Sauce</h4>
<ul>
 	<li>Goat Cheese, 100 grams</li>
 	<li>Plain yogurt, 2/3 cup</li>
 	<li>Salt, pepper, and chives (to taste)</li>
</ul>
EOT;
		$recipe_7_instructions  = <<<EOT
<h4>Zucchini Fritters</h4>
<ol>
 	<li>Mix all but the oil together until it’s no longer lumpy.</li>
 	<li>Heat oil in a large skillet, medium-high.</li>
 	<li>Using a tablespoon, make small balls and drop them into the oil (I’d recommend about 4-5 at a time).</li>
 	<li>Fry on each side, flipping after a few minutes when they are golden brown.</li>
 	<li>Drain on paper towel-lined plates.</li>
</ol>
<h4>Goat Cheese Sauce</h4>
<ol>
 	<li>Mix it all up! Ta da.</li>
</ol>
EOT;
		$recipe_8_ingredients   = <<<EOT
<ul>
 	<li>1 teaspoon butter</li>
 	<li>1 teaspoon oil</li>
 	<li>1 can white and yellow sweet corn</li>
 	<li>1 teaspoon cumin</li>
 	<li>1 teaspoon chili powder</li>
 	<li>1 lb. jumbo lump crab meat</li>
 	<li>1/2 cup sour cream</li>
 	<li>1/4 cup chopped chives</li>
 	<li>tortilla chips</li>
 	<li>1/2 cup shredded cheese (I used white cheddar)</li>
 	<li>1 avocado, sliced or cut into chunks</li>
 	<li>1 slice bacon, chopped or crumbled</li>
</ul>
EOT;
		$recipe_8_instructions  = <<<EOT
<ol>
 	<li>Preheat oven to 450 degrees. Melt the butter and oil in a small skillet over medium high heat. Add corn and spices. Let cook, stirring only a few times, until the corn turns golden brown on the outside.</li>
 	<li>Add crab meat, sour cream, and half of the chives (reserve other half for topping). Mix everything together in the skillet and keep over low heat for 3-4 minutes, to heat through.</li>
 	<li>Assemble one layer of chips on a baking sheet covered with aluminum foil. Spoon half of the crab mixture and half of the shredded cheese on top of the chips. Top with another layer of chips, the rest of the crab mixture and the rest of the cheese. Broil for 3-5 minutes.</li>
 	<li>Remove from oven and sprinkle with chives, bacon, and avocado pieces.</li>
</ol>
EOT;
		$recipe_9_ingredients   = <<<EOT
<ul>
 	<li>1 8 oz. package of Cream Cheese</li>
	<li>1 Can of Sweetened Condensed Milk</li>
	<li>1/3 Cup Lemon Juice</li>
	<li>1 tsp. Vanilla</li>
	<li>Graham Cracker Crusts</li>
	<li>Fresh Strawberries for Topping</li>
</ul>
EOT;
		$recipe_9_instructions  = <<<EOT
<ol>
	<li>Combine Softened Cream Cheese, Sweetened Condensed Milk and Vanilla in a Mixer and Mix Well.</li>
	<li>Slowly add in Lemon Juice and Mix</li>
	<li>Pour into Graham Cracker Crusts and Chill Overnight</li>
	<li>Top with Fresh Strawberries</li>
</ol>
EOT;
		$recipe_10_ingredients  = <<<EOT
<ul>
 	<li>Any size pork belly (mine was 9 lbs)</li>
 	<li>Jeff's original rub recipe</li>
 	<li>Foil pan(s)</li>
 	<li>Heavy duty foil</li>
 	<li>Honey</li>
 	<li>Jeff's original barbecue sauce recipe</li>
 	<li>Hot pepper jelly (optional)</li>
</ul>
EOT;
		$recipe_10_instructions = <<<EOT
<ol>
 	<li>Remove the rind from the pork belly using a sharp knife.</li>
 	<li>Place the pork belly in the freezer for 30 minutes to allow the fat to get really solid so it will cut more easily.</li>
 	<li>With the pork belly laying in front of you on a cutting board, slice the pork belly into pieces that are about 1.5 inches wide.</li>
 	<li>Further cut the slices into pieces that are 1.5 inches wide giving you cubes of meat that are 1.5 x 1.5 inches square.</li>
 	<li>Place ⅓ cup of Jeff's original rub into a zip top back and then place 2 lbs of meat into the bag. Shake and massage bag to coat the meat with the rub.</li>
 	<li>Pour the coated pieces of meat out onto a cutting board or bowl.</li>
 	<li>Arrange the pieces of meat onto a cooling rack, Bradley rack or Weber grill pan to make them easy to move into and out of the smoker.</li>
 	<li>Set up your smoker for cooking at about 225-240 °F using indirect heat. If your smoker has a water pan, fill it up.</li>
 	<li>When the smoker is ready, place the racks of meat onto the smoker grate and quickly close the lid/door.</li>
 	<li>Let the meat cook and smoke until a crust forms and the bark on the outside of the meat starts to turn to a light mahogany.</li>
 	<li>Place the meat into a foil pan and coat with about ¼ to ⅓ cup of honey and ¼ to ½ cup of Jeff's barbecue sauce per 2 lbs of meat.</li>
 	<li>Optional, you can omit the barbecue sauce and add 2-3 heaping tablespoons of hot pepper jelly with the honey.</li>
 	<li>Mix the glaze with the meat.</li>
 	<li>Cover the pan with foil and place back into he smoker for an additional 60-90 minutes or until the meat reaches 200°F. (no smoke required).</li>
 	<li>When the pork belly burnt ends are finished cooking, remove the foil and let them cool for a few minutes before serving.</li>
</ol>
EOT;
		return array(
			array(
				'easy-recipe-1.txt',
				array(
					'title'                       => 'Miracle No Knead Bread',
					'author_name'                 => 'Pinch of Yum, with inspiration from my friends Angela and <a href="http://www.girlversusdough.com/2015/08/13/no-knead-dutch-oven-bread/" target="_blank">Stephanie</a>',
					'description'                 => '',
					'ingredients'                 => $recipe_1_ingredients,
					'instructions'                => trim( $recipe_1_instructions ),
					'notes'                       => '',
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '',
					'cook_time'                   => '',
					'total_time'                  => '',
					'yield'                       => '8 servings',
					'category'                    => '',
					'method'                      => '',
					'cuisine'                     => '',
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
				'Miracle-No-Knead-Bread-3-2-Square',
				'You can read about ten other things I love to do with my adorable red Lodge Dutch Oven',
			),
			array(
				'easy-recipe-2.txt',
				array(
					'title'                       => 'No-Bake Salted Caramel Cups',
					'author_name'                 => 'Pinch of Yum',
					'description'                 => '',
					'ingredients'                 => $recipe_2_ingredients,
					'instructions'                => trim( $recipe_2_instructions ),
					'notes'                       => 'The crust for these can also be made with the <a href="http://pinchofyum.com/energy-bites" target="_blank">Peanut Butter Pretzel Energy Bites</a> base!',
					'average_rating'              => '2.8',
					'total_reviews'               => '5',
					'prep_time'                   => '',
					'cook_time'                   => '',
					'total_time'                  => '',
					'yield'                       => '10-12 cups',
					'category'                    => '',
					'method'                      => '',
					'cuisine'                     => '',
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
				'Also: oats for the win.',
			),
			array(
				'easy-recipe-3.txt',
				array(
					'title'                       => 'BBQ Chicken Sweet Potato Pizza',
					'author_name'                 => 'Pinch of Yum',
					'description'                 => '',
					'ingredients'                 => $recipe_3_ingredients,
					'instructions'                => trim( $recipe_3_instructions ),
					'notes'                       => "Amounts will vary depending on the size of your pizza, so just play it by ear. When it doubt, go for LESS toppings so it doesn't weigh the crust down too much.",
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '',
					'cook_time'                   => '',
					'total_time'                  => '',
					'yield'                       => '2',
					'category'                    => '',
					'method'                      => '',
					'cuisine'                     => '',
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
				'Sweet-Potato-Pizza-1-4-Square',
				'Hiya guys! Here, want a slice of sweet potato pizza, BBQ chicken style?',
			),
			array(
				'easy-recipe-4.txt',
				array(
					'title'                       => "West Indian Bean's & Rice",
					'author_name'                 => "Pinch of Yum's",
					'description'                 => "Suitable accompaniment's to this risotto-like dish are crusty bread, an avocado and orange salad with a cilantro vinaigrette and, to top it off, coconut pie.",
					'ingredients'                 => $recipe_4_ingredients,
					'instructions'                => trim( $recipe_4_instructions ),
					'notes'                       => '',
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '12 mins',
					'cook_time'                   => '2 hours',
					'total_time'                  => '2 hours 12 mins',
					'yield'                       => '6 servings',
					'category'                    => 'Main',
					'method'                      => '',
					'cuisine'                     => 'Caribbean',
					'serving_size'                => '2 cups',
					'calories'                    => '581',
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
					'total_time_raw'              => '2 hours 12 mins',
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
				'Maecenas sed diam eget risus varius blandit sit amet non magna.',
			),
			array(
				'easy-recipe-5.txt',
				array(
					'title'                       => 'Test Recipe!',
					'author_name'                 => '',
					'description'                 => 'This is a test recipe',
					'ingredients'                 => $recipe_5_ingredients,
					'instructions'                => trim( $recipe_5_instructions ),
					'notes'                       => '',
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '',
					'cook_time'                   => '',
					'total_time'                  => '',
					'yield'                       => '',
					'category'                    => '',
					'method'                      => '',
					'cuisine'                     => '',
					'serving_size'                => '1/2 cup',
					'calories'                    => '250',
					'sugar'                       => '2g',
					'sodium'                      => '150mg',
					'fat'                         => '4g',
					'saturated_fat'               => '0 g',
					'unsaturated_fat'             => '0 g',
					'trans_fat'                   => '1 g',
					'carbohydrates'               => '120 g',
					'fiber'                       => '18 g',
					'protein'                     => '12 g',
					'cholesterol'                 => '120g',
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
				'Below is a test recipe:',
			),
			array(
				'easy-recipe-6.txt',
				array(
					'title'                       => 'Spicy Sesame Zoodles with Crispy Tofu',
					'author_name'                 => 'Pinch of Yum',
					'description'                 => '',
					'ingredients'                 => $recipe_6_ingredients,
					'instructions'                => trim( $recipe_6_instructions ),
					'notes'                       => "I did NOT cook the zoodles. I wanted this to be more like a salad, cold and creamy and crunchy. Whether or not you cook the zoodles, when the sauce hits those zoodles they're going to get a little watery. I found that this was only a problem when I didn't serve immediately, so to avoid excess moisture, serve it right after you toss it up!<br><br>You may want to use more tofu depending on how many servings you are wanting to get out of this. I was fine with 12 ounces as 6 servings (just doing a small scoop of tofu on each pile of zoodles) but if you were super hungry you might want more.<br><br>Storing this: I would store the sauce separate from the zoodles and tofu, and then toss it all together just before serving.",
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '',
					'cook_time'                   => '',
					'total_time'                  => '',
					'yield'                       => '6',
					'category'                    => '',
					'method'                      => '',
					'cuisine'                     => '',
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
				'Are you down with this?',
			),
			array(
				'easy-recipe-7.txt',
				array(
					'title'                       => 'Zucchini Fritters & Goat Cheese Sauce',
					'author_name'                 => 'inspired by <a href="http://whatsheshaving.com/2010/08/07/zucchini-fritters/" target="_blank" title="I\'ll Have What She\'s Having">I\'ll Have What She\'s Having</a>',
					'description'                 => '',
					'ingredients'                 => $recipe_7_ingredients,
					'instructions'                => trim( $recipe_7_instructions ),
					'notes'                       => '',
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '',
					'cook_time'                   => '',
					'total_time'                  => '',
					'yield'                       => '',
					'category'                    => '',
					'method'                      => '',
					'cuisine'                     => '',
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
				'This is the apron that my husband wore last night.',
			),
			array(
				'easy-recipe-8.txt',
				array(
					'title'                       => 'Crab & Avocado Nachos',
					'author_name'                 => 'Pinch of Yum',
					'description'                 => '',
					'ingredients'                 => $recipe_8_ingredients,
					'instructions'                => trim( $recipe_8_instructions ),
					'notes'                       => '',
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '10 mins',
					'cook_time'                   => '5 mins',
					'total_time'                  => '15 minutes',
					'yield'                       => '4',
					'category'                    => '',
					'method'                      => '',
					'cuisine'                     => '',
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
				'Now, if you are thinking to yourself',
			),
			array(
				'easy-recipe-9.txt',
				array(
					'title'                       => 'No-Bake Strawberry Lemon Cheesecake Recipe',
					'author_name'                 => '',
					'description'                 => '',
					'ingredients'                 => $recipe_9_ingredients,
					'instructions'                => trim( $recipe_9_instructions ),
					'notes'                       => '',
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '',
					'cook_time'                   => '',
					'total_time'                  => '',
					'yield'                       => '',
					'category'                    => '',
					'method'                      => '',
					'cuisine'                     => '',
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
				'One of our Favorite Summer Recipes is this',
			),
			array(
				'easy-recipe-10.txt',
				array(
					'title'                       => 'Smoked Pork Belly Burnt Ends - Yes Please!',
					'author_name'                 => 'Jeff Phillips',
					'description'                 => "Here's my own personal take on pork belly burnt ends, how to prepare them, season them with my original rub recipe and smoke them to perfection. I think you are going to be knocked off your feet at how good these are.. brace yer' selves!",
					'ingredients'                 => $recipe_10_ingredients,
					'instructions'                => trim( $recipe_10_instructions ),
					'notes'                       => '',
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '30 mins',
					'cook_time'                   => '4 hours',
					'total_time'                  => '4 hours 30 mins',
					'yield'                       => '6-8',
					'category'                    => 'Entree',
					'method'                      => '',
					'cuisine'                     => 'Hot Smoking',
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
					'total_time_raw'              => '4 hours 30 mins',
					'diet'                        => '',
					'description_video_settings'  => '',
					'ingredients_video_settings'  => '',
					'instructions_video_settings' => '',
					'notes_video_settings'        => '',
					'additional_time_label'       => '',
					'additional_time_value'       => '',
					'image_sizes'                 => array(),
				),
				'IMG_4199-1000x667-320x321',
				'I have received a whole lot of questions about alternative ways to',
			),
		);
	}

	/**
	 * Removes the created image when the test is torn down.
	 */
	public function tearDown(): void {
		if ( isset( $this->created_image_id ) ) {
			wp_delete_post( $this->created_image_id, true );
		}
		parent::tearDown();
	}

}
