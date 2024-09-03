<?php
/**
 * Test case for the ZipList recipe converter.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Converters\ZipList;
use Tasty_Recipes\Objects\Recipe;

/**
 * Test case for the ZipList recipe converter.
 */
class Test_ZipList_Converter extends Tasty_Recipes_Converter_Testcase {

	/**
	 * Seed SQL file to use with tests.
	 *
	 * @var string
	 */
	protected $sql_source = 'ziplist.sql';

	/**
	 * Conversion class to use with tests.
	 *
	 * @var string
	 */
	protected static $conversion_class = 'Tasty_Recipes\Converters\ZipList';

	/**
	 * Modifies some comment data before the recipe is converted.
	 *
	 * @param string $source   Source post_content value.
	 * @param array  $expected Expected results of recipe conversion.
	 */
	protected function pre_recipe_conversion( $source, $expected ) {
		global $wpdb;
		if ( "Autumn Apple Ro'se Sangria" === $expected['title'] ) {
			$wpdb->update( $wpdb->comments, array( 'comment_post_ID' => $this->post_id ), array( 'comment_post_ID' => 33484 ) );
		}
		if ( 'Zip Recipes 1' === $expected['title'] ) {
			$wpdb->update( $wpdb->comments, array( 'comment_post_ID' => $this->post_id ), array( 'comment_post_ID' => 33484 ) );
			$wpdb->update( $wpdb->prefix . 'zrdn_visitor_ratings', array( 'recipe_id' => 6 ), array( 'recipe_id' => 3 ) );
		}
	}

