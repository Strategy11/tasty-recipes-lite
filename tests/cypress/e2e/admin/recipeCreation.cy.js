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

	const fillRowList = ( mode, lines ) => {
		const addLabel =
			'ingredients' === mode ? 'Add Ingredient' : 'Add Step';

		lines.forEach( ( line, index ) => {
			if ( index > 0 ) {
				cy.get( `.tasty-recipes-row-list[data-mode="${ mode }"]` )
					.closest( 'fieldset' )
					.contains( 'button', addLabel )
					.click();
			}

			cy.get(
				`.tasty-recipes-row-list[data-mode="${ mode }"] .tasty-recipes-row-input[contenteditable="true"]`
			)
				.eq( index )
				.scrollIntoView()
				.should( 'be.visible' )
				.click()
				.type( line, { delay: 0 } );
		} );
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

		// Author sits below the fold in the fixed modal scroll area.
		cy.get( '.tasty-recipes-frame-content #author_name' )
			.scrollIntoView()
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

		cy.get( '#author_name' ).should( 'have.value', 'admin' );

		cy.typeTastyInlineEditor(
			'tasty-recipes-recipe-description',
			'A delicious test recipe.'
		);

		fillRowList( 'ingredients', [
			'1 cup flour',
			'2 eggs',
			'1/2 cup sugar',
		] );

		fillRowList( 'instructions', [
			'Mix ingredients.',
			'Bake at 350°F for 25 min.',
		] );

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

		cy.typeTastyInlineEditor(
			'tasty-recipes-recipe-notes',
			'Let cool before serving.'
		);

		cy.get( '.tasty-recipes-button-insert' ).should( 'be.visible' ).click();

		cy.get( '.tasty-recipes-modal-select-skip-link', {
			timeout: 10_000,
		} ).click();

		cy.get( '.tasty-recipes-modal', { timeout: 10_000 } ).should(
			'not.be.visible'
		);
	} );

	it( 'should preserve row-list section titles and reordered items', () => {
		const recipeTitle = `Cypress Row List Recipe ${ Date.now() }`;

		openCreateRecipeModal();
		cy.get( '.tasty-recipes-frame-content input#title' ).type(
			recipeTitle
		);

		const ingredients = '.tasty-recipes-row-list[data-mode="ingredients"]';

		cy.get( `${ ingredients } .tasty-recipes-row-input` )
			.first()
			.click()
			.type( 'First ingredient' );
		cy.get( ingredients )
			.closest( 'fieldset' )
			.contains( 'button', 'Add Ingredient' )
			.click();
		cy.get( `${ ingredients } .tasty-recipes-row-input` )
			.eq( 1 )
			.click()
			.type( 'Second ingredient' );
		cy.get( `${ ingredients } .tasty-recipes-item-row` )
			.first()
			.find( 'button[aria-label="Move down"]' )
			.click( { force: true } );
		cy.get( `${ ingredients } .tasty-recipes-row-input` )
			.eq( 1 )
			.trigger( 'keydown', { altKey: true, key: 'ArrowUp' } );

		cy.get( ingredients )
			.closest( 'fieldset' )
			.contains( 'button', 'Add Section Title' )
			.click();
		cy.get( `${ ingredients } .tasty-recipes-row-input[type="text"]` )
			.last()
			.type( 'For serving' );

		cy.get( `${ ingredients }` )
			.siblings( 'input[name="ingredients"]' )
			.should(
				'have.value',
				'<ul><li>First ingredient</li><li>Second ingredient</li></ul><h4>For serving</h4>'
			);

		cy.get( '.tasty-recipes-button-insert' ).click();
		cy.get( '.tasty-recipes-modal-select-skip-link', {
			timeout: 10_000,
		} ).click();

		cy.wpDevEval( `
			$recipes = get_posts( array(
				'post_type'      => 'tasty_recipe',
				'posts_per_page' => 1,
				'post_status'    => 'any',
				'title'          => '${ recipeTitle.replaceAll( '\'', String.raw`\'` ) }',
			) );
			echo empty( $recipes )
				? 'missing'
				: get_post_meta( $recipes[0]->ID, 'ingredients', true );
		` ).then( result => {
			expect( result.stdout.trim() ).to.equal(
				'<ul><li>First ingredient</li><li>Second ingredient</li></ul><h4>For serving</h4>'
			);
		} );
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

		cy.typeTastyInlineEditor(
			'tasty-recipes-recipe-description',
			descriptionText
		);

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
