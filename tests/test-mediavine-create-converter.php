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
		$before_count = $this->count_tasty_recipes();

		parent::test_recipe_conversion( $source, $expected, $expected_image, $expected_content );
		$post_content = get_post( $this->post_id )->post_content;
		$this->assertStringNotContainsString( 'wp:mv/recipe', $post_content );
		$this->assertStringNotContainsString( '[mv_create]', $post_content );

		$this->assertSame( $before_count + 1, $this->count_tasty_recipes() );

		$recipe_ids = get_posts(
			array(
				'post_type'      => 'tasty_recipe',
				'posts_per_page' => 1,
				'orderby'        => 'ID',
				'order'          => 'DESC',
				'fields'         => 'ids',
			)
		);
		$this->assertNotEmpty( $recipe_ids );
		$create_key = get_post_meta( $recipe_ids[0], \Tasty_Recipes\Converters\Mediavine_Create::CREATE_KEY_META, true );
		$this->assertNotEmpty( $create_key );
	}

	/**
	 * Missing Create DB row must not leave empty-title recipe posts behind.
	 */
	public function test_missing_create_row_creates_no_recipe() {
		$before_count = $this->count_tasty_recipes();
		$shortcode    = '[mv_create key="999999" type="recipe"]';
		$recipe       = \Tasty_Recipes\Converters\Mediavine_Create::create_recipe_from_existing( $shortcode, $this->post_id );

		$this->assertFalse( $recipe );
		$this->assertSame( $before_count, $this->count_tasty_recipes() );
	}

	/**
	 * Elementor-only Create shortcodes are discovered and rewritten.
	 */
	public function test_elementor_only_create_shortcode_conversion() {
		global $wpdb;

		$wpdb->update(
			$wpdb->posts,
			array( 'post_content' => 'Elementor-only host post without Create shortcode.' ),
			array( 'ID' => $this->post_id )
		);
		clean_post_cache( $this->post_id );

		$elementor_data = wp_json_encode(
			array(
				array(
					'elType'     => 'widget',
					'widgetType' => 'shortcode',
					'settings'   => array(
						'shortcode' => '[mv_create key="13" type="recipe"]',
					),
				),
			),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		);
		update_post_meta( $this->post_id, '_elementor_data', wp_slash( $elementor_data ) );
		update_post_meta( $this->post_id, '_elementor_css', 'stale-css' );

		$conversion_class = static::$conversion_class;
		$this->assertContains( $this->post_id, $conversion_class::get_post_ids() );
		$this->assertGreaterThan( 0, $conversion_class::get_count() );

		$recipe = $conversion_class::convert_post( $this->post_id );
		$this->assertInstanceOf( Recipe::class, $recipe );

		$updated_elementor = get_post_meta( $this->post_id, '_elementor_data', true );
		$decoded_elementor = json_decode( $updated_elementor, true );
		$this->assertIsArray( $decoded_elementor );
		$this->assertSame(
			'[tasty-recipe id="' . $recipe->get_id() . '"]',
			$decoded_elementor[0]['settings']['shortcode']
		);
		$this->assertStringNotContainsString( '[mv_create', $updated_elementor );
		$this->assertSame( '', (string) get_post_meta( $this->post_id, '_elementor_css', true ) );
		$this->assertSame(
			'13',
			(string) get_post_meta( $recipe->get_id(), \Tasty_Recipes\Converters\Mediavine_Create::CREATE_KEY_META, true )
		);
	}

	/**
	 * Second convert for the same Create key reuses the existing Tasty recipe.
	 */
	public function test_create_key_reuse_across_posts() {
		global $wpdb;

		$conversion_class = static::$conversion_class;
		$shortcode        = '[mv_create key="13" type="recipe"]';

		$wpdb->update( $wpdb->posts, array( 'post_content' => $shortcode ), array( 'ID' => $this->post_id ) );
		clean_post_cache( $this->post_id );
		$first_recipe = $conversion_class::convert_post( $this->post_id );
		$this->assertInstanceOf( Recipe::class, $first_recipe );

		$second_post_id = self::factory()->post->create(
			array(
				'post_content' => $shortcode,
			)
		);
		$second_recipe  = $conversion_class::convert_post( $second_post_id );
		$this->assertInstanceOf( Recipe::class, $second_recipe );
		$this->assertSame( $first_recipe->get_id(), $second_recipe->get_id() );
	}

	/**
	 * Non-recipe Create shortcodes must not be converted.
	 */
	public function test_non_recipe_create_shortcode_is_not_converted() {
		global $wpdb;

		$before_count = $this->count_tasty_recipes();
		$shortcode    = '[mv_create key="13" type="list"]';

		$wpdb->update( $wpdb->posts, array( 'post_content' => $shortcode ), array( 'ID' => $this->post_id ) );
		clean_post_cache( $this->post_id );

		$conversion_class = static::$conversion_class;
		$this->assertNotContains( $this->post_id, $conversion_class::get_post_ids() );

		$recipe = $conversion_class::convert_post( $this->post_id );
		$this->assertFalse( $recipe );
		$this->assertSame( $before_count, $this->count_tasty_recipes() );
		$this->assertStringContainsString( $shortcode, get_post_field( 'post_content', $this->post_id ) );
	}

	/**
	 * Partial Create ingredient links convert brackets into an anchor.
	 */
	public function test_format_create_ingredient_partial_link() {
		$output = $this->invoke_format_create_ingredient_text(
			'1 cup [flour] sifted',
			'https://example.com/flour',
			true
		);

		$this->assertSame(
			'1 cup <a href="https://example.com/flour" rel="nofollow" target="_blank">flour</a> sifted',
			$output
		);
	}

	/**
	 * Orphan Create link brackets are stripped when no URL remains.
	 */
	public function test_format_create_ingredient_orphan_brackets() {
		$output = $this->invoke_format_create_ingredient_text( '[flour]', '', false );

		$this->assertSame( 'flour', $output );
		$this->assertStringNotContainsString( '[', $output );
		$this->assertStringNotContainsString( ']', $output );
	}

	/**
	 * Whole-line Create ingredient links still wrap the full text.
	 */
	public function test_format_create_ingredient_whole_line_link() {
		$output = $this->invoke_format_create_ingredient_text(
			'kale pesto, magic green sauce, or any other yummy sauce',
			'https://pinchofyum.com/5-minute-vegan-kale-pesto',
			true
		);

		$this->assertSame(
			'<a href="https://pinchofyum.com/5-minute-vegan-kale-pesto" rel="nofollow" target="_blank">kale pesto, magic green sauce, or any other yummy sauce</a>',
			$output
		);
	}

	/**
	 * Multi-bracket lines use the first Create link marker only.
	 */
	public function test_format_create_ingredient_multi_bracket_uses_first_pair() {
		$output = $this->invoke_format_create_ingredient_text(
			'1 cup [all-purpose flour] (or [bread flour])',
			'https://example.com/flour',
			false
		);

		$this->assertSame(
			'1 cup <a href="https://example.com/flour" target="_blank">all-purpose flour</a> (or [bread flour])',
			$output
		);
	}

	/**
	 * Unused empty-title tasty_recipe posts are removed by cleanup.
	 */
	public function test_cleanup_empty_title_orphans() {
		$orphan_id = self::factory()->post->create(
			array(
				'post_type'   => 'tasty_recipe',
				'post_title'  => '',
				'post_status' => 'publish',
			)
		);
		$kept_id   = self::factory()->post->create(
			array(
				'post_type'   => 'tasty_recipe',
				'post_title'  => '',
				'post_status' => 'publish',
			)
		);

		$embed_post_id = self::factory()->post->create(
			array(
				'post_content' => '[tasty-recipe id="' . $kept_id . '"]',
			)
		);
		$this->assertNotEmpty( $embed_post_id );

		$deleted = \Tasty_Recipes\Converters\Mediavine_Create::cleanup_empty_title_orphans();
		$this->assertGreaterThanOrEqual( 1, $deleted );
		$this->assertNull( get_post( $orphan_id ) );
		$this->assertNotNull( get_post( $kept_id ) );
	}

	/**
	 * Count tasty_recipe posts currently in the database.
	 *
	 * @return int
	 */
	private function count_tasty_recipes() {
		return (int) count(
			get_posts(
				array(
					'post_type'      => 'tasty_recipe',
					'post_status'    => 'any',
					'posts_per_page' => -1,
					'fields'         => 'ids',
				)
			)
		);
	}

	/**
	 * Invoke the private Create ingredient formatter for unit assertions.
	 *
	 * @param string $original_text Ingredient text.
	 * @param string $link          Link URL.
	 * @param bool   $nofollow      Whether the link is nofollow.
	 *
	 * @return string
	 */
	private function invoke_format_create_ingredient_text( $original_text, $link = '', $nofollow = false ) {
		$method = new ReflectionMethod( \Tasty_Recipes\Converters\Mediavine_Create::class, 'format_create_ingredient_text' );
		$method->setAccessible( true );

		return $method->invoke( null, $original_text, $link, $nofollow );
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
