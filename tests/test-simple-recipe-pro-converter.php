<?php
/**
 * Test case for the Simple Recipe Pro recipe converter.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Objects\Recipe;

/**
 * Test case for the Simple Recipe Pro recipe converter.
 */
class Test_Simple_Recipe_Pro_Converter extends Tasty_Recipes_Converter_Testcase {

	/**
	 * Seed SQL file to use with tests.
	 *
	 * @var string
	 */
	protected $sql_source = 'srp.sql';

	/**
	 * Conversion class to use with tests.
	 *
	 * @var string
	 */
	protected static $conversion_class = 'Tasty_Recipes\Converters\Simple_Recipe_Pro';

	/**
	 * Modifies the post ID context before the recipe is converted.
	 *
	 * @param string $source   Source post_content value.
	 * @param array  $expected Expected results of recipe conversion.
	 */
	protected function pre_recipe_conversion( $source, $expected ) {
		if ( 'Buffalo Cauliflower Tacos' === $expected['title'] ) {
			$this->post_id = 33792;
		}
		if ( 'A Test Recipe' === $expected['title'] ) {
			$this->post_id = 33795;
		}
		if ( 'Simple Recipe Pro recipe 3' === $expected['title'] ) {
			$this->post_id = 33827;
		}
		if ( 'Kale & Cranberry Salad with Roasted Root Vegetables' === $expected['title'] ) {
			$this->post_id = 1322;
		}
	}

