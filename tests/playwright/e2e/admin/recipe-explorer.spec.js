const { expect, test } = require( '../../support/test' );
const {
	fillTinyMceFrame,
	openCreateRecipeModal,
	waitForRecipeExplorerReady,
} = require( '../../support/ui' );

test.describe.configure( { mode: 'serial' } );

test.describe( 'Recipe Explorer', () => {
	test.describe( 'Empty State', () => {
		test.beforeAll( async ( { wpCli } ) => {
			await wpCli.deleteAllTastyRecipes();
		} );

		test.beforeEach( async ( { admin } ) => {
			await admin.visitAdminPage( 'edit.php?post_type=tasty_recipe' );
		} );

		test( 'should show empty state when no recipes exist', async ( { page } ) => {
			await expect( page.locator( '.tasty-recipes-empty-state' ) )
				.toBeVisible();
			await expect( page.locator( '.tasty-recipes-empty-state img' ) )
				.toBeVisible();
			await expect( page.locator( '.tasty-recipes-empty-state h2' ) )
				.toHaveText( 'No recipes yet' );
			await expect( page.locator( '.tasty-recipes-empty-state' ) )
				.toContainText( 'Click "Create Recipe" to start your culinary journey!' );
			await expect( page.locator( '.tasty-recipes-empty-state a' ) )
				.toHaveAttribute( 'href', /wptasty\.com/ );
			await expect(
				page.locator( '.tasty-recipes-empty-actions .tasty-button-primary' )
			).toHaveText( 'Create Recipe' );
		} );
	} );

	test.describe( 'Post Create', () => {
		test.beforeAll( async ( { wpCli } ) => {
			await wpCli.deleteAllTastyRecipes();
		} );

		test.beforeEach( async ( { admin } ) => {
			await admin.visitAdminPage( 'edit.php?post_type=tasty_recipe' );
		} );

		test.afterAll( async ( { wpCli } ) => {
			await wpCli.deleteAllTastyRecipes();
		} );

		test( 'should create a recipe from the Explorer Create button', async ( {
			page,
		} ) => {
			await openCreateRecipeModal( page );
			await expect(
				page.locator( 'h1.tasty-recipes-state-creating' )
			).toHaveText( 'Create Recipe' );
			await page
				.locator( '.tasty-recipes-frame-content input#title' )
				.fill( 'Explorer Created Recipe' );
			await fillTinyMceFrame(
				page,
				'iframe#tasty-recipes-recipe-description_ifr',
				'Created from Explorer.'
			);
			await page.locator( '.tasty-recipes-button-insert' ).click();
			await page.locator( '.tasty-recipes-modal-select-skip-link' ).click();

			await page.goto( '/wp-admin/edit.php?post_type=tasty_recipe' );
			await expect( page.locator( '.wp-list-table' ) )
				.toContainText( 'Explorer Created Recipe' );
		} );
	} );

	test.describe( 'Post Modal (Edit)', () => {
		test.beforeAll( async ( { wpCli } ) => {
			await wpCli.deleteAllTastyRecipes();
			await wpCli.createTastyRecipe( { title: 'Editable Recipe' } );
		} );

		test.beforeEach( async ( { admin } ) => {
			await admin.visitAdminPage( 'edit.php?post_type=tasty_recipe' );
		} );

		test.afterAll( async ( { wpCli } ) => {
			await wpCli.deleteAllTastyRecipes();
		} );

		test( 'should open edit modal when clicking the Edit action', async ( {
			page,
		} ) => {
			await waitForRecipeExplorerReady( page );
			await page
				.locator( '.wp-list-table tr', { hasText: 'Editable Recipe' } )
				.locator( '.row-actions .tasty-recipes-edit-recipe' )
				.evaluate( element => element.click() );

			await expect( page.locator( '.tasty-recipes-modal' ) ).toBeVisible();
			await expect(
				page.locator( 'h1.tasty-recipes-state-editing' )
			).toHaveText( 'Edit Recipe' );
			await expect(
				page.locator( '.tasty-recipes-frame-content input#title' )
			).toHaveValue( 'Editable Recipe' );
			await page.locator( '.tasty-recipes-modal-close' ).click( { force: true } );
		} );

		test( 'should save changes from edit modal and update list', async ( {
			page,
		} ) => {
			await waitForRecipeExplorerReady( page );
			await page
				.locator( '.wp-list-table tr', { hasText: 'Editable Recipe' } )
				.locator( '.row-actions .tasty-recipes-edit-recipe' )
				.evaluate( element => element.click() );
			await expect( page.locator( '.tasty-recipes-modal' ) ).toBeVisible();
			await page
				.locator( '.tasty-recipes-frame-content input#title' )
				.fill( 'Updated Recipe Title' );
			await page.locator( '.tasty-recipes-button-update' ).click();
			await expect( page.locator( '.tasty-recipes-modal' ) ).toBeHidden();
			await expect( page.locator( '.wp-list-table' ) )
				.toContainText( 'Updated Recipe Title' );
		} );
	} );

	test.describe( 'Post Embed', () => {
		test.beforeAll( async ( { wpCli } ) => {
			await wpCli.deleteAllTastyRecipes();
			await wpCli.wpDevEval( `
				$post_id = wp_insert_post( array(
					'post_type'   => 'post',
					'post_title'  => 'Embed Target Post',
					'post_status' => 'publish',
					'post_content' => 'Sample content.',
				) );
				echo $post_id;
			` );
		} );

		test.beforeEach( async ( { admin } ) => {
			await admin.visitAdminPage( 'edit.php?post_type=tasty_recipe' );
		} );

		test.afterAll( async ( { wpCli } ) => {
			await wpCli.deleteAllTastyRecipes();
		} );

		test( 'should embed recipe in an existing post', async ( { page } ) => {
			await openCreateRecipeModal( page );
			await page
				.locator( '.tasty-recipes-frame-content input#title' )
				.fill( 'Recipe to Embed' );
			await fillTinyMceFrame(
				page,
				'iframe#tasty-recipes-recipe-description_ifr',
				'Will be embedded.'
			);
			await page.locator( '.tasty-recipes-button-insert' ).click();
			await expect(
				page.locator( '.tasty-recipes-modal-select-options' )
			).toBeVisible();
			await page
				.locator( '.tasty-recipes-modal-select-option-title', {
					hasText: 'Embed in existing post',
				} )
				.click();
			await page
				.locator( '.tasty-recipes-modal-with-form-content input[type="text"]' )
				.fill( 'Embed Target Post' );
			await page
				.locator( '.tasty-recipes-modal-search-result' )
				.first()
				.locator( 'button' )
				.click();
			await page
				.locator(
					'.tasty-recipes-modal-embed-step-footer .tasty-button-primary'
				)
				.click();
			await expect( page.locator( '.tasty-recipes-modal' ) ).toBeHidden();
		} );

		test( 'should create a new post with recipe embedded', async ( {
			page,
			wpCli,
		} ) => {
			await wpCli.deleteAllTastyRecipes();
			await page.goto( '/wp-admin/edit.php?post_type=tasty_recipe' );
			await expect( page.locator( '.tasty-recipes-empty-state' ) )
				.toBeVisible();
			await page
				.locator( '.tasty-recipes-empty-actions .tasty-button-primary' )
				.click();
			await expect( page.locator( '.tasty-recipes-modal' ) ).toBeVisible();
			await page
				.locator( '.tasty-recipes-frame-content input#title' )
				.fill( 'Recipe for New Post' );
			await fillTinyMceFrame(
				page,
				'iframe#tasty-recipes-recipe-description_ifr',
				'Embedded in new post.'
			);
			await page.locator( '.tasty-recipes-button-insert' ).click();
			await expect(
				page.locator( '.tasty-recipes-modal-select-options' )
			).toBeVisible();
			await page
				.locator( '.tasty-recipes-modal-select-option-title', {
					hasText: 'Create new post',
				} )
				.click();
			await page.locator( '#tasty-post-name' ).fill(
				'Playwright New Post With Recipe'
			);
			await page
				.locator(
					'.tasty-recipes-modal-embed-step-footer .tasty-button-primary'
				)
				.click();
			await expect( page.locator( '.tasty-recipes-modal' ) ).toBeHidden();
		} );
	} );

	test.describe( 'Post Action Links', () => {
		test.beforeAll( async ( { wpCli } ) => {
			await wpCli.deleteAllTastyRecipes();
			await wpCli.createTastyRecipe( { title: 'Orphan Recipe' } );
			const embeddedRecipeId = await wpCli.createTastyRecipe( {
				title: 'Embedded Recipe',
			} );
			await wpCli.createPostWithRecipe( embeddedRecipeId, {
				title: 'Parent Post',
			} );
		} );

		test.beforeEach( async ( { admin } ) => {
			await admin.visitAdminPage( 'edit.php?post_type=tasty_recipe' );
		} );

		test.afterAll( async ( { wpCli } ) => {
			await wpCli.deleteAllTastyRecipes();
		} );

		test( 'should show Edit and default Trash for orphan recipes', async ( {
			page,
		} ) => {
			const row = page.locator( '.wp-list-table tr', {
				hasText: 'Orphan Recipe',
			} );
			await expect( row.locator( '.row-actions .tasty-recipes-edit-recipe' ) )
				.toBeAttached();
			await expect( row.locator( '.row-actions .trash a' ) ).toBeAttached();
			await expect( row.locator( '.tasty-recipes-no-delete' ) ).toHaveCount( 0 );
			await expect( row.locator( '.tasty-recipes-permanent-delete' ) )
				.toHaveCount( 0 );
		} );

		test( 'should show Edit and custom Trash for embedded recipes', async ( {
			page,
		} ) => {
			const row = page.locator( '.wp-list-table tr', {
				hasText: 'Embedded Recipe',
			} );
			await expect( row.locator( '.row-actions .tasty-recipes-edit-recipe' ) )
				.toBeAttached();
			await expect( row.locator( '.tasty-recipes-no-delete' ) ).toBeAttached();
		} );
	} );

	test.describe( 'Message Modals', () => {
		let deletableRecipeId;

		test.beforeAll( async ( { wpCli } ) => {
			await wpCli.deleteAllTastyRecipes();
			deletableRecipeId = await wpCli.createTastyRecipe( {
				title: 'Deletable Recipe',
			} );
			await wpCli.wpDevEval(
				`wp_trash_post( ${ deletableRecipeId } ); echo 'trashed';`
			);
			const embeddedRecipeId = await wpCli.createTastyRecipe( {
				title: 'Trashable Recipe',
			} );
			await wpCli.createPostWithRecipe( embeddedRecipeId, {
				title: 'Trash Parent Post',
			} );
		} );

		test.afterAll( async ( { wpCli } ) => {
			await wpCli.deleteAllTastyRecipes();
		} );

		test( 'should show trash modal for embedded recipe', async ( {
			admin,
			page,
		} ) => {
			await admin.visitAdminPage( 'edit.php?post_type=tasty_recipe' );
			await waitForRecipeExplorerReady( page );
			await page
				.locator( '.wp-list-table tr', { hasText: 'Trashable Recipe' } )
				.locator( '.tasty-recipes-no-delete' )
				.evaluate( element => element.click() );

			await expect( page.locator( 'body' ) ).toContainText(
				/Recipe Can.t be Deleted/
			);
			await expect( page.locator( 'body' ) ).toContainText(
				/recipe can.t be deleted since it is embedded/
			);
			await expect(
				page.locator( '.tasty-recipes-modal-actions .tasty-button-primary' )
			).toHaveText( 'Go to Post' );
		} );

		test( 'should show permanent delete modal and delete recipe', async ( {
			admin,
			page,
			wpCli,
		} ) => {
			await admin.visitAdminPage(
				'edit.php?post_type=tasty_recipe&post_status=trash'
			);
			await page
				.locator( '.tasty-tablenav-container' )
				.waitFor( { state: 'attached', timeout: 10000 } );
			const deleteButton = page.locator(
				`.tasty-recipes-permanent-delete[data-post-id="${ deletableRecipeId }"]`
			);
			await expect( deleteButton ).toBeAttached();
			await deleteButton.evaluate( element => element.click() );

			await expect( page.locator( 'body' ) )
				.toContainText( 'Permanently Delete Recipe' );
			await expect( page.locator( 'body' ) ).toContainText(
				'Are you sure you want to permanently delete the "Deletable Recipe" recipe?'
			);
			await expect( page.locator( 'body' ) )
				.toContainText( 'This action cannot be undone.' );
			await expect(
				page.locator( '.tasty-recipes-modal-actions .tasty-secondary-button' )
			).toHaveText( 'Cancel' );

			const responsePromise = page.waitForResponse( response => {
				const decodedUrl = decodeURIComponent( response.url() );
				return (
					response.request().method() === 'POST' &&
					decodedUrl.includes(
						'/tasty-recipes/v1/recipe-explorer/delete/'
					)
				);
			} );
			await page
				.locator( '.tasty-recipes-modal-actions .tasty-button-primary' )
				.click();
			const response = await responsePromise;
			expect( response.status() ).toBe( 200 );

			const result = await wpCli.wpDevEval(
				`$post = get_post( ${ deletableRecipeId } ); echo $post ? $post->post_status : 'missing';`
			);
			expect( result.stdout.trim() ).toBe( 'missing' );

			await page.goto(
				'/wp-admin/edit.php?post_type=tasty_recipe&post_status=trash'
			);
			await expect( deleteButton ).toHaveCount( 0 );
		} );
	} );
} );
