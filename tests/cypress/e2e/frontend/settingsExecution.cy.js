describe( 'Settings Execution', () => {
	let recipeId;
	let postUrl;

	before( () => {
		cy.loginCached();
		cy.deleteAllTastyRecipes();

		cy.createTastyRecipe( {
			title: 'Settings Test Recipe',
			author_name: 'Chef Cypress',
		} ).then( ( id ) => {
			recipeId = id;
			cy.createPostWithRecipe( id, {
				title: 'Settings Execution Test Post',
			} ).then( ( result ) => {
				postUrl = result.postUrl;
			} );
		} );
	} );

	beforeEach( () => {
		cy.loginCached();
		cy.viewport( 1280, 720 );
	} );

	after( () => {
		cy.loginCached();
		cy.deleteAllTastyRecipes();

		// Reset options to defaults.
		cy.setTastyOptions( {
			tasty_recipes_quick_links: [ 'jump', 'print' ],
			tasty_recipes_card_buttons: '',
			tasty_recipes_default_author_link: '',
			tasty_recipes_shareasale: '',
			tasty_recipes_poweredby: '',
		} );
	} );

	describe( 'Quick Links', () => {
		it( 'should show Jump to Recipe link when enabled', () => {
			cy.setTastyOption( 'tasty_recipes_quick_links', [
				'jump',
				'print',
			] );

			cy.visit( postUrl );

			cy.get( '.tasty-recipes-quick-links' ).should( 'exist' );

			cy.get( '.tasty-recipes-quick-links' )
				.contains( 'a', 'Jump to Recipe' )
				.should( 'exist' )
				.and( 'have.attr', 'href' )
				.and( 'include', '#tasty-recipes-' );
		} );

		it( 'should show Print Recipe link when enabled', () => {
			cy.setTastyOption( 'tasty_recipes_quick_links', [
				'jump',
				'print',
			] );

			cy.visit( postUrl );

			cy.get( '.tasty-recipes-quick-links' )
				.contains( 'a', 'Print Recipe' )
				.should( 'exist' )
				.and( 'have.attr', 'href' )
				.and( 'include', 'print' );
		} );

		it( 'should hide quick links when all disabled', () => {
			cy.setTastyOption( 'tasty_recipes_quick_links', [] );

			cy.visit( postUrl );

			cy.get( '.tasty-recipes-quick-links' ).should( 'not.exist' );
		} );
	} );

	describe( 'Recipe Card Buttons', () => {
		it( 'should show Print as first button by default', () => {
			cy.setTastyOption( 'tasty_recipes_card_buttons', {
				first: 'print',
				second: '',
			} );

			cy.visit( postUrl );

			cy.get( '.tasty-recipes a.tasty-recipes-print-button' )
				.should( 'exist' )
				.and( 'contain.text', 'Print' );
		} );

		it( 'should hide buttons when set to off', () => {
			cy.setTastyOption( 'tasty_recipes_card_buttons', {
				first: '',
				second: '',
			} );

			cy.visit( postUrl );

			// The container is always rendered by the template; verify no
			// actionable card buttons are rendered inside it when disabled.
			cy.get( '.tasty-recipes .tasty-recipes-buttons' ).should( 'exist' );
			cy.get( '.tasty-recipes .tasty-recipes-buttons a' ).should(
				'not.exist'
			);
		} );
	} );

	describe( 'Default Author Link', () => {
		it( 'should render author name as link when default author link is set', () => {
			cy.setTastyOption(
				'tasty_recipes_default_author_link',
				'https://example.com/chef-cypress'
			);

			cy.visit( postUrl );

			cy.get( 'a.tasty-recipes-author-name' )
				.should( 'exist' )
				.and( 'contain.text', 'Chef Cypress' )
				.and(
					'have.attr',
					'href',
					'https://example.com/chef-cypress'
				);
		} );

		it( 'should render author name as plain text when no link set', () => {
			cy.setTastyOption( 'tasty_recipes_default_author_link', '' );

			cy.visit( postUrl );

			cy.get( 'span.tasty-recipes-author-name' )
				.should( 'exist' )
				.and( 'contain.text', 'Chef Cypress' );

			cy.get( 'a.tasty-recipes-author-name' ).should( 'not.exist' );
		} );
	} );

	describe( 'ShareASale Affiliate ID', () => {
		it( 'should show powered-by with ShareASale link when ID is set', () => {
			cy.setTastyOption( 'tasty_recipes_shareasale', '12345' );

			cy.visit( postUrl );

			cy.get( '.tasty-recipes-plug' ).should( 'exist' );

			cy.get( '.tasty-recipes-plug a' )
				.should( 'have.attr', 'href' )
				.and( 'include', 'shareasale.com' )
				.and( 'include', '12345' );
		} );

		it( 'should not show powered-by when no ID or poweredby option', () => {
			cy.setTastyOptions( {
				tasty_recipes_shareasale: '',
				tasty_recipes_poweredby: '',
			} );

			cy.visit( postUrl );

			cy.get( '.tasty-recipes-plug' ).should( 'not.exist' );
		} );
	} );
} );
