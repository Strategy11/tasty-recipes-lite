describe( 'Recipe Creation', () => {
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

	it( 'should open and close the Create Recipe modal', () => {
		cy.contains( 'button', 'Create Recipe' ).click();
		cy.get( '.tasty-recipes-modal' ).should( 'be.visible' );

		cy.get( 'h1.tasty-recipes-state-creating' )
			.should( 'be.visible' )
			.and( 'have.text', 'Create Recipe' );

		cy.get( '.tasty-recipes-modal-close' ).click( { force: true } );
		cy.get( '.tasty-recipes-modal' ).should( 'not.be.visible' );

		cy.contains( 'button', 'Create Recipe' ).click();
		cy.get( '.tasty-recipes-modal' ).should( 'be.visible' );
		cy.get( '.tasty-recipes-modal-close' ).click( { force: true } );
	} );

	it( 'should fill in recipe fields and save', () => {
		cy.contains( 'button', 'Create Recipe' ).click();
		cy.get( '.tasty-recipes-modal' ).should( 'be.visible' );

		cy.get( '.tasty-recipes-frame-content' )
			.find( 'input#title' )
			.type( 'Cypress Created Recipe' );

		cy.get( '#author_name' )
			.should( 'be.visible' )
			.and( 'have.value', 'admin' );

		cy.log( 'Fill description via TinyMCE iframe' );
		cy.get(
			'iframe#tasty-recipes-recipe-description_ifr',
			{ timeout: 10000 }
		)
			.its( '0.contentDocument.body' )
			.should( 'not.be.empty' )
			.then( cy.wrap )
			.type( 'A delicious test recipe.', { delay: 0 } );

		cy.log( 'Fill ingredients via TinyMCE iframe' );
		cy.get(
			'iframe#tasty-recipes-recipe-ingredients_ifr',
			{ timeout: 10000 }
		)
			.its( '0.contentDocument.body' )
			.should( 'not.be.empty' )
			.then( cy.wrap )
			.type( '1 cup flour{enter}2 eggs{enter}1/2 cup sugar', {
				delay: 0,
			} );

		cy.log( 'Fill instructions via TinyMCE iframe' );
		cy.get(
			'iframe#tasty-recipes-recipe-instructions_ifr',
			{ timeout: 10000 }
		)
			.its( '0.contentDocument.body' )
			.should( 'not.be.empty' )
			.then( cy.wrap )
			.type( 'Mix ingredients.{enter}Bake at 350°F for 25 min.', {
				delay: 0,
			} );

		cy.log( 'Fill detail fields' );
		cy.get( '#tasty-recipes-recipe-prep_time' ).type( '10 minutes', {
			force: true,
		} );
		cy.get( '#tasty-recipes-recipe-cook_time' ).type( '25 minutes', {
			force: true,
		} );
		cy.get( '#tasty-recipes-recipe-total_time' ).type( '35 minutes', {
			force: true,
		} );
		cy.get( '#tasty-recipes-recipe-yield' ).type( '4 servings', {
			force: true,
		} );

		cy.log( 'Fill notes via TinyMCE iframe' );
		cy.get(
			'iframe#tasty-recipes-recipe-notes_ifr',
			{ timeout: 10000 }
		)
			.its( '0.contentDocument.body' )
			.should( 'not.be.empty' )
			.then( cy.wrap )
			.type( 'Let cool before serving.', { delay: 0 } );

		cy.log( 'Save the recipe' );
		cy.get( '.tasty-recipes-button-insert' )
			.should( 'be.visible' )
			.click();

		cy.log( 'Handle post-creation embed modal' );
		cy.get( '.tasty-recipes-modal-select-skip-link', {
			timeout: 10000,
		} ).click();

		cy.get( '.tasty-recipes-modal', { timeout: 10000 } ).should(
			'not.be.visible'
		);
	} );

	it( 'should verify saved recipe appears in Explorer list', () => {
		cy.get( '.wp-list-table' ).should( 'exist' );
		cy.contains( '.wp-list-table td', 'Cypress Created Recipe' ).should(
			'exist'
		);
	} );
} );
