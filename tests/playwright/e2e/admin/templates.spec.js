const { expect, test } = require( '../../support/test' );

test.describe.configure( { mode: 'serial' } );

test.describe( 'Templates', () => {
	let postUrl;

	test.beforeAll( async ( { wpCli } ) => {
		await wpCli.deleteAllTastyRecipes();
		const recipeId = await wpCli.createTastyRecipe( {
			title: 'Template Test Recipe',
		} );
		const post = await wpCli.createPostWithRecipe( recipeId, {
			title: 'Template Test Post',
		} );
		postUrl = post.postUrl;
	} );

	test.afterAll( async ( { wpCli } ) => {
		await wpCli.deleteAllTastyRecipes();
		await wpCli.setTastyOption( 'tasty_recipes_template', '' );
	} );

	test.describe( 'Preview UI', () => {
		test.beforeEach( async ( { admin } ) => {
			await admin.visitAdminPage( 'admin.php?page=tasty-recipes&tab=design' );
		} );

		test( 'should navigate through all templates', async ( { page } ) => {
			const chooser = page.locator( '.tasty-recipes-card-chooser' ).first();
			await expect( chooser ).toBeVisible();
			await expect( chooser.locator( 'h3' ) ).toContainText( /\(\d+\/\d+\)/ );

			const totalTemplates = await page.evaluate(
				() => Object.keys( window.tastyRecipesSettings.design.allTemplates ).length
			);
			const initialSlug = await page
				.locator( 'input[name="tasty_recipes_template"]' )
				.inputValue();

			for ( let index = 0; index < totalTemplates; index++ ) {
				await chooser.locator( 'button' ).last().click();
			}

			await expect(
				page.locator( 'input[name="tasty_recipes_template"]' )
			).toHaveValue( initialSlug );
		} );

		test( 'should move to next template when clicking next', async ( { page } ) => {
			const initialSlug = await page
				.locator( 'input[name="tasty_recipes_template"]' )
				.inputValue();

			await page
				.locator( '.tasty-recipes-card-chooser' )
				.first()
				.locator( 'button' )
				.last()
				.click();

			const nextSlug = await page
				.locator( 'input[name="tasty_recipes_template"]' )
				.inputValue();
			expect( nextSlug ).not.toBe( initialSlug );
			expect( nextSlug ).not.toBe( '' );
		} );

		test( 'should update preview iframe when switching templates', async ( {
			page,
		} ) => {
			const preview = page.locator( '#tasty-recipes-card-preview' );
			await expect( preview ).toBeVisible();
			const initialSrc = await preview.getAttribute( 'src' );

			await page
				.locator( '.tasty-recipes-card-chooser' )
				.first()
				.locator( 'button' )
				.last()
				.click();

			await expect.poll( async () => preview.getAttribute( 'src' ) )
				.not.toBe( initialSrc );
		} );

		test( 'should display color customization fields', async ( { page } ) => {
			const colorFields = [
				'Button Color',
				'Button Text Color',
				'Detail Label Color',
				'Detail Value Color',
			];

			for ( const label of colorFields ) {
				await expect( page.locator( 'label', { hasText: label } ) ).toBeVisible();
			}
			expect( await page.locator( '.tasty-swatch' ).count() )
				.toBeGreaterThanOrEqual( colorFields.length );
		} );
	} );

	test.describe( 'Template Execution', () => {
		test( 'should apply selected template on frontend', async ( {
			admin,
			page,
			wpCli,
		} ) => {
			await admin.visitAdminPage( 'admin.php?page=tasty-recipes&tab=design' );
			await expect(
				page.locator( '.tasty-recipes-card-chooser' ).first()
			).toBeVisible();

			const initialSlug = await page
				.locator( 'input[name="tasty_recipes_template"]' )
				.inputValue();
			await page
				.locator( '.tasty-recipes-card-chooser' )
				.first()
				.locator( 'button' )
				.last()
				.click();
			const selectedSlug = await page
				.locator( 'input[name="tasty_recipes_template"]' )
				.inputValue();
			expect( selectedSlug ).not.toBe( initialSlug );
			expect( selectedSlug ).not.toBe( '' );

			await page.locator( '#tasty-nav-submit' ).click();
			await expect( page ).toHaveURL( /tab=design/ );
			expect( await wpCli.getTastyOption( 'tasty_recipes_template' ) )
				.toBe( selectedSlug );

			await page.goto( postUrl );
			await expect( page.locator( '.tasty-recipes' ) ).toBeVisible();
		} );

		test( 'should apply color customizations on frontend', async ( {
			admin,
			page,
		} ) => {
			await admin.visitAdminPage( 'admin.php?page=tasty-recipes&tab=design' );
			await expect(
				page.locator( '.tasty-recipes-card-chooser' ).first()
			).toBeVisible();

			await page
				.locator( 'input[name="tasty_recipes_customization[button_color]"]' )
				.fill( '#ff5500' );
			await page.locator( '#tasty-nav-submit' ).click();
			await expect( page ).toHaveURL( /tab=design/ );

			await page.goto( postUrl );
			await expect( page.locator( '.tasty-recipes' ) ).toBeVisible();
			expect(
				await page.locator( '[data-tasty-recipes-customization]' ).count()
			).toBeGreaterThanOrEqual( 1 );
		} );

		test( 'should reset to default template', async ( { page, wpCli } ) => {
			await wpCli.setTastyOption( 'tasty_recipes_template', '' );
			await page.goto( postUrl );

			await expect( page.locator( '.tasty-recipes' ) ).toBeVisible();
		} );
	} );
} );
