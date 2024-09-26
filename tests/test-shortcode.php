<?php
/**
 * Tests behavior of the Shortcodes class.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Content_Model;
use Tasty_Recipes\Shortcodes;

/**
 * Tests behavior of the Shortcodes class.
 */
class Test_Shortcode extends Tasty_Recipes_Testcase {

	/**
	 * Ensures template renders as expected.
	 */
	public function test_rendered_template() {
		$this->prime_basic_test_post();
		$recipe = $this->create_mock_recipe_with_image();

		$rendered = Shortcodes::render_tasty_recipe_shortcode(
			array(
				'id'        => $recipe->get_id(),
				'className' => 'foobar custom-class',
			)
		);
		$dom      = $this->get_dom( $rendered );
		$this->assertStringContainsString( 'class="tasty-recipes tasty-recipes-' . $recipe->get_id() . ' tasty-recipes-display tasty-recipes-has-image foobar custom-class"', $rendered );
		$this->assertStringContainsString( 'West Indian Beans &#038; Coconut Rice', $rendered );
		$this->assertStringContainsString(
			'Moosewood Restaurant Simple Suppers',
			qp( $dom, 'span.tasty-recipes-author-name' )->text()
		);
		$this->assertStringContainsString(
			'15 minutes',
			qp( $dom, 'span.tasty-recipes-prep-time' )->text()
		);
		$this->assertStringContainsString(
			'1 hour 20 minutes',
			qp( $dom, 'span.tasty-recipes-cook-time' )->text()
		);
		$this->assertStringContainsString(
			'1 hour 35 minutes',
			qp( $dom, 'span.tasty-recipes-total-time' )->text()
		);
		$this->assertStringContainsString(
			'Main',
			qp( $dom, 'span.tasty-recipes-category' )->text()
		);
		$this->assertStringContainsString(
			'Caribbean',
			qp( $dom, 'span.tasty-recipes-cuisine' )->text()
		);
		$this->assertStringContainsString(
			'Cooking',
			qp( $dom, 'span.tasty-recipes-method' )->text()
		);
		$this->assertStringContainsString( '<p>It&#8217;s so tasty</p>', $rendered );
		$this->assertStringContainsString( 'For The Red Beans:', $rendered );
		$this->assertStringContainsString( '<p>How to make the rice:</p>', $rendered );
		$this->assertStringContainsString(
			'Rinse and drain the rice.',
			qp( $dom, 'p#instruction-step-1' )->text()
		);
		$this->assertStringContainsString(
			'Notes',
			qp( $dom, 'h3' )->eq( 2 )->text()
		);
		$this->assertStringContainsString( '<p>Make sure to eat it all!</p>', $rendered );
	}

	/**
	 * Ensure template renders with an author link.
	 */
	public function test_rendered_template_author_link() {
		$this->prime_basic_test_post();
		$recipe = $this->create_mock_recipe_with_image();
		update_option( Tasty_Recipes::DEFAULT_AUTHOR_LINK_OPTION, 'https://pinchofyum.com' );
		$rendered = Shortcodes::render_tasty_recipe_shortcode( array( 'id' => $recipe->get_id() ) );
		$this->assertStringContainsString( '<a data-tasty-recipes-customization="detail-value-color.color" class="tasty-recipes-author-name" href="https://pinchofyum.com">Moosewood Restaurant Simple Suppers</a>', $rendered );
		$rendered = Shortcodes::render_tasty_recipe_shortcode(
			array(
				'id'          => $recipe->get_id(),
				'author_link' => 'https://apple.com',
			)
		);
		$this->assertStringContainsString( '<a data-tasty-recipes-customization="detail-value-color.color" class="tasty-recipes-author-name" href="https://apple.com">Moosewood Restaurant Simple Suppers</a>', $rendered );
	}

	/**
	 * Ensures print template renders as expected.
	 */
	public function test_rendered_template_print() {
		$this->prime_basic_test_post();
		$recipe = $this->create_mock_recipe();
		// Mock print request.
		$GLOBALS['wp_query']->query_vars['print'] = $recipe->get_id();
		$rendered                                 = Shortcodes::render_tasty_recipe_shortcode( array( 'id' => $recipe->get_id() ) );
		$this->assertStringContainsString( 'class="tasty-recipes tasty-recipes-' . $recipe->get_id() . ' tasty-recipes-print tasty-recipes-no-image"', $rendered );
		$this->assertStringContainsString( 'onclick="window.print();event.preventDefault();"', $rendered );
	}

