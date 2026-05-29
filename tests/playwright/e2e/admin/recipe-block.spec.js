const { expect, test } = require( '../../support/test' );
const {
	closeWelcomeGuide,
	getEditorCanvas,
	insertTastyRecipeBlock,
	openBlockSettingsSidebar,
	selectEmbeddedRecipeInBlock,
} = require( '../../support/ui' );

test.describe.configure( { mode: 'serial' } );

test.describe( 'Recipe Block', () => {
	test.beforeAll( async ( { wpCli } ) => {
		await wpCli.deleteAllTastyRecipes();
		await wpCli.createTastyRecipe( { title: 'Block Test Recipe' } );
	} );

	test.afterAll( async ( { wpCli } ) => {
		await wpCli.deleteAllTastyRecipes();
	} );

	test.beforeEach( async ( { admin } ) => {
		await admin.visitAdminPage( 'post-new.php' );
	} );

	test.describe( 'Block Structure', () => {
		test( 'should show recipe placeholder when inserting block', async ( {
			editor,
			page,
		} ) => {
			await closeWelcomeGuide( page );
			await insertTastyRecipeBlock( editor, page );

			const canvas = getEditorCanvas( editor, page );
			await expect(
				canvas.locator( '.tasty-recipes-block-placeholder' )
			).toBeVisible();
			await expect(
				canvas.locator( '.tasty-recipes-block-placeholder-heading h2' )
			).toHaveText( 'Tasty Recipes' );
			await expect(
				canvas
					.locator( '.tasty-recipes-block-placeholder-buttons' )
					.getByRole( 'button', { name: 'Create new recipe' } )
			).toBeVisible();
			await expect(
				canvas
					.locator( '.tasty-recipes-block-placeholder-buttons' )
					.getByRole( 'button', { name: 'Embed existing recipe' } )
			).toBeVisible();
		} );

		test( 'should show embed select after clicking Embed existing recipe', async ( {
			editor,
			page,
		} ) => {
			await closeWelcomeGuide( page );
			await insertTastyRecipeBlock( editor, page );
			const canvas = getEditorCanvas( editor, page );

			await canvas
				.locator( '.tasty-recipes-block-placeholder-buttons' )
				.getByRole( 'button', { name: 'Embed existing recipe' } )
				.click();

			await expect(
				canvas.locator( '.tasty-recipes-block-placeholder-embed' )
			).toBeVisible();
			await expect(
				canvas.locator( '.tasty-recipes-select__control' )
			).toBeVisible();
		} );

		test( 'should render ServerSideRender preview after selecting a recipe', async ( {
			editor,
			page,
		} ) => {
			await closeWelcomeGuide( page );
			await insertTastyRecipeBlock( editor, page );
			const canvas = getEditorCanvas( editor, page );

			await selectEmbeddedRecipeInBlock( editor, page );

			await expect(
				canvas.locator( '.tasty-recipes-block-placeholder' )
			).toHaveCount( 0 );
			await expect( canvas.locator( '.tasty-recipes' ) ).toBeAttached( {
				timeout: 15000,
			} );
		} );
	} );

	test.describe( 'Block Settings UI', () => {
		test( 'should display InspectorControls panels', async ( {
			editor,
			page,
		} ) => {
			await closeWelcomeGuide( page );
			await insertTastyRecipeBlock( editor, page );
			await selectEmbeddedRecipeInBlock( editor, page );
			await openBlockSettingsSidebar( page );

			await expect(
				page.locator( '.components-panel__body-title', {
					hasText: 'Select Recipe',
				} )
			).toBeVisible();
			await expect(
				page.locator( '.components-panel__body-title', {
					hasText: 'Recipe options',
				} )
			).toBeVisible();
			await expect(
				page.locator( 'label', { hasText: 'Override author link' } )
			).toBeVisible();
		} );

		test( 'should show author link field when override is enabled', async ( {
			editor,
			page,
		} ) => {
			await closeWelcomeGuide( page );
			await insertTastyRecipeBlock( editor, page );
			await selectEmbeddedRecipeInBlock( editor, page );
			await openBlockSettingsSidebar( page );

			await page
				.locator( 'label', { hasText: 'Override author link' } )
				.locator( 'xpath=ancestor::*[contains(@class, "components-base-control")]' )
				.locator( 'input[type="checkbox"]' )
				.check( { force: true } );
			const authorLinkField = page
				.locator( '.components-panel__body', { hasText: 'Recipe options' } )
				.locator( 'input[type="text"]' );
			await authorLinkField.fill( 'https://example.com/author-override' );

			await expect( authorLinkField ).toBeVisible();
			await expect( authorLinkField ).toHaveValue(
				'https://example.com/author-override'
			);
		} );
	} );

	test.describe( 'Block Settings Execution', () => {
		test( 'should render recipe with overridden author link', async ( {
			editor,
			page,
			wpCli,
		} ) => {
			await wpCli.setTastyOption(
				'tasty_recipes_default_author_link',
				'https://default.example.com'
			);
			await closeWelcomeGuide( page );
			const canvas = getEditorCanvas( editor, page );

			await canvas
				.locator( 'h1.editor-post-title__input, #post-title-0' )
				.first()
				.fill( 'Author Link Override Test' );
			await insertTastyRecipeBlock( editor, page );
			await selectEmbeddedRecipeInBlock( editor, page );
			await openBlockSettingsSidebar( page );
			await page
				.locator( 'label', { hasText: 'Override author link' } )
				.locator( 'xpath=ancestor::*[contains(@class, "components-base-control")]' )
				.locator( 'input[type="checkbox"]' )
				.check( { force: true } );
			await page
				.locator( '.components-panel__body', { hasText: 'Recipe options' } )
				.locator( 'input[type="text"]' )
				.fill( 'https://override.example.com' );

			await page.locator( '.editor-post-publish-panel__toggle' ).click();
			await page.locator( '.editor-post-publish-button' ).click();
			await expect(
				page.locator( '.components-snackbar, .components-notice.is-success' )
			).toBeVisible( { timeout: 15000 } );

			const postHref = await page
				.locator( '.post-publish-panel__postpublish-buttons a', {
					hasText: 'View Post',
				} )
				.getAttribute( 'href' );
			await page.goto( postHref );
			await expect( page.locator( 'a.tasty-recipes-author-name' ) )
				.toHaveAttribute( 'href', 'https://override.example.com' );
		} );

		test( 'should render recipe with default author link when override is off', async ( {
			page,
			wpCli,
		} ) => {
			await wpCli.setTastyOption(
				'tasty_recipes_default_author_link',
				'https://default.example.com'
			);
			const recipeId = await wpCli.createTastyRecipe( {
				title: 'Default Link Recipe',
			} );
			const { postUrl } = await wpCli.createPostWithRecipe( recipeId, {
				title: 'Default Author Link Test',
			} );

			await page.goto( postUrl );
			await expect( page.locator( 'a.tasty-recipes-author-name' ) )
				.toHaveAttribute( 'href', 'https://default.example.com' );
		} );
	} );
} );
