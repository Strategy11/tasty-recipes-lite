const insertRecipeBlock = () => {
	cy.insertBlock( 'wp-tasty/tasty-recipe', 'Tasty Recipe' );

	cy.getBlockEditor()
		.find( '.tasty-recipes-block-placeholder', {
			timeout: 10000,
		} )
		.should( 'be.visible' );
};

const openEmbedSelect = () => {
	insertRecipeBlock();

	cy.getBlockEditor()
		.find( '.tasty-recipes-block-placeholder-buttons' )
		.contains( 'button', 'Embed existing recipe' )
		.click();

	cy.getBlockEditor()
		.find( '.tasty-recipes-block-placeholder-select', { timeout: 10000 } )
		.should( 'be.visible' );
};

const embedFirstRecipe = () => {
	openEmbedSelect();

	cy.window().should( ( win ) => {
		const recipe = ( win.tastyRecipesBlockEditor?.recipeIds || [] )[ 0 ];
		expect( recipe, 'an embeddable recipe should exist' ).to.exist;

		const block = win.wp.data
			.select( 'core/block-editor' )
			.getBlocks()
			.find( ( item ) => item.name === 'wp-tasty/tasty-recipe' );

		expect( block, 'the recipe block should exist' ).to.exist;

		win.wp.data
			.dispatch( 'core/block-editor' )
			.updateBlockAttributes( block.clientId, {
				id: Number( recipe.value ),
			} );
	} );

	cy.getBlockEditor()
		.find( '.tasty-recipes', { timeout: 15000 } )
		.should( 'exist' );
};

const openRecipeBlockSettings = () => {
	embedFirstRecipe();
	cy.openDocumentSettingsSidebar( 'Block' );

	cy.contains( '.components-panel__body-title', 'Select Recipe', {
		timeout: 10000,
	} ).should( 'be.visible' );
};

const enableAuthorLinkOverride = ( url ) => {
	cy.contains( 'label', 'Override author link' ).click();

	cy.get(
		'.interface-complementary-area input[type="text"], .editor-sidebar input[type="text"], .edit-post-sidebar input[type="text"]'
	)
		.filter( ':visible' )
		.first()
		.should( 'be.visible' )
		.clear()
		.type( url );
};

describe( 'Recipe Block', () => {
	before( () => {
		cy.loginCached();
		cy.deleteAllTastyRecipes();
		cy.createTastyRecipe( { title: 'Block Test Recipe' } );
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

			insertRecipeBlock();

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

			openEmbedSelect();

			cy.getBlockEditor()
				.find( '.tasty-recipes-block-placeholder-embed' )
				.should( 'be.visible' );
		} );

		it( 'should render ServerSideRender preview after selecting a recipe', () => {
			cy.visit( '/wp-admin/post-new.php' );
			cy.closeWelcomeGuide();

			embedFirstRecipe();

			cy.getBlockEditor()
				.find( '.tasty-recipes-block-placeholder' )
				.should( 'not.exist' );
		} );
	} );

	describe( 'Block Settings UI', () => {
		it( 'should display InspectorControls panels', () => {
			cy.visit( '/wp-admin/post-new.php' );
			cy.closeWelcomeGuide();

			openRecipeBlockSettings();

			cy.contains( '.components-panel__body-title', 'Recipe options' )
				.should( 'be.visible' );

			cy.contains( 'label', 'Override author link' ).should(
				'be.visible'
			);
		} );

		it( 'should show author link field when override is enabled', () => {
			cy.visit( '/wp-admin/post-new.php' );
			cy.closeWelcomeGuide();

			openRecipeBlockSettings();

			enableAuthorLinkOverride( 'https://example.com/author-override' );
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

			openRecipeBlockSettings();

			enableAuthorLinkOverride( 'https://override.example.com' );

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