	/**
	 * Ensures aggregate ratings render as expected.
	 */
	public function test_rendered_template_aggregate_ratings() {
		$this->prime_basic_test_post();
		$recipe   = $this->create_mock_recipe_with_ratings();
		$rendered = Shortcodes::render_tasty_recipe_shortcode( array( 'id' => $recipe->get_id() ) );
		$this->assertStringContainsString( '<span data-tasty-recipes-customization="detail-label-color.color" class="rating-label">', $rendered );
		$this->assertStringContainsString( '<span class="average">2.8</span>', $rendered );
		$this->assertStringContainsString( '<span class="count">5</span>', $rendered );
	}

	/**
	 * Recipe with a Vimeo embed should render Vimeo markup.
	 */
	public function test_rendered_vimeo_embed() {
		$this->prime_basic_test_post();
		$recipe   = $this->create_mock_recipe_with_vimeo_url();
		$expected = <<<EOT
<div class="tasty-recipe-video-embed" id="tasty-recipe-video-embed-{$recipe->get_id()}">
		<div class="tasty-recipe-responsive-iframe-container
EOT;
		$rendered = Shortcodes::render_tasty_recipe_shortcode( array( 'id' => $recipe->get_id() ) );
		$this->assertStringContainsString( $expected, $rendered );
		$this->assertStringContainsString( '<iframe class="fitvidsignore" src="https://player.vimeo.com/video/261212868?app_id=122963"   frameborder="0" title="One City: Many Futures / Ryan Hashagen, Gwen Shaw, &amp; Ben Chaney" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>', $rendered );
	}

	/**
	 * Ensures expected behavior of get_shortcode_for_recipe().
	 */
	public function test_get_shortcode_for_recipe() {
		$recipe = $this->create_mock_recipe();
		$this->assertEquals( '[tasty-recipe id="' . $recipe->get_id() . '"]', Shortcodes::get_shortcode_for_recipe( $recipe ) );
	}

	/**
	 * Ensures videos are auto-embedded on the frontend, but not in print.
	 */
	public function test_embed_video_frontend_not_print() {
		// Needs more modern oEmbed.
		$this->skip_if_less_than_wp_version( '4.9' );
		$instructions = <<<EOT
<ol>
	<li>First, make the chicken.
https://vimeo.com/261212868</li>
</ol>
EOT;
		$this->prime_basic_test_post();
		$recipe = $this->create_mock_recipe();
		$recipe->set_instructions( $instructions );
		// Should appear on the frontend.
		$rendered = Shortcodes::render_tasty_recipe_shortcode( array( 'id' => $recipe->get_id() ) );
		$this->assertStringContainsString( '<iframe ', $rendered );
		$this->assertStringContainsString( 'player.vimeo.com', $rendered );
		$this->assertStringContainsString( 'tasty-recipes-video-toggle', $rendered );
		// Shouldn't appear when printing.
		$GLOBALS['wp_query']->query_vars['print'] = $recipe->get_id();
		$rendered                                 = Shortcodes::render_tasty_recipe_shortcode( array( 'id' => $recipe->get_id() ) );
		$this->assertStringNotContainsString( '<iframe ', $rendered );
		$this->assertStringNotContainsString( 'player.vimeo.com', $rendered );
		$this->assertStringContainsString( '<li id="instruction-step-1">First, make the chicken.</li>', $rendered );
	}

	/**
	 * Ensures video control settings are applied to Vimeo videos.
	 */
	public function test_video_settings_applied_instructions_vimeo() {
		$this->skip_if_less_than_wp_version( '4.9' );
		$this->prime_basic_test_post();
		$recipe       = $this->create_mock_recipe();
		$instructions = <<<EOT
<ol>
	<li>First, make the chicken.
https://vimeo.com/261212868</li>
</ol>
EOT;
		$recipe->set_instructions( $instructions );
		$dom = $this->get_dom( Shortcodes::render_tasty_recipe_shortcode( array( 'id' => $recipe->get_id() ) ) );
		$this->assertEquals(
			'https://player.vimeo.com/video/261212868?app_id=122963',
			qp( $dom, 'iframe' )->eq( 0 )->attr( 'src' )
		);
		// Only autoplay.
		$recipe->set_instructions_video_settings( 'autoplay' );
		$dom = $this->get_dom( Shortcodes::render_tasty_recipe_shortcode( array( 'id' => $recipe->get_id() ) ) );
		$this->assertEquals(
			'https://player.vimeo.com/video/261212868?app_id=122963&autoplay=1&muted=1&autopause=0',
			qp( $dom, 'iframe' )->eq( 0 )->attr( 'src' )
		);
		// Autoplay, loop, and hide controls.
		$recipe->set_instructions_video_settings( 'autoplay loop hide-controls' );
		$dom = $this->get_dom( Shortcodes::render_tasty_recipe_shortcode( array( 'id' => $recipe->get_id() ) ) );
		$this->assertEquals(
			'https://player.vimeo.com/video/261212868?app_id=122963&autoplay=1&muted=1&autopause=0&loop=1&controls=0',
			qp( $dom, 'iframe' )->eq( 0 )->attr( 'src' )
		);
	}

