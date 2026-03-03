describe( 'Recipe Creation', () => {
	const openCreateRecipeModal = () => {
		cy.get(
			'.row-title.tasty-recipes-edit-recipe, .tasty-recipes-empty-state',
			{ timeout: 10_000 }
		).should( 'exist' );

		cy.get(
			'#tasty-create-recipe, .tasty-recipes-empty-actions .tasty-button-primary'
		)
			.first()
			.click();

		cy.get( '.tasty-recipes-modal', { timeout: 10_000 } ).should(
			'be.visible'
		);
	};

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

	it( 'should open create modal with expected initial state', () => {
		openCreateRecipeModal();

		cy.get( 'h1.tasty-recipes-state-creating' )
			.should( 'be.visible' )
			.and( 'have.text', 'Create Recipe' );

		cy.get( '.tasty-recipes-frame-content input#title' )
			.should( 'be.visible' )
			.and( 'have.value', '' );

		cy.get( '.tasty-recipes-frame-content #author_name' )
			.should( 'be.visible' )
			.and( 'have.value', 'admin' );

		cy.get( '.tasty-recipes-modal-close' ).click( { force: true } );
		cy.get( '.tasty-recipes-modal' ).should( 'not.be.visible' );
	} );

	it( 'should open and close the Create Recipe modal', () => {
		openCreateRecipeModal();

		cy.get( 'h1.tasty-recipes-state-creating' )
			.should( 'be.visible' )
			.and( 'have.text', 'Create Recipe' );

		cy.get( '.tasty-recipes-modal-close' ).click( { force: true } );
		cy.get( '.tasty-recipes-modal' ).should( 'not.be.visible' );

		openCreateRecipeModal();
		cy.get( '.tasty-recipes-modal-close' ).click( { force: true } );
	} );

	it( 'should fill in recipe fields and save', () => {
		openCreateRecipeModal();

		cy.get( '.tasty-recipes-frame-content' )
			.find( 'input#title' )
			.type( 'Cypress Created Recipe' );

		cy.get( '#author_name' )
			.should( 'be.visible' )
			.and( 'have.value', 'admin' );

		cy.get( 'iframe#tasty-recipes-recipe-description_ifr', {
			timeout: 10_000,
		} )
			.its( '0.contentDocument.body' )
			.should( 'not.be.empty' )
			.then( cy.wrap )
			.type( 'A delicious test recipe.', { delay: 0 } );

		cy.get( 'iframe#tasty-recipes-recipe-ingredients_ifr', {
			timeout: 10_000,
		} )
			.its( '0.contentDocument.body' )
			.should( 'not.be.empty' )
			.then( cy.wrap )
			.type( '1 cup flour{enter}2 eggs{enter}1/2 cup sugar', {
				delay: 0,
			} );

		cy.get( 'iframe#tasty-recipes-recipe-instructions_ifr', {
			timeout: 10_000,
		} )
			.its( '0.contentDocument.body' )
			.should( 'not.be.empty' )
			.then( cy.wrap )
			.type( 'Mix ingredients.{enter}Bake at 350°F for 25 min.', {
				delay: 0,
			} );

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

		cy.get( 'iframe#tasty-recipes-recipe-notes_ifr', {
			timeout: 10_000,
		} )
			.its( '0.contentDocument.body' )
			.should( 'not.be.empty' )
			.then( cy.wrap )
			.type( 'Let cool before serving.', { delay: 0 } );

		cy.get( '.tasty-recipes-button-insert' ).should( 'be.visible' ).click();

		cy.get( '.tasty-recipes-modal-select-skip-link', {
			timeout: 10_000,
		} ).click();

		cy.get( '.tasty-recipes-modal', { timeout: 10_000 } ).should(
			'not.be.visible'
		);
	} );

	it( 'should verify saved recipe appears in Explorer list', () => {
		cy.get( '.wp-list-table' ).should( 'exist' );
		cy.contains( '.wp-list-table td', 'Cypress Created Recipe' ).should(
			'exist'
		);
	} );

	it( 'should create a new recipe and persist fields in database', () => {
		const recipeTitle = `Cypress Persisted Recipe ${ Date.now() }`;
		const descriptionText =
			'This is a Cypress-created recipe description.';

		openCreateRecipeModal();

		cy.get( '.tasty-recipes-frame-content input#title' ).type(
			recipeTitle
		);

		cy.get( 'iframe#tasty-recipes-recipe-description_ifr', {
			timeout: 10_000,
		} )
			.its( '0.contentDocument.body' )
			.should( 'not.be.empty' )
			.then( cy.wrap )
			.type( descriptionText, { delay: 0 } );

		cy.get( '.tasty-recipes-button-insert' ).click();

		cy.get( '.tasty-recipes-modal-select-skip-link', {
			timeout: 10_000,
		} ).click();

		cy.visit( '/wp-admin/edit.php?post_type=tasty_recipe' );
		cy.contains( '.wp-list-table td', recipeTitle ).should( 'exist' );

		cy.wpDevEval( `
			$recipes = get_posts( array(
				'post_type'      => 'tasty_recipe',
				'posts_per_page' => 1,
				'post_status'    => 'any',
				'title'          => '${ recipeTitle.replaceAll( '\'', String.raw`\'` ) }',
			) );
			if ( empty( $recipes ) ) {
				echo 'missing';
				exit;
			}
			$recipe_id = $recipes[0]->ID;
			$description = (string) get_post_meta( $recipe_id, 'description', true );
			echo $recipe_id . '|' . $description;
		` ).then( result => {
			const output = result.stdout.trim();
			expect( output ).to.not.equal( 'missing' );

			const [ id, description ] = output.split( '|' );
			expect( parseInt( id, 10 ) ).to.be.greaterThan( 0 );
			expect( description ).to.include( descriptionText );
		} );
	} );
} );
