<?php
/**
 * Test case for the Yummy recipe converter.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Converters\Yummly;
use Tasty_Recipes\Objects\Recipe;

/**
 * Test case for the Yummly recipe converter.
 */
class Test_Yummly_Converter extends Tasty_Recipes_Converter_Testcase {

	/**
	 * Seed SQL file to use with tests.
	 *
	 * @var string
	 */
	protected $sql_source = 'yummly.sql';

	/**
	 * Conversion class to use with tests.
	 *
	 * @var string
	 */
	protected static $conversion_class = 'Tasty_Recipes\Converters\Yummly';

	/**
	 * Recipe sample data to use for tests.
	 *
	 * @return array
	 */
	public function recipe_samples() {
		$recipe_1_ingredients  = <<<EOT
<h4>For the Korean Beef:</h4>
<ul>
<li>1 14-ounce can pears, drained (fresh pears work, too)</li>
<li>1 two-inch knob of fresh ginger</li>
<li>4 cloves garlic</li>
<li>1/2 cup soy sauce</li>
<li>1/2 cup brown sugar</li>
<li>2 lbs. top sirloin (see notes)</li>
</ul>
<h4>For the Tacos:</h4>
<ul>
<li>chopped cilantro</li>
<li>chopped peanuts</li>
<li>kimchi !!!! or slaw of some sort</li>
<li>sriracha mayo or yum yum sauce</li>
<li>flour tortillas</li>
<li><em>italic text</em></li>
<li><strong>bold text</strong></li>
<li>And a <a href="https://google.com">link</a></li>
</ul>
EOT;
		$recipe_1_instructions = <<<EOT
<ol>
<li>Make the sauce: Pulse pears, ginger, garlic, soy sauce, and brown sugar until mostly smooth. Reserve half for later use (see notes).</li>
<li>Cook the meat: Trim any fat off the meat and cut into a few large chunks (it will shred, but cutting the meat helps it cook faster). Place in an Instant Pot and cover with half of the sauce. Cook on high pressure (manual) for 45 minutes. Release steam and shred the meat in the Instant Pot with two forks.</li>
<li>Tacos: Fill each tortilla with beef, cilantro, peanuts, kimchi, and spicy mayo. Oh.My.Gosh. It's so good. There is an image below this instruction</li>
<li><img src="http://tasty.handbuilt.co/wp-content/uploads/2017/11/Korean-BBQ-Beef-Tacos.jpg"></li>
</ol>
EOT;
		$recipe_1_notes        = <<<EOT
Slow Cooker: If you want to make this in the slow cooker, follow the same directions but cook the meat on high for 3-5 hours, or low for 6-8 hours.

Stovetop: If you want to make this on the stovetop, I recommend slicing the meat thinly and following these directions for super quick and yummy beef.

Cuts of Meat: Chuck roast can work, but I found that even with some trimming, it still had too much fat for my liking. Top sirloin is a little more spendy, but I love it in this recipe because there is less fat to work around and you really just get the awesomeness of the beef.

Marinating: If you have time to marinate the meat, DO IT. It really enhances the flavor to of the meat to meat to let it all kinda hang out together with the sauce for a while. If you don’t have time, no biggie. I usually don’t have time either, and I’d still give it 5/5 for flavor.

As written, you’ll have double the sauce you need! So save the remaining sauce (fridge or freezer) for a super fast and easy Round Two of your Korean Beef Tacos. LUCKYYYY.
EOT;
		return array(
			array(
				'[amd-yrecipe-recipe:1]',
				array(
					'title'                       => 'Instant Pot Korean Beef Tacos',
					'author_name'                 => '',
					'description'                 => 'Food truck fusion-style tacos, straight outta the Instant Pot! Flavorful garlic and ginger beef gets tucked into flour tortillas with kimchi, cilantro, peanuts, and spicy mayo. Oooo-ee!',
					'ingredients'                 => $recipe_1_ingredients,
					'instructions'                => trim( $recipe_1_instructions ),
					'notes'                       => $recipe_1_notes,
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '30 minutes',
					'cook_time'                   => '20 minutes',
					'total_time'                  => '2 hours',
					'yield'                       => '8 tacos',
					'category'                    => '',
					'method'                      => '',
					'cuisine'                     => '',
					'serving_size'                => '1 taco',
					'calories'                    => '227',
					'sugar'                       => '',
					'sodium'                      => '',
					'fat'                         => '4g',
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
					'total_time_raw'              => '2 hours',
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
		);
	}

}
