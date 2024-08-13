<?php
/**
 * Test case for the YumPrint recipe converter.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Objects\Recipe;

/**
 * Test case for the YumPrint recipe converter.
 */
class Test_YumPrint_Converter extends Tasty_Recipes_Converter_Testcase {

	/**
	 * Seed SQL file to use with tests.
	 *
	 * @var string
	 */
	protected $sql_source = 'yumprint.sql';

	/**
	 * Conversion class to use with tests.
	 *
	 * @var string
	 */
	protected static $conversion_class = 'Tasty_Recipes\Converters\YumPrint';

	/**
	 * Recipe sample data to use for tests.
	 *
	 * @return array
	 */
	public function recipe_samples() {
		$recipe_1_ingredients  = <<<EOT
<h4>For the enchilada sauce</h4>
<ul>
<li>2 Tbsp all-purpose flour</li>
<li>1 tsp unsweetened cocoa powder</li>
<li>2 Tbsp chili powder</li>
<li>1 tsp dried oregano</li>
<li>1 tsp ground cumin</li>
<li>2 cups vegetable broth</li>
<li>8 oz tomato sauce</li>
</ul>

<h4>For the enchiladas</h4>
<ul>
<li>1 Tbsp olive oil</li>
<li>1 medium yellow onion</li>
<li>3 medium gold potatoes</li>
<li>1 green bell pepper</li>
<li>2 pasilla (poblano) peppers</li>
<li>3 cloves garlic</li>
<li>1 tsp dried oregano</li>
<li>pinch salt</li>
<li>8 eight-inch flour tortillas</li>
<li>1 small can sliced olives</li>
<li>jack cheese (optional)</li>
</ul>
EOT;
		$recipe_1_instructions = <<<EOT
<ol>
<li>Preheat your oven to 400F. Grease a 9x13" baking pan.</li>
<li>Cut the potatoes into 1 cm cubes. Warm the olive oil in a large skillet over medium heat, add the potatoes, and cook about 10 minutes until almost soft, tossing only every now and then.</li>
<li>Cut the onion in half, then slice it. Seed the peppers, cut them in half, and slice into 1/4" strips. Mince the garlic, then add it all to the potatoes. Sautee until the veggies are almost soft, another 5 minutes or so.</li>
<li>While the veggies cook, prepare the sauce. Combine the dry ingredients in a dry saucepan. Mix together well. Add 1/4 cup of the vegetable and mix to make a paste, making sure there are no lumps remaining. Slowly add the remaining vegetable broth and bring to a boil. Add the tomato sauce and simmer 5 minutes.</li>
<li>When the veggies are done, make the enchiladas. Place about 1/3 cup of the filling in a tortilla, then roll up to make a tube. Place in your baking pan, then repeat until all the filling is used. Add cheese to the inside of the enchiladas, if desired.</li>
<li>Reserve 1/3 cup enchilada sauce, then pour the remainder evenly over the filled tortillas. Top with the drained olives and extra cheese, if desired.</li>
<li>Bake for 20 minutes, rotating halfway through. Let sit 5 minutes before serving, then dig in!</li>
</ol>
EOT;
		$recipe_1_notes        = <<<EOT
<ul>
<li>This is a note.</li>
<li>This is another note.</li>
</ul>
EOT;
		$recipe_2_ingredients  = <<<EOT
<ul>
<li>12 oz. package cheese tortellini (I used the rainbow colored) cooked according to directions. Do not overcook.</li>
<li>2 cups grape tomatoes, halved</li>
<li>1 cup whole Kalamata Olives, sliced</li>
<li>1 cup cubed feta cheese</li>
<li>1/4 cup red onion diced</li>
<li>1/2 red pepper (can use orange or yellow pepper) thinly sliced</li>
<li>1 cup sun dried tomatoes in olive oil and herbs, thinly sliced</li>
<li>1/4 cup Sicilian Lemon Balsamic vinegar( can substitute white balsamic vinegar)</li>
<li>2 Tablespoons Garlic Olive Oil</li>
<li>Zest from 1 medium lemon</li>
<li>1 1/2 Tablespoons extra virgin olive oil</li>
<li>12 basil leaves -- chiffonade the leaves (thinly slice)</li>
<li>Salt and freshly ground pepper q.b.{ quanto basta  (to taste)}</li>
</ul>
EOT;
		$recipe_2_instructions = <<<EOT
<ol>
<li>Cook the tortellini in salted boiling water according to package directions. Be sure not to overcook.</li>
<li>Place the tortellini in large bowl and and mix with the 1 1/2 Tablespoons extra-virgin olive oil. Cool.</li>
<li>Mix the halved grape tomatoes, sliced olives, diced onion, pepper slivers, sun-dried tomatoes, feta cheese, lemon zest with the pasta.</li>
<li>Whisk the balsamic vinegar and garlic olive oil together then pour over the salad. Mix well.</li>
<li>Salt and pepper to taste.</li>
<li>Add the basil chiffonade and gently mix together. Taste and make adjustments if needed.</li>
</ol>
EOT;
		return array(
			array(
				'[yumprint-recipe id="1"]',
				array(
					'title'                       => 'Potato & Pasilla Enchiladas',
					'author_name'                 => 'Raquel Smith',
					'description'                 => "The most delicious enchiladas I've ever made! This vegetarian recipe stuffs the enchiladas with potatoes and earthy pasilla peppers, and is topped with a delicious and super easy enchilada sauce.",
					'ingredients'                 => $recipe_1_ingredients,
					'instructions'                => trim( $recipe_1_instructions ),
					'notes'                       => $recipe_1_notes,
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '10',
					'cook_time'                   => '45',
					'total_time'                  => '55',
					'yield'                       => '8',
					'category'                    => '',
					'method'                      => '',
					'cuisine'                     => '',
					'serving_size'                => '4',
					'calories'                    => '917',
					'sugar'                       => '6',
					'sodium'                      => '1552',
					'fat'                         => '33',
					'saturated_fat'               => '14',
					'unsaturated_fat'             => '16',
					'trans_fat'                   => '0',
					'carbohydrates'               => '125',
					'fiber'                       => '',
					'protein'                     => '33',
					'cholesterol'                 => '50',
					'keywords'                    => '',
					'nutrifox_id'                 => '',
					'video_url'                   => '',
					'total_time_raw'              => '55',
					'diet'                        => '',
					'description_video_settings'  => '',
					'ingredients_video_settings'  => '',
					'instructions_video_settings' => '',
					'notes_video_settings'        => '',
					'additional_time_label'       => '',
					'additional_time_value'       => '',
					'image_sizes'                 => array(),
				),
				'Potato-Pasilla-Enchiladas-3',
			),
			array(
				'[yumprint-recipe id="185"]',
				array(
					'title'                       => 'Cheese Tortellini Salad With Sun-Dried Tomatoes',
					'author_name'                 => 'Marisa Franca @ All Our way',
					'description'                 => 'Rainbow colored tortellini mixed with Mediterranean style vegetables to give this pasta salad a unique look and taste. ',
					'ingredients'                 => $recipe_2_ingredients,
					'instructions'                => trim( $recipe_2_instructions ),
					'notes'                       => '',
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '30',
					'cook_time'                   => '12',
					'total_time'                  => '42',
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
					'total_time_raw'              => '42',
					'diet'                        => '',
					'description_video_settings'  => '',
					'ingredients_video_settings'  => '',
					'instructions_video_settings' => '',
					'notes_video_settings'        => '',
					'additional_time_label'       => '',
					'additional_time_value'       => '',
					'image_sizes'                 => array(),
				),
				'Cheese-Tortellini-Salad-With-Sun-Dried-Tomatoes',
			),
		);
	}

}
