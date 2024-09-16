<?php
/**
 * Tests the main Tasty_Recipes controller class.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Content_Model;
use Tasty_Recipes\Distribution_Metadata;
use Tasty_Recipes\Objects\Recipe;
use Tasty_Recipes\Utils;

/**
 * Tests the main Tasty_Recipes controller class.
 */
class Test_Tasty_Recipes extends Tasty_Recipes_Testcase {

	/**
	 * Ensures the tasty_recipes() function yields a Tasty_Recipes instance.
	 */
	public function test_tasty_recipes_function() {
		$this->assertInstanceOf( 'Tasty_Recipes', tasty_recipes() );
	}

	/**
	 * Ensures the 'tasty_recipe' post type is registered as expected.
	 */
	public function test_registered_post_type() {
		$obj = get_post_type_object( 'tasty_recipe' );
		$this->assertEquals( 'Recipes', $obj->label );
	}

	/**
	 * Ensures expected behavior for a post with both a shortcode and a recipe block.
	 */
	public function test_get_recipes_for_content_mixed() {
		$this->skip_if_less_than_wp_version( '5.0' );
		$content = <<<EOT
Some first text

[tasty-recipe id="232"]

Some more text

<!-- wp:wp-tasty/tasty-recipe {"id":33965,"lastUpdated":1532020114038} /-->
EOT;
		$this->assertEquals( array( 232, 33965 ), Tasty_Recipes::get_recipe_ids_from_content( $content ) );
	}

	/**
	 * Ensures expected behavior for disabling JSON+LD on a post.
	 */
	public function test_get_recipes_for_content_disable_json_ld() {
		$content = <<<EOT
Some first text

[tasty-recipe id="232" disable-json-ld]

Some more text

[tasty-recipe id="234"]
EOT;
		$this->assertEquals(
			array( 234 ),
			Tasty_Recipes::get_recipe_ids_from_content(
				$content,
				array(
					'disable-json-ld' => false,
				)
			)
		);
		$this->assertEquals(
			array( 232, 234 ),
			Tasty_Recipes::get_recipe_ids_from_content( $content )
		);
	}

	/**
	 * Ensures expected behavior for disabling JSON+LD on a post with blocks.
	 */
	public function test_get_recipes_for_content_disable_json_ld_blocks() {
		$this->skip_if_less_than_wp_version( '5.0' );
		$content = <<<EOT
Some first text

<!-- wp:wp-tasty/tasty-recipe {"id":33965,"lastUpdated":1532020114038,"disableJSON":true} /-->

Some more text

<!-- wp:wp-tasty/tasty-recipe {"id":234,"lastUpdated":1532020114038} /-->
EOT;
		$this->assertEquals(
			array( 234 ),
			Tasty_Recipes::get_recipe_ids_from_content(
				$content,
				array(
					'disable-json-ld' => false,
				)
			)
		);
		$this->assertEquals(
			array( 33965, 234 ),
			Tasty_Recipes::get_recipe_ids_from_content( $content )
		);
	}