	/**
	 * Recipe sample data to use for tests.
	 *
	 * @return array
	 */
	public function recipe_samples() {
		$recipe_1 = <<<EOT
Whoa whoa whoaaa. Buffalo Cauliflower Tacos with Avocado Crema are hap-pen-ning. This is not a sad moment on Pinch of Yum.

There is a restaurant in St. Paul, Minnesota called J. Selby’s. I adore it. It’s a “plant-based eatery” with pear ginger kombucha on tap, every kind of vegan bowl you could ever dream of, and deep fried buffalo cauliflower “wings” that will rock your world. The origins of this buffalo cauliflower taco obsession can be traced back to many kombuchas, “wings,” and burrito bowls shared by Bjork and I at the bar of this little restaurant. If you live in Minnesota and you want a fun place to eat, J. Selby’s will be your place.

Buffalo cauliflower (at least this version) is fairly simple to make – dip and bake – but flavor-wise, it’s not for the faint of heart. It packs some heat and a boatload of tang, like any good buffalo bar food. Its mostly-crispy breading sucks up the hot sauce and gives these little nuggets so much fiery flavor.

[simple-recipe]
EOT;
		$recipe_2 = <<<EOT
Simple Recipe Pro Test 2

[simple-recipe]
EOT;
		$recipe_3 = <<<EOT
[simple-recipe:33827a]
EOT;
		$recipe_4 = <<<EOT
[simple-recipe]
EOT;

		return array(
			array(
				$recipe_1,
				array(
					'title'                       => 'Buffalo Cauliflower Tacos',
					'author_name'                 => 'bjork',
					'description'                 => '<p>This colorful, spicy, crispy, mega-yum recipe was inspired by the amazing buffalo “wings” at&nbsp;<a href="http://www.jselbys.com/" data-mce-href="http://www.jselbys.com/">J. Selby’s</a>&nbsp;in Saint Paul and&nbsp;<a href="http://sarasullivan.com/buffalo-cauliflower-tacos-avocado-cream/" data-mce-href="http://sarasullivan.com/buffalo-cauliflower-tacos-avocado-cream/">this recipe</a>&nbsp;from Sarah Sullivan. Thanks to both sources of inspiration!</p>',
					'ingredients'                 => '<h4>Buffalo Cauliflower Tacos:</h4><ul><li>1 cup flour</li><li>1 cup milk</li><li>1/4 tsp garlic powder</li><li>1 head cauliflower</li><li>3/4 cup hot sauce</li><li>shredded cabbage or slaw</li><li>chopped fresh cilantro</li><li>tortillas</li></ul><h4>Avocado Crema:</h4><ul><li>2 avocados</li><li>1 clove garlic</li><li>1/4 cup sour cream</li><li>1/4 cup water</li><li>1/2 tsp salt</li><li>squeeze of lime</li></ul>',
					'instructions'                => '<ol><li><strong>Batter the cauliflower:&nbsp;</strong>Preheat oven to 450 degrees. Whisk flour, milk, salt, pepper, and garlic powder. Dip cauliflower pieces in the batter and let excess drip off. Place on parchment-lined baking sheet. Bake on an upper rack for 15-20 minutes until lightly crisped and browned.</li><li><strong>Spice the cauliflower:&nbsp;</strong>Gently toss the baked cauliflower in a bowl with the hot sauce. Return to pan. Bake for another 5-10 minutes.</li><li><strong>Avocado Crema:&nbsp;</strong>Mix all ingredients in a food processor or blender until smooth.</li><li><strong>Slaw:&nbsp;</strong>Toss the slaw with some of the avocado crema until it’s nice and creamy.</li><li><strong>Tacos:&nbsp;</strong>Fill each tortilla with slaw, cauliflower, a drizzle of avocado crema, and cilantro. Perfection!</li></ol>',
					'notes'                       => '<p><strong>Gluten Free:</strong>&nbsp;Use GF flour and GF corn tortillas. Note: you may need a few more tablespoons of milk to get the right consistency for the batter when using GF flour.</p><p><strong>Vegan:</strong>&nbsp;use non-dairy milk and skip the sour cream in the sauce.</p><p><strong>Cauliflower Pro Tip:</strong>&nbsp;It helps if you have a greased wire rack to back the cauliflower on – this allows excess batter to drip down and promotes crispiness.&nbsp;<img class="emoji" draggable="false" src="https://s.w.org/images/core/emoji/2.3/svg/1f44d-1f3fc.svg" alt="??" data-mce-resize="false" data-mce-placeholder="1" data-wp-emoji="1" data-mce-src="https://s.w.org/images/core/emoji/2.3/svg/1f44d-1f3fc.svg">&nbsp;But it works fine without, too.</p><p><strong>Notes about texture:</strong>&nbsp;The texture as written is pretty firm and crispy, but not deep-fried-level crispy. Not surprisingly, you’d have to deep fry to achieve that. Which you could totally do!&nbsp;<a href="https://pinchofyum.com/crispy-fish-tacos-jalapeno-sauce" data-mce-href="https://pinchofyum.com/crispy-fish-tacos-jalapeno-sauce">Here’s a recipe with a deep fry batter</a>&nbsp;that I’d recommend if you want to spend the time doing that (it’s delish). But to me, this is a really great middle-of-the-road alternative that is a) much easier, b) healthier, and c) still really tasty!</p>',
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '15 mins',
					'cook_time'                   => '30 minutes',
					'total_time'                  => '50 mins',
					'yield'                       => '8 tacos',
					'category'                    => '',
					'method'                      => '',
					'cuisine'                     => 'Mexican Fusion',
					'serving_size'                => '2 tacos',
					'calories'                    => '190kcal',
					'sugar'                       => '4g',
					'sodium'                      => '600mg',
					'fat'                         => '10g',
					'saturated_fat'               => '3g',
					'unsaturated_fat'             => '',
					'trans_fat'                   => '0',
					'carbohydrates'               => '22g',
					'fiber'                       => '5g',
					'protein'                     => '5g',
					'cholesterol'                 => '7mg',
					'keywords'                    => '',
					'nutrifox_id'                 => '',
					'video_url'                   => '',
					'total_time_raw'              => '50 mins',
					'diet'                        => '',
					'description_video_settings'  => '',
					'ingredients_video_settings'  => '',
					'instructions_video_settings' => '',
					'notes_video_settings'        => '',
					'additional_time_label'       => '',
					'additional_time_value'       => '',
					'image_sizes'                 => array(),
				),
				'Korean-BBQ-Beef-Tacos',
			),
			array(
				$recipe_2,
				array(
					'title'                       => 'A Test Recipe',
					'author_name'                 => 'bjork',
					'description'                 => '<p><strong>Bold text</strong>, <em>italic text</em>, <span style="text-decoration: underline;" data-mce-style="text-decoration: underline;">underlined text</span>, <del>striked out text</del>.</p><ul><li>Bulleted list item</li><li>And another one</li></ul><ol><li>Numbered list item</li><li>And another one</li></ol><p>A <a href="https://github.com" data-mce-href="https://github.com">follow link</a>. A <a href="https://github.com" target="_blank" rel="nofollow noopener" data-mce-href="https://github.com">nofollow link</a>.</p>',
					'ingredients'                 => '<ul><li>1 head Cauliflower</li><li>1 apple</li><li>1 tbsp olive oil</li></ul>',
					'instructions'                => '<p>Combine them in some way...</p><p><strong>Bold text</strong>, <em>italic text</em>, <span style="text-decoration: underline;" data-mce-style="text-decoration: underline;">underlined text</span>, <del>striked out text</del>.</p><ul><li>Bulleted list item</li><li>And another one</li></ul><ol><li>Numbered list item</li><li>And another one</li></ol><p>A <a data-mce-href="https://github.com" href="https://github.com">follow link</a>. A <a href="https://github.com" target="_blank" rel="nofollow noopener" data-mce-href="https://github.com">nofollow link</a>.</p>',
					'notes'                       => '<p><strong>Bold text</strong>, <em>italic text</em>, <span style="text-decoration: underline;" data-mce-style="text-decoration: underline;">underlined text</span>, <del>striked out text</del>.</p><ul><li>Bulleted list item</li><li>And another one</li></ul><ol><li>Numbered list item</li><li>And another one</li></ol><p>A <a href="https://github.com" data-mce-href="https://github.com">follow link</a>. A <a href="https://github.com" target="_blank" rel="nofollow noopener" data-mce-href="https://github.com">nofollow link</a>.</p>',
					'average_rating'              => '4',
					'total_reviews'               => '2',
					'prep_time'                   => '10 minutes',
					'cook_time'                   => '30 minutes',
					'total_time'                  => '40 minutes',
					'yield'                       => '10',
					'category'                    => '',
					'method'                      => '',
					'cuisine'                     => 'Italian',
					'serving_size'                => '1 muffin',
					'calories'                    => '35kcal',
					'sugar'                       => '3g',
					'sodium'                      => '16mg',
					'fat'                         => '2g',
					'saturated_fat'               => '0g',
					'unsaturated_fat'             => '',
					'trans_fat'                   => '0',
					'carbohydrates'               => '5g',
					'fiber'                       => '2g',
					'protein'                     => '1g',
					'cholesterol'                 => '0',
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
				'Pumpkin-Muffins-Out-of-Oven',
			),
			array(
				$recipe_3,
				array(
					'title'                       => 'Simple Recipe Pro recipe 3',
					'author_name'                 => 'Raquel Smith',
					'description'                 => 'Some description',
					'ingredients'                 => '<ul><li>1 apple</li><li>1 carrot</li></ul><h4>A group of ingredients</h4><ul><li>More ingredients</li><li>Another ingredient</li></ul><p>A paragraph</p>',
					'instructions'                => '<ol><li>Do this</li><li>Then that</li><li><strong>Bold</strong>, <em>italic</em>, <span style="text-decoration: underline;" data-mce-style="text-decoration: underline;">underline</span>, <del>strikethrough</del>, and <a href="https://google.com" target="_blank" rel="nofollow">a link</a></li></ol>',
					'notes'                       => '<p>Notes</p>',
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '10 mins',
					'cook_time'                   => '20 mins',
					'total_time'                  => '50 mins',
					'yield'                       => '22 muffins',
					'category'                    => 'Dinner',
					'method'                      => '',
					'cuisine'                     => 'American',
					'serving_size'                => '1 muffin',
					'calories'                    => '10 kcal',
					'sugar'                       => '30g',
					'sodium'                      => '500mg',
					'fat'                         => '100g',
					'saturated_fat'               => '5g',
					'unsaturated_fat'             => '',
					'trans_fat'                   => '',
					'carbohydrates'               => '10g',
					'fiber'                       => '20g',
					'protein'                     => '40g',
					'cholesterol'                 => '100mg',
					'keywords'                    => '',
					'nutrifox_id'                 => '',
					'video_url'                   => '',
					'total_time_raw'              => '50 mins',
					'diet'                        => '',
					'description_video_settings'  => '',
					'ingredients_video_settings'  => '',
					'instructions_video_settings' => '',
					'notes_video_settings'        => '',
					'additional_time_label'       => '',
					'additional_time_value'       => '',
					'image_sizes'                 => array(),
				),
				'Korean-BBQ-Beef-Tacos',
			),
			array(
				$recipe_4,
				array(
					'title'                       => 'Kale & Cranberry Salad with Roasted Root Vegetables',
					'author_name'                 => 'The Administrator',
					'description'                 => '',
					'ingredients'                 => '<h4>Salad Ingredients:</h4><ul><li>3 cups Beets, Red & Yellow, cubed</li><li>2 1/2 cups Butternut Squash, cubed</li><li>2 1/2 cups Sweet Potato, cubed</li><li>1 cup Roasted Red Onion</li><li>12 oz fresh kale (preferably Tuscan, and young leaves)</li><li>1/2 cup dried cranberries</li><li>1/2 cup Roasted Pumpkin & Sunflower Seeds</li></ul><h4>Dressing Ingredients:</h4><ul><li>12 oz fresh or frozen Cranberries</li><li>1 cup water</li><li>1/4  cup Raw Sugar</li><li>1 tablespoon Raw Honey</li><li>1  1/2 package Stevia – to taste</li><li>1 cup Balsamic Vinegar</li><li>1/4  cup Raw Apple Cider Vinegar</li><li>1 tablespoon Dijon Mustard</li><li>1 tablespoon fresh Lemon juice</li><li>1 1/2 cups Olive Oil</li><li>1 teaspoon Savory Spice Store Catanzaro seasoning</li><li>1/2  teaspoon Salt</li></ul>',
					'instructions'                => 'foo bar',
					'notes'                       => '',
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '0 minutes',
					'cook_time'                   => '0 minutes',
					'total_time'                  => '0 hours',
					'yield'                       => '8',
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
			),
		);
	}
}
