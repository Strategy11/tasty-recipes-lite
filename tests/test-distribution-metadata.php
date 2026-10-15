<?php
/**
 * Test case for the Distribution_Metadata controller class.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Distribution_Metadata;
use Tasty_Recipes\Objects\Recipe;
use Tasty_Recipes\Shortcodes;

/**
 * Test case for the Distribution_Metadata controller class.
 */
class Test_Distribution_Metadata extends Tasty_Recipes_Testcase {

	/**
	 * Ensures expected format for JSON+LD creation.
	 */
	public function test_google_schema_generation() {
		$this->prime_basic_test_post();
		update_option( Tasty_Recipes::DEFAULT_AUTHOR_LINK_OPTION, 'https://pinchofyum.com' );
		$recipe    = $this->create_mock_recipe_with_image();
		$schema    = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$schema    = Distribution_Metadata::enrich_google_schema_with_post_permalink( $schema, get_queried_object() );
		$size_1x1  = $recipe->get_image_size( array( 225, 225 ) );
		$size_4x3  = $recipe->get_image_size( array( 260, 195 ) );
		$size_16x9 = $recipe->get_image_size( array( 320, 180 ) );
		$size_full = $recipe->get_image_size( 'full' );
		$permalink = get_permalink( get_queried_object_id() );
		$expected = array(
			'@context'           => 'https://schema.org/',
			'@type'              => 'Recipe',
			'name'               => 'West Indian Beans & Coconut Rice',
			'author'             => array(
				'@type' => 'Person',
				'name'  => 'Moosewood Restaurant Simple Suppers',
				'url'   => 'https://pinchofyum.com',
			),
			'keywords'           => 'apples, pie',
			'image'              => array(
				$size_1x1['url'],
				$size_4x3['url'],
				$size_16x9['url'],
				$size_full['url'],
			),
			'description'        => "It's so tasty",
			'recipeIngredient'   => array(
				'1 1/2cups diced onions',
				'1 tablespoon vegetable oil',
				'1/2 teaspoon red pepper flakes or 1 fresh chile, minced',
				'1/2 teaspoon dried thyme',
				'Salt',
				'1/4 teaspoon allspice',
				'One 28-ounce can of diced tomatoes',
				'One 28-ounce can of red kidney beans, drained',
				'Chopped scallions and/or cilantro',
				'1 1/2 cups white rice',
				'1/2 teaspoon turmeric',
				'1/2 teaspoon salt',
				'1 teaspoon vegetable oil',
				'1 1/2 cups water',
				'3/4 cup coconut milk',
			),
			'recipeInstructions' => array(
				array(
					'@type'           => 'HowToSection',
					'name'            => 'How to make the rice',
					'itemListElement' => array(
						array(
							'@type' => 'HowToStep',
							'text'  => 'Rinse and drain the rice. In a saucepan on medium-high heat, sauté the rice, turmeric, and salt in the oil for a minute or two, stirring constantly. Add the water and coconut milk, bring to a boil, cover, and reduce the heat to low. Simmer until the rice is tender and the liquid absorbed, 15 to 20 minutes.',
							'url'   => $permalink . '#instruction-step-1',
						),
					),
				),
				array(
					'@type'           => 'HowToSection',
					'name'            => 'How to make the beans',
					'itemListElement' => array(
						array(
							'@type' => 'HowToStep',
							'text'  => 'While the rice is cooking, in a saucepan on medium heat, sauté the onions in the oil for 3 or 4 minutes. Add the red pepper flakes, sprinkle lightly with salt, cover, and cook, stirring occasionally, until the onions are soft, 5 to 8 minutes. Add the thyme, allspice, tomatoes, and beans, cover, and simmer for 10 to 15 minutes, stirring occasionally.',
							'url'   => $permalink . '#instruction-step-2',
						),
					),
				),
			),
			'recipeYield'        => array(
				2,
				'2 servings',
			),
			'recipeCategory'     => 'Main',
			'recipeCuisine'      => 'Caribbean',
			'suitableForDiet'    => 'https://schema.org/LowFatDiet',
			'cookingMethod'      => 'Cooking',
			'url'                => get_permalink( $this->post_id ),
			'prepTime'           => 'PT15M',
			'cookTime'           => 'PT1H20M',
			'totalTime'          => 'PT1H35M',
			'nutrition'          => array(
				'@type'                 => 'nutritionInformation',
				'calories'              => '581 calories',
				'servingSize'           => '2 cups',
				'sugarContent'          => '0 g',
				'sodiumContent'         => '1863 mg',
				'fatContent'            => '10 g',
				'saturatedFatContent'   => '2 g',
				'unsaturatedFatContent' => '0 g',
				'transFatContent'       => '1 g',
				'carbohydrateContent'   => '102 g',
				'fiberContent'          => '12 g',
				'proteinContent'        => '18 g',
				'cholesterolContent'    => '120 mg',
			),
		);
		if ( ! isset( $schema['nutrition'] ) ) {
			// Remove nutrition if not set because it isn't available in lite.
			unset( $expected['nutrition'] );
		}
		$this->assertEquals( $expected, $schema );
	}

	/**
	 * Ensures our JSON+LD is added when Yoast is running Schema.
	 */
	public function test_google_schema_added_yoast_seo() {
		// Yoast SEO requires at least WP 4.9.
		$this->skip_if_less_than_wp_version( '4.9' );
		$this->prime_basic_test_post();
		$this->create_mock_recipe_with_ratings();
		// Should contain both the Recipe and the Article.
		// Note: it's not possible to call this more than once.
		// See https://github.com/Yoast/wordpress-seo/issues/15054#issuecomment-623438372.
		$output = wp_json_encode( YoastSEO()->meta->for_current_page()->schema );
		$this->assertStringContainsString( '"@type":"WebSite"', $output );
		$this->assertStringContainsString( '"@type":"Recipe"', $output );
		$this->assertStringContainsString( '"@type":"Article"', $output );
	}

	/**
	 * Ensures <meta name="robots"> is noindex,nofollow for Yoast on the print view.
	 */
	public function test_meta_robots_yoast_seo() {
		$this->prime_basic_test_post();
		$recipe = $this->create_mock_recipe_with_ratings();
		// Defaults to not modifying.
		$this->assertNull( apply_filters( 'wpseo_robots', null ) );
		// noindex,nofollow on the print view.
		$this->go_to( tasty_recipes_get_print_url( $this->post_id, $recipe->get_id() ) );
		$this->assertEquals( 'noindex,follow', apply_filters( 'wpseo_robots', null ) );
	}

	/**
	 * Ensures expected format for JSON+LD without an author.
	 */
	public function test_google_schema_recipe_without_author() {
		$recipe = $this->create_mock_recipe();
		$recipe->set_author_name( '' );
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertFalse( isset( $schema['author'] ) );
	}

	/**
	 * Ensures expected format for JSON+LD with a featured image.
	 */
	public function test_google_schema_recipe_featured_image() {
		$this->prime_basic_test_post();
		// No featured image set for post.
		$recipe = $this->create_mock_recipe();
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertFalse( isset( $schema['image'] ) );
		// Set featured image for post.
		$this->add_featured_image_to_post();
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertTrue( isset( $schema['image'] ) );
		$this->assertEquals( 640, $schema['image']['width'] );
		$this->assertEquals( 480, $schema['image']['height'] );
	}

	/**
	 * Ensures expected format for JSON+LD on a post with an inline image.
	 */
	public function test_google_schema_recipe_inline_image() {
		$this->prime_basic_test_post();
		// No inline image for post.
		$recipe = $this->create_mock_recipe();
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertFalse( isset( $schema['image'] ) );
		// Add inline image to the post.
		$this->add_inline_image_to_post();
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertTrue( isset( $schema['image'] ) );
		$this->assertEquals( 640, $schema['image']['width'] );
		$this->assertEquals( 480, $schema['image']['height'] );
	}

	/**
	 * Ensures expected format for JSON+LD with ingredient list items.
	 */
	public function test_google_schema_ingredients_list_items() {
		$ingredients = <<<EOT
For The Red Beans:

<ul>
	<li>1 1/2cups diced onions</li>
	<li>1 tablespoon vegetable oil</li>
	<li>1/2 teaspoon red pepper flakes or 1 fresh chile, minced</li>
	<li>1/2 teaspoon dried thyme</li>
	<li>Salt</li>
	<li>1/4 teaspoon allspice</li>
	<li>One 28-ounce can of diced tomatoes</li>
	<li>One 28-ounce can of red kidney beans, drained</li>
	<li>Chopped scallions and/or cilantro</li>
</ul>

For The Coconut Rice:

<ul>
	<li>1 1/2 cups white rice</li>
	<li>1/2 teaspoon turmeric</li>
	<li>1/2 teaspoon salt</li>
	<li>1 teaspoon vegetable oil</li>
	<li>1 1/2 cups water</li>
	<li>3/4 cup coconut milk</li>
</ul>
EOT;
		$recipe      = $this->create_mock_recipe();
		$recipe->set_ingredients( $ingredients );
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertEquals(
			array(
				'1 1/2cups diced onions',
				'1 tablespoon vegetable oil',
				'1/2 teaspoon red pepper flakes or 1 fresh chile, minced',
				'1/2 teaspoon dried thyme',
				'Salt',
				'1/4 teaspoon allspice',
				'One 28-ounce can of diced tomatoes',
				'One 28-ounce can of red kidney beans, drained',
				'Chopped scallions and/or cilantro',
				'1 1/2 cups white rice',
				'1/2 teaspoon turmeric',
				'1/2 teaspoon salt',
				'1 teaspoon vegetable oil',
				'1 1/2 cups water',
				'3/4 cup coconut milk',
			),
			$schema['recipeIngredient']
		);
	}

	/**
	 * Ensures expected format for JSON+LD with instruction list items.
	 */
	public function test_google_schema_instructions_list_items() {
		$instructions = <<<EOT
Is this a heading?
<p>&nbsp;</p>
<p style="--tw-translate-x:0">&nbsp;</p>
<ol>
<li class="instruction">Preheat the oven to 350 degrees. Mash the sweet potatoes and add the melted butter, milk, sugar, vanilla, beaten eggs, and a pinch of salt. Stir until incorporated. Pour into a shallow baking dish or a cast iron skillet.</li>
<li class="instruction">Combine the butter, brown sugar, flour, and pecan pieces in a small bowl, using your fingers to create moist crumbs. Sprinkle generously over the casserole. Bake for 25-35 minutes, until the edges pull away from the sides of the pan and the top is golden brown. Let stand for the mixture to cool and solidify a little bit before serving.</li>
</ol>
<p class="block">&nbsp;</p>
EOT;
		$recipe       = $this->create_mock_recipe();
		$recipe->set_instructions( $instructions );
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertEquals(
			array(
				array(
					'@type' => 'HowToStep',
					'text'  => 'Preheat the oven to 350 degrees. Mash the sweet potatoes and add the melted butter, milk, sugar, vanilla, beaten eggs, and a pinch of salt. Stir until incorporated. Pour into a shallow baking dish or a cast iron skillet.',
					'url'   => '#instruction-step-1',
				),
				array(
					'@type' => 'HowToStep',
					'text'  => 'Combine the butter, brown sugar, flour, and pecan pieces in a small bowl, using your fingers to create moist crumbs. Sprinkle generously over the casserole. Bake for 25-35 minutes, until the edges pull away from the sides of the pan and the top is golden brown. Let stand for the mixture to cool and solidify a little bit before serving.',
					'url'   => '#instruction-step-2',
				),
			),
			$schema['recipeInstructions']
		);
	}

