<?php
/**
 * Test case for the Cookbook recipe converter.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Objects\Recipe;

/**
 * Test case for the Cookbook recipe converter.
 */
class Test_Cookbook_Converter extends Tasty_Recipes_Converter_Testcase {

	/**
	 * Seed SQL file to use with tests.
	 *
	 * @var string
	 */
	protected $sql_source = 'cookbook.sql';

	/**
	 * Conversion class to use with tests.
	 *
	 * @var string
	 */
	protected static $conversion_class = 'Tasty_Recipes\Converters\Cookbook';

	/**
	 * Recipe sample data to use for tests.
	 *
	 * @return array
	 */
	public function recipe_samples() {
		$recipe_1 = <<<EOT

This is kimchi. Are you guys on that bandwagon? Or are you closing your browser window?

<!--Cookbook Recipe 33771-->


<div class="cookbook-recipe" id="cookbook-recipe-33771" data-recipe-id="33771" itemscope="itemscope" itemtype="https://schema.org/Recipe">

	<div class="cookbook-extras"><div class="cookbook-image"><img width="200" height="300" src="http://tasty.handbuilt.co/wp-content/uploads/2017/11/Korean-BBQ-Beef-Tacos-200x300.jpg" class="attachment-medium size-medium" alt="" itemprop="image" srcset="http://tasty.handbuilt.co/wp-content/uploads/2017/11/Korean-BBQ-Beef-Tacos-200x300.jpg 200w, http://tasty.handbuilt.co/wp-content/uploads/2017/11/Korean-BBQ-Beef-Tacos-768x1152.jpg 768w, http://tasty.handbuilt.co/wp-content/uploads/2017/11/Korean-BBQ-Beef-Tacos-683x1024.jpg 683w, http://tasty.handbuilt.co/wp-content/uploads/2017/11/Korean-BBQ-Beef-Tacos-1200x1800.jpg 1200w, http://tasty.handbuilt.co/wp-content/uploads/2017/11/Korean-BBQ-Beef-Tacos.jpg 1392w" sizes="(max-width: 200px) 85vw, 200px" /></div><a class="cookbook-button-link cookbook-button-link-print-preview" href="http://tasty.handbuilt.co/?p=33769&#038;cookbook-print=33771" rel="nofollow" target="_blank">Print</a></div><h2 class="cookbook-name" itemprop="name">Instant Pot Korean Beef Tacos</h2><div class="cookbook-container cookbook-container-time"><p class="cookbook-details cookbook-details-prep-time"><span class="cookbook-element-title cookbook-element-title-prep-time">Prep</span> <time class="cookbook-element-wrapper cookbook-element-wrapper-prep-time" itemprop="prepTime" datetime="PT30M"><span class="cookbook-element-content cookbook-element-content-prep-time"><span class="cookbook-time-value cookbook-time-value-minutes">30</span> <span class="cookbook-time-label cookbook-time-label-minutes">mins</span></span></time></p><p class="cookbook-details cookbook-details-cook-time"><span class="cookbook-element-title cookbook-element-title-cook-time">Cook</span> <time class="cookbook-element-wrapper cookbook-element-wrapper-cook-time" itemprop="cookTime" datetime="PT20M"><span class="cookbook-element-content cookbook-element-content-cook-time"><span class="cookbook-time-value cookbook-time-value-minutes">20</span> <span class="cookbook-time-label cookbook-time-label-minutes">mins</span></span></time></p><p class="cookbook-details cookbook-details-inactive-time"><span class="cookbook-element-title cookbook-element-title-inactive-time">Inactive</span> <time class="cookbook-element-wrapper cookbook-element-wrapper-inactive-time"><span class="cookbook-element-content cookbook-element-content-inactive-time"><span class="cookbook-time-value cookbook-time-value-hours">1</span> <span class="cookbook-time-label cookbook-time-label-hours">hour</span></span></time></p><p class="cookbook-details cookbook-details-total-time"><span class="cookbook-element-title cookbook-element-title-total-time">Total</span> <time class="cookbook-element-wrapper cookbook-element-wrapper-total-time" itemprop="totalTime" datetime="PT1H50M"><span class="cookbook-element-content cookbook-element-content-total-time"><span class="cookbook-time-value cookbook-time-value-hours">1</span> <span class="cookbook-time-label cookbook-time-label-hours">hour</span>, <span class="cookbook-time-value cookbook-time-value-minutes">50</span> <span class="cookbook-time-label cookbook-time-label-minutes">mins</span></span></time></p></div><p class="cookbook-details cookbook-details-author" itemtype="https://schema.org/Person" itemscope="itemscope" itemprop="author"><span class="cookbook-element-title cookbook-element-title-author">Author</span> <span class="cookbook-element-content cookbook-author" itemprop="name">Lindsay Ostrom</span></p><p class="cookbook-details cookbook-details-servings"><span class="cookbook-element-title cookbook-element-title-servings">Yield</span> <span class="cookbook-element-content cookbook-element-content-servings-yield" itemprop="recipeYield"><span class="cookbook-servings">8</span> <span class="cookbook-servings-unit">tacos</span></span></p>
	<div class="cookbook-summary" itemprop="description"><p>Food truck fusion-style tacos, straight outta the Instant Pot! Flavorful garlic and ginger beef gets tucked into flour tortillas with kimchi, cilantro, peanuts, and spicy mayo. Oooo-ee!</p>
<p>A <a href="https://google.com">link</a>. <strong>Bold Text</strong>. <em>Italic Text</em>. </p>
<ul>
<li>An unordered list item.</li>
<li>And another one.</li>
</ul>
<ol>
<li>And an ordered list item.</li>
<li>And another one, for funzies.</li>
</ol>
<h4>A Heading!</h4>
</div><div class="cookbook-container cookbook-container-ingredients"><h3 class="cookbook-title cookbook-title-ingredients">Ingredients</h3><h4 class="cookbook-subtitle">For the Korean Beef:</h4><ul class="cookbook-ingredients-list"><li class="cookbook-ingredient" itemprop="recipeIngredient">1 14-ounce can pears, drained (fresh pears work, too)</li><li class="cookbook-ingredient" itemprop="recipeIngredient">1 two-inch knob of fresh ginger</li><li class="cookbook-ingredient" itemprop="recipeIngredient">4 cloves garlic</li><li class="cookbook-ingredient" itemprop="recipeIngredient">1/2 cup soy sauce</li><li class="cookbook-ingredient" itemprop="recipeIngredient">1/2 cup brown sugar</li><li class="cookbook-ingredient" itemprop="recipeIngredient">2 lbs. top sirloin (see notes)</li></ul><h4 class="cookbook-subtitle">For the Tacos:</h4><ul class="cookbook-ingredients-list"><li class="cookbook-ingredient" itemprop="recipeIngredient">chopped cilantro</li><li class="cookbook-ingredient" itemprop="recipeIngredient">chopped peanuts</li><li class="cookbook-ingredient" itemprop="recipeIngredient">kimchi !!!! or slaw of some <a href="https://www.google.com">sort </a></li><li class="cookbook-ingredient" itemprop="recipeIngredient">sriracha mayo or yum yum sauce</li><li class="cookbook-ingredient" itemprop="recipeIngredient">flour tortillas</li></ul></div><div class="cookbook-container cookbook-container-instructions"><h3 class="cookbook-title cookbook-title-instructions">Instructions</h3><ol class="cookbook-instructions-list"><li class="cookbook-instruction" itemprop="recipeInstructions"><strong>Make the sauce:</strong> Pulse pears, ginger, garlic, soy sauce, and brown sugar until mostly smooth. Reserve half for later use (see notes).</li><li class="cookbook-instruction" itemprop="recipeInstructions"><strong>Cook the meat:</strong> Trim any fat off the meat and cut into a few large chunks (it will shred, but cutting the meat helps it cook faster). Place in an Instant Pot and cover with half of the sauce. Cook on high pressure (manual) for 45 minutes. Release steam and shred the meat in the Instant Pot with two forks.</li><li class="cookbook-instruction" itemprop="recipeInstructions"><strong>Tacos:</strong> Fill each tortilla with beef, cilantro, peanuts, kimchi, and spicy mayo. Oh.My.Gosh. It’s so good.</li></ol></div>
	<div class="cookbook-container cookbook-container-notes"><h3 class="cookbook-title cookbook-title-notes">Notes</h3><div class="cookbook-notes"><p><strong>Slow Cooker:</strong> If you want to make this in the slow cooker, follow the same directions but cook the meat on high for 3-5 hours, or low for 6-8 hours.</p>
<p><strong>Stovetop:</strong> If you want to make this on the stovetop, I recommend slicing the meat thinly and following these directions for super quick and yummy beef.</p>
<p><strong>Cuts of Meat:</strong> Chuck roast can work, but I found that even with some trimming, it still had too much fat for my liking. Top sirloin is a little more spendy, but I love it in this recipe because there is less fat to work around and you really just get the awesomeness of the beef.</p>
<p><strong>Marinating:</strong> If you have time to marinate the meat, DO IT. It really enhances the flavor to of the meat to meat to let it all kinda hang out together with the sauce for a while. If you don’t have time, no biggie. I usually don’t have time either, and I’d still give it 5/5 for flavor.</p>
<p>As written, you’ll have double the sauce you need! So save the remaining sauce (fridge or freezer) for a super fast and easy Round Two of your Korean Beef Tacos. LUCKYYYY.</p>
<ul>
<li>What if I have a note in a list?</li>
<li>Another one!</li>
</ul>
</div></div><p class="cookbook-tag cookbook-tag-courses"><span class="cookbook-element-title cookbook-element-title-courses">Courses</span> <span class="cookbook-element-content cookbook-courses" itemprop="recipeCategory">Dinner</span></p><p class="cookbook-tag cookbook-tag-cuisine"><span class="cookbook-element-title cookbook-element-title-cuisine">Cuisine</span> <span class="cookbook-element-content cookbook-cuisine" itemprop="recipeCuisine">Korean, Mexican</span></p><div class="cookbook-nutrition" itemprop="nutrition" itemscope="itemscope" itemtype="https://schema.org/NutritionInformation"><div class="cookbook-nutrition-header"><h3 class="cookbook-title cookbook-title-nutrition">Nutrition Facts</h3><p class="cookbook-nutrition-serving-size-container"><span class="cookbook-nutrition-label cookbook-nutrition-label-serving-size">Serving Size</span> <span class="cookbook-nutrition-content cookbook-nutrition-content-serving-size" itemprop="servingSize">1 taco</span></p></div><table class="cookbook-nutrition-table"><thead class="cookbook-nutrition-table-head"><tr class="cookbook-nutrition-table-row cookbook-nutrition-table-row-amount"><th colspan="3" class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-head"><p class="cookbook-nutrition-table-item cookbook-nutrition-table-item-amount">Amount Per Serving</p></th></tr></thead><tbody class="cookbook-nutrition-table-body"><tr class="cookbook-nutrition-table-row cookbook-nutrition-table-row-calories"><th colspan="3" class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-head"><p class="cookbook-nutrition-table-item cookbook-nutrition-table-item-calories"><strong class="cookbook-nutrition-label cookbook-nutrition-label-calories">Calories</strong> <span class="cookbook-nutrition-content cookbook-nutrition-content-calories" itemprop="calories">227</span></p></th></tr><tr class="cookbook-nutrition-table-row cookbook-nutrition-table-row-thick"><td colspan="3" class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-small"><p class="cookbook-nutrition-table-item cookbook-nutrition-table-item-daily-value">% Daily Value</p></td></tr><tr class="cookbook-nutrition-table-row cookbook-nutrition-table-row-fat"><th colspan="2" class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-fat"><p class="cookbook-nutrition-table-item cookbook-nutrition-table-item-fat"><strong class="cookbook-nutrition-label cookbook-nutrition-label-fat">Total Fat</strong> <span class="cookbook-nutrition-content cookbook-nutrition-content-fat" itemprop="fatContent">4 g</span></p></th><td class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-fat-daily"><p class="cookbook-nutrition-table-item cookbook-nutrition-table-item-fat-daily">6%</p></td></tr><tr class="cookbook-nutrition-table-row cookbook-nutrition-table-row-saturated-fat"><td class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-blank"></td><th class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-saturated-fat"><p class="cookbook-nutrition-table-item cookbook-nutrition-table-item-saturated-fat"><span class="cookbook-nutrition-label cookbook-nutrition-label-saturated-fat">Saturated Fat</span> <span class="cookbook-nutrition-content cookbook-nutrition-content-saturated-fat" itemprop="saturatedFatContent">1 g</span></p></th><td class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-saturated-fat-daily"><p class="cookbook-nutrition-table-item cookbook-nutrition-table-item-saturated-fat-daily">5%</p></td></tr><tr class="cookbook-nutrition-table-row cookbook-nutrition-table-row-unsaturated-fat"><td class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-blank"></td><th class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-unsaturated-fat"><p class="cookbook-nutrition-table-item cookbook-nutrition-table-item-unsaturated-fat"><span class="cookbook-nutrition-label cookbook-nutrition-label-unsaturated-fat">Unsaturated Fat</span> <span class="cookbook-nutrition-content cookbook-nutrition-content-unsaturated-fat" itemprop="unsaturatedFatContent">1 g</span></p></th><td class="cookbook-nutrition-table-cell"></td></tr><tr class="cookbook-nutrition-table-row cookbook-nutrition-table-row-trans-fat"><td class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-blank"></td><th class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-trans-fat"><p class="cookbook-nutrition-table-item cookbook-nutrition-table-item-trans-fat"><span class="cookbook-nutrition-label cookbook-nutrition-label-trans-fat">Trans Fat</span> <span class="cookbook-nutrition-content cookbook-nutrition-content-trans-fat" itemprop="transFatContent">1 g</span></p></th><td class="cookbook-nutrition-table-cell"></td></tr><tr class="cookbook-nutrition-table-row cookbook-nutrition-table-row-cholesterol"><th colspan="2" class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-cholesterol"><p class="cookbook-nutrition-table-item cookbook-nutrition-table-item-cholesterol"><strong class="cookbook-nutrition-label cookbook-nutrition-label-cholesterol">Cholesterol</strong> <span class="cookbook-nutrition-content cookbook-nutrition-content-cholesterol" itemprop="cholesterolContent">67 mg</span></p></th><td class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-cholesterol-daily"><p class="cookbook-nutrition-table-item cookbook-nutrition-table-item-cholesterol-daily">22%</p></td></tr><tr class="cookbook-nutrition-table-row cookbook-nutrition-table-row-sodium"><th colspan="2" class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-sodium"><p class="cookbook-nutrition-table-item cookbook-nutrition-table-item-sodium"><strong class="cookbook-nutrition-label cookbook-nutrition-label-sodium">Sodium</strong> <span class="cookbook-nutrition-content cookbook-nutrition-content-sodium" itemprop="sodiumContent">486 mg</span></p></th><td class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-sodium-daily"><p class="cookbook-nutrition-table-item cookbook-nutrition-table-item-sodium-daily">20%</p></td></tr><tr class="cookbook-nutrition-table-row cookbook-nutrition-table-row-carbohydrates"><th colspan="2" class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-carbohydrates"><p class="cookbook-nutrition-table-item cookbook-nutrition-table-item-carbohydrates"><strong class="cookbook-nutrition-label cookbook-nutrition-label-carbohydrates">Total Carbohydrates</strong> <span class="cookbook-nutrition-content cookbook-nutrition-content-carbohydrates" itemprop="carbohydrateContent">20 g</span></p></th><td class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-carbohydrates-daily"><p class="cookbook-nutrition-table-item cookbook-nutrition-table-item-carbohydrates-daily">7%</p></td></tr><tr class="cookbook-nutrition-table-row cookbook-nutrition-table-row-fiber"><td class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-blank"></td><th class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-fiber"><p class="cookbook-nutrition-table-item cookbook-nutrition-table-item-fiber"><span class="cookbook-nutrition-label cookbook-nutrition-label-fiber">Dietary Fiber</span> <span class="cookbook-nutrition-content cookbook-nutrition-content-fiber" itemprop="fiberContent">3 g</span></p></th><td class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-fiber-daily"><p class="cookbook-nutrition-table-item cookbook-nutrition-table-item-fiber-daily">12%</p></td></tr><tr class="cookbook-nutrition-table-row cookbook-nutrition-table-row-sugar"><td class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-blank"></td><th class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-sugar"><p class="cookbook-nutrition-table-item cookbook-nutrition-table-item-sugar"><span class="cookbook-nutrition-label cookbook-nutrition-label-sugar">Sugars</span> <span class="cookbook-nutrition-content cookbook-nutrition-content-sugar" itemprop="sugarContent">17 g</span></p></th><td class="cookbook-nutrition-table-cell"></td></tr><tr class="cookbook-nutrition-table-row cookbook-nutrition-table-row-protein"><th colspan="2" class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-protein"><p class="cookbook-nutrition-table-item cookbook-nutrition-table-item-protein"><strong class="cookbook-nutrition-label cookbook-nutrition-label-protein">Protein</strong> <span class="cookbook-nutrition-content cookbook-nutrition-content-protein" itemprop="proteinContent">27 g</span></p></th><td class="cookbook-nutrition-table-cell cookbook-nutrition-table-cell-protein-daily"><p class="cookbook-nutrition-table-item cookbook-nutrition-table-item-protein-daily">54%</p></td></tr></tbody></table><p class="cookbook-nutrition-footnote">* Percent Daily Values are based on a 2,000 calorie diet. Your daily values may be higher or lower depending on your calorie needs.</p></div>
</div>


<!--End Cookbook Recipe-->
EOT;
		$recipe_2 = <<<EOT

<!--Cookbook Recipe 33912-->


<div class="cookbook-recipe" id="cookbook-recipe-33912" data-recipe-id="33912" itemscope="itemscope" itemtype="https://schema.org/Recipe">

	<div class="cookbook-extras"><a class="cookbook-button-link cookbook-button-link-print-preview" href="http://tasty.handbuilt.co/?p=33911&#038;cookbook-print=33912" rel="nofollow" target="_blank">Print</a></div><h2 class="cookbook-name" itemprop="name">Something with Nutrition Info</h2><div class="cookbook-container cookbook-container-time"><p class="cookbook-details cookbook-details-prep-time"><span class="cookbook-element-title cookbook-element-title-prep-time">Prep</span> <time class="cookbook-element-wrapper cookbook-element-wrapper-prep-time" itemprop="prepTime" datetime="PT20M"><span class="cookbook-element-content cookbook-element-content-prep-time"><span class="cookbook-time-value cookbook-time-value-minutes">20</span> <span class="cookbook-time-label cookbook-time-label-minutes">mins</span></span></time></p><p class="cookbook-details cookbook-details-cook-time"><span class="cookbook-element-title cookbook-element-title-cook-time">Cook</span> <time class="cookbook-element-wrapper cookbook-element-wrapper-cook-time" itemprop="cookTime" datetime="PT50M"><span class="cookbook-element-content cookbook-element-content-cook-time"><span class="cookbook-time-value cookbook-time-value-minutes">50</span> <span class="cookbook-time-label cookbook-time-label-minutes">mins</span></span></time></p><p class="cookbook-details cookbook-details-total-time"><span class="cookbook-element-title cookbook-element-title-total-time">Total</span> <time class="cookbook-element-wrapper cookbook-element-wrapper-total-time" itemprop="totalTime" datetime="PT1H10M"><span class="cookbook-element-content cookbook-element-content-total-time"><span class="cookbook-time-value cookbook-time-value-hours">1</span> <span class="cookbook-time-label cookbook-time-label-hours">hour</span>, <span class="cookbook-time-value cookbook-time-value-minutes">10</span> <span class="cookbook-time-label cookbook-time-label-minutes">mins</span></span></time></p></div><p class="cookbook-details cookbook-details-servings"><span class="cookbook-element-title cookbook-element-title-servings">Yield</span> <span class="cookbook-element-content cookbook-element-content-servings-yield" itemprop="recipeYield"><span class="cookbook-servings">2</span> <span class="cookbook-servings-unit">slices</span></span></p>
	<div class="cookbook-container cookbook-container-ingredients"><h3 class="cookbook-title cookbook-title-ingredients">Ingredients</h3><ul class="cookbook-ingredients-list"><li class="cookbook-ingredient" itemprop="recipeIngredient">1 apple</li><li class="cookbook-ingredient" itemprop="recipeIngredient">1 cup raspberries</li><li class="cookbook-ingredient" itemprop="recipeIngredient">1 pie crust</li></ul></div><div class="cookbook-container cookbook-container-instructions"><h3 class="cookbook-title cookbook-title-instructions">Instructions</h3><p class="cookbook-instruction-item">Make pie</p><p class="cookbook-instruction-item">eat pie</p></div>
	<p class="cookbook-tag cookbook-tag-courses"><span class="cookbook-element-title cookbook-element-title-courses">Courses</span> <span class="cookbook-element-content cookbook-courses" itemprop="recipeCategory">Dessert</span></p>
</div>


<!--End Cookbook Recipe-->
EOT;
		$recipe_3 = <<<EOT

[cookbook_recipe id="33912"]
EOT;
		return array(
			array(
				$recipe_1,
				array(
					'title'                       => 'Instant Pot Korean Beef Tacos',
					'author_name'                 => 'Lindsay Ostrom',
					'description'                 => '<p>Food truck fusion-style tacos, straight outta the Instant Pot! Flavorful garlic and ginger beef gets tucked into flour tortillas with kimchi, cilantro, peanuts, and spicy mayo. Oooo-ee!</p><p>A <a href="https://google.com">link</a>. <strong>Bold Text</strong>. <em>Italic Text</em>. </p><ul><li>An unordered list item.</li><li>And another one.</li></ul><ol><li>And an ordered list item.</li><li>And another one, for funzies.</li></ol><h4>A Heading!</h4>',
					'ingredients'                 => '<h4>For the Korean Beef:</h4><ul><li>1 14-ounce can pears, drained (fresh pears work, too)</li><li>1 two-inch knob of fresh ginger</li><li>4 cloves garlic</li><li>1/2 cup soy sauce</li><li>1/2 cup brown sugar</li><li>2 lbs. top sirloin (see notes)</li></ul><h4>For the Tacos:</h4><ul><li>chopped cilantro</li><li>chopped peanuts</li><li>kimchi !!!! or slaw of some <a href="https://www.google.com">sort </a></li><li>sriracha mayo or yum yum sauce</li><li>flour tortillas</li></ul>',
					'instructions'                => '<ol><li><strong>Make the sauce:</strong> Pulse pears, ginger, garlic, soy sauce, and brown sugar until mostly smooth. Reserve half for later use (see notes).</li><li><strong>Cook the meat:</strong> Trim any fat off the meat and cut into a few large chunks (it will shred, but cutting the meat helps it cook faster). Place in an Instant Pot and cover with half of the sauce. Cook on high pressure (manual) for 45 minutes. Release steam and shred the meat in the Instant Pot with two forks.</li><li><strong>Tacos:</strong> Fill each tortilla with beef, cilantro, peanuts, kimchi, and spicy mayo. Oh.My.Gosh. It’s so good.</li></ol>',
					'notes'                       => '<p><strong>Slow Cooker:</strong> If you want to make this in the slow cooker, follow the same directions but cook the meat on high for 3-5 hours, or low for 6-8 hours.</p><p><strong>Stovetop:</strong> If you want to make this on the stovetop, I recommend slicing the meat thinly and following these directions for super quick and yummy beef.</p><p><strong>Cuts of Meat:</strong> Chuck roast can work, but I found that even with some trimming, it still had too much fat for my liking. Top sirloin is a little more spendy, but I love it in this recipe because there is less fat to work around and you really just get the awesomeness of the beef.</p><p><strong>Marinating:</strong> If you have time to marinate the meat, DO IT. It really enhances the flavor to of the meat to meat to let it all kinda hang out together with the sauce for a while. If you don’t have time, no biggie. I usually don’t have time either, and I’d still give it 5/5 for flavor.</p><p>As written, you’ll have double the sauce you need! So save the remaining sauce (fridge or freezer) for a super fast and easy Round Two of your Korean Beef Tacos. LUCKYYYY.</p><ul><li>What if I have a note in a list?</li><li>Another one!</li></ul>',
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '30 minutes',
					'cook_time'                   => '20 minutes',
					'total_time'                  => '1 hours 50 minutes',
					'yield'                       => '8 tacos',
					'category'                    => 'Dinner',
					'method'                      => '',
					'cuisine'                     => 'Korean, Mexican',
					'serving_size'                => '1 taco',
					'calories'                    => '227',
					'sugar'                       => '17 g',
					'sodium'                      => '486 mg',
					'fat'                         => '4 g',
					'saturated_fat'               => '1 g',
					'unsaturated_fat'             => '1 g',
					'trans_fat'                   => '1 g',
					'carbohydrates'               => '20 g',
					'fiber'                       => '3 g',
					'protein'                     => '27 g',
					'cholesterol'                 => '67 mg',
					'keywords'                    => '',
					'nutrifox_id'                 => '',
					'video_url'                   => '',
					'total_time_raw'              => '1 hours 50 minutes',
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
				$recipe_2,
				array(
					'title'                       => 'Something with Nutrition Info',
					'author_name'                 => '',
					'description'                 => '',
					'ingredients'                 => '<ul><li>1 apple</li><li>1 cup raspberries</li><li>1 pie crust</li></ul>',
					'instructions'                => '<p>Make pie</p><p>eat pie</p>',
					'notes'                       => '',
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '20 minutes',
					'cook_time'                   => '50 minutes',
					'total_time'                  => '1 hours 10 minutes',
					'yield'                       => '2 slices',
					'category'                    => 'Dessert',
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
					'total_time_raw'              => '1 hours 10 minutes',
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
					'title'                       => 'Something with Nutrition Info',
					'author_name'                 => '',
					'description'                 => '',
					'ingredients'                 => '<ul><li>1 apple</li><li>1 cup raspberries</li><li>1 pie crust</li></ul>',
					'instructions'                => '<p>Make pie</p><p>eat pie</p>',
					'notes'                       => '',
					'average_rating'              => '',
					'total_reviews'               => '0',
					'prep_time'                   => '20 minutes',
					'cook_time'                   => '50 minutes',
					'total_time'                  => '1 hours 10 minutes',
					'yield'                       => '2 slices',
					'category'                    => 'Dessert',
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
					'total_time_raw'              => '1 hours 10 minutes',
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
		);
	}

	/**
	 * A contributor can't convert a Cookbook recipe they can't access.
	 */
	public function test_contributor_cannot_convert_private_cookbook_recipe() {
		global $wpdb;

		$contributor_id = $this->factory->user->create( array( 'role' => 'contributor' ) );
		$post_id        = $this->factory->post->create(
			array(
				'post_author' => $contributor_id,
				'post_status' => 'draft',
			)
		);
		wp_update_post(
			array(
				'ID'          => 33771,
				'post_status' => 'private',
			)
		);
		$wpdb->update( $wpdb->posts, array( 'post_content' => '[cookbook_recipe id="33771"]' ), array( 'ID' => $post_id ) );
		clean_post_cache( $post_id );

		wp_set_current_user( $contributor_id );
		$this->assertFalse( Tasty_Recipes\Converters\Cookbook::convert_post( $post_id ) );
		$this->assertStringContainsString( '[cookbook_recipe id="33771"]', get_post_field( 'post_content', $post_id ) );

		// An administrator converting the contributor's post can't copy it either.
		wp_set_current_user( $this->admin_id );
		$this->assertFalse( Tasty_Recipes\Converters\Cookbook::convert_post( $post_id ) );

		// The contributor can convert their own private recipe.
		wp_update_post(
			array(
				'ID'          => 33771,
				'post_author' => $contributor_id,
			)
		);
		wp_set_current_user( $contributor_id );
		$this->assertInstanceOf( Recipe::class, Tasty_Recipes\Converters\Cookbook::convert_post( $post_id ) );
	}

	/**
	 * Only Cookbook recipe posts can be converted.
	 */
	public function test_cookbook_converter_ignores_other_post_types() {
		global $wpdb;

		$other_id = $this->factory->post->create( array( 'post_title' => 'Not a recipe' ) );
		$wpdb->update( $wpdb->posts, array( 'post_content' => '[cookbook_recipe id="' . $other_id . '"]' ), array( 'ID' => $this->post_id ) );
		clean_post_cache( $this->post_id );

		$this->assertFalse( Tasty_Recipes\Converters\Cookbook::convert_post( $this->post_id ) );
	}
}
