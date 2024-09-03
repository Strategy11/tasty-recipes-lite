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
			'<span data-amount="2">2</span>',
			qp( $dom, 'span.tasty-recipes-yield' )->html()
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
		$this->assertStringContainsString( '<span data-amount="1" data-unit="tablespoon">1 tablespoon</span> vegetable oil', $rendered );
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
		$this->assertStringContainsString( '<span data-tasty-recipes-customization="body-color.color" class="tasty-recipes-serving-size">2 cups</span>', $rendered );
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
	 * Ensures expected behavior of process_ingredients_annotate_with_spans().
	 */
	public function test_process_ingredients_annotate_with_spans_list() {
		$original = <<<EOT
<h4>For the Pork:</h4>
<ul>
<li>1 tablespoon oil</li>
<li>1 shallot</li>
<li>1 clove garlic</li>
<li>1 jalapeno, ribs and seeds removed</li>
<li>2 teaspoons fish sauce</li>
<li>18 ounces boneless pork loin, sliced into thin strips</li>
<li>2 tablespoons sugar</li>
<li>2 tablespoons water</li>
</ul>
<h4>For the Pineapple Salsa</h4>
<ul>
<li>1 cup chopped pineapple</li>
<li>1 cup chopped cucumber</li>
<li>1/2 cup chopped cilantro</li>
<li>1/2 cup chopped red onion or shallot</li>
<li>a squeeze of lime juice</li>
<li>a pinch of salt</li>
<li>tortillas for serving</li>
<li>cilantro and lime for serving</li>
<li>chili sauce for topping – see notes</li>
</ul>
EOT;
		$expected = <<<EOT
<h4>For the Pork:</h4>
<ul>
<li><span data-amount="1" data-unit="tablespoon">1 tablespoon</span> oil</li>
<li><span data-amount="1">1</span> shallot</li>
<li><span data-amount="1">1</span> clove garlic</li>
<li><span data-amount="1">1</span> jalapeno, ribs and seeds removed</li>
<li><span data-amount="2" data-unit="teaspoon">2 teaspoons</span> fish sauce</li>
<li><span data-amount="18" data-unit="ounce">18 ounces</span> boneless pork loin, sliced into thin strips</li>
<li><span data-amount="2" data-unit="tablespoon">2 tablespoons</span> sugar</li>
<li><span data-amount="2" data-unit="tablespoon">2 tablespoons</span> water</li>
</ul>
<h4>For the Pineapple Salsa</h4>
<ul>
<li><span data-amount="1" data-unit="cup">1 cup</span> chopped pineapple</li>
<li><span data-amount="1" data-unit="cup">1 cup</span> chopped cucumber</li>
<li><span data-amount="0.5" data-unit="cup">1/2 cup</span> chopped cilantro</li>
<li><span data-amount="0.5" data-unit="cup">1/2 cup</span> chopped red onion or shallot</li>
<li data-has-non-numeric-amount>a squeeze of lime juice</li>
<li data-has-non-numeric-amount>a pinch of salt</li>
<li>tortillas for serving</li>
<li>cilantro and lime for serving</li>
<li>chili sauce for topping – see notes</li>
</ul>
EOT;
		$actual   = Shortcodes::process_ingredients_annotate_with_spans( $original );
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Ensures expected behavior of process_ingredients_annotate_with_spans().
	 */
	public function test_process_ingredients_annotate_with_spans_paragraphs() {
		$original = <<<EOT
<p>1 apple</p>
<p>1 orange</p>
<h2>This is a heading</h2>
<p>1/2 banana</p>
<p>2 mangoes</p>
<p>a squeeze of lime juice</p>
EOT;
		$expected = <<<EOT
<p><span data-amount="1">1</span> apple</p>
<p><span data-amount="1">1</span> orange</p>
<h2>This is a heading</h2>
<p><span data-amount="0.5">1/2</span> banana</p>
<p><span data-amount="2">2</span> mangoes</p>
<p><span data-has-non-numeric-amount>a squeeze of lime juice</span></p>
EOT;
		$actual   = Shortcodes::process_ingredients_annotate_with_spans( $original );
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Ensures expected behavior of process_ingredients_for_checkboxes().
	 */
	public function test_process_ingredients_for_checkboxes_list() {
		$original = <<<EOT
<h4>For the Pork:</h4>
<ul>
<li>1 tablespoon oil</li>
<li>1 shallot</li>
<li>1 clove garlic</li>
</ul>
EOT;
		$expected = <<<EOT
<h4>For the Pork:</h4>
<ul>
<li data-tr-ingredient-checkbox=""><span class="tr-ingredient-checkbox-container"><input type="checkbox" name="ingredient_checkbox_[A-Za-z0-9]+" id="ingredient_checkbox_[A-Za-z0-9]+" aria-label="1 tablespoon oil"><label for="ingredient_checkbox_[A-Za-z0-9]+"></label></span>1 tablespoon oil</li>
<li data-tr-ingredient-checkbox=""><span class="tr-ingredient-checkbox-container"><input type="checkbox" name="ingredient_checkbox_[A-Za-z0-9]+" id="ingredient_checkbox_[A-Za-z0-9]+" aria-label="1 shallot"><label for="ingredient_checkbox_[A-Za-z0-9]+"></label></span>1 shallot</li>
<li data-tr-ingredient-checkbox=""><span class="tr-ingredient-checkbox-container"><input type="checkbox" name="ingredient_checkbox_[A-Za-z0-9]+" id="ingredient_checkbox_[A-Za-z0-9]+" aria-label="1 clove garlic"><label for="ingredient_checkbox_[A-Za-z0-9]+"></label></span>1 clove garlic</li>
</ul>
EOT;
		$actual   = Shortcodes::process_ingredients_for_checkboxes( $original );
		$this->assertMatchesRegularExpression( '~' . $expected . '~i', $actual );
	}

	/**
	 * Ensures expected behavior of process_ingredients_for_checkboxes().
	 */
	public function test_process_ingredients_for_checkboxes_paragraphs() {
		$original = <<<EOT
<p>1 apple</p>
<p>1 orange</p>
<h2>This is a heading</h2>
<p>1/2 banana</p>
<p>2 mangoes</p>
EOT;
		$expected = <<<EOT
<p data-tr-ingredient-checkbox=""><span class="tr-ingredient-checkbox-container"><input type="checkbox" name="ingredient_checkbox_[A-Za-z0-9]+" id="ingredient_checkbox_[A-Za-z0-9]+" aria-label="1 apple"><label for="ingredient_checkbox_[A-Za-z0-9]+"></label></span>1 apple</p>
<p data-tr-ingredient-checkbox=""><span class="tr-ingredient-checkbox-container"><input type="checkbox" name="ingredient_checkbox_[A-Za-z0-9]+" id="ingredient_checkbox_[A-Za-z0-9]+" aria-label="1 orange"><label for="ingredient_checkbox_[A-Za-z0-9]+"></label></span>1 orange</p>
<h2>This is a heading</h2>
<p data-tr-ingredient-checkbox=""><span class="tr-ingredient-checkbox-container"><input type="checkbox" name="ingredient_checkbox_[A-Za-z0-9]+" id="ingredient_checkbox_[A-Za-z0-9]+" aria-label="1/2 banana"><label for="ingredient_checkbox_[A-Za-z0-9]+"></label></span>1/2 banana</p>
<p data-tr-ingredient-checkbox=""><span class="tr-ingredient-checkbox-container"><input type="checkbox" name="ingredient_checkbox_[A-Za-z0-9]+" id="ingredient_checkbox_[A-Za-z0-9]+" aria-label="2 mangoes"><label for="ingredient_checkbox_[A-Za-z0-9]+"></label></span>2 mangoes</p>
EOT;
		$actual   = Shortcodes::process_ingredients_for_checkboxes( $original );
		$this->assertMatchesRegularExpression( '~' . $expected . '~i', $actual );
	}

	/**
	 * Ensures expected behavior of process_ingredients_for_checkboxes().
	 */
	public function test_process_ingredients_for_checkboxes_paragraphs_two() {
		$original = <<<EOT
<p><span data-amount="0.5" data-unit="cup">1/2 cup</span> (<span data-amount="100" data-unit="g">100 g</span>) granulated sugar</p>
<p><span data-amount="0.25" data-unit="cup">1/4 cup</span> (<span data-amount="60">60</span> ml) water</p>
EOT;
		$expected = <<<EOT
<p data-tr-ingredient-checkbox=""><span class="tr-ingredient-checkbox-container"><input type="checkbox" name="ingredient_checkbox_[A-Za-z0-9]+" id="ingredient_checkbox_[A-Za-z0-9]+" aria-label="1/2 cup \(100 g\) granulated sugar"><label for="ingredient_checkbox_[A-Za-z0-9]+"></label></span><span data-amount="0.5" data-unit="cup">1\/2 cup</span> \(<span data-amount="100" data-unit="g">100 g</span>\) granulated sugar</p>
<p data-tr-ingredient-checkbox=""><span class="tr-ingredient-checkbox-container"><input type="checkbox" name="ingredient_checkbox_[A-Za-z0-9]+" id="ingredient_checkbox_[A-Za-z0-9]+" aria-label="1/4 cup \(60 ml\) water"><label for="ingredient_checkbox_[A-Za-z0-9]+"></label></span><span data-amount="0.25" data-unit="cup">1\/4 cup</span> \(<span data-amount="60">60</span> ml\) water</p>
EOT;
		$actual   = Shortcodes::process_ingredients_for_checkboxes( $original );
		$this->assertMatchesRegularExpression( '~' . $expected . '~i', $actual );
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

	/**
	 * Ensure filter_tasty_recipes_recipe_card_output strips out unused footer fields on the Bold template.
	 */
	public function test_filter_tasty_recipes_recipe_card_output_remove_unused_footer_bold() {
		$this->prime_basic_test_post();
		$recipe = $this->create_mock_recipe_with_image();
		update_option( Tasty_Recipes::TEMPLATE_OPTION, 'bold' );
		$dom = $this->get_dom( Shortcodes::render_tasty_recipe_shortcode( array( 'id' => $recipe->get_id() ) ) );
		// Default settings.
		$this->assertEquals(
			'svg-instagram',
			qp( $dom, '.tasty-recipes-entry-footer svg' )->eq( 0 )->attr( 'class' )
		);
		$this->assertEquals(
			'Did you make this recipe?',
			qp( $dom, '.tasty-recipes-entry-footer .tasty-recipes-footer-copy h3' )->eq( 0 )->text()
		);
		$this->assertEquals(
			"Share a photo and tag us — we can't wait to see what you've made!",
			qp( $dom, '.tasty-recipes-entry-footer .tasty-recipes-footer-copy div' )->eq( 0 )->text()
		);
		// No description.
		update_option(
			Tasty_Recipes::CUSTOMIZATION_OPTION,
			array(
				'footer_social_platform' => 'instagram',
				'footer_heading'         => 'Did you make this recipe?',
				'footer_description'     => '',
			)
		);
		$dom = $this->get_dom( Shortcodes::render_tasty_recipe_shortcode( array( 'id' => $recipe->get_id() ) ) );
		$this->assertEquals(
			'svg-instagram',
			qp( $dom, '.tasty-recipes-entry-footer svg' )->eq( 0 )->attr( 'class' )
		);
		$this->assertEquals(
			'Did you make this recipe?',
			qp( $dom, '.tasty-recipes-entry-footer .tasty-recipes-footer-copy h3' )->eq( 0 )->text()
		);
		$this->assertEquals(
			0,
			qp( $dom, '.tasty-recipes-entry-footer .tasty-recipes-footer-copy div' )->length
		);
		// No heading nor description.
		update_option(
			Tasty_Recipes::CUSTOMIZATION_OPTION,
			array(
				'footer_social_platform' => 'instagram',
				'footer_heading'         => '',
				'footer_description'     => '',
			)
		);
		$dom = $this->get_dom( Shortcodes::render_tasty_recipe_shortcode( array( 'id' => $recipe->get_id() ) ) );
		$this->assertEquals(
			'svg-instagram',
			qp( $dom, '.tasty-recipes-entry-footer svg' )->eq( 0 )->attr( 'class' )
		);
		$this->assertEquals(
			0,
			qp( $dom, '.tasty-recipes-entry-footer .tasty-recipes-footer-copy' )->length
		);
		$this->assertEquals(
			0,
			qp( $dom, '.tasty-recipes-entry-footer .tasty-recipes-footer-copy h3' )->length
		);
		$this->assertEquals(
			0,
			qp( $dom, '.tasty-recipes-entry-footer .tasty-recipes-footer-copy div' )->length
		);
		// Heading and description but no image.
		update_option(
			Tasty_Recipes::CUSTOMIZATION_OPTION,
			array(
				'footer_social_platform' => '',
				'footer_heading'         => 'Did you make this recipe?',
				'footer_description'     => "<p>Share a photo and tag us — we can't wait to see what you've made!</p>",
			)
		);
		$dom = $this->get_dom( Shortcodes::render_tasty_recipe_shortcode( array( 'id' => $recipe->get_id() ) ) );
		$this->assertEquals(
			0,
			qp( $dom, '.tasty-recipes-entry-footer svg' )->length
		);
		$this->assertEquals(
			'Did you make this recipe?',
			qp( $dom, '.tasty-recipes-entry-footer .tasty-recipes-footer-copy h3' )->eq( 0 )->text()
		);
		$this->assertEquals(
			"Share a photo and tag us — we can't wait to see what you've made!",
			qp( $dom, '.tasty-recipes-entry-footer .tasty-recipes-footer-copy div' )->eq( 0 )->text()
		);
	}

	/**
	 * Ensure filter_tasty_recipes_recipe_card_output strips out unused footer fields for all templates.
	 */
	public function test_filter_tasty_recipes_recipe_card_output_remove_unused_footer_all() {
		$this->prime_basic_test_post();
		$recipe = $this->create_mock_recipe_with_image();
		// Everything empty.
		update_option(
			Tasty_Recipes::CUSTOMIZATION_OPTION,
			array(
				'footer_social_platform' => '',
				'footer_heading'         => '',
				'footer_description'     => '',
			)
		);
		foreach ( array_keys( Tasty_Recipes\Admin::TEMPLATE_OPTIONS ) as $i => $template ) {
			update_option( Tasty_Recipes::TEMPLATE_OPTION, $template );
			$dom = $this->get_dom( Shortcodes::render_tasty_recipe_shortcode( array( 'id' => $recipe->get_id() ) ) );
			$this->assertEquals(
				0,
				qp( $dom, '.tasty-recipes-entry-footer' )->length,
				sprintf( 'Failed for template "%s"', $template )
			);
		}
		$this->assertEquals( 7, $i + 1 );
	}
}