	/**
	 * Ensures video control settings are applied to YouTube videos.
	 */
	public function test_video_settings_applied_instructions_youtube() {
		// Needs more modern oEmbed.
		$this->skip_if_less_than_wp_version( '4.9' );
		$this->prime_basic_test_post();
		$recipe       = $this->create_mock_recipe();
		$instructions = <<<EOT
<ol>
	<li>First, make the chicken.
https://www.youtube.com/watch?v=GRtWNZwgGn8</li>
</ol>
EOT;
		$recipe->set_instructions( $instructions );
		$dom = $this->get_dom( Shortcodes::render_tasty_recipe_shortcode( array( 'id' => $recipe->get_id() ) ) );
		$this->assertEquals(
			'https://www.youtube.com/embed/GRtWNZwgGn8?feature=oembed',
			qp( $dom, 'iframe' )->eq( 0 )->attr( 'src' )
		);
		// Only autoplay.
		$recipe->set_instructions_video_settings( 'autoplay' );
		$dom = $this->get_dom( Shortcodes::render_tasty_recipe_shortcode( array( 'id' => $recipe->get_id() ) ) );
		$this->assertEquals(
			'https://www.youtube.com/embed/GRtWNZwgGn8?feature=oembed&autoplay=1',
			qp( $dom, 'iframe' )->eq( 0 )->attr( 'src' )
		);
		// Autoplay, loop, and hide controls.
		$recipe->set_instructions_video_settings( 'autoplay loop hide-controls' );
		$dom = $this->get_dom( Shortcodes::render_tasty_recipe_shortcode( array( 'id' => $recipe->get_id() ) ) );
		$this->assertEquals(
			'https://www.youtube.com/embed/GRtWNZwgGn8?feature=oembed&autoplay=1&loop=1&controls=0',
			qp( $dom, 'iframe' )->eq( 0 )->attr( 'src' )
		);
	}

	/**
	 * Ensures the expected behavior of our advanced oEmbed.
	 */
	public function test_advanced_oembed() {
		// Needs more modern oEmbed.
		$this->skip_if_less_than_wp_version( '4.9' );
		$instructions = <<<EOT
<ol>
	<li>First, make the chicken.
https://vimeo.com/261212868</li>
</ol>
EOT;
		$this->assertStringContainsString( '<iframe', Content_Model::autoembed_advanced( $instructions ) );
		$instructions = <<<EOT
<ol>
<li class="instruction">https://vimeo.com/261212868<br />Preheat the oven to 350 degrees. Mash the sweet potatoes and add the melted butter, milk, sugar, vanilla, beaten eggs, and a pinch of salt. Stir until incorporated. Pour into a shallow baking dish or a cast iron skillet.</li>
</ol>
EOT;
		$this->assertStringContainsString( '<iframe', Content_Model::autoembed_advanced( $instructions ) );
		$instructions = <<<EOT
<ol>
<li class="instruction">Preheat the oven to 350 degrees. Mash the sweet potatoes and add the melted butter, milk, sugar, vanilla, beaten eggs, and a pinch of salt. Stir until incorporated. Pour into a shallow baking dish or a cast iron skillet.<br />https://vimeo.com/261212868</li>
</ol>
EOT;
		$this->assertStringContainsString( '<iframe', Content_Model::autoembed_advanced( $instructions ) );
		$instructions = <<<EOT
<ol>
<li class="instruction">Preheat the oven to 350 degrees. Mash the sweet potatoes and add the melted butter, milk, sugar, vanilla, beaten eggs, and a pinch of salt. Stir until incorporated. Pour into a shallow baking dish or a cast iron skillet.<p>https://vimeo.com/261212868</p></li>
</ol>
EOT;
		$this->assertStringContainsString( '<iframe', Content_Model::autoembed_advanced( $instructions ) );
	}

