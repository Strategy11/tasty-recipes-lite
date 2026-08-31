describe( 'Recipe Explorer', () => {
	describe( 'Empty State', () => {
		before( () => {
			cy.loginCached();
			cy.deleteAllTastyRecipes();
		} );

		beforeEach( () => {
			cy.loginCached();
			cy.visit( '/wp-admin/edit.php?post_type=tasty_recipe' );
			cy.viewport( 1280, 720 );
		} );

		it( 'should show empty state when no recipes exist', () => {
			cy.get( '.tasty-recipes-empty-state' ).should( 'be.visible' );

			cy.get( '.tasty-recipes-empty-state img' ).should( 'be.visible' );

			cy.get( '.tasty-recipes-empty-state h2' ).should(
				'have.text',
				'No recipes yet'
			);

			cy.get( '.tasty-recipes-empty-state' )
				.contains(
					'Click "Create Recipe" to start your culinary journey!'
				)
				.should( 'exist' );

			cy.get( '.tasty-recipes-empty-state a' )
				.should( 'have.attr', 'href' )
				.and( 'include', 'wptasty.com' );

			cy.get( '.tasty-recipes-empty-actions .tasty-button-primary' )
				.should( 'be.visible' )
				.and( 'have.text', 'Create Recipe' );
		} );
	} );

	describe( 'Post Create', () => {
		before( () => {
			cy.loginCached();
			cy.deleteAllTastyRecipes();
		} );

		beforeEach( () => {
			cy.loginCached();
			cy.visit( '/wp-admin/edit.php?post_type=tasty_recipe' );
			cy.viewport( 1280, 720 );
		} );

		after( () => {
			cy.deleteAllTastyRecipes();
		} );

		it( 'should create a recipe from the Explorer Create button', () => {
			// Wait for the explorer page JS to finish initialising.
			// fixTitles() adds .tasty-recipes-edit-recipe to .row-title
			// elements (non-empty) and showEmptyStateIfNeeded() renders
			// .tasty-recipes-empty-state (empty). Both run synchronously
			// in the same domReady callback as registerEvents(), so when
			// either is observable the click handlers are attached.
			cy.get(
				'.row-title.tasty-recipes-edit-recipe, .tasty-recipes-empty-state',
				{ timeout: 10000 }
			).should( 'exist' );

			// In empty state the React EmptyState renders a <button>;
			// when recipes exist the PHP tab bar renders <a id="tasty-create-recipe">.
			cy.get(
				'#tasty-create-recipe, .tasty-recipes-empty-actions .tasty-button-primary'
			)
				.first()
				.click();
			cy.get( '.tasty-recipes-modal' ).should( 'be.visible' );

			cy.get( 'h1.tasty-recipes-state-creating' )
				.should( 'be.visible' )
				.and( 'have.text', 'Create Recipe' );

			cy.get( '.tasty-recipes-frame-content' )
				.find( 'input#title' )
				.type( 'Explorer Created Recipe' );

			cy.typeTastyInlineEditor(
				'tasty-recipes-recipe-description',
				'Created from Explorer.'
			);

			cy.get( '.tasty-recipes-button-insert' ).click();

			cy.log( 'Handle the After Creation embed modal' );
			cy.get( '.tasty-recipes-modal-select-skip-link', {
				timeout: 10000,
			} ).click();

			cy.visit( '/wp-admin/edit.php?post_type=tasty_recipe' );

			cy.contains( '.wp-list-table td', 'Explorer Created Recipe' ).should(
				'exist'
			);
		} );
	} );

	describe( 'Post Modal (Edit)', () => {
		let recipeId;

		before( () => {
			cy.loginCached();
			cy.deleteAllTastyRecipes();
			cy.createTastyRecipe( { title: 'Editable Recipe' } ).then(
				( id ) => {
					recipeId = id;
				}
			);
		} );

		beforeEach( () => {
			cy.loginCached();
			cy.visit( '/wp-admin/edit.php?post_type=tasty_recipe' );
			cy.viewport( 1280, 720 );
		} );

		after( () => {
			cy.deleteAllTastyRecipes();
		} );

		it( 'should open edit modal when clicking the Edit action', () => {
			// Wait for explorer page JS (registerEvents attaches edit listener).
			cy.get( '.row-title.tasty-recipes-edit-recipe', {
				timeout: 10000,
			} ).should( 'exist' );

			cy.contains( '.wp-list-table td', 'Editable Recipe' )
				.closest( 'tr' )
				.find( '.row-actions .tasty-recipes-edit-recipe' )
				.click( { force: true } );

			cy.get( '.tasty-recipes-modal' ).should( 'be.visible' );

			cy.get( 'h1.tasty-recipes-state-editing' )
				.should( 'be.visible' )
				.and( 'have.text', 'Edit Recipe' );

			cy.get( '.tasty-recipes-frame-content' )
				.find( 'input#title' )
				.should( 'have.value', 'Editable Recipe' );

			cy.get( '.tasty-recipes-modal-close' ).click( { force: true } );
		} );

		it( 'should save changes from edit modal and update list', () => {
			cy.get( '.row-title.tasty-recipes-edit-recipe', {
				timeout: 10000,
			} ).should( 'exist' );

			cy.contains( '.wp-list-table td', 'Editable Recipe' )
				.closest( 'tr' )
				.find( '.row-actions .tasty-recipes-edit-recipe' )
				.click( { force: true } );

			cy.get( '.tasty-recipes-modal' ).should( 'be.visible' );

			cy.get( '.tasty-recipes-frame-content' )
				.find( 'input#title' )
				.clear()
				.type( 'Updated Recipe Title' );

			cy.get( '.tasty-recipes-button-update' ).click();

			cy.get( '.tasty-recipes-modal', { timeout: 10000 } ).should(
				'not.be.visible'
			);

			cy.contains( '.wp-list-table td', 'Updated Recipe Title' ).should(
				'exist'
			);
		} );
	} );

	describe( 'Post Embed', () => {
		let recipeId;

		before( () => {
			cy.loginCached();
			cy.deleteAllTastyRecipes();

			// Create a published post to embed into.
			cy.wpDevEval( `
				$post_id = wp_insert_post( array(
					'post_type'   => 'post',
					'post_title'  => 'Embed Target Post',
					'post_status' => 'publish',
					'post_content' => 'Sample content.',
				) );
				echo $post_id;
			` );
		} );

		beforeEach( () => {
			cy.loginCached();
			cy.visit( '/wp-admin/edit.php?post_type=tasty_recipe' );
			cy.viewport( 1280, 720 );
		} );

		after( () => {
			cy.deleteAllTastyRecipes();
		} );

		it( 'should embed recipe in an existing post', () => {
			cy.get(
				'.row-title.tasty-recipes-edit-recipe, .tasty-recipes-empty-state',
				{ timeout: 10000 }
			).should( 'exist' );

			cy.get(
				'#tasty-create-recipe, .tasty-recipes-empty-actions .tasty-button-primary'
			)
				.first()
				.click();
			cy.get( '.tasty-recipes-modal' ).should( 'be.visible' );

			cy.get( '.tasty-recipes-frame-content' )
				.find( 'input#title' )
				.type( 'Recipe to Embed' );

			cy.typeTastyInlineEditor(
				'tasty-recipes-recipe-description',
				'Will be embedded.'
			);

			cy.get( '.tasty-recipes-button-insert' ).click();

			cy.log( 'After Creation modal should appear' );
			cy.get( '.tasty-recipes-modal-select-options', {
				timeout: 10000,
			} ).should( 'be.visible' );

			cy.log( 'Select "Embed in existing post"' );
			cy.contains(
				'.tasty-recipes-modal-select-option-title',
				'Embed in existing post'
			).click();

			cy.log( 'Search for the target post' );
			cy.get( '.tasty-recipes-modal-with-form-content input[type="text"]' )
				.should( 'be.visible' )
				.type( 'Embed Target Post' );

			cy.get( '.tasty-recipes-modal-search-result', { timeout: 10000 } )
				.should( 'be.visible' )
				.first()
				.find( 'button' )
				.click();

			cy.log( 'Click Create & Embed' );
			cy.get(
				'.tasty-recipes-modal-embed-step-footer .tasty-button-primary'
			).click();

			cy.get( '.tasty-recipes-modal', { timeout: 10000 } ).should(
				'not.be.visible'
			);
		} );

		it( 'should create a new post with recipe embedded', () => {
			// Delete any recipes from the previous test so we start from
			// empty state. The React EmptyState button works reliably
			// because its onClick handler is inline (no external event
			// listener attachment race).
			cy.deleteAllTastyRecipes();
			cy.visit( '/wp-admin/edit.php?post_type=tasty_recipe' );

			cy.get( '.tasty-recipes-empty-state', { timeout: 10000 } ).should(
				'exist'
			);

			cy.get( '.tasty-recipes-empty-actions .tasty-button-primary' ).click();
			cy.get( '.tasty-recipes-modal' ).should( 'be.visible' );

			cy.get( '.tasty-recipes-frame-content' )
				.find( 'input#title' )
				.type( 'Recipe for New Post' );

			cy.typeTastyInlineEditor(
				'tasty-recipes-recipe-description',
				'Embedded in new post.'
			);

			cy.get( '.tasty-recipes-button-insert' ).click();

			cy.log( 'After Creation modal should appear' );
			cy.get( '.tasty-recipes-modal-select-options', {
				timeout: 10000,
			} ).should( 'be.visible' );

			cy.log( 'Select "Create new post"' );
			cy.contains(
				'.tasty-recipes-modal-select-option-title',
				'Create new post'
			).click();

			cy.log( 'Enter post title' );
			cy.get( '#tasty-post-name' )
				.should( 'be.visible' )
				.clear()
				.type( 'Cypress New Post With Recipe' );

			cy.log( 'Click Create & Embed' );
			cy.get(
				'.tasty-recipes-modal-embed-step-footer .tasty-button-primary'
			).click();

			cy.get( '.tasty-recipes-modal', { timeout: 10000 } ).should(
				'not.be.visible'
			);
		} );
	} );

	describe( 'Post Action Links', () => {
		before( () => {
			cy.loginCached();
			cy.deleteAllTastyRecipes();

			// Create an orphan recipe (no parent post).
			cy.createTastyRecipe( { title: 'Orphan Recipe' } );

			// Create an embedded recipe with parent post.
			cy.createTastyRecipe( { title: 'Embedded Recipe' } ).then(
				( id ) => {
					cy.createPostWithRecipe( id, {
						title: 'Parent Post',
					} );
				}
			);
		} );

		beforeEach( () => {
			cy.loginCached();
			cy.visit( '/wp-admin/edit.php?post_type=tasty_recipe' );
			cy.viewport( 1280, 720 );
		} );

		after( () => {
			cy.deleteAllTastyRecipes();
		} );

		it( 'should show Edit and default Trash for orphan recipes', () => {
			cy.contains( '.wp-list-table td', 'Orphan Recipe' )
				.closest( 'tr' )
				.within( () => {
					cy.get( '.row-actions .tasty-recipes-edit-recipe' ).should(
						'exist'
					);
					// Orphan recipes keep the default WordPress Trash link.
					cy.get( '.row-actions .trash a' ).should( 'exist' );
					// No custom "no-delete" modal since there's no parent post.
					cy.get( '.tasty-recipes-no-delete' ).should( 'not.exist' );
					// "Delete Permanently" only appears in Trash view.
					cy.get( '.tasty-recipes-permanent-delete' ).should(
						'not.exist'
					);
				} );
		} );

		it( 'should show Edit and custom Trash for embedded recipes', () => {
			cy.contains( '.wp-list-table td', 'Embedded Recipe' )
				.closest( 'tr' )
				.within( () => {
					cy.get( '.row-actions .tasty-recipes-edit-recipe' ).should(
						'exist'
					);
					// Embedded recipes show the custom "no-delete" trash modal trigger.
					cy.get( '.tasty-recipes-no-delete' ).should( 'exist' );
				} );
		} );
	} );

	describe( 'Message Modals', () => {
		let deletableRecipeId;

		before( () => {
			cy.loginCached();
			cy.deleteAllTastyRecipes();

			// Create an orphan recipe, then trash it so it appears in Trash view
			// where the "Delete Permanently" action is available.
			cy.createTastyRecipe( { title: 'Deletable Recipe' } ).then(
				( id ) => {
					deletableRecipeId = id;

					// Trash it via WP-CLI so it shows in the Trash view.
					cy.wpDevEval(
						`wp_trash_post( ${ id } ); echo 'trashed';`
					);
				}
			);

			// Create an embedded recipe for trash modal test.
			cy.createTastyRecipe( { title: 'Trashable Recipe' } ).then(
				( id ) => {
					cy.createPostWithRecipe( id, {
						title: 'Trash Parent Post',
					} );
				}
			);
		} );

		beforeEach( () => {
			cy.loginCached();
			cy.viewport( 1280, 720 );
		} );

		after( () => {
			cy.deleteAllTastyRecipes();
		} );

		it( 'should show trash modal for embedded recipe', () => {
			cy.visit( '/wp-admin/edit.php?post_type=tasty_recipe' );

			// Wait for explorer page JS (registerEvents attaches trash listener).
			cy.get( '.row-title.tasty-recipes-edit-recipe', {
				timeout: 10000,
			} ).should( 'exist' );

			cy.contains( '.wp-list-table td', 'Trashable Recipe' )
				.closest( 'tr' )
				.find( '.tasty-recipes-no-delete' )
				.click( { force: true } );

			// The modal title uses a curly right single quotation mark (\u2019).
			cy.contains( /Recipe Can.t be Deleted/ ).should( 'be.visible' );

			cy.contains(
				/recipe can.t be deleted since it is embedded/
			).should( 'be.visible' );

			cy.get( '.tasty-recipes-modal-actions .tasty-button-primary' )
				.should( 'be.visible' )
				.and( 'have.text', 'Go to Post' );
		} );

		it( 'should show permanent delete modal and delete recipe', () => {
			// Visit the Trash view where "Delete Permanently" is available.
			cy.visit(
				'/wp-admin/edit.php?post_type=tasty_recipe&post_status=trash'
			);

			// Wait for the explorer page JS to initialise. Trash view has
			// no .row-title links, so we check for .tasty-tablenav-container
			// which rearrangeTopNav() creates in the same synchronous
			// domReady callback as registerEvents().
			cy.get( '.tasty-tablenav-container', { timeout: 10000 } ).should(
				'exist'
			);

			// Intercept the delete API call so we can wait for it later.
			// Depending on permalink settings, the request can be:
			// - /wp-json/tasty-recipes/v1/recipe-explorer/delete/:id
			// - /?rest_route=/tasty-recipes/v1/recipe-explorer/delete/:id
			cy.intercept( 'POST', '**', ( req ) => {
				const decodedUrl = decodeURIComponent( req.url );
				if (
					decodedUrl.includes( '/tasty-recipes/v1/recipe-explorer/delete/' )
				) {
					req.alias = 'deleteRecipe';
				}
			} );

			// Target the exact recipe row by post ID to avoid ambiguity when
			// a stale test run left another recipe with the same title.
			cy.get(
				`.tasty-recipes-permanent-delete[data-post-id="${ deletableRecipeId }"]`
			)
				.should( 'exist' )
				.click( { force: true } );

			cy.contains( 'Permanently Delete Recipe' ).should( 'be.visible' );

			cy.contains(
				'Are you sure you want to permanently delete the "Deletable Recipe" recipe?'
			).should( 'be.visible' );

			cy.contains( 'This action cannot be undone.' ).should(
				'be.visible'
			);

			cy.get(
				'.tasty-recipes-modal-actions .tasty-secondary-button'
			).should( 'have.text', 'Cancel' );

			cy.get( '.tasty-recipes-modal-actions .tasty-button-primary' )
				.should( 'have.text', 'Delete' )
				.click();

			// Wait for the delete API to complete. The response triggers
			// window.location.reload(), so give Cypress enough time to
			// handle the page transition and re-query the fresh DOM.
			cy.wait( '@deleteRecipe' ).then( ( { response } ) => {
				expect( response && response.statusCode ).to.equal( 200 );
				expect( response && response.body && response.body.success ).to.equal(
					true
				);
			} );

			// Assert the recipe is permanently removed in the database.
			cy.wpDevEval(
				`$post = get_post( ${ deletableRecipeId } ); echo $post ? $post->post_status : 'missing';`
			).then( ( result ) => {
				expect( result.stdout.trim() ).to.equal( 'missing' );
			} );

			// Refresh the Trash view and confirm the exact recipe row is gone.
			cy.visit(
				'/wp-admin/edit.php?post_type=tasty_recipe&post_status=trash'
			);
			cy.get(
				`.tasty-recipes-permanent-delete[data-post-id="${ deletableRecipeId }"]`
			).should( 'not.exist' );
		} );
	} );
} );
