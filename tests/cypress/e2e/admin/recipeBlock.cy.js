describe( 'Recipe Block', () => {
	let recipeId;

	before( () => {
		cy.loginCached();
		cy.deleteAllTastyRecipes();
		cy.createTastyRecipe( { title: 'Block Test Recipe' } ).then(
			( id ) => {
				recipeId = id;
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

	describe( 'Block Structure', () => {
		it( 'should show recipe placeholder when inserting block', () => {
			cy.visit( '/wp-admin/post-new.php' );
			cy.closeWelcomeGuide();

			cy.insertBlock( 'wp-tasty/tasty-recipe', 'Tasty Recipe' );

			cy.getBlockEditor()
				.find( '.tasty-recipes-block-placeholder', {
					timeout: 10000,
				} )
				.should( 'be.visible' );

			cy.getBlockEditor()
				.find( '.tasty-recipes-block-placeholder-heading h2' )
				.should( 'have.text', 'Tasty Recipes' );

			cy.getBlockEditor()
				.find( '.tasty-recipes-block-placeholder-buttons' )
				.within( () => {
					cy.contains( 'button', 'Create new recipe' ).should(
						'be.visible'
					);
					cy.contains(
						'button',
						'Embed existing recipe'
					).should( 'be.visible' );
				} );
		} );

		it( 'should show embed select after clicking Embed existing recipe', () => {
			cy.visit( '/wp-admin/post-new.php' );
			cy.closeWelcomeGuide();

			cy.insertBlock( 'wp-tasty/tasty-recipe', 'Tasty Recipe' );

			cy.getBlockEditor()
				.find( '.tasty-recipes-block-placeholder', {
					timeout: 10000,
				} )
				.should( 'be.visible' );

			cy.getBlockEditor()
				.find( '.tasty-recipes-block-placeholder-buttons' )
				.contains( 'button', 'Embed existing recipe' )
				.click();

			cy.getBlockEditor()
				.find( '.tasty-recipes-block-placeholder-embed' )
				.should( 'be.visible' );

			cy.getBlockEditor()
				.find( '.tasty-recipes-select__control' )
				.should( 'be.visible' );
		} );

		it( 'should render ServerSideRender preview after selecting a recipe', () => {
			cy.visit( '/wp-admin/post-new.php' );
			cy.closeWelcomeGuide();

			cy.insertBlock( 'wp-tasty/tasty-recipe', 'Tasty Recipe' );

			cy.getBlockEditor()
				.find( '.tasty-recipes-block-placeholder', {
					timeout: 10000,
				} )
				.should( 'be.visible' );

			cy.getBlockEditor()
				.find( '.tasty-recipes-block-placeholder-buttons' )
				.contains( 'button', 'Embed existing recipe' )
				.click();

			cy.getBlockEditor()
				.find( '.tasty-recipes-select__control' )
				.click();

			cy.getBlockEditor()
				.find( '.tasty-recipes-select__menu', { timeout: 5000 } )
				.should( 'be.visible' );

			cy.getBlockEditor()
				.find( '.tasty-recipes-select__option' )
				.first()
				.click();

			cy.getBlockEditor()
				.find( '.tasty-recipes-block-placeholder' )
				.should( 'not.exist' );

			cy.getBlockEditor()
				.find( '.tasty-recipes', { timeout: 15000 } )
				.should( 'exist' );
		} );
	} );

	describe( 'Block Settings UI', () => {
		it( 'should display InspectorControls panels', () => {
			cy.visit( '/wp-admin/post-new.php' );
			cy.closeWelcomeGuide();

			cy.insertBlock( 'wp-tasty/tasty-recipe', 'Tasty Recipe' );

			cy.getBlockEditor()
				.find( '.tasty-recipes-block-placeholder', {
					timeout: 10000,
				} )
				.should( 'be.visible' );

			cy.getBlockEditor()
				.find( '.tasty-recipes-block-placeholder-buttons' )
				.contains( 'button', 'Embed existing recipe' )
				.click();

			cy.getBlockEditor()
				.find( '.tasty-recipes-select__control' )
				.click();

			cy.getBlockEditor()
				.find( '.tasty-recipes-select__option' )
				.first()
				.click();

			cy.openDocumentSettingsSidebar( 'Block' );

			// InspectorControls render in the sidebar (outside the iframe).
			cy.contains( '.components-panel__body-title', 'Select Recipe' )
				.should( 'be.visible' );

			cy.contains( '.components-panel__body-title', 'Recipe options' )
				.should( 'be.visible' );

			cy.contains( 'label', 'Override author link' ).should(
				'be.visible'
			);
		} );

		it( 'should show author link field when override is enabled', () => {
			cy.visit( '/wp-admin/post-new.php' );
			cy.closeWelcomeGuide();

			cy.insertBlock( 'wp-tasty/tasty-recipe', 'Tasty Recipe' );

			cy.getBlockEditor()
				.find( '.tasty-recipes-block-placeholder', {
					timeout: 10000,
				} )
				.should( 'be.visible' );

			cy.getBlockEditor()
				.find( '.tasty-recipes-block-placeholder-buttons' )
				.contains( 'button', 'Embed existing recipe' )
				.click();

			cy.getBlockEditor()
				.find( '.tasty-recipes-select__control' )
				.click();

			cy.getBlockEditor()
				.find( '.tasty-recipes-select__option' )
				.first()
				.click();

			cy.openDocumentSettingsSidebar( 'Block' );

			cy.contains( 'label', 'Override author link' )
				.closest( '.components-base-control' )
				.find( 'input[type="checkbox"]' )
				.check( { force: true } );

			// After enabling, a text input should appear for the author link URL.
			cy.contains( '.components-panel__body-title', 'Recipe options' )
				.closest( '.components-panel__body' )
				.find( 'input[type="text"]' )
				.should( 'be.visible' )
				.type( 'https://example.com/author-override' );
		} );
	} );

	describe( 'Block Settings Execution', () => {
		it( 'should render recipe with overridden author link', () => {
			cy.setTastyOption(
				'tasty_recipes_default_author_link',
				'https://default.example.com'
			);

			cy.visit( '/wp-admin/post-new.php' );
			cy.closeWelcomeGuide();

			cy.getBlockEditor()
				.find(
					'h1.editor-post-title__input, #post-title-0',
					{ timeout: 10000 }
				)
				.first()
				.click()
				.type( 'Author Link Override Test' );

			cy.insertBlock( 'wp-tasty/tasty-recipe', 'Tasty Recipe' );

			cy.getBlockEditor()
				.find( '.tasty-recipes-block-placeholder', {
					timeout: 10000,
				} )
				.should( 'be.visible' );

			cy.getBlockEditor()
				.find( '.tasty-recipes-block-placeholder-buttons' )
				.contains( 'button', 'Embed existing recipe' )
				.click();

			cy.getBlockEditor()
				.find( '.tasty-recipes-select__control' )
				.click();

			cy.getBlockEditor()
				.find( '.tasty-recipes-select__option' )
				.first()
				.click();

			cy.openDocumentSettingsSidebar( 'Block' );

			cy.contains( 'label', 'Override author link' )
				.closest( '.components-base-control' )
				.find( 'input[type="checkbox"]' )
				.check( { force: true } );

			cy.contains( '.components-panel__body-title', 'Recipe options' )
				.closest( '.components-panel__body' )
				.find( 'input[type="text"]' )
				.clear()
				.type( 'https://override.example.com' );

			// Publish the post.
			cy.get( '.editor-post-publish-panel__toggle' ).should(
				'be.enabled'
			);
			cy.get( '.editor-post-publish-panel__toggle' ).click();
			cy.get( '.editor-post-publish-button' ).click();
			cy.get(
				'.components-snackbar, .components-notice.is-success',
				{ timeout: 15000 }
			).should( 'be.visible' );

			// Visit the post on the frontend.
			cy.get( '.post-publish-panel__postpublish-buttons a' )
				.contains( 'View Post' )
				.invoke( 'attr', 'href' )
				.then( ( href ) => {
					cy.visit( href );
				} );

			cy.get( 'a.tasty-recipes-author-name' )
				.should( 'exist' )
				.and(
					'have.attr',
					'href',
					'https://override.example.com'
				);
		} );

		it( 'should render recipe with default author link when override is off', () => {
			cy.setTastyOption(
				'tasty_recipes_default_author_link',
				'https://default.example.com'
			);

			cy.createTastyRecipe( {
				title: 'Default Link Recipe',
			} ).then( ( id ) => {
				cy.createPostWithRecipe( id, {
					title: 'Default Author Link Test',
				} ).then( ( { postUrl } ) => {
					cy.visit( postUrl );
				} );
			} );

			cy.get( 'a.tasty-recipes-author-name' )
				.should( 'exist' )
				.and(
					'have.attr',
					'href',
					'https://default.example.com'
				);
		} );
	} );
} );