	/**
	 * Ensures expected format for JSON+LD with instruction list items with an image.
	 */
	public function test_google_schema_instructions_list_items_with_image() {
		$file          = __DIR__ . '/data/canola.jpg';
		$attachment_id = $this->create_upload_object( $file );
		$image_html    = get_image_send_to_editor( $attachment_id, '', '', 'none', '', false, 'full' );
		$image_src     = wp_get_attachment_image_src( $attachment_id, 'full' );
		$instructions  = <<<EOT
Is this a heading?

<ol>
<li class="instruction">{$image_html} Preheat the oven to 350 degrees. Mash the sweet potatoes and add the melted butter, milk, sugar, vanilla, beaten eggs, and a pinch of salt. Stir until incorporated. Pour into a shallow baking dish or a cast iron skillet.</li>
<li class="instruction">Combine the butter, brown sugar, flour, and pecan pieces in a small bowl, using your fingers to create moist crumbs. Sprinkle generously over the casserole. Bake for 25-35 minutes, until the edges pull away from the sides of the pan and the top is golden brown. Let stand for the mixture to cool and solidify a little bit before serving.
	<img src="{$image_src[0]}">
</li>
</ol>
EOT;
		$recipe        = $this->create_mock_recipe();
		$recipe->set_instructions( $instructions );
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertEquals(
			array(
				array(
					'@type' => 'HowToStep',
					'text'  => 'Preheat the oven to 350 degrees. Mash the sweet potatoes and add the melted butter, milk, sugar, vanilla, beaten eggs, and a pinch of salt. Stir until incorporated. Pour into a shallow baking dish or a cast iron skillet.',
					'image' => array(
						'@type'  => 'ImageObject',
						'url'    => $image_src[0],
						'height' => $image_src[2],
						'width'  => $image_src[1],
					),
					'url'   => '#instruction-step-1',
				),
				array(
					'@type' => 'HowToStep',
					'text'  => 'Combine the butter, brown sugar, flour, and pecan pieces in a small bowl, using your fingers to create moist crumbs. Sprinkle generously over the casserole. Bake for 25-35 minutes, until the edges pull away from the sides of the pan and the top is golden brown. Let stand for the mixture to cool and solidify a little bit before serving.',
					'image' => $image_src[0],
					'url'   => '#instruction-step-2',
				),
			),
			$schema['recipeInstructions']
		);
	}

	/**
	 * Ensures expected format for JSON+LD with instruction list items with video.
	 */
	public function test_google_schema_instructions_list_items_with_video() {
		$this->prime_basic_test_post();
		add_shortcode( 'adthrive-in-post-video-player', '__return_empty_string' );
		$instructions = <<<EOT
Is this a heading?

<ol>
<li class="instruction">https://vimeo.com/261212868<br />Preheat the oven to 350 degrees. Mash the sweet potatoes and add the melted butter, milk, sugar, vanilla, beaten eggs, and a pinch of salt. Stir until incorporated. Pour into a shallow baking dish or a cast iron skillet.</li>
<li class="instruction">Preheat the oven to 350 degrees. Mash the sweet potatoes and add the melted butter, milk, sugar, vanilla, beaten eggs, and a pinch of salt. Stir until incorporated. Pour into a shallow baking dish or a cast iron skillet.<br />https://vimeo.com/261212868</li>
<li>First, make the chicken.
https://vimeo.com/261212868</li>
<li class="instruction">[adthrive-in-post-video-player video-id="iVKGcLPq" upload-date="2017-02-24T20:40:06.000Z" name="Black Pepper Stir Fried Noodles" description="Black Pepper Stir Fried Noodles - this simple 30 minute stir fry is packed with AMAZING flavor!"]
Combine the butter, brown sugar, flour, and pecan pieces in a small bowl, using your fingers to create moist crumbs. Sprinkle generously over the casserole. Bake for 25-35 minutes, until the edges pull away from the sides of the pan and the top is golden brown. Let stand for the mixture to cool and solidify a little bit before serving.</li>
</ol>
EOT;
		$recipe       = $this->create_mock_recipe();
		$recipe->set_instructions( $instructions );
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertEquals(
			array(
				array(
					'@type' => 'HowToStep',
					'text'  => 'Preheat the oven to 350 degrees. Mash the sweet potatoes and add the melted butter, milk, sugar, vanilla, beaten eggs, and a pinch of salt. Stir until incorporated. Pour into a shallow baking dish or a cast iron skillet.',
					'video' => array(
						'@type'        => 'VideoObject',
						'@context'     => 'http://schema.org',
						'name'         => 'One City: Many Futures / Ryan Hashagen, Gwen Shaw, & Ben Chaney',
						'description'  => '',
						'duration'     => 'PT14M15S',
						'embedUrl'     => 'https://player.vimeo.com/video/261212868?app_id=122963',
						'contentUrl'   => 'https://vimeo.com/261212868',
						'thumbnailUrl' => array( 'https://i.vimeocdn.com/video/690156753_640.jpg' ),
						'uploadDate'   => '2018-03-21T18:00:42+00:00',
					),
					'url'   => '#instruction-step-1',
				),
				array(
					'@type' => 'HowToStep',
					'text'  => 'Preheat the oven to 350 degrees. Mash the sweet potatoes and add the melted butter, milk, sugar, vanilla, beaten eggs, and a pinch of salt. Stir until incorporated. Pour into a shallow baking dish or a cast iron skillet.',
					'video' => array(
						'@type'        => 'VideoObject',
						'@context'     => 'http://schema.org',
						'name'         => 'One City: Many Futures / Ryan Hashagen, Gwen Shaw, & Ben Chaney',
						'description'  => '',
						'duration'     => 'PT14M15S',
						'embedUrl'     => 'https://player.vimeo.com/video/261212868?app_id=122963',
						'contentUrl'   => 'https://vimeo.com/261212868',
						'thumbnailUrl' => array( 'https://i.vimeocdn.com/video/690156753_640.jpg' ),
						'uploadDate'   => '2018-03-21T18:00:42+00:00',
					),
					'url'   => '#instruction-step-2',
				),
				array(
					'@type' => 'HowToStep',
					'text'  => 'First, make the chicken.',
					'video' => array(
						'@type'        => 'VideoObject',
						'@context'     => 'http://schema.org',
						'name'         => 'One City: Many Futures / Ryan Hashagen, Gwen Shaw, & Ben Chaney',
						'description'  => '',
						'duration'     => 'PT14M15S',
						'embedUrl'     => 'https://player.vimeo.com/video/261212868?app_id=122963',
						'contentUrl'   => 'https://vimeo.com/261212868',
						'thumbnailUrl' => array( 'https://i.vimeocdn.com/video/690156753_640.jpg' ),
						'uploadDate'   => '2018-03-21T18:00:42+00:00',
					),
					'url'   => '#instruction-step-3',
				),
				array(
					'@type' => 'HowToStep',
					'text'  => 'Combine the butter, brown sugar, flour, and pecan pieces in a small bowl, using your fingers to create moist crumbs. Sprinkle generously over the casserole. Bake for 25-35 minutes, until the edges pull away from the sides of the pan and the top is golden brown. Let stand for the mixture to cool and solidify a little bit before serving.',
					'video' => array(
						'@type'        => 'VideoObject',
						'@context'     => 'http://schema.org',
						'name'         => 'Black Pepper Stir Fried Noodles',
						'description'  => 'Black Pepper Stir Fried Noodles - this simple 30 minute stir fry is packed with AMAZING flavor!',
						'uploadDate'   => '2017-02-24T20:40:06+00:00',
						'thumbnailUrl' => array( 'https://content.jwplatform.com/thumbs/iVKGcLPq-720.jpg' ),
						'contentUrl'   => 'https://content.jwplatform.com/videos/iVKGcLPq.mp4',
					),
					'url'   => '#instruction-step-4',
				),
			),
			$schema['recipeInstructions']
		);
	}

	/**
	 * Ensures expected format for JSON+LD with ratings.
	 */
	public function test_google_schema_ratings() {
		// Test errors for < WP 4.9 on PHP 7.2.
		$this->skip_if_less_than_wp_version( '4.9' );
		$this->prime_basic_test_post();
		$recipe = $this->create_mock_recipe_with_ratings();
		$schema = Distribution_Metadata::get_enriched_google_schema_for_recipe( $recipe, get_queried_object() );
		$this->assertEquals(
			array(
				'@type'       => 'AggregateRating',
				'reviewCount' => '5',
				'ratingValue' => '2.8',
			),
			$schema['aggregateRating']
		);
		$this->assertEquals(
			array(
				array(
					'@type'         => 'Review',
					'reviewRating'  => array(
						'@type'       => 'Rating',
						'ratingValue' => '1',
					),
					'author'        => array(
						'@type' => 'Person',
						'name'  => 'First author',
					),
					'datePublished' => '2017-01-01',
					'reviewBody'    => 'First comment',
				),
				array(
					'@type'         => 'Review',
					'reviewRating'  => array(
						'@type'       => 'Rating',
						'ratingValue' => '3',
					),
					'author'        => array(
						'@type' => 'Person',
						'name'  => 'Second author',
					),
					'datePublished' => '2017-01-02',
					'reviewBody'    => 'Second comment',
				),
				array(
					'@type'         => 'Review',
					'reviewRating'  => array(
						'@type'       => 'Rating',
						'ratingValue' => '4',
					),
					'author'        => array(
						'@type' => 'Person',
						'name'  => 'Third author',
					),
					'datePublished' => '2017-01-03',
					'reviewBody'    => 'Third comment',
				),
			),
			$schema['review']
		);
	}

	/**
	 * Ensures expected format for JSON+LD with Nutrifox ingredient data.
	 */
	public function test_google_schema_nutrifox_data() {
		$recipe = $this->create_mock_recipe_with_nutrifox_id();
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertEquals(
			array(
				'@type'               => 'nutritionInformation',
				'calories'            => '493 calories',
				'servingSize'         => '1/2 spaghetti squash',
				'sugarContent'        => '11.8 g',
				'sodiumContent'       => '1013.7 mg',
				'fatContent'          => '34.1 g',
				'saturatedFatContent' => '6.2 g',
				'transFatContent'     => '0 g',
				'carbohydrateContent' => '35.2 g',
				'fiberContent'        => '8 g',
				'proteinContent'      => '20.3 g',
				'cholesterolContent'  => '0 mg',
			),
			$schema['nutrition']
		);
	}