	/**
	 * Recipe sample data to use for tests.
	 *
	 * @return array
	 */
	public function recipe_samples() {
		$recipe_1_ingredients  = <<<EOT
<ul>
<li>1 bottle rosé wine</li>
<li>2 cups apple cider</li>
<li>1 cup brandy</li>
<li>1 cup club soda</li>
<li>3 apples, cored, quartered, and thickly sliced</li>
<li>1 lime, quartered</li>
<li>1 lemon, quartered</li>
<li>3 cinnamon sticks</li>
<li>1 orange, quartered and thickly sliced</li>
</ul>
EOT;
		$recipe_1_instructions = <<<EOT
<ol>
<li>Combine all ingredients in a pitcher. Chill for at least 4 hours, but preferably overnight, before serving.</li>
</ol>
EOT;
		$recipe_1_notes        = $recipe_3_notes = '';
		$recipe_3_ingredients  = <<<EOT
<h4>For the pancakes</h4>
<ul>
<li>2 cups whole wheat pastry flour</li>
<li>3 T. organic sugar, coconut sugar or other sugar of choice</li>
<li>2 T. baking powder</li>
<li>¾ tsp. salt</li>
<li>3 T. ground flax seed</li>
<li>2 cups non-dairy milk</li>
<li>¼ cup aquafaba (liquid from canned chickpeas or white beans), or water</li>
<li>1 t. vanilla extract</li>
<li>1 cup pomegranate arils (from 1 pomegranate)</li>
<li>½ cup chocolate chips</li>
</ul>
<h4>For the pomegranate syrup</h4>
<ul>
<li>1 cup pomegranate seeds (from 1 pomegranate)</li>
<li>¾ cup apple cider</li>
<li>¾ cup maple syrup</li>
<li><a href="http://eatwithinyourmeans.com/pomegranate-chocolate-chip-pancakes/" target="_blank">This is a link to test</a></li>
</ul>
EOT;
		$recipe_3_instructions = <<<EOT
<h4>Make the Pancakes</h4>
<ol>
<li>Preheat an electric skillet to <strong>375F</strong> or heat a cast iron or nonstick skillet on medium heat.</li>
<li>In a medium bowl, <em>stir together</em> the dry ingredients. Stir in the chocolate chips to coat with the flour. Set the pomegranate arils to the side.</li>
<li>In a 4-cup measuring cup or small bowl, measure and mix the liquid ingredients together.</li>
<li>Add the liquid ingredients to the dry and mix until just combined (small lumps are ok).</li>
<li>Using a large ice cream disher or ¼ cup measure, pour batter onto skillet and cook for 2 minutes. While the pancakes are cooking, sprinkle pomegranate arils on top of the batter.</li>
<li>After 2 minutes are up, or the bottom of the pancakes look golden brown, flip the pancakes carefully and cook for 2 minutes more.</li>
</ol>
<h4>Make the Pomegranate Syrup</h4>
<ol>
<li>Press out the juice from the pomegranate arils through a fine meshed sieve into a small saucepan. Add the maple syrup and apple cider to the saucepan, and bring to a boil.</li>
<li>Reduce the temperature to medium and let the mixture reduce by approximately half, or until the syrup coats the back of a spoon.</li>
<li>Serve the pancakes with the pomegranate syrup (if using), or with maple syrup.</li>
</ol>
EOT;
		return array(
			array(
				'[amd-zlrecipe-recipe:1]',
				array(
					'title'                       => "Autumn Apple Ro'se Sangria",
					'author_name'                 => '',
					'description'                 => 'This festive <em>Autumn Apple Rosé Sangria</em> is bubbly, just the right amount of sweet, and perfect for your Thanksgiving or other fall celebrations!',
					'ingredients'                 => $recipe_1_ingredients,
					'instructions'                => trim( $recipe_1_instructions ),
					'notes'                       => $recipe_1_notes,
					'average_rating'              => '3.5',
					'total_reviews'               => '2',
					'prep_time'                   => '10 minutes',
					'cook_time'                   => '',
					'total_time'                  => '10 minutes',
					'yield'                       => '8 servings',
					'category'                    => '',
					'method'                      => '',
					'cuisine'                     => '',
					'serving_size'                => '1 cup',
					'calories'                    => '300',
					'sugar'                       => '',
					'sodium'                      => '',
					'fat'                         => '0g',
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
			),
			array(
				'[amd-zlrecipe-recipe:3]',
				array(
					'title'                       => 'Pomegranate Chocolate Chip Pancakes',
					'author_name'                 => '',
					'description'                 => "Pomegranate and chocolate make a perfect pairing in these pomegranate chocolate chip pancakes! These pancakes are vegan but you'd never know it - give them a try!",
					'ingredients'                 => $recipe_3_ingredients,
					'instructions'                => trim( $recipe_3_instructions ),
					'notes'                       => $recipe_3_notes,
					'average_rating'              => '4',
					'total_reviews'               => '2',
					'prep_time'                   => '10 minutes',
					'cook_time'                   => '10 minutes',
					'total_time'                  => '20 minutes',
					'yield'                       => '8-12 pancakes',
					'category'                    => '',
					'method'                      => '',
					'cuisine'                     => '',
					'serving_size'                => '1 pancake',
					'calories'                    => '250',
					'sugar'                       => '',
					'sodium'                      => '',
					'fat'                         => '6g',
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
					'total_time_raw'              => '20 minutes',
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
				'[amd-zlrecipe-recipe:6]',
				array(
					'title'                       => 'Zip Recipes 1',
					'author_name'                 => '',
					'description'                 => 'This is some description text',
					'ingredients'                 => '',
					'instructions'                => '',
					'notes'                       => '',
					'average_rating'              => 3.8,
					'total_reviews'               => '4',
					'prep_time'                   => '4 hours 5 minutes',
					'cook_time'                   => '10 minutes',
					'total_time'                  => '5 hours 30 minutes',
					'yield'                       => '3',
					'category'                    => 'Entree',
					'method'                      => '',
					'cuisine'                     => 'French',
					'serving_size'                => '1/2 cup',
					'calories'                    => '300',
					'sugar'                       => '16',
					'sodium'                      => '230mg',
					'fat'                         => '20g',
					'saturated_fat'               => '0g',
					'unsaturated_fat'             => '',
					'trans_fat'                   => '1g',
					'carbohydrates'               => '30',
					'fiber'                       => '4',
					'protein'                     => '20',
					'cholesterol'                 => '50',
					'keywords'                    => '',
					'nutrifox_id'                 => '',
					'video_url'                   => '',
					'total_time_raw'              => '5 hours 30 minutes',
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
				'<!-- wp:zip-recipes/recipe-block {"id":"1"} /-->',
				array(
					'title'                       => "Autumn Apple Ro'se Sangria",
					'author_name'                 => '',
					'description'                 => 'This festive <em>Autumn Apple Rosé Sangria</em> is bubbly, just the right amount of sweet, and perfect for your Thanksgiving or other fall celebrations!',
					'ingredients'                 => $recipe_1_ingredients,
					'instructions'                => trim( $recipe_1_instructions ),
					'notes'                       => $recipe_1_notes,
					'average_rating'              => '3.5',
					'total_reviews'               => '2',
					'prep_time'                   => '10 minutes',
					'cook_time'                   => '',
					'total_time'                  => '10 minutes',
					'yield'                       => '8 servings',
					'category'                    => '',
					'method'                      => '',
					'cuisine'                     => '',
					'serving_size'                => '1 cup',
					'calories'                    => '300',
					'sugar'                       => '',
					'sodium'                      => '',
					'fat'                         => '0g',
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
			),
		);
	}
}
