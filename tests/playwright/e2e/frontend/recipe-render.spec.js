const { expect, test } = require( '../../support/test' );

test.describe.configure( { mode: 'serial' } );

test.describe( 'Recipe Render', () => {
	let postUrl;

	test.beforeAll( async ( { wpCli } ) => {
		await wpCli.deleteAllTastyRecipes();
		await wpCli.setTastyOption( 'tasty_recipes_template', '' );

		const recipeId = await wpCli.createTastyRecipe( {
			title: 'Chocolate Chip Cookies',
			author_name: 'Chef Playwright',
			description: '<p>Classic homemade chocolate chip cookies.</p>',
			ingredients:
				'<ul><li>2 cups flour</li><li>1 cup butter</li><li>1 cup chocolate chips</li></ul>',
			instructions:
				'<ol><li>Cream butter and sugar.</li><li>Add flour gradually.</li><li>Fold in chocolate chips.</li><li>Bake at 375°F for 10 minutes.</li></ol>',
			notes: '<p>Store in an airtight container for up to 5 days.</p>',
			prep_time: '15 minutes',
			cook_time: '10 minutes',
			total_time: '25 minutes',
			yield: '24 cookies',
			category: 'Dessert',
			cuisine: 'American',
			calories: '150',
			keywords: 'cookies, chocolate, baking',
		} );
		const post = await wpCli.createPostWithRecipe( recipeId, {
			title: 'Recipe Render Test',
		} );
		postUrl = post.postUrl;
	} );

	test.afterAll( async ( { wpCli } ) => {
		await wpCli.deleteAllTastyRecipes();
		await wpCli.setTastyOptions( {
			tasty_recipes_quick_links: [ 'jump', 'print' ],
			tasty_recipes_card_buttons: '',
			tasty_recipes_default_author_link: '',
			tasty_recipes_shareasale: '',
			tasty_recipes_poweredby: '',
		} );
	} );

	test.describe( 'Recipe Card', () => {
		test.beforeEach( async ( { page } ) => {
			await page.goto( postUrl );
		} );

		test( 'should render recipe card with correct structure', async ( {
			page,
		} ) => {
			await expect( page.locator( '.tasty-recipes' ) ).toBeVisible();
			await expect( page.locator( '.tasty-recipes-title' ) ).toContainText(
				'Chocolate Chip Cookies'
			);
			await expect(
				page.locator(
					'.tasty-recipes .tasty-recipes-description, .tasty-recipes .tasty-recipes-ingredients'
				)
			).toHaveCount( 2 );
		} );

		test( 'should render description', async ( { page } ) => {
			await expect(
				page.locator( '.tasty-recipes-description' )
			).toContainText( 'Classic homemade chocolate chip cookies.' );
		} );

		test( 'should render ingredients section', async ( { page } ) => {
			await expect(
				page.locator( '.tasty-recipes-ingredients-header h3' )
			).toContainText( 'Ingredients' );
			await expect(
				page.locator( '.tasty-recipes-ingredients-body li' )
			).toHaveCount( 3 );
			await expect(
				page.locator( '.tasty-recipes-ingredients-body' )
			).toContainText( '2 cups flour' );
			await expect(
				page.locator( '.tasty-recipes-ingredients-body' )
			).toContainText( '1 cup butter' );
			await expect(
				page.locator( '.tasty-recipes-ingredients-body' )
			).toContainText( '1 cup chocolate chips' );
		} );

		test( 'should render instructions section', async ( { page } ) => {
			await expect( page.locator( '.tasty-recipe-instructions' ) ).toBeVisible();
			await expect(
				page.locator( '.tasty-recipes-instructions-header h3' )
			).toContainText( 'Instructions' );
			await expect(
				page.locator( '.tasty-recipes-instructions-body li' )
			).toHaveCount( 4 );
			await expect(
				page.locator( '.tasty-recipes-instructions-body' )
			).toContainText( 'Cream butter and sugar.' );
			await expect(
				page.locator( '.tasty-recipes-instructions-body' )
			).toContainText( 'Bake at 375°F for 10 minutes.' );
		} );

		test( 'should render recipe details', async ( { page } ) => {
			const details = page.locator( '.tasty-recipes-details' );
			await expect( details ).toBeVisible();
			await expect( details ).toContainText( 'Chef Playwright' );
			await expect( details ).toContainText( '15 minutes' );
			await expect( details ).toContainText( '10 minutes' );
			await expect( details ).toContainText( '25 minutes' );
			await expect( details ).toContainText( '24 cookies' );
		} );

		test( 'should render print button', async ( { page } ) => {
			const printButton = page.locator( 'a.tasty-recipes-print-button' ).first();
			await expect( printButton ).toContainText( 'Print' );
			await expect( printButton ).toHaveAttribute( 'href', /print/ );
		} );

		test( 'should render notes section', async ( { page } ) => {
			await expect( page.locator( '.tasty-recipes-notes h3' ) ).toContainText(
				'Notes'
			);
			await expect(
				page.locator( '.tasty-recipes-notes-body' )
			).toContainText( 'Store in an airtight container for up to 5 days.' );
		} );

		test( 'should include keywords in structured data', async ( { page } ) => {
			const recipe = await getStructuredRecipe( page );

			expect( String( recipe.keywords ).toLowerCase() ).toContain( 'cookies' );
		} );

		test( 'should contain valid JSON-LD structured data', async ( { page } ) => {
			const recipe = await getStructuredRecipe( page );

			expect( recipe.name ).toBe( 'Chocolate Chip Cookies' );
			expect( Array.isArray( recipe.recipeIngredient ) ).toBe( true );
			expect( Array.isArray( recipe.recipeInstructions ) ).toBe( true );
		} );
	} );

	test.describe( 'Quick Links', () => {
		test( 'should show Jump to Recipe link when enabled', async ( {
			page,
			wpCli,
		} ) => {
			await wpCli.setTastyOption( 'tasty_recipes_quick_links', [
				'jump',
				'print',
			] );
			await page.goto( postUrl );

			await expect(
				page.locator( '.tasty-recipes-quick-links' )
			).toBeVisible();
			await expect(
				page.locator( '.tasty-recipes-quick-links a', {
					hasText: 'Jump to Recipe',
				} )
			).toHaveAttribute( 'href', /#tasty-recipes-/ );
		} );

		test( 'should show Print Recipe link when enabled', async ( {
			page,
			wpCli,
		} ) => {
			await wpCli.setTastyOption( 'tasty_recipes_quick_links', [
				'jump',
				'print',
			] );
			await page.goto( postUrl );

			await expect(
				page.locator( '.tasty-recipes-quick-links a', {
					hasText: 'Print Recipe',
				} )
			).toHaveAttribute( 'href', /print/ );
		} );

		test( 'should hide quick links when all disabled', async ( {
			page,
			wpCli,
		} ) => {
			await wpCli.setTastyOption( 'tasty_recipes_quick_links', [] );
			await page.goto( postUrl );

			await expect(
				page.locator( '.tasty-recipes-quick-links' )
			).toHaveCount( 0 );
		} );
	} );

	test.describe( 'Recipe Card Buttons', () => {
		test( 'should show Print as first button by default', async ( {
			page,
			wpCli,
		} ) => {
			await wpCli.setTastyOption( 'tasty_recipes_card_buttons', {
				first: 'print',
				second: '',
			} );
			await page.goto( postUrl );

			await expect(
				page.locator( '.tasty-recipes a.tasty-recipes-print-button' )
			).toContainText( 'Print' );
		} );

		test( 'should hide buttons when set to off', async ( { page, wpCli } ) => {
			await wpCli.setTastyOption( 'tasty_recipes_card_buttons', {
				first: '',
				second: '',
			} );
			await page.goto( postUrl );

			await expect(
				page.locator( '.tasty-recipes .tasty-recipes-buttons' )
			).toBeAttached();
			await expect(
				page.locator( '.tasty-recipes .tasty-recipes-buttons a' )
			).toHaveCount( 0 );
		} );
	} );

	test.describe( 'Default Author Link', () => {
		test( 'should render author name as link when default author link is set', async ( {
			page,
			wpCli,
		} ) => {
			await wpCli.setTastyOption(
				'tasty_recipes_default_author_link',
				'https://example.com/chef-playwright'
			);
			await page.goto( postUrl );

			await expect( page.locator( 'a.tasty-recipes-author-name' ) )
				.toContainText( 'Chef Playwright' );
			await expect( page.locator( 'a.tasty-recipes-author-name' ) )
				.toHaveAttribute( 'href', 'https://example.com/chef-playwright' );
		} );

		test( 'should render author name as plain text when no link set', async ( {
			page,
			wpCli,
		} ) => {
			await wpCli.setTastyOption( 'tasty_recipes_default_author_link', '' );
			await page.goto( postUrl );

			await expect(
				page.locator( 'span.tasty-recipes-author-name' )
			).toContainText( 'Chef Playwright' );
			await expect(
				page.locator( 'a.tasty-recipes-author-name' )
			).toHaveCount( 0 );
		} );
	} );

	test.describe( 'WP Tasty Affiliate Link', () => {
		test( 'should show powered-by with affiliate link when ID is set', async ( {
			page,
			wpCli,
		} ) => {
			await wpCli.setTastyOption( 'tasty_recipes_shareasale', '12345' );
			await page.goto( postUrl );

			await expect( page.locator( '.tasty-recipes-plug' ) ).toBeVisible();
			await expect( page.locator( '.tasty-recipes-plug a' ) )
				.toHaveAttribute( 'href', /wptasty\.com\/ref\/12345/ );
		} );

		test( 'should not show powered-by when no ID or poweredby option', async ( {
			page,
			wpCli,
		} ) => {
			await wpCli.setTastyOptions( {
				tasty_recipes_shareasale: '',
				tasty_recipes_poweredby: '',
			} );
			await page.goto( postUrl );

			await expect( page.locator( '.tasty-recipes-plug' ) ).toHaveCount( 0 );
		} );
	} );
} );

const getStructuredRecipe = async page => {
	const scripts = await page
		.locator( 'script[type="application/ld+json"]' )
		.allTextContents();

	for ( const script of scripts ) {
		const json = JSON.parse( script );
		const graph = json[ '@graph' ] || [ json ];
		const recipe = graph.find( item => item[ '@type' ] === 'Recipe' );

		if ( recipe ) {
			return recipe;
		}
	}

	throw new Error( 'Recipe JSON-LD was not found.' );
};