	/**
	 * Ensures expected format for JSON+LD multiple cuisines.
	 */
	public function test_google_schema_only_one_cuisine_category_method() {
		$recipe = $this->create_mock_recipe();
		$recipe->set_cuisine( 'Indian, Asian' );
		$recipe->set_category( 'Breakfast, Lunch, Dinner' );
		$recipe->set_method( 'Cooking' );
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertEquals( 'Asian', $schema['recipeCuisine'] );
		$this->assertEquals( 'Breakfast', $schema['recipeCategory'] );
		$this->assertEquals( 'Cooking', $schema['cookingMethod'] );
	}

	/**
	 * Ensures expected format for JSON+LD with a Vimeo URL.
	 */
	public function test_google_schema_vimeo_embed_url() {
		$recipe = $this->create_mock_recipe_with_vimeo_url();
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertEquals( 'One City: Many Futures / Ryan Hashagen, Gwen Shaw, & Ben Chaney', $schema['video']['name'] );
		$this->assertEquals( '', $schema['video']['description'] );
		$this->assertEquals( 'PT14M15S', $schema['video']['duration'] );
		$this->assertEquals( 'https://player.vimeo.com/video/261212868?app_id=122963', $schema['video']['embedUrl'] );
		$this->assertEquals( 'https://vimeo.com/261212868', $schema['video']['contentUrl'] );
		$this->assertEquals( array( 'https://i.vimeocdn.com/video/690156753_640.jpg' ), $schema['video']['thumbnailUrl'] );
		$this->assertEquals( '2018-03-21T18:00:42+00:00', $schema['video']['uploadDate'] );
	}

	/**
	 * Ensures expected format for JSON+LD with a YouTube URL.
	 */
	public function test_google_schema_youtube_embed_url() {
		$recipe = $this->create_mock_recipe_with_youtube_url();
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertEquals( 'Boeing 747-400 Cockpit - Breakoff Landing Amsterdam Schiphol', $schema['video']['name'] );
		$this->assertEquals( 'https://www.youtube.com/embed/GRtWNZwgGn8?feature=oembed', $schema['video']['embedUrl'] );
		$this->assertEquals( 'https://www.youtube.com/watch?v=GRtWNZwgGn8', $schema['video']['contentUrl'] );
		$this->assertEquals( array( 'https://i.ytimg.com/vi/GRtWNZwgGn8/hqdefault.jpg' ), $schema['video']['thumbnailUrl'] );
	}

	/**
	 * Ensures expected format for JSON+LD with a MediaVine URL.
	 */
	public function test_google_schema_mediavine_embed_url() {
		$recipe = $this->create_mock_recipe_with_mediavine_url();
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertEquals( 'Easy Strawberry Syrup', $schema['video']['name'] );
		$this->assertEquals( 'An easy strawberry syrup recipe that can be used on ice cream, pancakes, oatmeal and more. ', $schema['video']['description'] );
		$this->assertEquals( 'PT45S', $schema['video']['duration'] );
		$this->assertEquals( '//video.mediavine.com/videos/yqm6njhxgp1xf9he5sjm.js', $schema['video']['embedUrl'] );
		$this->assertEquals( array( 'https://mediavine-res.cloudinary.com/video/upload/s--qJv4mQvO--/c_limit,f_auto,fl_lossy,h_1080,q_auto,w_1920/v1516661408/yqm6njhxgp1xf9he5sjm.jpg' ), $schema['video']['thumbnailUrl'] );
		$this->assertEquals( 'https://mediavine-res.cloudinary.com/video/upload/t_original/v1516661408/yqm6njhxgp1xf9he5sjm.mp4', $schema['video']['contentUrl'] );
		$this->assertEquals( '2018-01-22T22:50:16+00:00', $schema['video']['uploadDate'] );
	}

	/**
	 * Ensures expected format for JSON+LD with an AdThrive shortcode.
	 */
	public function test_google_schema_adthrive_shortcode() {
		$this->prime_basic_test_post();
		$recipe = $this->create_mock_recipe_with_adthrive_shortcode();
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertEquals( 'Black Pepper Stir Fried Noodles', $schema['video']['name'] );
		$this->assertEquals( 'Black Pepper Stir Fried Noodles - this simple 30 minute stir fry is packed with AMAZING flavor!', $schema['video']['description'] );
		$this->assertEquals( '2017-02-24T20:40:06+00:00', $schema['video']['uploadDate'] );
		$this->assertEquals( array( 'https://content.jwplatform.com/thumbs/iVKGcLPq-720.jpg' ), $schema['video']['thumbnailUrl'] );
		$this->assertEquals( 'https://content.jwplatform.com/videos/iVKGcLPq.mp4', $schema['video']['contentUrl'] );
	}

