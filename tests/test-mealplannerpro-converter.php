<?php
/**
 * Test case for the Meal Planner Pro recipe converter.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Objects\Recipe;

/**
 * Test case for the Meal Planner Pro recipe converter.
 */
class Test_MealPlannerPro_Converter extends Tasty_Recipes_Converter_Testcase {

	/**
	 * Seed SQL file to use with tests.
	 *
	 * @var string
	 */
	protected $sql_source = 'mpp.sql';

	/**
	 * Conversion class to use with tests.
	 *
	 * @var string
	 */
	protected static $conversion_class = 'Tasty_Recipes\Converters\MealPlannerPro';

	/**
	 * Overrides base test method to add a featured image to every post.
	 *
	 * @dataProvider recipe_samples
	 *
	 * @param string $source           String to use as the source post_content value.
	 * @param array  $expected         Expected result for processed recipe.
	 * @param string $expected_image   Expected image file name.
	 * @param string $expected_content Expected post content value, when relevant.
	 */
	public function test_recipe_conversion( $source, $expected, $expected_image, $expected_content = '' ) {
		$this->add_featured_image_to_post();
		parent::test_recipe_conversion( $source, $expected, $expected_image, $expected_content );
	}

	/**
	 * Recipe sample data to use for tests.
	 *
	 * @return array
	 */
	public function recipe_samples() {
		$recipe_1_ingredients  = <<<EOT
<h4>for the <em>dough</em>:</h4>
<ul>
<li>15 1/2 oz <em>all-purpose</em> flour</li>
<li>3 Tbsp granulated sugar</li>
<li>1 1/4 tsp salt</li>
<li>1 1/2 tsp active dry yeast</li>
<li>8 oz non-dairy milk, lukewarm*</li>
<li>6 Tbsp (3 oz) vegan butter, melted</li>
</ul>
<h4>for the filling:</h4>
<ul>
<li>1/4 cup granulated sugar1</li>
<li>1/2 tsp ground cinnamon</li>
<li>2 tsp all-purpose flour</li>
<li>2 tsp non-dairy milk</li>
</ul>
<h4>for the frosting:</h4>
<ul>
<li>1 1/4 cups powdered sugar</li>
<li>1/2 tsp vanilla extract</li>
<li>2-3 Tbsp non-dairy milk</li>
</ul>
EOT;
		$recipe_1_instructions = <<<EOT
<h4>make the dough:</h4>
<ol>
<li>Combine the flour, sugar, and <a href="https://example.com">salt</a> in a <a href="https://example.com" rel='nofollow'>large bowl</a> (use the bowl of your stand mixer if you have one) and mix to combine. Mix in the yeast. Add the lukewarm nondairy milk and the melted butter and mix to create a shaggy dough using a danish dough whisk or a large wooden spoon.</li>
<li>If you have a stand mixer, mix using the dough hook on low speed for 7-8 minutes, until smooth. If you are kneading by hand, dump the dough onto a lightly floured solid surface and knead for 10-12 minutes, until smooth. The dough should be tacky but not terribly sticky.</li>
<li>Oil your hands with spray oil, then remove the dough from the bowl and form into a ball by stretching the dough from the sides around to the bottom. Spray the bowl with the oil, then place the dough seam-side down in the bowl. Spray the top of the dough with the oil, then cover the bowl with plastic wrap.</li>
<li>Let rise in a warm place* for 60-90 minutes, until doubled in size.</li>
</ol>
<h4>form the rolls:</h4>
<ol>
<li>Combine the granulated sugar, cinnamon, and flour for the filling in a small bowl. Grease a 8×12″ baking dish with spray oil.</li>
<li>When the dough is done rising, use some spray oil to grease a solid work surface and dump the dough out of the bowl. Use a rolling pin to roll into a 12×15″ rectangle.</li>
<li>Use a silicone pastry brush to spread a thin layer of almond milk on top of the dough, then sprinkle the filling all across, leaving a 1/2″ at one side to help the edges seal.</li>
<li>Roll the dough from the short edge into a tube. When you reach the edge that wasn’t covered by the filling, pinch the seam to seal.</li>
<li>Take a long piece of dental floss and bring it under the tube of dough. Cross the floss across the top, then pull tight to cut the roll. Cut the roll into 10 even pieces and arrange in the baking dish with about 1 inch between rolls.</li>
<li>Cover the dish with plastic wrap and refrigerate overnight.</li>
<li>The next morning, remove the rolls from the fridge (they should have gotten a bit puffy) and let sit for 30 minutes. While the rolls rest, preheat oven to 350ºF.</li>
<li>Remove the plastic wrap and bake for about 30 minutes, rotating halfway thorough, until golden brown.</li>
<li>While the rolls bake, combine the ingredients for the frosting, starting with just 3 Tbsp of the non-dairy milk. Stir, then add more milk as necessary 1 tsp at a time. If you accidentally add too much liquid, just stir in more powdered sugar.</li>
<li>When the rolls are done baking, remove from the oven and let sit 5 minutes. Drizzle the rolls with the frosting and serve immediately. Enjoy!</li>
</ol>
EOT;
		$recipe_1_notes        = <<<EOT
<ul>
<li>I’ve used both <em>soy milk</em> and <strong>almond milk</strong> successfully with this recipe.</li>
<li>Warm places: in a sunny window, on top of the fridge, on the dash of your car (no joke), in the microwave with a hot cup of water (but DON’T actually microwave the dough..), in your oven with the dough-proofing setting on, etc.</li>
</ul>
EOT;
		$recipe_2_ingredients  = <<<EOT
<ul>
<li>1 leek</li>
<li>2 tsp olive oil</li>
<li>4 eggs</li>
<li>1 cup milk</li>
<li>1/4 cup artichoke hearts, cut into bite-sized pieces</li>
<li>1/4 cup sliced roasted red pepper</li>
<li>1/4 tsp salt</li>
<li>1 cup cheddar cheese</li>
<li>1 recipe <a href="http://mycaliforniaroots.com/vegan-pie-crust/" target="_blank">pie crust</a></li>
</ul>
EOT;
		$recipe_2_instructions = <<<EOT
<ol>
<li>Prepare the pie crust. Let rest in the fridge for 30 minutes, then roll out into a 12" circle and place in a greased 10" tart pan. Let the extra crust hang over the edge and freeze for 20 minutes.</li>
<li>Preheat your oven to 350ºF.</li>
<li>Slice the leek into 1/4" rounds. Heat a skillet over medium-high heat and add the olive oil and leeks. Cook, stirring often, until the leeks are soft and browned in some spots.</li>
<li>Scramble the eggs in a large bowl. Add the cooked leeks, milk, artichoke hearts, roasted red pepper, salt, and about 2/3 of the cheese. Mix to combine.</li>
<li>Place a sheet of parchment over the frozen pie crust, then fill with pie weights. Bake for 10 minutes, then remove the pie weights and remove the overhanging crust.</li>
<li>Pour in the egg filling. Top with the remaining cheese, then bake for about 35 minutes, until golden on top and mostly firm in the middle.</li>
<li>Let cool for about 10 minutes before removing from the tart pan and serving. Eat!</li>
</ol>
EOT;
		$recipe_2_notes        = <<<EOT
<ul>
<li>I used canned artichoke hearts and roasted red pepper.</li>
<li>I used the back of a spoon to press the overhanging crust into the edge of the tart pan, causing it to separate. If you're using a pie dish, scissors might work better.</li>
<li>If you are using my pie crust recipe but you aren't looking for a vegan crust, just replace the vegan butter with regular salted butter.</li>
<li>If you are dairy-free, use the vegan pie crust as written and use plain, unsweetened soy milk in the quiche and omit the cheese.</li>
</ul>
EOT;
		$recipe_3_ingredients  = <<<EOT
<h4>For the dough:</h4>
<ul>
<li>2 cups flour</li>
<li>1 egg</li>
<li>1 egg yolk</li>
<li>1/2 cup sugar</li>
<li>1/2 cup cold butter, cut into small cubes</li>
<li>1/2 tsp baking powder</li>
<li>1 tsp vanilla</li>
<li>pinch of salt</li>
</ul>
<h4>For the filling:</h4>
<ul>
<li>2 pears, peeled and diced</li>
<li>3 1/2 Tbsp sugar</li>
<li>1/2 cup water</li>
<li>juice from 1/2 lemon</li>
</ul>
EOT;
		$recipe_3_instructions = <<<EOT
<h4>For the dough:</h4>
<ol>
<li>Pre- heat oven to 350°.</li>
<li>On a flat surface add together flour and salt. Make a well in the center and add the egg and yolk, baking powder, sugar, vanilla and butter. Work the ingredients together quickly to form a soft dough.</li>
<li>Wrap the dough in plastic and refrigerate for approximately 30 minutes.</li>
</ol>
<h4>For the filling:</h4>
<ol>
<li>While the dough is in the fridge, add the diced pears, lemon juice and water to a small to medium sized pot.</li>
<li>Cook on low, stirring occasionally, until the pears become soft and the liquid has evaporated (make sure they don't turn burn). Remove from heat, add the sugar, and mix well.</li>
<li>Remove the dough from the fridge, knead a couple of times and then roll out about half the dough into about a 1/8" sheet.</li>
<li>Use a cookie cutter to cut into shapes. Place cut out flowers on a parchment-lined cookie sheet.</li>
<li>In the middle of each cookie place a teaspoon full of filling and top with second cookie.</li>
<li>Use your fingers to pinch the sides of the cookies closed. Dipping finger tips in water helps to seal. Continue until all cut outs are complete, then repeat with the remaining dough.</li>
<li>Bake for approximately 13-15 minutes. Let cool completely and dust with plenty of powdered sugar.</li>
</ol>
EOT;
		$recipe_3_notes        = <<<EOT
EOT;
		return array(
			array(
				'[mpprecipe-recipe:1]',
				array(
					'title'                       => "Vegan Cinnamon Roll's",
					'author_name'                 => 'Raquel Smith',
					'description'                 => 'These easy vegan cinnamon rolls are just like the real thing, but with no dairy and no eggs! Fluffy, sweet, and a bit chewy, these are everything you’ve ever dreamed of in a cinnamon roll.',
					'ingredients'                 => $recipe_1_ingredients,
					'instructions'                => trim( $recipe_1_instructions ),
					'notes'                       => $recipe_1_notes,
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '2 hours',
					'cook_time'                   => '30 minutes',
					'total_time'                  => '2 hours 30 minutes',
					'yield'                       => '10 rolls',
					'category'                    => 'Breakfast',
					'method'                      => '',
					'cuisine'                     => 'American',
					'serving_size'                => '1 roll',
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
					'total_time_raw'              => '2 hours 30 minutes',
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
			),
			array(
				'[mpprecipe-recipe:22]',
				array(
					'title'                       => 'Leek & Artichoke Quiche',
					'author_name'                 => 'Raquel Smith',
					'description'                 => "This Leek & Artichoke Quiche with Red Peppers is my <strong>favorite brunch recipe</strong> ever. It's also amazing for dinner! With extra pie crust for all you crust-lovers out there!",
					'ingredients'                 => $recipe_2_ingredients,
					'instructions'                => trim( $recipe_2_instructions ),
					'notes'                       => $recipe_2_notes,
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '1 hour',
					'cook_time'                   => '35 minutes',
					'total_time'                  => '1 hour 35 minutes',
					'yield'                       => '8 slices',
					'category'                    => 'Breakfast',
					'method'                      => '',
					'cuisine'                     => 'American',
					'serving_size'                => '1 slice',
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
					'total_time_raw'              => '1 hour 35 minutes',
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
				'[mpprecipe-recipe:174]',
				array(
					'title'                       => 'Italian Pear Filled Cookies',
					'author_name'                 => 'An Italian in my kitchen',
					'description'                 => '',
					'ingredients'                 => $recipe_3_ingredients,
					'instructions'                => trim( $recipe_3_instructions ),
					'notes'                       => $recipe_3_notes,
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '',
					'cook_time'                   => '',
					'total_time'                  => '',
					'yield'                       => '20 cookies',
					'category'                    => 'Baked Goods',
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
				'canola',
			),
		);
	}
}
