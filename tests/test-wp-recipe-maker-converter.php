<?php
/**
 * Test case for the WP Recipe Maker recipe converter.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Objects\Recipe;

/**
 * Test case for the WP Recipe Maker recipe converter.
 */
class Test_WP_Recipe_Maker_Converter extends Tasty_Recipes_Converter_Testcase {

	/**
	 * Seed SQL file to use with tests.
	 *
	 * @var string
	 */
	protected $sql_source = 'wprm.sql';

	/**
	 * Conversion class to use with tests.
	 *
	 * @var string
	 */
	protected static $conversion_class = 'Tasty_Recipes\Converters\WP_Recipe_Maker';

	/**
	 * Temporarily filter the image attributes applied to the image tag by
	 * WordPress core.
	 *
	 * In 6.1.0, the `decode="async"` attribute is added. We unset this so that
	 * we can test against 6.0 and 6.1.
	 *
	 * @param array $attr Image attributes.
	 */
	public function filter_wp_get_attachment_image_attributes( $attr ) {
		unset( $attr['decoding'] );

		return $attr;
	}

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
		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'filter_wp_get_attachment_image_attributes' ) );
		parent::test_recipe_conversion( $source, $expected, $expected_image, $expected_content );
		remove_filter( 'wp_get_attachment_image_attributes', array( $this, 'filter_wp_get_attachment_image_attributes' ) );

		$post_content = get_post( $this->post_id )->post_content;
		$this->assertStringNotContainsString( '<!--WPRM Recipe', $post_content );
		$this->assertStringNotContainsString( '<!-- wp:wp-recipe-maker/recipe', $post_content );
	}

	/**
	 * Tests conversion of an empty block.
	 */
	public function test_recipe_conversion_empty_block() {
		global $wpdb;
		$empty_block = '<!-- wp:wp-recipe-maker/recipe /-->';
		$wpdb->update( $wpdb->posts, array( 'post_content' => $empty_block ), array( 'ID' => $this->post_id ) );
		clean_post_cache( $this->post_id );
		$conversion_class = static::$conversion_class;
		// Empty block post shouldn't be a part of the set.
		$this->assertEquals( array(), array_intersect( array( $this->post_id ), $conversion_class::get_post_ids() ) );
	}

	/**
	 * Recipe sample data to use for tests.
	 *
	 * @return array
	 */
	public function recipe_samples() {
		$recipe_1             = <<<EOT
Daniel foo bar

<!--WPRM Recipe 33720-->
<div class="wprm-fallback-recipe">
	<h2 class="wprm-fallback-recipe-name">Cashew Coffee</h2>
	<img class="wprm-fallback-recipe-image" src="http://tastyrecipes.dev/wp-content/uploads/2017/08/Cashew-Coffee-1-3-150x150.jpg"/>	<p class="wprm-fallback-recipe-summary">
		<p><b>Bold text</b>, <i>italic text</i>, <u>underlined text</u>, <sub>subscript</sub>, <sup>superscript</sup>, and a <a href="google.com" target="_blank" rel="nofollow">nofollow link</a>.</p>	</p>
	<div class="wprm-fallback-recipe-ingredients">
		<ul><li>1 1/2 cups coffee</li><li>1/4 cup cashews (roasted)</li><li>1 tbsp honey</li><li>1/2 tsp coarse sea salt</li></ul><h4>An ingredient heading</h4><ul><li>1 cup for serving</li></ul>	</div>
	<div class="wprm-fallback-recipe-instructions">
		<ol><li><p>Blend everything together! Minus the cup for serving.</p><br><img src="http://tastyrecipes.dev/wp-content/uploads/2017/08/Cashew-Coffee-1-3-150x150.jpg"/><br /></li></ol><h4>An instruction heading</h4><ol><li><p><b>Bold text</b>, <i>italic text</i>, <u>underlined text</u>, <sub>subscript</sub>, <sup>superscript</sup>, and a <a href="google.com" target="_blank" rel="nofollow">nofollow link</a>.</p></li></ol>	</div>
	<div class="wprm-fallback-recipe-notes">
		<p><strong>Paragraph text: Notes about cold vs. hot cashew coffee:</strong> Cashews straight out the package should blend just fine with hot coffee (although if you’re particular, just give them an overnight soak). If you want to a COLD cashew coffee, you have two options.</p><ul><li>Unordered list item 1</li><li>Unordered list item 2</li></ul><ol><li>Ordered list item 1</li><li>Ordered list item 2</li></ol><p><strong>Bold text</strong>, <em>italic text</em>, and a <a href="http://google.com">link</a>.</p><blockquote><p>A quote, because it's an option in the formatter... not that anyone would ever use it.</p></blockquote><p> </p>	</div>
</div>
<!--End WPRM Recipe-->
EOT;
		$recipe_1_ingredients = <<<EOT
<ul>
<li>1 1/2 cups coffee</li>
<li>1/4 cup cashews (roasted)</li>
<li>1 tbsp honey</li>
<li>1/2 tsp coarse sea salt</li>
</ul>
<h4>An ingredient heading</h4>
<ul>
<li>1 cup for serving</li>
</ul>
EOT;
		$wp_major             = substr( $GLOBALS['wp_version'], 0, 3 );
		$img_string           = '<img width="200" height="300" src="http://pinchofyum.com/wp-content/uploads/2017/08/Cashew-Coffee-1-3-200x300.jpg" class="attachment-medium size-medium" alt="" />';
		if ( '4.6' === $wp_major ) {
			$img_string = '<img width="200" height="300" src="http://pinchofyum.com/wp-content/uploads/2017/08/Cashew-Coffee-1-3-200x300.jpg" class="attachment-medium size-medium" alt="Cashew-Coffee-1-3" />';
		}
		if ( version_compare( $wp_major, '5.4', '>' ) ) {
			$img_string = '<img width="200" height="300" src="http://pinchofyum.com/wp-content/uploads/2017/08/Cashew-Coffee-1-3-200x300.jpg" class="attachment-medium size-medium" alt="" loading="lazy" srcset="http://pinchofyum.com/wp-content/uploads/2017/08/Cashew-Coffee-1-3-200x300.jpg 200w, http://pinchofyum.com/wp-content/uploads/2017/08/Cashew-Coffee-1-3-768x1152.jpg 768w, http://pinchofyum.com/wp-content/uploads/2017/08/Cashew-Coffee-1-3-683x1024.jpg 683w, http://pinchofyum.com/wp-content/uploads/2017/08/Cashew-Coffee-1-3.jpg 1200w" sizes="(max-width: 200px) 100vw, 200px" />';
		}
		$recipe_1_instructions = <<<EOT
<ol>
<li>Blend everything together! Minus the cup for serving.<br> {$img_string}</li>
</ol>
<h4>An instruction heading</h4>
<ol>
<li><b>Bold text</b>, <i>italic text</i>, <u>underlined text</u>, <sub>subscript</sub>, <sup>superscript</sup>, and a <a href="google.com" target="_blank" rel="nofollow">nofollow link</a>.</li>
</ol>
EOT;
		$recipe_1_notes        = <<<EOT
<p><strong>Paragraph text: Notes about cold vs. hot cashew coffee:</strong> Cashews straight out the package should blend just fine with hot coffee (although if you’re particular, just give them an overnight soak). If you want to a COLD cashew coffee, you have two options.</p><ul><li>Unordered list item 1</li><li>Unordered list item 2</li></ul><ol><li>Ordered list item 1</li><li>Ordered list item 2</li></ol><p><strong>Bold text</strong>, <em>italic text</em>, and a <a href="http://google.com">link</a>.</p><blockquote><p>A quote, because it's an option in the formatter... not that anyone would ever use it.</p></blockquote><p> </p>
EOT;
		$recipe_2              = <<<EOT
Another author test. This tests typing in a custom author.

<!--WPRM Recipe 33726-->
<div class="wprm-fallback-recipe">
	<h2 class="wprm-fallback-recipe-name">Custom author</h2>
		<p class="wprm-fallback-recipe-summary">
		<p>This tests typing in a custom author name. The default is "don't show." The other option is "post author."</p>	</p>
	<div class="wprm-fallback-recipe-ingredients">
		<ul><li>1 apple</li></ul>	</div>
	<div class="wprm-fallback-recipe-instructions">
			</div>
	<div class="wprm-fallback-recipe-notes">
			</div>
</div>
<!--End WPRM Recipe-->
EOT;
		$recipe_2_ingredients  = <<<EOT
<ul>
<li>1 apple</li>
</ul>
EOT;
		$recipe_3              = <<<EOT
<!--WPRM Recipe 4051-->
<div class="wprm-fallback-recipe">
	<h2 class="wprm-fallback-recipe-name">Vegan Broccoli Casserole</h2>
		<p class="wprm-fallback-recipe-summary">
		Skip the canned cream of mushroom soup and make this classic broccoli casserole plant-based! Creamy, hearty, comforting, and so flavorful!	</p>
	<div class="wprm-fallback-recipe-ingredients">
		<ul><li>2 32 oz. bags frozen broccoli cuts</li><li>2 cups raw cashews or pepitas (soaked for 2-8 hours if possible (*see note for quick-soak method))</li><li>3 cups water or plain unsweetened plant milk</li><li>1 tablespoon miso paste (optional)</li><li>1 teaspoon salt</li><li>2 tablespoons nutritional yeast</li><li>2 tablespoons apple cider vinegar (lemon juice, or unsweetened rice vinegar)</li><li>1 yellow onion (diced)</li><li>8 ounces crimini or button mushrooms (diced)</li><li>salt and pepper to taste</li><li>1-2 sleeves Ritz crackers (crushed into crumbs, or substitute 2 GF plain bread crumbs for gluten-free option)</li></ul>	</div>
	<div class="wprm-fallback-recipe-instructions">
		<h4>Make the Savory Cashew Cream</h4><ol><li>After soaking the cashews or pepitas, drain, rinse, and add to a blender or food processor.</li><li>Add water/milk, miso, salt, nutritional yeast, and vinegar, and blend until smooth and creamy. If using a high-speed blender, no soaking is necessary.</li></ol><h4>Make the casserole</h4><ol><li>Preheat the oven to 425 degrees F.</li><li>Add the frozen broccoli to a 13x9 casserole dish and place it in the oven while it is preheating. Bake for 15-20 minutes until the broccoli is heated through and tender, stirring occasionally to ensure even heating.</li><li>After the broccoli is cooked, set aside and reduce oven temperature to 350F.</li><li>Preheat a large skillet or saucepan over medium-high heat.</li><li>Add mushrooms and onions to the pan and cook for 5-6 minutes, until mushrooms have released their liquid and onions are browned and soft.</li><li>Add cashew cream to the onion/mushroom mixture and stir to combine.</li><li>Reduce heat to medium, and cook for 4-5 minutes until mixture has thickened slightly. Remove from heat.</li><li>Pour the sauce over the broccoli in the casserole dish and stir to combine. Season to taste with salt and pepper.</li><li>Spread cracker crumbs over the top of the broccoli mixture in an even layer.</li><li>10. Bake casserole, uncovered, for 30 minutes on the middle rack.</li></ol>	</div>
	<div class="wprm-fallback-recipe-notes">
		<p>Quick-soak method for raw cashews or pepitas: In a medium saucepan, add nuts or seeds along with enough water to cover. Bring to boil, then maintain a low boil for 15 minutes. Drain and rinse, and proceed with the recipe.</p>	</div>
</div>
<!--End WPRM Recipe-->
EOT;
		$recipe_3_ingredients  = <<<EOT
<ul>
<li>2 32 oz. bags frozen broccoli cuts</li>
<li>2 cups raw cashews or pepitas (soaked for 2-8 hours if possible (*see note for quick-soak method))</li>
<li>3 cups water or plain unsweetened plant milk</li>
<li>1 tablespoon miso paste (optional)</li>
<li>1 teaspoon salt</li>
<li>2 tablespoons nutritional yeast</li>
<li>2 tablespoons apple cider vinegar (lemon juice, or unsweetened rice vinegar)</li>
<li>1 yellow onion (diced)</li>
<li>8 ounces crimini or button mushrooms (diced)</li>
<li>salt and pepper to taste</li>
<li>1-2 sleeves Ritz crackers (crushed into crumbs, or substitute 2 GF plain bread crumbs for gluten-free option)</li>
</ul>
EOT;
		$recipe_3_instructions = <<<EOT
<h4>Make the Savory Cashew Cream</h4>
<ol>
<li>After soaking the cashews or pepitas*, drain, rinse, and add to a blender or food processor.</li>
<li>Add water/milk, miso, salt, nutritional yeast, and vinegar, and blend until smooth and creamy. If using a high-speed blender, no soaking is necessary.</li>
</ol>
<h4>Make the casserole</h4>
<ol>
<li>Preheat the oven to 425 degrees F.</li>
<li>Add the frozen broccoli to a 13x9 casserole dish and place it in the oven while it is preheating. Bake for 15-20 minutes until the broccoli is heated through and tender, stirring occasionally to ensure even heating.</li>
<li>After the broccoli is cooked, set aside and reduce oven temperature to 350F.</li>
<li>Preheat a large skillet or saucepan over medium-high heat.</li>
<li>Add mushrooms and onions to the pan and cook for 5-6 minutes, until mushrooms have released their liquid and onions are browned and soft.</li>
<li>Add cashew cream to the onion/mushroom mixture and stir to combine.</li>
<li>Reduce heat to medium, and cook for 4-5 minutes until mixture has thickened slightly. Remove from heat.</li>
<li>Pour the sauce over the broccoli in the casserole dish and stir to combine. Season to taste with salt and pepper.</li>
<li>Spread cracker crumbs over the top of the broccoli mixture in an even layer.</li>
<li>Bake casserole, uncovered, for 30 minutes on the middle rack.</li>
</ol>
EOT;
		$recipe_4              = <<<EOT
WPRM recipe 33983 converted to Tasty Recipes

<!--WPRM Recipe 33983-->
<div class="wprm-fallback-recipe">
<h2 class="wprm-fallback-recipe-name">A Recipe</h2>
1234
<div class="wprm-fallback-recipe-ingredients">
<ul>
 	<li>1 cup rice</li>
</ul>
</div>
<div class="wprm-fallback-recipe-instructions">
<ol>
 	<li>Cook</li>
</ol>
</div>
<div class="wprm-fallback-recipe-notes"></div>
</div>
<!--End WPRM Recipe-->
EOT;
		$recipe_4_ingredients  = <<<EOT
<ul>
<li>1 cup rice</li>
</ul>
EOT;
		$recipe_4_instructions = <<<EOT
<ol>
<li>Cook</li>
</ol>
EOT;
		$block_with_shortcode  = <<<EOT
<!-- wp:wp-recipe-maker/recipe {"id":33720,"updated":1547760309416} -->
<!--WPRM Recipe 33720-->
<div class="wprm-fallback-recipe">
	<h2 class="wprm-fallback-recipe-name">Test Recipe</h2>
		<p class="wprm-fallback-recipe-summary">
			</p>
	<div class="wprm-fallback-recipe-ingredients">
		<ul><li>1 apple</li><li>1 orange</li></ul>	</div>
	<div class="wprm-fallback-recipe-instructions">
		<ol><li><p>Chop</p></li></ol>	</div>
	<div class="wprm-fallback-recipe-notes">
			</div>
</div>
<!--End WPRM Recipe-->
<!-- /wp:wp-recipe-maker/recipe -->
EOT;
		return array(
			array(
				$recipe_1,
				array(
					'title'                       => 'Cashew Coffee',
					'author_name'                 => 'bjork',
					'description'                 => '<p><b>Bold text</b>, <i>italic text</i>, <u>underlined text</u>, <sub>subscript</sub>, <sup>superscript</sup>, and a <a href="google.com" target="_blank" rel="nofollow">nofollow link</a>.</p>',
					'ingredients'                 => $recipe_1_ingredients,
					'instructions'                => $recipe_1_instructions,
					'notes'                       => $recipe_1_notes,
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '10 minutes',
					'cook_time'                   => '5 minutes',
					'total_time'                  => '15 minutes',
					'yield'                       => '1 cup',
					'category'                    => 'Breakfast, Drinks',
					'method'                      => '',
					'cuisine'                     => 'American, Italian',
					'serving_size'                => '',
					'calories'                    => '348',
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
					'total_time_raw'              => '15 minutes',
					'diet'                        => '',
					'description_video_settings'  => '',
					'ingredients_video_settings'  => '',
					'instructions_video_settings' => '',
					'notes_video_settings'        => '',
					'additional_time_label'       => '',
					'additional_time_value'       => '',
					'image_sizes'                 => array(),
				),
				'Cashew-Coffee-1-3',
			),
			array(
				$recipe_2,
				array(
					'title'                       => 'Custom author',
					'author_name'                 => 'Raquel Smith',
					'description'                 => '<p>This tests typing in a custom author name. The default is "don\'t show." The other option is "post author."</p>',
					'ingredients'                 => $recipe_2_ingredients,
					'instructions'                => '',
					'notes'                       => '',
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '2 hours 5 minutes',
					'cook_time'                   => '',
					'total_time'                  => '2 hours 5 minutes',
					'yield'                       => '1',
					'category'                    => '',
					'method'                      => '',
					'cuisine'                     => '',
					'serving_size'                => '',
					'calories'                    => '100',
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
				$recipe_3,
				array(
					'title'                       => 'Vegan Broccoli Casserole',
					'author_name'                 => 'The Administrator',
					'description'                 => 'Skip the canned cream of mushroom soup and make this classic broccoli casserole plant-based! Creamy, hearty, comforting, and so flavorful!',
					'ingredients'                 => $recipe_3_ingredients,
					'instructions'                => $recipe_3_instructions,
					'notes'                       => '<p>*Quick-soak method for raw cashews or pepitas: In a medium saucepan, add nuts or seeds along with enough water to cover. Bring to boil, then maintain a low boil for 15 minutes. Drain and rinse, and proceed with the recipe.</p>',
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '30 minutes',
					'cook_time'                   => '30 minutes',
					'total_time'                  => '55 minutes',
					'yield'                       => '8 servings',
					'category'                    => '',
					'method'                      => '',
					'cuisine'                     => '',
					'serving_size'                => '1 cup',
					'calories'                    => '347',
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
					'video_url'                   => 'https://youtu.be/mbxuY7zGoQo',
					'total_time_raw'              => '55 minutes',
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
				$recipe_4,
				array(
					'title'                       => 'A Recipe',
					'author_name'                 => '',
					'description'                 => '<p>1234</p>',
					'ingredients'                 => $recipe_4_ingredients,
					'instructions'                => $recipe_4_instructions,
					'notes'                       => '',
					'average_rating'              => '5',
					'total_reviews'               => '2',
					'prep_time'                   => '',
					'cook_time'                   => '',
					'total_time'                  => '',
					'yield'                       => '',
					'category'                    => '',
					'method'                      => '',
					'cuisine'                     => '',
					'serving_size'                => '',
					'calories'                    => '366',
					'sugar'                       => '3',
					'sodium'                      => '201',
					'fat'                         => '27',
					'saturated_fat'               => '9',
					'unsaturated_fat'             => '',
					'trans_fat'                   => '',
					'carbohydrates'               => '5',
					'fiber'                       => '1',
					'protein'                     => '23',
					'cholesterol'                 => '124',
					'keywords'                    => '',
					'nutrifox_id'                 => '',
					'video_url'                   => 'https://www.youtube.com/watch?v=Bx4Sk8VFTwI',
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
				'<!-- wp:wp-recipe-maker/recipe {"id":33720,"updated":1547760309416} /-->',
				array(
					'title'                       => 'Cashew Coffee',
					'author_name'                 => 'bjork',
					'description'                 => '<p><b>Bold text</b>, <i>italic text</i>, <u>underlined text</u>, <sub>subscript</sub>, <sup>superscript</sup>, and a <a href="google.com" target="_blank" rel="nofollow">nofollow link</a>.</p>',
					'ingredients'                 => $recipe_1_ingredients,
					'instructions'                => $recipe_1_instructions,
					'notes'                       => $recipe_1_notes,
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '10 minutes',
					'cook_time'                   => '5 minutes',
					'total_time'                  => '15 minutes',
					'yield'                       => '1 cup',
					'category'                    => 'Breakfast, Drinks',
					'method'                      => '',
					'cuisine'                     => 'American, Italian',
					'serving_size'                => '',
					'calories'                    => '348',
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
					'total_time_raw'              => '15 minutes',
					'diet'                        => '',
					'description_video_settings'  => '',
					'ingredients_video_settings'  => '',
					'instructions_video_settings' => '',
					'notes_video_settings'        => '',
					'additional_time_label'       => '',
					'additional_time_value'       => '',
					'image_sizes'                 => array(),
				),
				'Cashew-Coffee-1-3',
			),
			array(
				$block_with_shortcode,
				array(
					'title'                       => 'Cashew Coffee',
					'author_name'                 => 'bjork',
					'description'                 => '<p><b>Bold text</b>, <i>italic text</i>, <u>underlined text</u>, <sub>subscript</sub>, <sup>superscript</sup>, and a <a href="google.com" target="_blank" rel="nofollow">nofollow link</a>.</p>',
					'ingredients'                 => $recipe_1_ingredients,
					'instructions'                => $recipe_1_instructions,
					'notes'                       => $recipe_1_notes,
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '10 minutes',
					'cook_time'                   => '5 minutes',
					'total_time'                  => '15 minutes',
					'yield'                       => '1 cup',
					'category'                    => 'Breakfast, Drinks',
					'method'                      => '',
					'cuisine'                     => 'American, Italian',
					'serving_size'                => '',
					'calories'                    => '348',
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
					'total_time_raw'              => '15 minutes',
					'diet'                        => '',
					'description_video_settings'  => '',
					'ingredients_video_settings'  => '',
					'instructions_video_settings' => '',
					'notes_video_settings'        => '',
					'additional_time_label'       => '',
					'additional_time_value'       => '',
					'image_sizes'                 => array(),
				),
				'Cashew-Coffee-1-3',
			),
		);
	}
}