	/**
	 * Ensures expected format for JSON+LD with an AdThrive shortcode in a post.
	 */
	public function test_google_schema_enrich_post_video_adthrive() {
		$this->prime_basic_test_post();
		$post_content = <<<EOT
This is my first line of text.

[adthrive-in-post-video-player video-id="iVKGcLPq" upload-date="2017-02-24T20:40:06.000Z" name="Black Pepper Stir Fried Noodles" description="Black Pepper Stir Fried Noodles - this simple 30 minute stir fry is packed with AMAZING flavor!"]

This is my second line of text.
EOT;
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_content' => $post_content,
			)
		);
		$post   = get_post( $this->post_id );
		$recipe = $this->create_mock_recipe();
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertFalse( isset( $schema['video'] ) );
		$schema = Distribution_Metadata::enrich_google_schema_with_post_video( $schema, $recipe, $post );
		$this->assertEquals( 'Black Pepper Stir Fried Noodles', $schema['video']['name'] );
		$this->assertEquals( '2017-02-24T20:40:06+00:00', $schema['video']['uploadDate'] );
		$this->assertEquals( array( 'https://content.jwplatform.com/thumbs/iVKGcLPq-720.jpg' ), $schema['video']['thumbnailUrl'] );
		$this->assertEquals( 'https://content.jwplatform.com/videos/iVKGcLPq.mp4', $schema['video']['contentUrl'] );
	}

	/**
	 * Ensures expected format for JSON+LD with an AdThrive shortcode in a post.
	 */
	public function test_google_schema_enrich_post_video_adthrive_second() {
		$this->prime_basic_test_post();
		$post_content = <<<EOT
This is my first line of text.

[adthrive-in-post-video-player video-id="ytxxCAd7" upload-date="2019-02-05T21:16:23.000Z" name="Northern-style Thai Vegan coconut soup" description="This Northern-style Thai Vegan coconut soup is a healthy recipe that is so easy to make! This will quickly become your new favourite soup recipe! "]

This is my second line of text.
EOT;
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_content' => $post_content,
			)
		);
		$post   = get_post( $this->post_id );
		$recipe = $this->create_mock_recipe();
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertFalse( isset( $schema['video'] ) );
		$schema = Distribution_Metadata::enrich_google_schema_with_post_video( $schema, $recipe, $post );
		$this->assertEquals( 'Northern-style Thai Vegan coconut soup', $schema['video']['name'] );
		$this->assertEquals( 'This Northern-style Thai Vegan coconut soup is a healthy recipe that is so easy to make! This will quickly become your new favourite soup recipe! ', $schema['video']['description'] );
		$this->assertEquals( '2019-02-05T21:16:23+00:00', $schema['video']['uploadDate'] );
		$this->assertEquals( array( 'https://content.jwplatform.com/thumbs/ytxxCAd7-720.jpg' ), $schema['video']['thumbnailUrl'] );
		$this->assertEquals( 'https://content.jwplatform.com/videos/ytxxCAd7.mp4', $schema['video']['contentUrl'] );
	}

	/**
	 * Ensures expected format for JSON+LD with an ignored AdThrive shortcode in a post.
	 */
	public function test_google_schema_enrich_post_video_adthrive_ignore() {
		$this->prime_basic_test_post();
		$post_content = <<<EOT
This is my first line of text.

<!-- tasty-recipes-ignore -->[adthrive-in-post-video-player video-id="iVKGcLPq" upload-date="2017-02-24T20:40:06.000Z" name="Black Pepper Stir Fried Noodles" description="Black Pepper Stir Fried Noodles - this simple 30 minute stir fry is packed with AMAZING flavor!"]<!-- /tasty-recipes-ignore -->

This is my second line of text.

[adthrive-in-post-video-player video-id="iVKGcLPq" upload-date="2017-02-24T20:40:06.000Z" name="Foo Bar" description="Black Pepper Stir Fried Noodles - this simple 30 minute stir fry is packed with AMAZING flavor!"]

This is a final line of text.

EOT;
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_content' => $post_content,
			)
		);
		$post   = get_post( $this->post_id );
		$recipe = $this->create_mock_recipe();
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertFalse( isset( $schema['video'] ) );
		$schema = Distribution_Metadata::enrich_google_schema_with_post_video( $schema, $recipe, $post );
		$this->assertEquals( 'Foo Bar', $schema['video']['name'] );
	}

	/**
	 * Ensures expected format for JSON+LD with a YouTube video in a post.
	 */
	public function test_google_schema_enrich_post_video_youtube() {
		$this->prime_basic_test_post();
		$post_content = <<<EOT
This is my first line of text.

https://www.youtube.com/watch?v=GRtWNZwgGn8

This is my second line of text.
EOT;
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_content' => $post_content,
			)
		);
		$post   = get_post( $this->post_id );
		$recipe = $this->create_mock_recipe();
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertFalse( isset( $schema['video'] ) );
		$schema = Distribution_Metadata::enrich_google_schema_with_post_video( $schema, $recipe, $post );
		$this->assertEquals( 'Boeing 747-400 Cockpit - Breakoff Landing Amsterdam Schiphol', $schema['video']['name'] );
		$this->assertEquals( 'https://www.youtube.com/embed/GRtWNZwgGn8?feature=oembed', $schema['video']['embedUrl'] );
		$this->assertEquals( array( 'https://i.ytimg.com/vi/GRtWNZwgGn8/hqdefault.jpg' ), $schema['video']['thumbnailUrl'] );
	}

	/**
	 * Ensures expected format for JSON+LD with a Jetpack YouTube shortcode in a post.
	 */
	public function test_google_schema_enrich_post_video_jetpack_youtube() {
		$this->prime_basic_test_post();
		global $wpdb;

		$post_content = <<<EOT
This is my first line of text.

<p><span class="embed-youtube" style="text-align:center; display: block;"><iframe class='youtube-player' type='text/html' width='740' height='447' src='http://www.youtube.com/embed/GRtWNZwgGn8?version=3&#038;rel=0&#038;fs=1&#038;autohide=2&#038;showsearch=0&#038;showinfo=1&#038;iv_load_policy=1&#038;wmode=transparent' allowfullscreen='true' style='border:0;'></iframe></span></p>

This is my second line of text.
EOT;
		$wpdb->update(
			$wpdb->posts,
			array(
				'post_content' => $post_content,
			),
			array(
				'ID' => $this->post_id,
			)
		);
		clean_post_cache( $this->post_id );
		$post   = get_post( $this->post_id );
		$recipe = $this->create_mock_recipe();
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertFalse( isset( $schema['video'] ) );
		$schema = Distribution_Metadata::enrich_google_schema_with_post_video( $schema, $recipe, $post );
		$this->assertEquals( 'Boeing 747-400 Cockpit - Breakoff Landing Amsterdam Schiphol', $schema['video']['name'] );
		$this->assertEquals( 'https://www.youtube.com/embed/GRtWNZwgGn8?feature=oembed', $schema['video']['embedUrl'] );
		$this->assertEquals( array( 'https://i.ytimg.com/vi/GRtWNZwgGn8/hqdefault.jpg' ), $schema['video']['thumbnailUrl'] );
	}

	/**
	 * Ensures expected format for JSON+LD with a Vimeo URL in a post.
	 */
	public function test_google_schema_enrich_post_video_vimeo() {
		$this->prime_basic_test_post();
		$post_content = <<<EOT
This is my first line of text.

https://vimeo.com/261212868

This is YouTube

https://www.youtube.com/watch?v=GRtWNZwgGn8

This is my second line of text.
EOT;
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_content' => $post_content,
			)
		);
		$post   = get_post( $this->post_id );
		$recipe = $this->create_mock_recipe();
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertFalse( isset( $schema['video'] ) );
		$schema = Distribution_Metadata::enrich_google_schema_with_post_video( $schema, $recipe, $post );
		$this->assertEquals( 'One City: Many Futures / Ryan Hashagen, Gwen Shaw, & Ben Chaney', $schema['video']['name'] );
		$this->assertEquals( '', $schema['video']['description'] );
		$this->assertEquals( 'PT14M15S', $schema['video']['duration'] );
		$this->assertEquals( 'https://player.vimeo.com/video/261212868?app_id=122963', $schema['video']['embedUrl'] );
		$this->assertEquals( array( 'https://i.vimeocdn.com/video/690156753_640.jpg' ), $schema['video']['thumbnailUrl'] );
		$this->assertEquals( '2018-03-21T18:00:42+00:00', $schema['video']['uploadDate'] );
	}

	/**
	 * Ensures expected format for JSON+LD with a MediaVine URL in a post.
	 */
	public function test_google_schema_enrich_post_video_mediavine() {
		$this->prime_basic_test_post();
		$post_content = <<<EOT
This is my first line of text.

https://dashboard.mediavine.com/videos/yqm6njhxgp1xf9he5sjm/edit

This is my second line of text.
EOT;
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_content' => $post_content,
			)
		);
		$post   = get_post( $this->post_id );
		$recipe = $this->create_mock_recipe();
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertFalse( isset( $schema['video'] ) );
		$schema = Distribution_Metadata::enrich_google_schema_with_post_video( $schema, $recipe, $post );
		$this->assertEquals( 'Easy Strawberry Syrup', $schema['video']['name'] );
		$this->assertEquals( 'An easy strawberry syrup recipe that can be used on ice cream, pancakes, oatmeal and more. ', $schema['video']['description'] );
		$this->assertEquals( 'PT45S', $schema['video']['duration'] );
		$this->assertEquals( '//video.mediavine.com/videos/yqm6njhxgp1xf9he5sjm.js', $schema['video']['embedUrl'] );
		$this->assertEquals( 'https://mediavine-res.cloudinary.com/video/upload/t_original/v1516661408/yqm6njhxgp1xf9he5sjm.mp4', $schema['video']['contentUrl'] );
		$this->assertEquals( array( 'https://mediavine-res.cloudinary.com/video/upload/s--qJv4mQvO--/c_limit,f_auto,fl_lossy,h_1080,q_auto,w_1920/v1516661408/yqm6njhxgp1xf9he5sjm.jpg' ), $schema['video']['thumbnailUrl'] );
		$this->assertEquals( '2018-01-22T22:50:16+00:00', $schema['video']['uploadDate'] );
	}

	/**
	 * Ensures expected format for JSON+LD with a MediaVine shortcode in a post.
	 */
	public function test_google_schema_enrich_post_video_mediavine_shortcode() {
		$this->skip_if_less_than_wp_version( '4.9' );
		$this->prime_basic_test_post();
		$post_content = <<<EOT
This is my first line of text.

<div class="mv-video-target mv-video-id-yqm6njhxgp1xf9he5sjm" data-video-id="yqm6njhxgp1xf9he5sjm" data-ratio="16:9" data-volume="70" data-disable-optimize="1" data-disable-autoplay="1"></div>

This is my second line of text.
EOT;
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_content' => $post_content,
			)
		);
		$post   = get_post( $this->post_id );
		$recipe = $this->create_mock_recipe();
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertFalse( isset( $schema['video'] ) );
		$schema = Distribution_Metadata::enrich_google_schema_with_post_video( $schema, $recipe, $post );
		$this->assertEquals( 'Easy Strawberry Syrup', $schema['video']['name'] );
		$this->assertEquals( 'An easy strawberry syrup recipe that can be used on ice cream, pancakes, oatmeal and more. ', $schema['video']['description'] );
		$this->assertEquals( 'PT45S', $schema['video']['duration'] );
		$this->assertEquals( '//video.mediavine.com/videos/yqm6njhxgp1xf9he5sjm.js', $schema['video']['embedUrl'] );
		$this->assertEquals( 'https://mediavine-res.cloudinary.com/video/upload/t_original/v1516661408/yqm6njhxgp1xf9he5sjm.mp4', $schema['video']['contentUrl'] );
		$this->assertEquals( array( 'https://mediavine-res.cloudinary.com/video/upload/s--qJv4mQvO--/c_limit,f_auto,fl_lossy,h_1080,q_auto,w_1920/v1516661408/yqm6njhxgp1xf9he5sjm.jpg' ), $schema['video']['thumbnailUrl'] );
		$this->assertEquals( '2018-01-22T22:50:16+00:00', $schema['video']['uploadDate'] );
	}

	/**
	 * Ensures expected format for JSON+LD with an ignored YouTube video in a post.
	 */
	public function test_google_schema_enrich_post_video_ignore() {
		$this->prime_basic_test_post();
		$post_content = <<<EOT
This is my first line of text.

<!-- tasty-recipes-ignore -->

https://www.youtube.com/watch?v=GRtWNZwgGn8

<!-- /tasty-recipes-ignore -->

This is my second line of text.
EOT;
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_content' => $post_content,
			)
		);
		$post   = get_post( $this->post_id );
		$recipe = $this->create_mock_recipe();
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertFalse( isset( $schema['video'] ) );
		$schema = Distribution_Metadata::enrich_google_schema_with_post_video( $schema, $recipe, $post );
		$this->assertFalse( isset( $schema['video'] ) );
	}

	/**
	 * Ensures enriching the post video is properly cached.
	 */
	public function test_google_schema_enrich_post_video_cache_works() {
		$this->prime_basic_test_post();
		$post_content = <<<EOT
This is my first line of text.

https://www.youtube.com/watch?v=GRtWNZwgGn8

This is my second line of text.
EOT;
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_content' => $post_content,
			)
		);
		$post        = get_post( $this->post_id );
		$recipe      = $this->create_mock_recipe();
		$orig_schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		// Not set initially.
		$this->assertFalse( isset( $orig_schema['video'] ) );

		// Cache should be empty.
		$cache_key   = '_tr_oembed_' . md5( 'https://www.youtube.com/watch?v=GRtWNZwgGn8' );
		$cache_value = get_post_meta( $this->post_id, $cache_key, true );
		$this->assertTrue( empty( $cache_value ) );

		// Fetching should populate cache.
		$schema      = Distribution_Metadata::enrich_google_schema_with_post_video( $orig_schema, $recipe, $post );
		$cache_value = get_post_meta( $this->post_id, $cache_key, true );
		$this->assertFalse( empty( $cache_value ) );
		$this->assertEquals( 'Boeing 747-400 Cockpit - Breakoff Landing Amsterdam Schiphol', $schema['video']['name'] );

		// Modify cache so we can verify cache hit.
		$cache_value->title = 'Foo bar';
		update_post_meta( $post->ID, $cache_key, $cache_value );

		// Re-fetching should populate from cache.
		$schema      = Distribution_Metadata::enrich_google_schema_with_post_video( $orig_schema, $recipe, $post );
		$cache_value = get_post_meta( $this->post_id, $cache_key, true );
		$this->assertFalse( empty( $cache_value ) );
		$this->assertEquals( 'Foo bar', $schema['video']['name'] );
	}

	/**
	 * Ensures filtering the post video enrichment works.
	 */
	public function test_google_schema_enrich_post_video_via_filter() {
		$this->prime_basic_test_post();
		$post_content = <<<EOT
This Vimeo video should be ignored because the actual URL is in post meta.

https://vimeo.com/261212868
EOT;
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_content' => $post_content,
			)
		);
		$post   = get_post( $this->post_id );
		$recipe = $this->create_mock_recipe();
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertFalse( isset( $schema['video'] ) );
		update_post_meta( $post->ID, 'post_video', 'https://www.youtube.com/watch?v=GRtWNZwgGn8' );
		$filter = function( $retval, $post ) {
			return get_post_meta( $post->ID, 'post_video', true );
		};
		add_filter( 'tasty_recipes_enrich_google_schema_post_video_url', $filter, 10, 2 );
		$schema = Distribution_Metadata::enrich_google_schema_with_post_video( $schema, $recipe, $post );
		remove_filter( 'tasty_recipes_enrich_google_schema_post_video_url', $filter );
		$this->assertEquals( 'Boeing 747-400 Cockpit - Breakoff Landing Amsterdam Schiphol', $schema['video']['name'] );
		$this->assertEquals( 'https://www.youtube.com/embed/GRtWNZwgGn8?feature=oembed', $schema['video']['embedUrl'] );
	}

	/**
	 * Ensures spected behavior for JSON+LD of a post with an author.
	 */
	public function test_google_schema_enrich_post_author() {
		// Test errors for < WP 4.9 on PHP 7.2.
		$this->skip_if_less_than_wp_version( '4.9' );
		$this->prime_basic_test_post();
		$recipe = $this->create_mock_recipe();
		$recipe->set_author_name( '' );
		$post = get_post( $this->post_id );
		// No author.
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertFalse( isset( $schema['author'] ) );
		$schema = Distribution_Metadata::enrich_google_schema_with_post_author( $schema, $recipe, $post );
		$this->assertEquals(
			array(
				'@type' => 'Person',
				'name'  => 'The Administrator',
			),
			$schema['author']
		);
		// With an author.
		$recipe->set_author_name( 'Test Author' );
		$schema = Distribution_Metadata::get_enriched_google_schema_for_recipe( $recipe, $post );
		$this->assertEquals(
			array(
				'@type' => 'Person',
				'name'  => 'Test Author',
			),
			$schema['author']
		);
		// With an override link.
		wp_update_post(
			array(
				'ID'           => $post->ID,
				'post_content' => str_replace( ']', ' author_link="https://apple.com"]', Shortcodes::get_shortcode_for_recipe( $recipe ) ),
			)
		);
		$post   = get_post( $post->ID );
		$schema = Distribution_Metadata::get_enriched_google_schema_for_recipe( $recipe, $post );
		$this->assertEquals(
			array(
				'@type' => 'Person',
				'name'  => 'Test Author',
				'url'   => 'https://apple.com',
			),
			$schema['author']
		);
	}

	/**
	 * Ensure the prep time includes the additional time.
	 */
	public function test_prep_time_includes_additional_minutes() {
		$recipe = $this->create_mock_recipe();
		$recipe->set_prep_time( '20 minutes' );
		$recipe->set_additional_time_value( '5 minutes' );
		$recipe->set_cook_time( '20 minutes' );
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertEquals( 'PT25M', $schema['prepTime'] );
		$this->assertEquals( 'PT45M', $schema['totalTime'] );
	}

	/**
	 * Ensure the prep time includes the additional time.
	 */
	public function test_prep_time_includes_additional_hours() {
		$recipe = $this->create_mock_recipe();
		$recipe->set_prep_time( '20 minutes' );
		$recipe->set_additional_time_value( '1 hour 25 minutes' );
		$recipe->set_cook_time( '20 minutes' );
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertEquals( 'PT1H45M', $schema['prepTime'] );
		$this->assertEquals( 'PT2H5M', $schema['totalTime'] );
	}

	/**
	 * Ensures expected format for total time greater than one hour.
	 */
	public function test_total_time_greater_than_one_hour() {
		$this->assertEquals( 'PT2H21M', Distribution_Metadata::get_duration_for_time( '2 hours 21 minutes' ) );
		$this->assertEquals( '2 hours 21 minutes', Distribution_Metadata::get_time_for_duration( 'PT2H21M' ) );
	}

	/**
	 * Ensures expected format for total time exactly one hour.
	 */
	public function test_total_time_exact_hour() {
		$this->assertEquals( 'PT1H', Distribution_Metadata::get_duration_for_time( '1 hour' ) );
		$this->assertEquals( '1 hour', Distribution_Metadata::get_time_for_duration( 'PT1H' ) );
		$this->assertEquals( 'PT2H', Distribution_Metadata::get_duration_for_time( '2 hours' ) );
		$this->assertEquals( '2 hours', Distribution_Metadata::get_time_for_duration( 'PT2H' ) );
	}

	/**
	 * Ensures expected format for total time of less than one hour.
	 */
	public function test_total_time_less_than_hour() {
		$this->assertEquals( 'PT8M', Distribution_Metadata::get_duration_for_time( '8 minutes' ) );
		$this->assertEquals( '8 minutes', Distribution_Metadata::get_time_for_duration( 'PT8M' ) );
		$this->assertEquals( 'PT1M', Distribution_Metadata::get_duration_for_time( '1 minute' ) );
		$this->assertEquals( '1 minute', Distribution_Metadata::get_time_for_duration( 'PT1M' ) );
	}

	/**
	 * Ensures expected format for total time greater than 24 hours.
	 */
	public function test_total_time_over_24h() {
		$this->assertEquals( 'PT25H', Distribution_Metadata::get_duration_for_time( '25 hours' ) );
		$this->assertEquals( '25 hours', Distribution_Metadata::get_time_for_duration( 'PT25H' ) );
		$this->assertEquals( 'PT48H30M', Distribution_Metadata::get_duration_for_time( '48 hours 30 minutes' ) );
		$this->assertEquals( '48 hours 30 minutes', Distribution_Metadata::get_time_for_duration( 'PT48H30M' ) );
		$this->assertEquals( 'PT48H30M', Distribution_Metadata::get_duration_for_time( '2 days 30 minutes' ) );
		$this->assertEquals( 'PT48H', Distribution_Metadata::get_duration_for_time( '2 days' ) );
	}

	/**
	 * Ensures expected format for times as integers.
	 */
	public function test_integer_times_as_minutes() {
		$this->assertEquals( 'PT21M', Distribution_Metadata::get_duration_for_time( '21' ) );
	}

	/**
	 * Ensures expected format for an invalid time.
	 */
	public function test_invalid_time() {
		$this->assertEquals( '', Distribution_Metadata::get_time_for_duration( null ) );
	}

	/**
	 * Ensures expected format for a time with parens.
	 */
	public function test_remove_parens_in_time() {
		$this->assertEquals( 'PT25M', Distribution_Metadata::get_duration_for_time( '25 minutes (plus dipping)' ) );
	}

	/**
	 * Ensures expected format for numerical format times.
	 */
	public function test_handle_numerical_times() {
		$this->assertEquals( 'PT25M', Distribution_Metadata::get_duration_for_time( '00:25' ) );
		$this->assertEquals( 'PT1H25M', Distribution_Metadata::get_duration_for_time( '01:25' ) );
		$this->assertEquals( 'PT1H5M', Distribution_Metadata::get_duration_for_time( '01:05' ) );
		$this->assertEquals( 'PT1H5M', Distribution_Metadata::get_duration_for_time( '1:05' ) );
	}

	/**
	 * Non-English units and joining words become durations, and junk is omitted.
	 */
	public function test_non_english_and_unparsed_times() {
		$this->assertEquals( 'PT15M', Distribution_Metadata::get_duration_for_time( '15 minutos' ) );
		$this->assertEquals( 'PT1H30M', Distribution_Metadata::get_duration_for_time( '1 hora y 30 minutos' ) );
		$this->assertEquals( 'PT1H45M', Distribution_Metadata::get_duration_for_time( '1 hora y 45 minutos' ) );
		$this->assertEquals( 'PT1H30M', Distribution_Metadata::get_duration_for_time( '1 hour y 30 minutes' ) );
		$this->assertEquals( 'PT48H30M', Distribution_Metadata::get_duration_for_time( '2 days and 30 minutes' ) );
		$this->assertEquals( 'PT30M', Distribution_Metadata::get_duration_for_time( 'about 30 minutes' ) );
		$this->assertEquals( 'PT168H', Distribution_Metadata::get_duration_for_time( '1 week' ) );
		$this->assertEquals( '', Distribution_Metadata::get_duration_for_time( 'not-a-time' ) );
		$this->assertEquals( '', Distribution_Metadata::get_duration_for_time( '1.5 hours' ) );
		$this->assertSame( 5400, Distribution_Metadata::get_seconds_for_time( '1 hour 30 min' ) );
		$this->assertNull( Distribution_Metadata::get_seconds_for_time( '1 hr 30 min' ) );
		$this->assertSame( 1209600, Distribution_Metadata::get_seconds_for_time( '1 fortnight' ) );

		$now = time();
		$this->assertSame(
			strtotime( '1 month', $now ) - $now,
			Distribution_Metadata::get_seconds_for_time( '1 month' )
		);
	}

	/**
	 * Spanish times are valid schema durations, and an unparsed time is left out.
	 */
	public function test_schema_omits_unparsed_times() {
		$recipe = $this->create_mock_recipe();
		$recipe->set_prep_time( '15 minutos' );
		$recipe->set_cook_time( '1 hora y 30 minutos' );
		$recipe->set_total_time( '1 hora y 45 minutos' );
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertEquals( 'PT15M', $schema['prepTime'] );
		$this->assertEquals( 'PT1H30M', $schema['cookTime'] );
		$this->assertEquals( 'PT1H45M', $schema['totalTime'] );
		$this->assertStringNotContainsString( 'PT-', wp_json_encode( $schema ) );

		$recipe = $this->create_mock_recipe();
		$recipe->set_prep_time( '20 minutes' );
		$recipe->set_cook_time( 'not-a-time' );
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertEquals( 'PT20M', $schema['prepTime'] );
		$this->assertArrayNotHasKey( 'cookTime', $schema );
		$this->assertEquals( 'PT20M', $schema['totalTime'] );
	}

	/**
	 * Ensures expected behavior for heading testing.
	 */
	public function test_get_heading() {
		$this->assertEquals( 'For The Red Beans', Distribution_Metadata::get_heading( 'For The Red Beans:' ) );
		$this->assertEquals( 'For The Red Beans', Distribution_Metadata::get_heading( '<strong>For The Red Beans</strong>' ) );
		$this->assertEquals( 'For The Red Beans', Distribution_Metadata::get_heading( '<h3>For The Red Beans</h3>' ) );
		$this->assertEquals( 'For The Red Beans', Distribution_Metadata::get_heading( '<h5>For The Red Beans</h5>' ) );
		$this->assertFalse( Distribution_Metadata::get_heading( 'For The Red Beans' ) );
		$this->assertFalse( Distribution_Metadata::get_heading( 'Chopped scallions and/or cilantro' ) );
	}

	/**
	 * Ensures expected behavior for custom strtotime method.
	 */
	public function test_strtotime_no_now() {
		$this->assertEquals( Distribution_Metadata::strtotime( '25 minutes' ), strtotime( '25 minutes' ) );
	}

	/**
	 * Ensures expected behavior for a component list on a single line.
	 */
	public function test_parse_recipe_component_list_single_line() {
		$this->assertEquals(
			array(
				array(
					'type'  => 'heading',
					'value' => 'For the cranberry jam',
				),
				array(
					'type'  => 'item',
					'value' => '12 ounces fresh or frozen cranberries',
				),
				array(
					'type'  => 'item',
					'value' => '1 orange',
				),
				array(
					'type'  => 'item',
					'value' => '1 sprig rosemary',
				),
				array(
					'type'  => 'item',
					'value' => '1 cup brown sugar',
				),
				array(
					'type'  => 'heading',
					'value' => 'For the sandwiches',
				),
				array(
					'type'  => 'item',
					'value' => '1 loaf sliced country-style bread, approximately 14 slices',
				),
				array(
					'type'  => 'item',
					'value' => '1/2 cup Dijon mustard',
				),
				array(
					'type'  => 'item',
					'value' => '1 pound thinly sliced turkey breast',
				),
				array(
					'type'  => 'item',
					'value' => '6 ounces smoked Gouda, shredded',
				),
				array(
					'type'  => 'item',
					'value' => '1 cup prepared cranberry jam&nbsp;(ingredients above)',
				),
			),
			Distribution_Metadata::parse_recipe_component_list( '<h4>For the cranberry jam</h4><ul><li>12 ounces fresh or frozen <strong>cranberries</strong></li><li>1 <strong>orange</strong></li><li>1 sprig <strong>rosemary</strong></li><li>1 cup <strong>brown sugar</strong></li></ul><h4>For the sandwiches</h4><ul><li>1 loaf sliced <strong>country-style bread</strong>, approximately 14 slices</li><li>1/2 cup <strong>Dijon mustard</strong></li><li>1 pound thinly sliced <strong>turkey breast</strong></li><li>6 ounces <strong>smoked Gouda</strong>, shredded</li><li>1 cup prepared <strong>cranberry jam&nbsp;</strong>(ingredients above)</li></ul>' )
		);
	}

	/**
	 * Ensures expected behavior for a component list on multiple lines.
	 */
	public function test_parse_recipe_component_list_multi_line() {
		$ingredients = <<<EOT
For The Red Beans:

<ul>
	<li>1 1/2cups diced onions</li>
	<li>1 tablespoon vegetable oil</li>
	<li>1/2 teaspoon red pepper flakes or 1 fresh chile, minced</li>
	<li>1/2 teaspoon dried thyme</li>
	<li>Salt</li>
	<li>1/4 teaspoon allspice</li>
	<li>One 28-ounce can of diced tomatoes</li>
	<li>One 28-ounce can of red kidney beans, drained</li>
	<li>Chopped scallions and/or cilantro</li>
</ul>

For The Coconut Rice:

<ul>
	<li>1 1/2 cups white rice</li>
	<li>1/2 teaspoon turmeric</li>
	<li>1/2 teaspoon salt</li>
	<li>1 teaspoon vegetable oil</li>
	<li>1 1/2 cups water</li>
	<li>3/4 cup coconut milk</li>
</ul>
EOT;
		$this->assertEquals(
			array(
				array(
					'type'  => 'heading',
					'value' => 'For The Red Beans',
				),
				array(
					'type'  => 'item',
					'value' => '1 1/2cups diced onions',
				),
				array(
					'type'  => 'item',
					'value' => '1 tablespoon vegetable oil',
				),
				array(
					'type'  => 'item',
					'value' => '1/2 teaspoon red pepper flakes or 1 fresh chile, minced',
				),
				array(
					'type'  => 'item',
					'value' => '1/2 teaspoon dried thyme',
				),
				array(
					'type'  => 'item',
					'value' => 'Salt',
				),
				array(
					'type'  => 'item',
					'value' => '1/4 teaspoon allspice',
				),
				array(
					'type'  => 'item',
					'value' => 'One 28-ounce can of diced tomatoes',
				),
				array(
					'type'  => 'item',
					'value' => 'One 28-ounce can of red kidney beans, drained',
				),
				array(
					'type'  => 'item',
					'value' => 'Chopped scallions and/or cilantro',
				),
				array(
					'type'  => 'heading',
					'value' => 'For The Coconut Rice',
				),
				array(
					'type'  => 'item',
					'value' => '1 1/2 cups white rice',
				),
				array(
					'type'  => 'item',
					'value' => '1/2 teaspoon turmeric',
				),
				array(
					'type'  => 'item',
					'value' => '1/2 teaspoon salt',
				),
				array(
					'type'  => 'item',
					'value' => '1 teaspoon vegetable oil',
				),
				array(
					'type'  => 'item',
					'value' => '1 1/2 cups water',
				),
				array(
					'type'  => 'item',
					'value' => '3/4 cup coconut milk',
				),
			),
			Distribution_Metadata::parse_recipe_component_list( $ingredients )
		);
	}

	/**
	 * Ensures expected behavior when parsing instruction multiline.
	 */
	public function test_parse_recipe_instruction_multiline() {
		$recipe       = $this->create_mock_recipe();
		$instructions = <<<EOT
<strong>Making the rice:</strong> Rinse and drain the rice. In a saucepan on medium-high heat, sauté the rice, turmeric, and salt in the oil for a minute or two, stirring constantly. Add the water and coconut milk, bring to a boil, cover, and reduce the heat to low. Simmer until the rice is tender and the liquid absorbed, 15 to 20 minutes.

<strong>Making the beans</strong>: While the rice is cooking, in a saucepan on medium heat, sauté the onions in the oil for 3 or 4 minutes. Add the red pepper flakes, sprinkle lightly with salt, cover, and cook, stirring occasionally, until the onions are soft, 5 to 8 minutes. Add the thyme, allspice, tomatoes, and beans, cover, and simmer for 10 to 15 minutes, stirring occasionally.
EOT;
		$recipe->set_instructions( $instructions );
		$this->assertEquals(
			array(
				array(
					'type'  => 'item',
					'name'  => 'Making the rice',
					'value' => 'Rinse and drain the rice. In a saucepan on medium-high heat, sauté the rice, turmeric, and salt in the oil for a minute or two, stirring constantly. Add the water and coconut milk, bring to a boil, cover, and reduce the heat to low. Simmer until the rice is tender and the liquid absorbed, 15 to 20 minutes.',
					'url'   => '#instruction-step-1',
				),
				array(
					'type'  => 'item',
					'name'  => 'Making the beans',
					'value' => 'While the rice is cooking, in a saucepan on medium heat, sauté the onions in the oil for 3 or 4 minutes. Add the red pepper flakes, sprinkle lightly with salt, cover, and cook, stirring occasionally, until the onions are soft, 5 to 8 minutes. Add the thyme, allspice, tomatoes, and beans, cover, and simmer for 10 to 15 minutes, stirring occasionally.',
					'url'   => '#instruction-step-2',
				),
			),
			Distribution_Metadata::parse_recipe_instruction_list( $instructions )
		);
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertEquals(
			array(
				array(
					'@type' => 'HowToStep',
					'text'  => 'Rinse and drain the rice. In a saucepan on medium-high heat, sauté the rice, turmeric, and salt in the oil for a minute or two, stirring constantly. Add the water and coconut milk, bring to a boil, cover, and reduce the heat to low. Simmer until the rice is tender and the liquid absorbed, 15 to 20 minutes.',
					'name'  => 'Making the rice',
					'url'   => '#instruction-step-1',
				),
				array(
					'@type' => 'HowToStep',
					'text'  => 'While the rice is cooking, in a saucepan on medium heat, sauté the onions in the oil for 3 or 4 minutes. Add the red pepper flakes, sprinkle lightly with salt, cover, and cook, stirring occasionally, until the onions are soft, 5 to 8 minutes. Add the thyme, allspice, tomatoes, and beans, cover, and simmer for 10 to 15 minutes, stirring occasionally.',
					'name'  => 'Making the beans',
					'url'   => '#instruction-step-2',
				),
			),
			$schema['recipeInstructions']
		);
	}

	/**
	 * Ensures nested instruction lists stay part of their parent step.
	 */
	public function test_parse_recipe_instruction_list_with_nested_list() {
		$instructions = '<ol><li><strong>Rice:</strong> Rinse it.<ul><li>Use cold water</li><li>Drain well</li></ul></li><li>Cook the rice.</li></ol>';

		$this->assertEquals(
			array(
				array(
					'type'  => 'item',
					'name'  => 'Rice',
					'value' => 'Rinse it.' . PHP_EOL . 'Use cold water' . PHP_EOL . 'Drain well',
					'url'   => '#instruction-step-1',
				),
				array(
					'type'  => 'item',
					'value' => 'Cook the rice.',
					'url'   => '#instruction-step-2',
				),
			),
			Distribution_Metadata::parse_recipe_instruction_list( $instructions )
		);
	}

	/**
	 * Ensures step numbers are only applied to top-level instruction items.
	 */
	public function test_apply_instruction_step_numbers_with_nested_list() {
		$this->assertEquals(
			'<ol><li id="instruction-step-1">Step 1<ul><li>a</li><li>b</li></ul></li><li id="instruction-step-2">Step 2</li></ol>',
			Distribution_Metadata::apply_instruction_step_numbers( '<ol><li>Step 1<ul><li>a</li><li>b</li></ul></li><li>Step 2</li></ol>' )
		);
	}

	/**
	 * Ensures nested ingredient lists stay part of their parent ingredient.
	 */
	public function test_parse_recipe_component_list_with_nested_list() {
		$this->assertEquals(
			array(
				array(
					'type'  => 'heading',
					'value' => 'For the cake',
				),
				array(
					'type'  => 'item',
					'value' => 'Flour' . PHP_EOL . 'sifted' . PHP_EOL . 'sieved',
				),
				array(
					'type'  => 'item',
					'value' => 'Sugar',
				),
			),
			Distribution_Metadata::parse_recipe_component_list( '<h4>For the cake</h4><ul><li>Flour<ul><li>sifted</li><li>sieved</li></ul></li><li>Sugar</li></ul>' )
		);
	}

	/**
	 * Ensures unbalanced nested lists still produce every item.
	 */
	public function test_parse_recipe_component_list_with_unbalanced_nested_list() {
		$this->assertEquals(
			array(
				array(
					'type'  => 'item',
					'value' => 'a' . PHP_EOL . 'b',
				),
				array(
					'type'  => 'item',
					'value' => 'c',
				),
			),
			Distribution_Metadata::parse_recipe_component_list( '<ul><li>a<ul><li>b</li></li><li>c</li></ul>' )
		);
	}

	/**
	 * Ensures expected behavior when parsing instruction list.
	 */
	public function test_parse_recipe_instruction_list() {
		$recipe       = $this->create_mock_recipe();
		$instructions = <<<EOT
<ul>
	<li><strong>Making the rice:</strong> Rinse and drain the rice. In a saucepan on medium-high heat, sauté the rice, turmeric, and salt in the oil for a minute or two, stirring constantly. Add the water and coconut milk, bring to a boil, cover, and reduce the heat to low. Simmer until the rice is tender and the liquid absorbed, 15 to 20 minutes.</li>
	<li><strong>Making the beans</strong>: While the rice is cooking, in a saucepan on medium heat, sauté the onions in the oil for 3 or 4 minutes. Add the red pepper flakes, sprinkle lightly with salt, cover, and cook, stirring occasionally, until the onions are soft, 5 to 8 minutes. Add the thyme, allspice, tomatoes, and beans, cover, and simmer for 10 to 15 minutes, stirring occasionally.</li>
</ul>
EOT;
		$recipe->set_instructions( $instructions );
		$this->assertEquals(
			array(
				array(
					'type'  => 'item',
					'name'  => 'Making the rice',
					'value' => 'Rinse and drain the rice. In a saucepan on medium-high heat, sauté the rice, turmeric, and salt in the oil for a minute or two, stirring constantly. Add the water and coconut milk, bring to a boil, cover, and reduce the heat to low. Simmer until the rice is tender and the liquid absorbed, 15 to 20 minutes.',
					'url'   => '#instruction-step-1',
				),
				array(
					'type'  => 'item',
					'name'  => 'Making the beans',
					'value' => 'While the rice is cooking, in a saucepan on medium heat, sauté the onions in the oil for 3 or 4 minutes. Add the red pepper flakes, sprinkle lightly with salt, cover, and cook, stirring occasionally, until the onions are soft, 5 to 8 minutes. Add the thyme, allspice, tomatoes, and beans, cover, and simmer for 10 to 15 minutes, stirring occasionally.',
					'url'   => '#instruction-step-2',
				),
			),
			Distribution_Metadata::parse_recipe_instruction_list( $instructions )
		);
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertEquals(
			array(
				array(
					'@type' => 'HowToStep',
					'text'  => 'Rinse and drain the rice. In a saucepan on medium-high heat, sauté the rice, turmeric, and salt in the oil for a minute or two, stirring constantly. Add the water and coconut milk, bring to a boil, cover, and reduce the heat to low. Simmer until the rice is tender and the liquid absorbed, 15 to 20 minutes.',
					'name'  => 'Making the rice',
					'url'   => '#instruction-step-1',
				),
				array(
					'@type' => 'HowToStep',
					'text'  => 'While the rice is cooking, in a saucepan on medium heat, sauté the onions in the oil for 3 or 4 minutes. Add the red pepper flakes, sprinkle lightly with salt, cover, and cook, stirring occasionally, until the onions are soft, 5 to 8 minutes. Add the thyme, allspice, tomatoes, and beans, cover, and simmer for 10 to 15 minutes, stirring occasionally.',
					'name'  => 'Making the beans',
					'url'   => '#instruction-step-2',
				),
			),
			$schema['recipeInstructions']
		);
	}

	/**
	 * Ensures expected behavior when parsing instruction complexity.
	 */
	public function test_parse_recipe_instruction_complex_one() {
		$recipe       = $this->create_mock_recipe();
		$instructions = <<<EOT
<h4>To begin</h4>
<ol>
<li><strong>Make Batter:</strong> Mix the flour, cornstarch, salt, and paprika. Set aside half of the flour mixture in a small bowl. Mix the other half of the flour mixture with 3/4 cup cold water to form a loose batter.</li>
</ol>
<h4>And this second part</h4>
<ol>
<li>
<p><strong>Make Sauce:</strong> Whisk all sauce ingredients together. Set aside.</p>
</li>
<li>
<p><strong>Fry Chicken:</strong> Heat oil over medium heat until a drop of water sizzles across the top. Dredge the chicken pieces in the dry flour mixture, then dip into the batter. Let excess batter drip off. Add battered chicken pieces to the oil. Fry for 2-3 minutes on each side. Remove from the pan and drain on paper towel lined plate. Drain out any excess oil from the pan. (SEE NOTES FOR AN EASY SHORTCUT ON THIS STEP.)</p>
</li>
<li>
<p><strong>Finish:</strong> Put chicken back in the pan. Add sauce and cashews. Stir for about 1 minute, until coated and you can start to smell the garlic. Done! Serve with rice and maybe some roasted or steamed broccoli. SO GOOD!</p>
</li>
</ol>
EOT;
		$recipe->set_instructions( $instructions );
		$this->assertEquals(
			array(
				array(
					'type'  => 'heading',
					'value' => 'To begin',
				),
				array(
					'type'  => 'item',
					'value' => 'Mix the flour, cornstarch, salt, and paprika. Set aside half of the flour mixture in a small bowl. Mix the other half of the flour mixture with 3/4 cup cold water to form a loose batter.',
					'name'  => 'Make Batter',
					'url'   => '#instruction-step-1',
				),
				array(
					'type'  => 'heading',
					'value' => 'And this second part',
				),
				array(
					'type'  => 'item',
					'value' => 'Whisk all sauce ingredients together. Set aside.',
					'name'  => 'Make Sauce',
					'url'   => '#instruction-step-2',
				),
				array(
					'type'  => 'item',
					'value' => 'Heat oil over medium heat until a drop of water sizzles across the top. Dredge the chicken pieces in the dry flour mixture, then dip into the batter. Let excess batter drip off. Add battered chicken pieces to the oil. Fry for 2-3 minutes on each side. Remove from the pan and drain on paper towel lined plate. Drain out any excess oil from the pan. (SEE NOTES FOR AN EASY SHORTCUT ON THIS STEP.)',
					'name'  => 'Fry Chicken',
					'url'   => '#instruction-step-3',
				),
				array(
					'type'  => 'item',
					'value' => 'Put chicken back in the pan. Add sauce and cashews. Stir for about 1 minute, until coated and you can start to smell the garlic. Done! Serve with rice and maybe some roasted or steamed broccoli. SO GOOD!',
					'name'  => 'Finish',
					'url'   => '#instruction-step-4',
				),
			),
			Distribution_Metadata::parse_recipe_instruction_list( $instructions )
		);
	}

	/**
	 * Ensures expected behavior when parsing instruction complexity.
	 */
	public function test_parse_recipe_instruction_complex_two() {
		$recipe       = $this->create_mock_recipe();
		$instructions = <<<EOT
<ol>
<li class="instruction"><strong>Prep:</strong> Preheat the oven to 350°F. Line a large rimmed baking sheet with parchment paper.</li>
<li class="instruction"><strong>Mix dry ingredients: </strong>In a medium bowl, combine the coconut sugar and eggs and whisk until smooth. Whisk in the vanilla and salt until blended.</li>
<li class="instruction"><strong>Mix all ingredients:</strong> Add the peanut butter and whisk until the dough is smooth and the peanut butter has completely blended into the batter (you might need to switch to a big spoon toward the end, which you’ll need for mixing in the chocolate chips anyway). Add the chocolate chips and stir until the chocolate chips are evenly incorporated.</li>
<li class="instruction"><strong>Bake:</strong> For cookies that stay perfectly mounded, chill the dough for 15 minutes before proceeding. Use a 1-tablespoon cookie dough scoop or two spoons to scoop heaping tablespoons of dough onto the prepared baking sheets, leaving 1 to 2 inches of space around each one. Chill any remaining dough while you bake the first batch.</li>
</ol>
EOT;
		$recipe->set_instructions( $instructions );
		$this->assertEquals(
			array(
				array(
					'type'  => 'item',
					'value' => 'Preheat the oven to 350°F. Line a large rimmed baking sheet with parchment paper.',
					'name'  => 'Prep',
					'url'   => '#instruction-step-1',
				),
				array(
					'type'  => 'item',
					'value' => 'In a medium bowl, combine the coconut sugar and eggs and whisk until smooth. Whisk in the vanilla and salt until blended.',
					'name'  => 'Mix dry ingredients',
					'url'   => '#instruction-step-2',
				),
				array(
					'type'  => 'item',
					'value' => 'Add the peanut butter and whisk until the dough is smooth and the peanut butter has completely blended into the batter (you might need to switch to a big spoon toward the end, which you’ll need for mixing in the chocolate chips anyway). Add the chocolate chips and stir until the chocolate chips are evenly incorporated.',
					'name'  => 'Mix all ingredients',
					'url'   => '#instruction-step-3',
				),
				array(
					'type'  => 'item',
					'value' => 'For cookies that stay perfectly mounded, chill the dough for 15 minutes before proceeding. Use a 1-tablespoon cookie dough scoop or two spoons to scoop heaping tablespoons of dough onto the prepared baking sheets, leaving 1 to 2 inches of space around each one. Chill any remaining dough while you bake the first batch.',
					'name'  => 'Bake',
					'url'   => '#instruction-step-4',
				),
			),
			Distribution_Metadata::parse_recipe_instruction_list( $instructions )
		);
	}

	/**
	 * Ensures expected behavior when parsing instruction complexity.
	 */
	public function test_parse_recipe_instruction_complex_three() {
		$recipe       = $this->create_mock_recipe();
		$instructions = <<<EOT
<ol>
<li><b>Prep:</b> Preheat the oven to 475 degrees (broil setting).</li>
<li><strong>Instant Pot: </strong>Place all chicken ingredients in the Instant Pot. Cook on high pressure for 10 minutes (20 if frozen). Release the pressure and shred chicken directly in pot.</li>
<li><strong>Broil</strong>: Transfer shredded chicken to a baking sheet lined with foil. Broil for 10-15 minutes until browned.</li>
<li><strong>Sauce: </strong>Pulse jalapeño ranch ingredients in a food processor until smooth. Toss some of the dressing with finely shredded cabbage to make a slaw.</li>
<li><strong>Done:</strong> Serve chicken in warm tortillas with slaw and an extra drizzle of jalapeño ranch, slices of avocado, extra pineapple, cilantro, red onion, etc.</li>
</ol>
<p>&nbsp;</p>
EOT;
		$recipe->set_instructions( $instructions );
		$this->assertEquals(
			array(
				array(
					'type'  => 'item',
					'value' => 'Preheat the oven to 475 degrees (broil setting).',
					'name'  => 'Prep',
					'url'   => '#instruction-step-1',
				),
				array(
					'type'  => 'item',
					'value' => 'Place all chicken ingredients in the Instant Pot. Cook on high pressure for 10 minutes (20 if frozen). Release the pressure and shred chicken directly in pot.',
					'name'  => 'Instant Pot',
					'url'   => '#instruction-step-2',
				),
				array(
					'type'  => 'item',
					'value' => 'Transfer shredded chicken to a baking sheet lined with foil. Broil for 10-15 minutes until browned.',
					'name'  => 'Broil',
					'url'   => '#instruction-step-3',
				),
				array(
					'type'  => 'item',
					'value' => 'Pulse jalapeño ranch ingredients in a food processor until smooth. Toss some of the dressing with finely shredded cabbage to make a slaw.',
					'name'  => 'Sauce',
					'url'   => '#instruction-step-4',
				),
				array(
					'type'  => 'item',
					'value' => 'Serve chicken in warm tortillas with slaw and an extra drizzle of jalapeño ranch, slices of avocado, extra pineapple, cilantro, red onion, etc.',
					'name'  => 'Done',
					'url'   => '#instruction-step-5',
				),
			),
			Distribution_Metadata::parse_recipe_instruction_list( $instructions )
		);
	}

	/**
	 * Ensures expected behavior when parsing instruction list.
	 */
	public function test_parse_recipe_instruction_unstructured_list() {
		$this->prime_basic_test_post();
		$recipe       = $this->create_mock_recipe();
		$permalink    = get_permalink( $this->post_id );
		$instructions = <<<EOT
1. <strong>Making the rice:</strong> Rinse and drain the rice. In a saucepan on medium-high heat, sauté the rice, turmeric, and salt in the oil for a minute or two, stirring constantly. Add the water and coconut milk, bring to a boil, cover, and reduce the heat to low. Simmer until the rice is tender and the liquid absorbed, 15 to 20 minutes.

2. <strong>Making the beans</strong>: While the rice is cooking, in a saucepan on medium heat, sauté the onions in the oil for 3 or 4 minutes. Add the red pepper flakes, sprinkle lightly with salt, cover, and cook, stirring occasionally, until the onions are soft, 5 to 8 minutes. Add the thyme, allspice, tomatoes, and beans, cover, and simmer for 10 to 15 minutes, stirring occasionally.
EOT;
		$recipe->set_instructions( $instructions );
		$this->assertEquals(
			array(
				array(
					'type'  => 'item',
					'name'  => 'Making the rice',
					'value' => 'Rinse and drain the rice. In a saucepan on medium-high heat, sauté the rice, turmeric, and salt in the oil for a minute or two, stirring constantly. Add the water and coconut milk, bring to a boil, cover, and reduce the heat to low. Simmer until the rice is tender and the liquid absorbed, 15 to 20 minutes.',
					'url'   => '#instruction-step-1',
				),
				array(
					'type'  => 'item',
					'name'  => 'Making the beans',
					'value' => 'While the rice is cooking, in a saucepan on medium heat, sauté the onions in the oil for 3 or 4 minutes. Add the red pepper flakes, sprinkle lightly with salt, cover, and cook, stirring occasionally, until the onions are soft, 5 to 8 minutes. Add the thyme, allspice, tomatoes, and beans, cover, and simmer for 10 to 15 minutes, stirring occasionally.',
					'url'   => '#instruction-step-2',
				),
			),
			Distribution_Metadata::parse_recipe_instruction_list( $instructions )
		);
		$schema = Distribution_Metadata::enrich_google_schema_with_post_permalink(
			Distribution_Metadata::get_google_schema_for_recipe( $recipe ),
			get_queried_object()
		);
		$this->assertEquals(
			array(
				array(
					'@type' => 'HowToStep',
					'text'  => 'Rinse and drain the rice. In a saucepan on medium-high heat, sauté the rice, turmeric, and salt in the oil for a minute or two, stirring constantly. Add the water and coconut milk, bring to a boil, cover, and reduce the heat to low. Simmer until the rice is tender and the liquid absorbed, 15 to 20 minutes.',
					'name'  => 'Making the rice',
					'url'   => $permalink . '#instruction-step-1',
				),
				array(
					'@type' => 'HowToStep',
					'text'  => 'While the rice is cooking, in a saucepan on medium heat, sauté the onions in the oil for 3 or 4 minutes. Add the red pepper flakes, sprinkle lightly with salt, cover, and cook, stirring occasionally, until the onions are soft, 5 to 8 minutes. Add the thyme, allspice, tomatoes, and beans, cover, and simmer for 10 to 15 minutes, stirring occasionally.',
					'name'  => 'Making the beans',
					'url'   => $permalink . '#instruction-step-2',
				),
			),
			$schema['recipeInstructions']
		);
	}

	/**
	 * Ensures expected behavior for parsing a simple recipe component list without structured markup.
	 */
	public function test_parse_recipe_component_list_simple_without_markup() {
		// Needs more modern oEmbed.
		$this->skip_if_less_than_wp_version( '4.9' );
		$file          = __DIR__ . '/data/canola.jpg';
		$attachment_id = $this->create_upload_object( $file );
		$image_html    = get_image_send_to_editor( $attachment_id, '', '', 'none', '', false, 'full' );
		$image_src     = wp_get_attachment_image_src( $attachment_id, 'full' );

		$instructions = <<<EOT
First, rinse and drain the rice. In a saucepan on medium-high heat, sauté the rice, turmeric, and salt in the oil for a minute or two, stirring constantly.

{$image_html}

Next, add the water and coconut milk, bring to a boil, cover, and reduce the heat to low. Simmer until the rice is tender and the liquid absorbed, 15 to 20 minutes.

While the rice is cooking, in a saucepan on medium heat, sauté the onions in the oil for 3 or 4 minutes.

https://vimeo.com/261212868

Then, add the red pepper flakes, sprinkle lightly with salt, cover, and cook, stirring occasionally, until the onions are soft, 5 to 8 minutes.

Lastly, add the thyme, allspice, tomatoes, and beans, cover, and simmer for 10 to 15 minutes, stirring occasionally.
EOT;
		$this->assertEquals(
			array(
				array(
					'type'         => 'item',
					'value'        => 'First, rinse and drain the rice. In a saucepan on medium-high heat, sauté the rice, turmeric, and salt in the oil for a minute or two, stirring constantly.',
					'url'          => '#instruction-step-1',
					'image_src'    => $image_src[0],
					'image_width'  => $image_src[1],
					'image_height' => $image_src[2],
				),
				array(
					'type'  => 'item',
					'value' => 'Next, add the water and coconut milk, bring to a boil, cover, and reduce the heat to low. Simmer until the rice is tender and the liquid absorbed, 15 to 20 minutes.',
					'url'   => '#instruction-step-2',
				),
				array(
					'type'      => 'item',
					'value'     => 'While the rice is cooking, in a saucepan on medium heat, sauté the onions in the oil for 3 or 4 minutes.',
					'video_url' => 'https://vimeo.com/261212868',
					'url'       => '#instruction-step-3',
				),
				array(
					'type'  => 'item',
					'value' => 'Then, add the red pepper flakes, sprinkle lightly with salt, cover, and cook, stirring occasionally, until the onions are soft, 5 to 8 minutes.',
					'url'   => '#instruction-step-5',
				),
				array(
					'type'  => 'item',
					'value' => 'Lastly, add the thyme, allspice, tomatoes, and beans, cover, and simmer for 10 to 15 minutes, stirring occasionally.',
					'url'   => '#instruction-step-6',
				),
			),
			Distribution_Metadata::parse_recipe_instruction_list( $instructions )
		);
	}

	/**
	 * Ensures expected behavior for parsing a simple recipe component list with numbers denoted.
	 */
	public function test_parse_recipe_component_list_simple_with_numbers() {
		// Needs more modern oEmbed.
		$this->skip_if_less_than_wp_version( '4.9' );
		$file          = __DIR__ . '/data/canola.jpg';
		$attachment_id = $this->create_upload_object( $file );
		$image_html    = get_image_send_to_editor( $attachment_id, '', '', 'none', '', false, 'full' );
		$image_src     = wp_get_attachment_image_src( $attachment_id, 'full' );

		$instructions = <<<EOT
This is some text that introduces the instructions.

1. First, rinse and drain the rice. In a saucepan on medium-high heat, sauté the rice, turmeric, and salt in the oil for a minute or two, stirring constantly.

Next, add the water and coconut milk, bring to a boil, cover, and reduce the heat to low. Simmer until the rice is tender and the liquid absorbed, 15 to 20 minutes.

{$image_html}

2. While the rice is cooking, in a saucepan on medium heat, sauté the onions in the oil for 3 or 4 minutes.

Then, add the red pepper flakes, sprinkle lightly with salt, cover, and cook, stirring occasionally, until the onions are soft, 5 to 8 minutes.

https://vimeo.com/261212868

Lastly, add the thyme, allspice, tomatoes, and beans, cover, and simmer for 10 to 15 minutes, stirring occasionally.
EOT;
		$this->assertEquals(
			array(
				array(
					'type'  => 'item',
					'value' => 'This is some text that introduces the instructions.',
					'url'   => '#instruction-step-1',
				),
				array(
					'type'         => 'item',
					'value'        => "First, rinse and drain the rice. In a saucepan on medium-high heat, sauté the rice, turmeric, and salt in the oil for a minute or two, stirring constantly.\nNext, add the water and coconut milk, bring to a boil, cover, and reduce the heat to low. Simmer until the rice is tender and the liquid absorbed, 15 to 20 minutes.",
					'url'          => '#instruction-step-2',
					'image_src'    => $image_src[0],
					'image_width'  => $image_src[1],
					'image_height' => $image_src[2],
				),
				array(
					'type'      => 'item',
					'value'     => "While the rice is cooking, in a saucepan on medium heat, sauté the onions in the oil for 3 or 4 minutes.\nThen, add the red pepper flakes, sprinkle lightly with salt, cover, and cook, stirring occasionally, until the onions are soft, 5 to 8 minutes.\n\nLastly, add the thyme, allspice, tomatoes, and beans, cover, and simmer for 10 to 15 minutes, stirring occasionally.",
					'url'       => '#instruction-step-3',
					'video_url' => 'https://vimeo.com/261212868',
				),
			),
			Distribution_Metadata::parse_recipe_instruction_list( $instructions )
		);
	}

	/**
	 * Ensures expected behavior for parsing a simple recipe component list with numbers denoted
	 * and image above the list item.
	 */
	public function test_parse_recipe_component_list_simple_with_numbers_and_top_image() {
		// Needs more modern oEmbed.
		$this->skip_if_less_than_wp_version( '4.9' );
		$file          = __DIR__ . '/data/canola.jpg';
		$attachment_id = $this->create_upload_object( $file );
		$image_html    = get_image_send_to_editor( $attachment_id, '', '', 'none', '', false, 'full' );
		$image_src     = wp_get_attachment_image_src( $attachment_id, 'full' );

		$instructions = <<<EOT
{$image_html}

1. First, rinse and drain the rice. In a saucepan on medium-high heat, sauté the rice, turmeric, and salt in the oil for a minute or two, stirring constantly.

Next, add the water and coconut milk, bring to a boil, cover, and reduce the heat to low. Simmer until the rice is tender and the liquid absorbed, 15 to 20 minutes.

https://vimeo.com/261212868

2. While the rice is cooking, in a saucepan on medium heat, sauté the onions in the oil for 3 or 4 minutes.

Then, add the red pepper flakes, sprinkle lightly with salt, cover, and cook, stirring occasionally, until the onions are soft, 5 to 8 minutes.

Lastly, add the thyme, allspice, tomatoes, and beans, cover, and simmer for 10 to 15 minutes, stirring occasionally.
EOT;
		$this->assertEquals(
			array(
				array(
					'type'         => 'item',
					'value'        => "First, rinse and drain the rice. In a saucepan on medium-high heat, sauté the rice, turmeric, and salt in the oil for a minute or two, stirring constantly.\nNext, add the water and coconut milk, bring to a boil, cover, and reduce the heat to low. Simmer until the rice is tender and the liquid absorbed, 15 to 20 minutes.",
					'url'          => '#instruction-step-2',
					'image_src'    => $image_src[0],
					'image_width'  => $image_src[1],
					'image_height' => $image_src[2],
				),
				array(
					'type'      => 'item',
					'value'     => "While the rice is cooking, in a saucepan on medium heat, sauté the onions in the oil for 3 or 4 minutes.\nThen, add the red pepper flakes, sprinkle lightly with salt, cover, and cook, stirring occasionally, until the onions are soft, 5 to 8 minutes.\nLastly, add the thyme, allspice, tomatoes, and beans, cover, and simmer for 10 to 15 minutes, stirring occasionally.",
					'url'       => '#instruction-step-3',
					'video_url' => 'https://vimeo.com/261212868',
				),
			),
			Distribution_Metadata::parse_recipe_instruction_list( $instructions )
		);
	}

	/**
	 * Ensures expected behavior for total time override feature.
	 */
	public function test_total_time_override() {
		$recipe = $this->create_mock_recipe();
		$recipe->set_prep_time( '21' );
		$recipe->set_cook_time( '15' );
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertEquals( 'PT36M', $schema['totalTime'] );
		$recipe->set_total_time( '40 minutes' );
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertEquals( 'PT40M', $schema['totalTime'] );
	}

	/**
	 * @covers Distribution_Metadata::is_empty()
	 */
	public function test_is_empty() {
		$tests = array(
			array(
				'string'   => '&nbsp;',
				'expected' => true,
			),
			array(
				'string'   => ' ',
				'expected' => true,
			),
			array(
				'string'   => '',
				'expected' => true,
			),
			array(
				'string'   => '0',
				'expected' => false,
			),
			array(
				'string'   => "\u{00a0}",
				'expected' => true,
			),
			array(
				'string'   => ' &nbsp; ',
				'expected' => true,
			),
		);

		foreach ( $tests as $test ) {
			$this->assertEquals( $test['expected'], Distribution_Metadata::is_empty( $test['string'] ), $test['string'] . ' is incorrect for is_empty()' );
		}
	}
}
