const { expect, test } = require( '../../support/test' );
const {
	fillTinyMceFrame,
	openCreateRecipeModal,
} = require( '../../support/ui' );

test.describe.configure( { mode: 'serial' } );

test.describe( 'Recipe Creation', () => {
	test.beforeAll( async ( { wpCli } ) => {
		await wpCli.deleteAllTastyRecipes();
	} );

	test.beforeEach( async ( { admin } ) => {
		await admin.visitAdminPage( 'edit.php?post_type=tasty_recipe' );
	} );

	test.afterAll( async ( { wpCli } ) => {
		await wpCli.deleteAllTastyRecipes();
	} );

	test( 'should open create modal with expected initial state', async ( {
		page,
	} ) => {
		await openCreateRecipeModal( page );

		await expect(
			page.locator( 'h1.tasty-recipes-state-creating' )
		).toHaveText( 'Create Recipe' );
		await expect(
			page.locator( '.tasty-recipes-frame-content input#title' )
		).toHaveValue( '' );
		await expect(
			page.locator( '.tasty-recipes-frame-content #author_name' )
		).toHaveValue( 'admin' );

		await page.locator( '.tasty-recipes-modal-close' ).click( { force: true } );
		await expect( page.locator( '.tasty-recipes-modal' ) ).toBeHidden();
	} );

	test( 'should open and close the Create Recipe modal', async ( { page } ) => {
		await openCreateRecipeModal( page );
		await expect(
			page.locator( 'h1.tasty-recipes-state-creating' )
		).toHaveText( 'Create Recipe' );

		await page.locator( '.tasty-recipes-modal-close' ).click( { force: true } );
		await expect( page.locator( '.tasty-recipes-modal' ) ).toBeHidden();

		await openCreateRecipeModal( page );
		await page.locator( '.tasty-recipes-modal-close' ).click( { force: true } );
	} );

	test( 'should fill in recipe fields and save', async ( { page } ) => {
		await openCreateRecipeModal( page );

		await page
			.locator( '.tasty-recipes-frame-content input#title' )
			.fill( 'Playwright Created Recipe' );
		await expect( page.locator( '#author_name' ) ).toHaveValue( 'admin' );

		await fillTinyMceFrame(
			page,
			'iframe#tasty-recipes-recipe-description_ifr',
			'A delicious test recipe.'
		);
		await fillTinyMceFrame(
			page,
			'iframe#tasty-recipes-recipe-ingredients_ifr',
			'1 cup flour\n2 eggs\n1/2 cup sugar'
		);
		await fillTinyMceFrame(
			page,
			'iframe#tasty-recipes-recipe-instructions_ifr',
			'Mix ingredients.\nBake at 350°F for 25 min.'
		);

		await page.locator( '#tasty-recipes-recipe-prep_time' ).fill( '10 minutes' );
		await page.locator( '#tasty-recipes-recipe-cook_time' ).fill( '25 minutes' );
		await page.locator( '#tasty-recipes-recipe-total_time' ).fill( '35 minutes' );
		await page.locator( '#tasty-recipes-recipe-yield' ).fill( '4 servings' );

		await fillTinyMceFrame(
			page,
			'iframe#tasty-recipes-recipe-notes_ifr',
			'Let cool before serving.'
		);

		await page.locator( '.tasty-recipes-button-insert' ).click();
		await page
			.locator( '.tasty-recipes-modal-select-skip-link' )
			.waitFor( { state: 'visible', timeout: 10000 } );
		await page.locator( '.tasty-recipes-modal-select-skip-link' ).click();
		await expect( page.locator( '.tasty-recipes-modal' ) ).toBeHidden();
	} );

	test( 'should verify saved recipe appears in Explorer list', async ( {
		page,
	} ) => {
		await expect( page.locator( '.wp-list-table' ) ).toBeVisible();
		await expect( page.locator( '.wp-list-table' ) ).toContainText(
			'Playwright Created Recipe'
		);
	} );

	test( 'should create a new recipe and persist fields in database', async ( {
		page,
		wpCli,
	} ) => {
		const recipeTitle = `Playwright Persisted Recipe ${ Date.now() }`;
		const descriptionText =
			'This is a Playwright-created recipe description.';

		await openCreateRecipeModal( page );
		await page
			.locator( '.tasty-recipes-frame-content input#title' )
			.fill( recipeTitle );
		await fillTinyMceFrame(
			page,
			'iframe#tasty-recipes-recipe-description_ifr',
			descriptionText
		);
		await page.locator( '.tasty-recipes-button-insert' ).click();
		await page
			.locator( '.tasty-recipes-modal-select-skip-link' )
			.waitFor( { state: 'visible', timeout: 10000 } );
		await page.locator( '.tasty-recipes-modal-select-skip-link' ).click();

		await page.goto( '/wp-admin/edit.php?post_type=tasty_recipe' );
		await expect( page.locator( '.wp-list-table' ) ).toContainText(
			recipeTitle
		);

		const result = await wpCli.wpDevEval( `
			$recipes = get_posts( array(
				'post_type'      => 'tasty_recipe',
				'posts_per_page' => 1,
				'post_status'    => 'any',
				'title'          => '${ wpCli.escapePhpString( recipeTitle ) }',
			) );
			if ( empty( $recipes ) ) {
				echo 'missing';
				exit;
			}
			$recipe_id = $recipes[0]->ID;
			$description = (string) get_post_meta( $recipe_id, 'description', true );
			echo $recipe_id . '|' . $description;
		` );
		const output = result.stdout.trim();

		expect( output ).not.toBe( 'missing' );
		const [ id, description ] = output.split( '|' );
		expect( Number.parseInt( id, 10 ) ).toBeGreaterThan( 0 );
		expect( description ).toContain( descriptionText );
	} );
} );
