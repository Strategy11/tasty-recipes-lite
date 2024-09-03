<?php
/**
 * Tests our unit amount parser.
 *
 * @package Tasty_Recipes
 */

use Tasty_Recipes\Unit_Amount_Parser;

/**
 * Tests our unit amount parser.
 */
class Test_Unit_Amount_Parser extends Tasty_Recipes_Testcase {

	/**
	 * Verifies our ingredient parser annotates ingredients as expected.
	 *
	 * @dataProvider data_ingredient_annotation_samples
	 *
	 * @param string $original Original ingredient string.
	 * @param string $expected Expected result string.
	 */
	public function test_annotate_with_spans( $original, $expected ) {
		$actual = Unit_Amount_Parser::annotate_string_with_spans( $original );
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Verifies our non-numeric detector works as expected.
	 *
	 * @dataProvider data_non_numeric_samples
	 *
	 * @param string $string String to test.
	 * @param bool   $result Expected result.
	 */
	public function test_non_numeric_detection( $string, $result ) {
		$this->assertEquals( $result, Unit_Amount_Parser::string_has_non_numeric_amount( $string ), 'Failed string: ' . $string );
	}

	/**
	 * Sample data to use.
	 */
	public function data_ingredient_annotation_samples() {
		return array(
			/**
			 * Amounts with units.
			 */
			array(
				'1 tbsp olive oil',
				'<span data-amount="1" data-unit="tbsp">1 tbsp</span> olive oil',
			),
			array(
				'1 1/8 cups granulated sugar',
				'<span data-amount="1.125" data-unit="cup">1 1/8 cups</span> granulated sugar',
			),
			array(
				'1.5 tsp Paprika',
				'<span data-amount="1.5" data-unit="tsp">1.5 tsp</span> Paprika',
			),
			array(
				'1/2 Tsp. garlic powder',
				'<span data-amount="0.5" data-unit="tsp">1/2 Tsp</span>. garlic powder',
			),
			array(
				'½ cup of ground flax seeds',
				'<span data-amount="0.5" data-unit="cup">½ cup</span> of ground flax seeds',
			),
			array(
				'1⁄4 cup apricot jam',
				'<span data-amount="0.25" data-unit="cup">1⁄4 cup</span> apricot jam',
			),
			array(
				'scant 1 cup flour',
				'scant <span data-amount="1" data-unit="cup">1 cup</span> flour',
			),
			array(
				'up to 1/2 cup vegetable broth',
				'up to <span data-amount="0.5" data-unit="cup">1/2 cup</span> vegetable broth',
			),
			array(
				'1 ½ cup of sugar',
				'<span data-amount="1.5" data-unit="cup">1 ½ cup</span> of sugar',
			),
			array(
				'1½ cups pecan halves',
				'<span data-amount="1.5" data-unit="cup">1½ cups</span> pecan halves',
			),
			/**
			 * Amounts without units.
			 */
			array(
				'2 stalks celery, finely chopped',
				'<span data-amount="2">2</span> stalks celery, finely chopped',
			),
			array(
				'1 (15-ounce) can chickpeas, drained and rinsed',
				'<span data-amount="1">1</span> (15-ounce) can chickpeas, drained and rinsed',
			),
			array(
				'1 – 2 habanero or cayenne peppers or milder alternative like aji dulce, serrano or jalapeño',
				'<span data-amount="1">1</span> – <span data-amount="2">2</span> habanero or cayenne peppers or milder alternative like aji dulce, serrano or jalapeño',
			),
			array(
				'1-2 carrots, diced',
				'<span data-amount="1">1</span>-<span data-amount="2">2</span> carrots, diced',
			),
			array(
				'1 28 oz. can crushed tomatoes (we used fire roasted tomatoes)',
				'<span data-amount="1">1</span> 28 oz. can crushed tomatoes (we used fire roasted tomatoes)',
			),
			array(
				'2 lb. lean ground turkey or 50/50 mix with lean ground beef',
				'<span data-amount="2">2</span> lb. lean ground turkey or 50/50 mix with lean ground beef',
			),
			array(
				'3-ish cups cooked chicken (I use shredded rotisserie chicken)',
				'<span data-amount="3">3</span>-ish cups cooked chicken (I use shredded rotisserie chicken)',
			),
			array(
				'1 (796ml) can diced tomatoes',
				'<span data-amount="1">1</span> (796ml) can diced tomatoes',
			),
			array(
				'1 can (680mL) of tomato sauce',
				'<span data-amount="1">1</span> can (680mL) of tomato sauce',
			),
			array(
				'1x 15 ounce can black beans or chickpeas rinsed and drained',
				'<span data-amount="1">1</span>x 15 ounce can black beans or chickpeas rinsed and drained',
			),
			array(
				'1-2 cups oil for frying',
				'<span data-amount="1">1</span>-<span data-amount="2">2</span> cups oil for frying',
			),
			array(
				'zest of 1 lemon',
				'zest of <span data-amount="1">1</span> lemon',
			),
			array(
				'about 10 fresh strawberries, or berries of your choice',
				'about <span data-amount="10">10</span> fresh strawberries, or berries of your choice',
			),
			array(
				'1 lime, cut into 4 slices',
				'<span data-amount="1">1</span> lime, cut into <span data-amount="4">4</span> slices',
			),
			array(
				'1/2-1 teaspoon chili flakes',
				'<span data-amount="0.5">1/2</span>-<span data-amount="1">1</span> teaspoon chili flakes',
			),
			array(
				'½- ¾  cup grated low fat cheese',
				'<span data-amount="0.5">½</span>- <span data-amount="0.75">¾</span>  cup grated low fat cheese',
			),
			array(
				'6 to 8 ounces smoked Gouda cheese',
				'<span data-amount="6">6</span> to <span data-amount="8">8</span> ounces smoked Gouda cheese',
			),
			array(
				'Around 1 teaspoon hot sauce (or to taste)',
				'Around <span data-amount="1" data-unit="teaspoon">1 teaspoon</span> hot sauce (or to taste)',
			),
			/**
			 * Ingredients with secondary ingredients.
			 */
			array(
				'4 garlic cloves, minced or 2 tbsp of minced garlic from a jar',
				'<span data-amount="4">4</span> garlic cloves, minced or <span data-amount="2" data-unit="tbsp">2 tbsp</span> of minced garlic from a jar',
			),
			array(
				'1 tablespoon freshly grated turmeric (or 1 teaspoon ground)',
				'<span data-amount="1" data-unit="tablespoon">1 tablespoon</span> freshly grated turmeric (or <span data-amount="1" data-unit="teaspoon">1 teaspoon</span> ground)',
			),
			array(
				'2 garlic cloves, minced (about 1 tablespoon)',
				'<span data-amount="2">2</span> garlic cloves, minced (about <span data-amount="1" data-unit="tablespoon">1 tablespoon</span>)',
			),
			array(
				'1 medium sweet potato, peeled and chopped (about 3 cups)',
				'<span data-amount="1">1</span> medium sweet potato, peeled and chopped (about <span data-amount="3" data-unit="cup">3 cups</span>)',
			),
			array(
				'1 lb rigatoni + 2 cups reserved pasta water',
				'<span data-amount="1">1</span> lb rigatoni + <span data-amount="2" data-unit="cup">2 cups</span> reserved pasta water',
			),
			array(
				'2 cups cooked black-eyed peas (from about 3/4 cup dried beans)',
				'<span data-amount="2" data-unit="cup">2 cups</span> cooked black-eyed peas (from about <span data-amount="0.75" data-unit="cup">3/4 cup</span> dried beans)',
			),
			array(
				'2 small heads broccoli - about 4-6 cups',
				'<span data-amount="2">2</span> small heads broccoli - about <span data-amount="4">4</span>-<span data-amount="6">6</span> cups',
			),
			/**
			 * Amounts with secondary amounts inside parens.
			 */
			array(
				'3/4 cup flour (you can add up to 1/4 cup for more dense, thicker muffins)',
				'<span data-amount="0.75" data-unit="cup">3/4 cup</span> flour (you can add up to <span data-amount="0.25" data-unit="cup">1/4 cup</span> for more dense, thicker muffins)',
			),
			array(
				'1 jar spaghetti sauce (if you like a lot of sauce, go for 1 1/2 jars)',
				'<span data-amount="1">1</span> jar spaghetti sauce (if you like a lot of sauce, go for <span data-amount="1.5">1 1/2</span> jars)',
			),
			array(
				'1 tablespoon seasoning mix (or 1/2 tsp. onion powder, 1/2 tsp. garlic powder, 1 tsp. salt, 1/2 tsp. pepper, 1/2 tsp. parsley)',
				'<span data-amount="1" data-unit="tablespoon">1 tablespoon</span> seasoning mix (or <span data-amount="0.5" data-unit="tsp">1/2 tsp</span>. onion powder, <span data-amount="0.5" data-unit="tsp">1/2 tsp</span>. garlic powder, <span data-amount="1" data-unit="tsp">1 tsp</span>. salt, <span data-amount="0.5" data-unit="tsp">1/2 tsp</span>. pepper, <span data-amount="0.5" data-unit="tsp">1/2 tsp</span>. parsley)',
			),
			array(
				'2 1/2 cups chicken broth (I used 2 cans)',
				'<span data-amount="2.5" data-unit="cup">2 1/2 cups</span> chicken broth (I used <span data-amount="2">2</span> cans)',
			),
			array(
				'1/5 cup freshly squeezed lemon juice (2 lemons)',
				'<span data-amount="0.2" data-unit="cup">1/5 cup</span> freshly squeezed lemon juice (<span data-amount="2">2</span> lemons)',
			),
			array(
				'juice of 3-4 lemons (about 1/2 cup)',
				'juice of <span data-amount="3">3</span>-<span data-amount="4">4</span> lemons (about <span data-amount="0.5" data-unit="cup">1/2 cup</span>)',
			),
			array(
				'1 piece fresh grated ginger (1-2 tablespoons)',
				'<span data-amount="1">1</span> piece fresh grated ginger (<span data-amount="1">1</span>-<span data-amount="2">2</span> tablespoons)',
			),
			array(
				'2 1/2 cups (3/4-inch) diced peeled Yukon gold potato (about 1 pound)',
				'<span data-amount="2.5" data-unit="cup">2 1/2 cups</span> (3/4-inch) diced peeled Yukon gold potato (about <span data-amount="1">1</span> pound)',
			),
			array(
				'1 16-ounce jar creamy peanut butter (1 1/4 cups)',
				'<span data-amount="1">1</span> 16-ounce jar creamy peanut butter (<span data-amount="1.25" data-unit="cup">1 1/4 cups</span>)',
			),
			array(
				'1 cup water (8 oz)',
				'<span data-amount="1" data-unit="cup">1 cup</span> water (<span data-amount="8" data-unit="oz">8 oz</span>)',
			),
			array(
				'1 cup (8 oz) water',
				'<span data-amount="1" data-unit="cup">1 cup</span> (<span data-amount="8" data-unit="oz">8 oz</span>) water',
			),
			array(
				'200 grams (7 ounces) (extra) firm tofu',
				'<span data-amount="200" data-unit="gram">200 grams</span> (<span data-amount="7" data-unit="ounce">7 ounces</span>) (extra) firm tofu',
			),
			array(
				'370g water (3/4 cup + 4 tablespoons)  at room temperature',
				'<span data-amount="370" data-unit="g">370g</span> water (<span data-amount="0.75" data-unit="cup">3/4 cup</span> + <span data-amount="4" data-unit="tablespoon">4 tablespoons</span>)  at room temperature',
			),
			array(
				'320 grams flour (2 to 2 2/3 cups – see recipe notes!)',
				'<span data-amount="320" data-unit="gram">320 grams</span> flour (<span data-amount="2">2</span> to <span data-amount="2.6666666666667">2 2/3</span> cups – see recipe notes!)',
			),
			/**
			 * Non-standard amounts.
			 */
			array(
				'juice of one lemon',
				'juice of <span data-amount="1">one</span> lemon',
			),
			array(
				'juice of five lemons',
				'juice of <span data-amount="5">five</span> lemons',
			),
			array(
				'juice of one half of a lemon',
				'juice of <span data-amount="0.5">one half of a</span> lemon',
			),
			array(
				'half a jalapeño (if you like spicy - leave ribs and seeds!)',
				'<span data-amount="0.5">half a</span> jalapeño (if you like spicy - leave ribs and seeds!)',
			),
			array(
				'half a loaf of pumpkin bread, sliced thickly (about 6 pieces)',
				'<span data-amount="0.5">half a</span> loaf of pumpkin bread, sliced thickly (about <span data-amount="6">6</span> pieces)',
			),
			array(
				'half an onion, chopped',
				'<span data-amount="0.5">half an</span> onion, chopped',
			),
			array(
				'half of a yellow onion, minced',
				'<span data-amount="0.5">half of a</span> yellow onion, minced',
			),
			array(
				'4',
				'<span data-amount="4">4</span>',
			),
			// Error avoidance.
			array(
				'1 cup thick caramel (could be <a href="https://sallysbakingaddiction.com/homemade-salted-caramel-recipe/">homemade salted caramel</a>, <a href="https://heartbeetkitchen.com/2015/recipes/type/dessert/salty-chocolate-date-caramels/">date caramel</a>, or store bought caramel pieces melted into a sauce - but make sure it is a little bit salty!)',
				'<span data-amount="1" data-unit="cup">1 cup</span> thick caramel (could be <a href="https://sallysbakingaddiction.com/homemade-salted-caramel-recipe/">homemade salted caramel</a>, <a href="https://heartbeetkitchen.com/2015/recipes/type/dessert/salty-chocolate-date-caramels/">date caramel</a>, or store bought caramel pieces melted into a sauce - but make sure it is a little bit salty!)',
			),
			array(
				'1 cup thick caramel (could be <a href="https://sallysbakingaddiction.com/homemade-salted-caramel-recipe/">1 cup homemade salted caramel</a>, <a href="https://heartbeetkitchen.com/2015/recipes/type/dessert/salty-chocolate-date-caramels/">date caramel</a>, or store bought caramel pieces melted into a sauce - but make sure it is a little bit salty!)',
				'<span data-amount="1" data-unit="cup">1 cup</span> thick caramel (could be <a href="https://sallysbakingaddiction.com/homemade-salted-caramel-recipe/"><span data-amount="1" data-unit="cup">1 cup</span> homemade salted caramel</a>, <a href="https://heartbeetkitchen.com/2015/recipes/type/dessert/salty-chocolate-date-caramels/">date caramel</a>, or store bought caramel pieces melted into a sauce - but make sure it is a little bit salty!)',
			),
			array(
				'<a href="https://heartbeetkitchen.com/2015/recipes/type/dessert/salty-chocolate-date-caramels/">date caramel</a> is so tasty',
				'<a href="https://heartbeetkitchen.com/2015/recipes/type/dessert/salty-chocolate-date-caramels/">date caramel</a> is so tasty',
			),
			array(
				'about 1 lb boneless chicken meat',
				'about <span data-amount="1">1</span> lb boneless chicken meat',
			),
			array(
				'1 cup sliced celery, (use the tender leaves at the center of the celery stalk if you have them)',
				'<span data-amount="1" data-unit="cup">1 cup</span> sliced celery, (use the tender leaves at the center of the celery stalk if you have them)',
			),
			array(
				'Chosen Foods 100% Pure Avocado Oil, for drizzling',
				'Chosen Foods 100% Pure Avocado Oil, for drizzling',
			),
			array(
				'Chosen Foods 100%',
				'Chosen Foods 100%',
			),
			/**
			 * Extra cruft in the string.
			 */
			array(
				'<b></b><span class="s1">2 lbs.&nbsp;<strong>beef chuck</strong>,&nbsp;<em>fat trimmed, cut in small-ish pieces</em></span>',
				'<span class="s1"><span data-amount="2">2</span> lbs. <strong>beef chuck</strong>, <em>fat trimmed, cut in small-ish pieces</em></span>',
			),
			array(
				'<span class="s1">2 tablespoons&nbsp;<strong>cornstarch</strong>&nbsp;in 2 tablespoons water</span>',
				'<span class="s1"><span data-amount="2" data-unit="tablespoon">2 tablespoons</span> <strong>cornstarch</strong> in 2 tablespoons water</span>',
			),
			array(
				'<strong>1 (28 oz) can crushed tomatoes</strong>',
				'<strong><span data-amount="1">1</span> (28 oz) can crushed tomatoes</strong>',
			),
			array(
				'<span style="font-weight: 400">¾ cup <strong>unsalted butter</strong>, softened</span>',
				'<span style="font-weight: 400"><span data-amount="0.75" data-unit="cup">¾ cup</span> <strong>unsalted butter</strong>, softened</span>',
			),
			array(
				'<span class="nutrifox-quantity" data-nf-original="usc" data-nf-usc="1" data-nf-metric="250" data-amount="1">1</span> <span class="nutrifox-unit" data-nf-original="usc" data-nf-usc="cup" data-nf-metric="mL">cup</span> <strong>chicken</strong> or <strong>vegetable broth</strong>',
				'<span class="nutrifox-quantity" data-nf-original="usc" data-nf-usc="1" data-nf-metric="250" data-amount="1">1</span> <span class="nutrifox-unit" data-nf-original="usc" data-nf-usc="cup" data-nf-metric="mL">cup</span> <strong>chicken</strong> or <strong>vegetable broth</strong>',
			),
			// TODO:
			// - optional. For more curry taste 1-2 tablespoons of your favorite curry paste.
			// - 2 Giant Hershey's milk chocolate bars, 7 ounces each.
		);
	}

	/**
	 * Sample data to use when detecting non-numeric amounts.
	 */
	public function data_non_numeric_samples() {
		return array(
			array(
				'a sprinkle of garlic powder (optional)',
				true,
			),
			array(
				'grated zest of a lemon',
				false,
			),
			array(
				'a pinch of salt',
				true,
			),
			array(
				'a few shakes of garlic powder',
				true,
			),
			array(
				"additional seasonings optional (I added a pinch of this Go-To seasoning from What's Gaby Cooking)",
				false,
			),
			array(
				'a squeeze of lemon juice, salt, and pepper to finish',
				true,
			),
			array(
				'a pinch of garlic powder (to taste)',
				true,
			),
			array(
				'pinch of garlic powder',
				true,
			),
			array(
				'protein of choice (we used two crumbled veggies burgers)',
				false,
			),
			array(
				'Fresh strawberries + a pinch of sugar',
				false,
			),
			array(
				'a handful of fresh parsley (optional)',
				true,
			),
			array(
				'a sprig of fresh rosemary (sub a small pinch of dried herbs)',
				true,
			),
			array(
				'a clove of fresh garlic, peeled',
				true,
			),
			array(
				'a small knob of fresh ginger, peeled',
				true,
			),
			array(
				'a swish of oil',
				true,
			),
			array(
				'sprinkle of cinnamon',
				true,
			),
			array(
				'a generous pinch of flaked sea salt <- again, may I suggest Amazon',
				true,
			),
		);
	}
}
