describe( 'Create Tasty Recipe (Admin)', () => {
	const openCreateRecipeModal = () => {
		// Wait until Explorer JS has initialized and bound action handlers.
		cy.get(
			'.row-title.tasty-recipes-edit-recipe, .tasty-recipes-empty-state',
			{ timeout: 10000 }
		).should( 'exist' );

		cy.get(
			'#tasty-create-recipe, .tasty-recipes-empty-actions .tasty-button-primary'
		)
			.first()
			.click();

		cy.get( '.tasty-recipes-modal', { timeout: 10000 } ).should(
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

	it( 'should create a new recipe and persist fields', () => {
		const recipeTitle = `Cypress Created Recipe ${ Date.now() }`;
		const descriptionText = 'This is a Cypress-created recipe description.';

		openCreateRecipeModal();

		cy.get( '.tasty-recipes-frame-content input#title' ).type( recipeTitle );

		cy.get( 'iframe#tasty-recipes-recipe-description_ifr', {
			timeout: 10000,
		} )
			.its( '0.contentDocument.body' )
			.should( 'not.be.empty' )
			.then( cy.wrap )
			.type( descriptionText, { delay: 0 } );

		cy.get( '.tasty-recipes-button-insert' ).click();

		// After successful creation, the embed chooser modal appears.
		cy.get( '.tasty-recipes-modal-select-skip-link', { timeout: 10000 } ).click();

		cy.visit( '/wp-admin/edit.php?post_type=tasty_recipe' );
		cy.contains( '.wp-list-table td', recipeTitle ).should( 'exist' );

		cy.wpDevEval( `
			$recipes = get_posts( array(
				'post_type'      => 'tasty_recipe',
				'posts_per_page' => 1,
				'post_status'    => 'any',
				'title'          => '${ recipeTitle.replace( /'/g, "\\'" ) }',
			) );
			if ( empty( $recipes ) ) {
				echo 'missing';
				exit;
			}
			$recipe_id = $recipes[0]->ID;
			$description = (string) get_post_meta( $recipe_id, 'description', true );
			echo $recipe_id . '|' . $description;
		` ).then( ( result ) => {
			const output = result.stdout.trim();
			expect( output ).to.not.equal( 'missing' );

			const [ id, description ] = output.split( '|' );
			expect( parseInt( id, 10 ) ).to.be.greaterThan( 0 );
			expect( description ).to.include( descriptionText );
		} );
	} );
} );
