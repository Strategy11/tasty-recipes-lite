<?php
/**
 * Test case for the WordPress.com recipe converter.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Objects\Recipe;

/**
 * Test case for the WordPress.com recipe converter.
 */
class Test_WPCom_Converter extends Tasty_Recipes_Converter_Testcase {

	/**
	 * Conversion class to use with tests.
	 *
	 * @var string
	 */
	protected static $conversion_class = 'Tasty_Recipes\Converters\WPCom';

	/**
	 * Recipe sample data to use for tests.
	 *
	 * @return array
	 */
	public function recipe_samples() {
		$recipe_1_ingredients  = <<<EOT
<ul>
<li>2 pounds vine ripened tomatoes</li>
<li>3 cloves garlic</li>
<li>1/2 cup chopped fresh basil</li>
</ul>
<h4>Second Ingredient Set</h4>
<ul>
<li>2 pounds vine ripened tomatoes</li>
<li>3 cloves garlic</li>
<li>1/2 cup chopped fresh basil</li>
</ul>
EOT;
		$recipe_1_instructions = <<<EOT
<ol>
<li>In a medium bowl, toss together tomatoes, garlic, basil, mint, salt and black pepper.</li>
<li>Cook and drain pasta.</li>
<li>Mix together.</li>
</ol>
<h4>Second Instruction Set</h4>
<ol>
<li>In a medium bowl, toss together tomatoes, garlic, basil, mint, salt and black pepper.</li>
<li>Cook and drain pasta.</li>
<li>Mix together.</li>
</ol>
EOT;
		return array(
			array(
				file_get_contents( __DIR__ . '/data/wpcom-recipe-1.txt' ),
				array(
					'title'                       => 'Summer Pasta with Basil, Tomatoes and Cheese',
					'author_name'                 => '',
					'description'                 => 'A fresh, light, Italian-inspired pasta recipe perfect for a late summer dinner.',
					'ingredients'                 => $recipe_1_ingredients,
					'instructions'                => $recipe_1_instructions,
					'notes'                       => 'Credit: allrecipes.com',
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '',
					'cook_time'                   => '',
					'total_time'                  => '2hr 30mins',
					'yield'                       => '4-6',
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
					'total_time_raw'              => '2hr 30mins',
					'diet'                        => '',
					'description_video_settings'  => '',
					'ingredients_video_settings'  => '',
					'instructions_video_settings' => '',
					'notes_video_settings'        => '',
					'additional_time_label'       => '',
					'additional_time_value'       => '',
					'image_sizes'                 => array(),
				),
				'food-dinner-pasta-broccoli',
			),
		);

	}

}