	/**
	 * Ensures Jump to Recipe link is rendered.
	 */
	public function test_render_jump_to_recipe() {
		global $post;

		$this->prime_basic_test_post();
		$recipe   = $this->create_mock_recipe();
		$post     = get_post( $this->post_id );
		$expected = '<a class="tasty-recipes-jump-link tasty-recipes-scrollto" href="#tasty-recipes-';
		update_option( Tasty_Recipes::QUICK_LINKS_OPTION, 'both' );
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_content' => Shortcodes::get_shortcode_for_recipe( $recipe ),
			)
		);

		// Doesn't render Quick Links on the homepage.
		$this->go_to( home_url( '/' ) );
		$actual = Shortcodes::filter_the_content_late( $post->post_content );
		$this->assertStringNotContainsString( $expected, $actual );

		// Quick Links should render on the individual post.
		$this->go_to( get_permalink( $this->post_id ) );
		$actual = Shortcodes::filter_the_content_late( $post->post_content );
		$this->assertStringContainsString( $expected, $actual );
	}

	/**
	 * Ensures Jump to Recipe link is rendered.
	 */
	public function test_render_jump_to_recipe_shortcode() {
		global $post;

		$this->prime_basic_test_post();
		$recipe   = $this->create_mock_recipe();
		$post     = get_post( $this->post_id );
		$expected = '<a class="tasty-recipes-jump-link tasty-recipes-scrollto" href="#tasty-recipes-';
		update_option( Tasty_Recipes::QUICK_LINKS_OPTION, '' );

		$recipe_shortcode = Shortcodes::get_shortcode_for_recipe( $recipe );
		$content          = <<<EOT
Nullam id dolor id nibh ultricies vehicula ut id elit. Maecenas faucibus mollis interdum. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Cras mattis consectetur purus sit amet fermentum. Cras mattis consectetur purus sit amet fermentum. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor.

Cras justo odio, dapibus ac facilisis in, egestas eget quam. Donec sed odio dui. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Curabitur blandit tempus porttitor.

[tasty-recipes-quick-links]

Nullam id dolor id nibh ultricies vehicula ut id elit. Maecenas faucibus mollis interdum. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Cras mattis consectetur purus sit amet fermentum. Cras mattis consectetur purus sit amet fermentum. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor.

Cras justo odio, dapibus ac facilisis in, egestas eget quam. Donec sed odio dui. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Curabitur blandit tempus porttitor.

{$recipe_shortcode}
EOT;
		wp_update_post(
			array(
				'ID'           => $this->post_id,
				'post_content' => $content,
			)
		);

		// Quick Links should render on the individual post.
		$this->go_to( get_permalink( $this->post_id ) );
		$actual = apply_filters( 'the_content', $post->post_content );
		$this->assertStringContainsString( $expected, $actual );
	}

	/**
	 * Ensure the filter_tasty_recipes_recipe_card_output method works as expected.
	 */
	public function test_filter_tasty_recipes_recipe_card_output() {
		update_option(
			Tasty_Recipes::CUSTOMIZATION_OPTION,
			array(
				'primary_color'   => '#FFF',
				'secondary_color' => '#EEE',
			)
		);
		// No existing style attribute with one customization.
		$this->assertEquals(
			'<div id="tasty-recipe-123" style="background-color: #FFF !important;" data-tasty-recipes-customization="primary-color.background-color">',
			Shortcodes::filter_tasty_recipes_recipe_card_output( '<div id="tasty-recipe-123" data-tasty-recipes-customization="primary-color.background-color">' )
		);
		// No existing style attribute with two customizations.
		$this->assertEquals(
			'<div id="tasty-recipe-123" style="border-color: #EEE !important; background-color: #FFF !important;" data-tasty-recipes-customization="secondary-color.border-color primary-color.background-color">',
			Shortcodes::filter_tasty_recipes_recipe_card_output( '<div id="tasty-recipe-123" data-tasty-recipes-customization="secondary-color.border-color primary-color.background-color">' )
		);
		// Existing style attribute with one customization.
		$this->assertEquals(
			'<div id="tasty-recipe-123" style="color: red; background-color: #FFF !important;" data-tasty-recipes-customization="primary-color.background-color">',
			Shortcodes::filter_tasty_recipes_recipe_card_output( '<div id="tasty-recipe-123" style="color: red;" data-tasty-recipes-customization="primary-color.background-color">' )
		);
		// Existing style attribute with two customizations.
		$this->assertEquals(
			'<div id="tasty-recipe-123" style="color: red; border-color: #EEE !important; background-color: #FFF !important;" data-tasty-recipes-customization="secondary-color.border-color primary-color.background-color">',
			Shortcodes::filter_tasty_recipes_recipe_card_output( '<div id="tasty-recipe-123" style="color: red;" data-tasty-recipes-customization="secondary-color.border-color primary-color.background-color">' )
		);
	}
}