	/**
	 * Ensures expected behavior parsing nested blocks.
	 */
	public function test_get_recipes_for_content_nested_blocks() {
		$this->skip_if_less_than_wp_version( '5.0' );
		$content = <<<EOT
<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"width":"100%"} -->
<div class="wp-block-column" style="flex-basis:100%"><!-- wp:gallery {"ids":[1888,1887,1886,1891],"linkTo":"none"} -->
<figure class="wp-block-gallery columns-3 is-cropped"><ul class="blocks-gallery-grid"><li class="blocks-gallery-item"><figure><img src="https://comfortandthyme.com/wp-content/uploads/2021/05/Raw-Salmon-Filets4-LR5-19-21-1-771x1024.jpg" alt="" data-id="1888" data-full-url="https://comfortandthyme.com/wp-content/uploads/2021/05/Raw-Salmon-Filets4-LR5-19-21-1.jpg" data-link="https://comfortandthyme.com/?attachment_id=1888" class="wp-image-1888"/><figcaption class="blocks-gallery-item__caption">Salmon filets</figcaption></figure></li><li class="blocks-gallery-item"><figure><img src="https://comfortandthyme.com/wp-content/uploads/2021/05/Pan-Searing-Salmon2-LR5-21-21-1024x768.jpg" alt="" data-id="1887" data-full-url="https://comfortandthyme.com/wp-content/uploads/2021/05/Pan-Searing-Salmon2-LR5-21-21.jpg" data-link="https://comfortandthyme.com/?attachment_id=1887" class="wp-image-1887"/><figcaption class="blocks-gallery-item__caption">Pan-Searing Salmon Filets</figcaption></figure></li><li class="blocks-gallery-item"><figure><img src="https://comfortandthyme.com/wp-content/uploads/2021/05/Crispy-Salmon1-LR5-21-21-1024x768.jpg" alt="" data-id="1886" data-full-url="https://comfortandthyme.com/wp-content/uploads/2021/05/Crispy-Salmon1-LR5-21-21.jpg" data-link="https://comfortandthyme.com/?attachment_id=1886" class="wp-image-1886"/><figcaption class="blocks-gallery-item__caption">Crispy Salmon Skin</figcaption></figure></li><li class="blocks-gallery-item"><figure><img src="https://comfortandthyme.com/wp-content/uploads/2021/05/Crispy-Pan-Roasted-Salmon8-LR5-21-21-1024x778.jpg" alt="" data-id="1891" data-full-url="https://comfortandthyme.com/wp-content/uploads/2021/05/Crispy-Pan-Roasted-Salmon8-LR5-21-21.jpg" data-link="https://comfortandthyme.com/?attachment_id=1891" class="wp-image-1891"/><figcaption class="blocks-gallery-item__caption">Crispy Pan-Roasted Salmon with a Parsley Dill Sauce</figcaption></figure></li></ul></figure>
<!-- /wp:gallery -->

<!-- wp:heading {"style":{"color":{"text":"#08551a"}},"fontSize":"large"} -->
<h2 class="has-text-color has-large-font-size" style="color:#08551a">Wild vs. Farmed Salmon</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul><li>Wild salmon are caught in the pacific ocean and the streams, lakes, and rivers of the Pacific Northwest. These beautiful fish have sustained the people of the Pacific Northwest for hundreds and probably thousands of years. </li><li>Loaded with omega-3, nutrients, and minerals, wild-caught salmon is the best choice if you like a full-bodied fish flavor. Here in the Pacific Northwest, we're a bit spoiled and have many different varieties available throughout the year. King, Chinook, Coho, and Sockeye are the most popular. Wild salmon is normally darker in color and has a bolder flavor than farmed salmon due to its nutrient-rich diet.</li><li>Atlantic salmon is farmed, and lighter in color than wild-caught salmon. It has a much more delicate flavor profile and is usually available in grocery stores year-round.</li><li>Both wild and farmed salmon are good options. My suggestion is to select whichever salmon is freshest, available, and suits your tastebuds. They're all good.</li></ul>
<!-- /wp:list -->

<!-- wp:wp-tasty/tasty-recipe {"id":1922,"lastUpdated":1621830044177} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->
EOT;
		$this->assertEquals(
			array( 1922 ),
			Tasty_Recipes::get_recipe_ids_from_content( $content )
		);
	}

	/**
	 * Ensures expected behavior for fetching a Tasty Recipe shortcode from a page builder post.
	 */
	public function test_get_recipes_for_content_page_builder() {
		add_shortcode( 'fusion_builder_container', '__return_empty_string' );
		$content = <<<EOT
[fusion_builder_container hundred_percent="no" hundred_percent_height="no" hundred_percent_height_scroll="no" hundred_percent_height_center_content="yes" equal_height_columns="no" menu_anchor="" hide_on_mobile="small-visibility,medium-visibility,large-visibility" status="published" publish_date="" class="" id="" background_color="" background_image="" background_position="center center" background_repeat="no-repeat" fade="no" background_parallax="none" enable_mobile="no" parallax_speed="0.3" video_mp4="" video_webm="" video_ogv="" video_url="" video_aspect_ratio="16:9" video_loop="yes" video_mute="yes" video_preview_image="" border_size="" border_color="" border_style="solid" margin_top="" margin_bottom="" padding_top="" padding_right="" padding_bottom="" padding_left=""][fusion_builder_row][fusion_builder_column type="1_2" layout="1_2" spacing="" center_content="no" link="" target="_self" min_height="" hide_on_mobile="small-visibility,medium-visibility,large-visibility" class="" id="" background_color="" background_image="" background_image_id="" background_position="left top" background_repeat="no-repeat" hover_type="none" border_size="0" border_color="" border_style="solid" border_position="all" border_radius="" box_shadow="no" dimension_box_shadow="" box_shadow_blur="0" box_shadow_spread="0" box_shadow_color="" box_shadow_style="" padding_top="" padding_right="" padding_bottom="" padding_left="" margin_top="" margin_bottom="" animation_type="" animation_direction="left" animation_speed="0.3" animation_offset="" last="no"][fusion_imageframe image_id="209077|full" max_width="" style_type="" blur="" stylecolor="" hover_type="none" bordersize="" bordercolor="" borderradius="" align="none" lightbox="no" gallery_id="" lightbox_image="" lightbox_image_id="" alt="" link="" linktarget="_self" hide_on_mobile="small-visibility,medium-visibility,large-visibility" class="" id="" animation_type="" animation_direction="left" animation_speed="0.3" animation_offset="" filter_hue="0" filter_saturation="100" filter_brightness="100" filter_contrast="100" filter_invert="0" filter_sepia="0" filter_opacity="100" filter_blur="0" filter_hue_hover="0" filter_saturation_hover="100" filter_brightness_hover="100" filter_contrast_hover="100" filter_invert_hover="0" filter_sepia_hover="0" filter_opacity_hover="100" filter_blur_hover="0"]https://www.ooliveoil.com/wp-content/uploads/2020/05/BitterGirl-MoscowMule_web.jpg[/fusion_imageframe][fusion_separator style_type="none" hide_on_mobile="small-visibility,medium-visibility,large-visibility" class="" id="" sep_color="" top_margin="" bottom_margin="20px" border_size="" icon="" icon_circle="" icon_circle_color="" width="" alignment="center" /][fusion_title title_type="text" rotation_effect="bounceIn" display_time="1200" highlight_effect="circle" loop_animation="off" highlight_width="9" highlight_top_margin="0" before_text="" rotation_text="" highlight_text="" after_text="" hide_on_mobile="small-visibility,medium-visibility,large-visibility" class="" id="" content_align="center" size="2" font_size="" animated_font_size="" fusion_font_family_title_font="" fusion_font_subset_title_font="" fusion_font_variant_title_font="" line_height="" letter_spacing="" margin_top="" margin_bottom="" margin_top_mobile="" margin_bottom_mobile="" text_color="" animated_text_color="" highlight_color="" style_type="default" sep_color="" animation_type="" animation_direction="left" animation_speed="0.3" animation_offset=""]

O White Balsamic Vinegar

[/fusion_title][fusion_text columns="" column_min_width="" column_spacing="" rule_style="default" rule_size="" rule_color="" hide_on_mobile="small-visibility,medium-visibility,large-visibility" class="" id="" animation_type="" animation_direction="left" animation_speed="0.3" animation_offset=""][add_to_cart id="1871"][/fusion_text][/fusion_builder_column][fusion_builder_column type="1_2" layout="1_2" spacing="" center_content="no" link="" target="_self" min_height="" hide_on_mobile="small-visibility,medium-visibility,large-visibility" class="" id="" background_color="" background_image="" background_image_id="" background_position="left top" background_repeat="no-repeat" hover_type="none" border_size="0" border_color="" border_style="solid" border_position="all" border_radius="" box_shadow="no" dimension_box_shadow="" box_shadow_blur="0" box_shadow_spread="0" box_shadow_color="" box_shadow_style="" padding_top="" padding_right="" padding_bottom="" padding_left="" margin_top="" margin_bottom="" animation_type="" animation_direction="left" animation_speed="0.3" animation_offset="" last="no"][fusion_text columns="" column_min_width="" column_spacing="" rule_style="default" rule_size="" rule_color="" hide_on_mobile="small-visibility,medium-visibility,large-visibility" class="" id="" animation_type="" animation_direction="left" animation_speed="0.3" animation_offset=""]

[tasty-recipe id="209200" /]

[/fusion_text][/fusion_builder_column][/fusion_builder_row][/fusion_builder_container]
EOT;
		$this->assertEquals(
			array( 209200 ),
			Tasty_Recipes::get_recipe_ids_from_content(
				$content
			)
		);
	}

	/**
	 * Ensures expected behavior for fetching a Tasty Recipe shortcode from a Divy page builder post.
	 */
	public function test_get_recipes_for_divy_page_builder() {
		$this->assertEquals(
			array( 209610 ),
			Tasty_Recipes::get_recipe_ids_from_content(
				file_get_contents( __DIR__ . '/data/divy-post-1.txt' )
			)
		);
	}

	/**
	 * Ensures expected behavior when there's no recipe.
	 */
	public function test_has_recipe_false() {
		$this->prime_basic_test_post();
		$content = <<<EOT
Some first text
EOT;
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_content' => $content,
			)
		);
		$this->assertFalse( Tasty_Recipes::has_recipe( $this->post_id ) );
	}

	/**
	 * Ensures expected behavior when a post has a recipe shortcode.
	 */
	public function test_has_recipe_shortcode() {
		$this->prime_basic_test_post();
		$content = <<<EOT
Some first text

[tasty-recipe id="232"]
EOT;
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_content' => $content,
			)
		);
		$this->assertTrue( Tasty_Recipes::has_recipe( $this->post_id ) );
	}

	/**
	 * Ensures expected behavior when a post has a recipe block.
	 */
	public function test_has_recipe_block() {
		$this->skip_if_less_than_wp_version( '5.0' );
		$this->prime_basic_test_post();
		$content = <<<EOT
Some first text

<!-- wp:wp-tasty/tasty-recipe {"id":33965,"lastUpdated":1532020114038} /-->
EOT;
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_content' => $content,
			)
		);
		$this->assertTrue( Tasty_Recipes::has_recipe( $this->post_id ) );
	}

	/**
	 * Ensures expected behavior of cron applying unit conversion to a recipe.
	 */
	public function test_cron_apply_unit_conversion() {
		$recipe = $this->create_mock_recipe();
		// No API request expected yet.
		update_option( Tasty_Recipes::UNIT_CONVERSION_OPTION, true );
		do_action( 'tasty_recipes_apply_unit_conversion' );
		$this->assertNotAPIRequest( 'nutrifox.com' );
		$this->assertEmpty( get_post_meta( $recipe->get_id(), 'nutrifox_conversion_response', true ) );
		$this->assertEmpty( get_post_meta( $recipe->get_id(), 'nutrifox_conversion_error', true ) );
		// Run unit conversion cron.
		update_option( Tasty_Recipes::UNIT_CONVERSION_OPTION, true );
		update_option( Tasty_Recipes::AUTOMATIC_UNIT_CONVERSION_OPTION, 1 );
		do_action( 'tasty_recipes_apply_unit_conversion' );
		$this->assertExpectedAPIRequest( 'nutrifox.com' );
		$this->assertNotEmpty( get_post_meta( $recipe->get_id(), 'nutrifox_conversion_response', true ) );
		$this->assertEmpty( get_post_meta( $recipe->get_id(), 'nutrifox_conversion_error', true ) );
		// No API request on the second run.
		$this->reset_api_tracking_vars();
		do_action( 'tasty_recipes_apply_unit_conversion' );
		$this->assertNotAPIRequest( 'nutrifox.com' );
		$this->assertNotEmpty( get_post_meta( $recipe->get_id(), 'nutrifox_conversion_response', true ) );
		$this->assertEmpty( get_post_meta( $recipe->get_id(), 'nutrifox_conversion_error', true ) );
	}

	/**
	 * Ensures expected behavior of cron enriching a recipe's YouTube description.
	 */
	public function test_cron_enrich_recipe_youtube() {
		$recipe = $this->create_mock_recipe_with_youtube_url();
		// Need to monkey with the data because the description is there by default.
		$response_data = get_post_meta( $recipe->get_id(), 'video_url_response', true );
		unset( $response_data->description );
		unset( $response_data->duration );
		update_post_meta( $recipe->get_id(), 'video_url_response', $response_data );
		// Schema shouldn't have the duration right now.
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertEquals( 'Boeing 747-400 Cockpit - Breakoff Landing Amsterdam Schiphol', $schema['video']['name'] );
		$this->assertFalse( isset( $schema['video']['duration'] ) );
		// After running cron, duration should now be set.
		do_action( 'tasty_recipes_enrich_youtube_embeds' );
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertEquals( 'PT13M', $schema['video']['duration'] );
	}

	/**
	 * Ensures expected behavior of cron enriching a post's YouTube description.
	 */
	public function test_cron_enrich_post_oembed_youtube() {
		global $wpdb;
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
		// Primes the initial oembed cache.
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		Distribution_Metadata::enrich_google_schema_with_post_video( $schema, $recipe, $post );
		// Need to monkey with the data because the description is there by default.
		$meta_key      = $wpdb->get_var( $wpdb->prepare( "SELECT meta_key FROM {$wpdb->postmeta} WHERE meta_key LIKE %s AND post_id=%d", '_tr_oembed_%', $post->ID ) );
		$response_data = get_post_meta( $post->ID, $meta_key, true );
		unset( $response_data->description );
		unset( $response_data->duration );
		update_post_meta( $post->ID, $meta_key, $response_data );
		// Schema shouldn't have the duration right now.
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$this->assertFalse( isset( $schema['video'] ) );
		$schema = Distribution_Metadata::enrich_google_schema_with_post_video( $schema, $recipe, $post );
		$this->assertEquals( 'Boeing 747-400 Cockpit - Breakoff Landing Amsterdam Schiphol', $schema['video']['name'] );
		$this->assertFalse( isset( $schema['video']['duration'] ) );
		// After running cron, duration should now be set.
		do_action( 'tasty_recipes_enrich_youtube_embeds' );
		$schema = Distribution_Metadata::get_google_schema_for_recipe( $recipe );
		$schema = Distribution_Metadata::enrich_google_schema_with_post_video( $schema, $recipe, $post );
		$this->assertEquals( 'Boeing 747-400 Cockpit - Breakoff Landing Amsterdam Schiphol', $schema['video']['name'] );
		$this->assertEquals( 'PT13M', $schema['video']['duration'] );
	}

	/**
	 * Ensures the print links are generated as expected.
	 */
	public function test_tasty_recipes_get_print_url() {
		$this->setup_permalink_structure();
		$this->prime_basic_test_post();
		$recipe = $this->create_mock_recipe();
		// Without a query string.
		$this->assertStringContainsString(
			'/print/' . $recipe->get_id(),
			tasty_recipes_get_print_url( $this->post_id, $recipe->get_id() )
		);
		// With a query string.
		add_filter(
			'post_link',
			function( $link ) {
				return add_query_arg( 'lang', 'de', $link );
			}
		);
		$this->assertStringContainsString(
			'/print/' . $recipe->get_id() . '?lang=de',
			tasty_recipes_get_print_url( $this->post_id, $recipe->get_id() )
		);
	}

	/**
	 * Ensure data is fetched from Nutrifox when the recipe is initially created.
	 */
	public function test_create_recipe_generate_nutrifox_conversion() {
		$ingredients = <<<EOT
For The Red Beans:

1 1/2cups diced onions
1 tablespoon vegetable oil
1/2 teaspoon red pepper flakes or 1 fresh chile, minced
1/2 teaspoon dried thyme
Salt
1/4 teaspoon allspice
One 28-ounce can of diced tomatoes
One 28-ounce can of red kidney beans, drained
Chopped scallions and/or cilantro

For The Coconut Rice:
1 1/2 cups white rice
1/2 teaspoon turmeric
1/2 teaspoon salt
1 teaspoon vegetable oil
1 1/2 cups water
3/4 cup coconut milk
EOT;
		update_option( Tasty_Recipes::UNIT_CONVERSION_OPTION, true );
		$recipe = Recipe::create();
		$this->assertNotAPIRequest( 'nutrifox.com' );
		$recipe->set_ingredients( $ingredients );
		$this->assertExpectedAPIRequest( 'nutrifox.com' );
		$this->assertNotEmpty( get_post_meta( $recipe->get_id(), 'nutrifox_conversion_response', true ) );
		$this->assertEmpty( get_post_meta( $recipe->get_id(), 'nutrifox_conversion_error', true ) );
	}

	/**
	 * Ensure data is fetched from Nutrifox when ingredients are updated, but only when they're updated.
	 */
	public function test_update_ingredients_generate_nutrifox_conversion() {
		update_option( Tasty_Recipes::UNIT_CONVERSION_OPTION, true );
		$recipe = $this->create_mock_recipe();
		// Expect the API request the first time.
		$this->assertExpectedAPIRequest( 'nutrifox.com' );
		$this->assertNotEmpty( get_post_meta( $recipe->get_id(), 'nutrifox_conversion_response', true ) );
		$this->assertEmpty( get_post_meta( $recipe->get_id(), 'nutrifox_conversion_error', true ) );
		// Same ingredients shouldn't trigger a new API request but data should still be there.
		$this->reset_api_tracking_vars();
		$recipe->set_ingredients( $recipe->get_ingredients() );
		$this->assertNotAPIRequest( 'nutrifox.com' );
		$this->assertNotEmpty( get_post_meta( $recipe->get_id(), 'nutrifox_conversion_response', true ) );
		$this->assertEmpty( get_post_meta( $recipe->get_id(), 'nutrifox_conversion_error', true ) );
		// If disabled, updating should delete the post meta to avoid out of date.
		update_option( Tasty_Recipes::UNIT_CONVERSION_OPTION, false );
		$this->reset_api_tracking_vars();
	}

}
