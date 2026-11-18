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

	/**
	 * Creates a contributor's draft with the given content and makes them the current user.
	 *
	 * @param string $content Post content.
	 *
	 * @return int Post ID.
	 */
	private function prime_contributor_post( $content ) {
		global $wpdb;

		$contributor_id = $this->factory->user->create( array( 'role' => 'contributor' ) );
		$post_id        = $this->factory->post->create(
			array(
				'post_author' => $contributor_id,
				'post_status' => 'draft',
			)
		);
		$wpdb->update( $wpdb->posts, array( 'post_content' => $content ), array( 'ID' => $post_id ) );
		clean_post_cache( $post_id );
		wp_set_current_user( $contributor_id );
		return $post_id;
	}

	/**
	 * Converted fields are sanitized for users without unfiltered_html.
	 */
	public function test_contributor_conversion_is_sanitized() {
		$content = '[recipe title="T\x3cimg src=x onerror=alert(1)\x3e" description="D\x3cimg src=x onerror=alert(2)\x3e"][recipe-ingredients title="\x3cb onmouseover=alert(3)\x3eSec\x3c/b\x3e"]' . PHP_EOL . '- egg' . PHP_EOL . '[/recipe-ingredients][/recipe]';
		$post_id = $this->prime_contributor_post( $content );

		$recipe = Tasty_Recipes\Converters\WPCom::convert_post( $post_id );
		$this->assertInstanceOf( Recipe::class, $recipe );
		$this->assertSame( 'T', $recipe->get_title() );
		$this->assertStringNotContainsString( 'onerror', $recipe->get_description() );
		$this->assertStringNotContainsString( 'onmouseover', $recipe->get_ingredients() );
		$this->assertStringContainsString( '<b>Sec</b>', $recipe->get_ingredients() );
		$this->assertStringContainsString( '<li>egg</li>', $recipe->get_ingredients() );
	}

	/**
	 * Images aren't imported for users who can't upload files.
	 */
	public function test_contributor_conversion_does_not_import_images() {
		$content = '[recipe title="Pasta" image="https://en-support.files.wordpress.com/2014/01/food-dinner-pasta-broccoli.jpg"][recipe-ingredients]' . PHP_EOL . '- pasta' . PHP_EOL . '[/recipe-ingredients][/recipe]';
		$post_id = $this->prime_contributor_post( $content );

		$recipe = Tasty_Recipes\Converters\WPCom::convert_post( $post_id );
		$this->assertInstanceOf( Recipe::class, $recipe );
		$this->assertSame( 'Pasta', $recipe->get_title() );
		$this->assertEmpty( $recipe->get_image_id() );
	}
}
