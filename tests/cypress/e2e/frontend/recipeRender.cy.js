describe( 'Recipe Render', () => {
	let recipeId;
	let postUrl;

	before( () => {
		cy.login();
		cy.deleteAllTastyRecipes();

		// Reset to default template.
		cy.setTastyOption( 'tasty_recipes_template', '' );

		cy.createTastyRecipe( {
			title: 'Chocolate Chip Cookies',
			author_name: 'Test Chef',
			description: '<p>Classic homemade chocolate chip cookies.</p>',
			ingredients:
				'<ul><li>2 cups flour</li><li>1 cup butter</li><li>1 cup chocolate chips</li></ul>',
			instructions:
				'<ol><li>Cream butter and sugar.</li><li>Add flour gradually.</li><li>Fold in chocolate chips.</li><li>Bake at 375°F for 10 minutes.</li></ol>',
			notes: '<p>Store in an airtight container for up to 5 days.</p>',
			prep_time: '15 minutes',
			cook_time: '10 minutes',
			total_time: '25 minutes',
			yield: '24 cookies',
			category: 'Dessert',
			cuisine: 'American',
			calories: '150',
			keywords: 'cookies, chocolate, baking',
		} ).then( ( id ) => {
			recipeId = id;
			cy.createPostWithRecipe( id, {
				title: 'Recipe Render Test',
			} ).then( ( result ) => {
				postUrl = result.postUrl;
			} );
		} );
	} );

	beforeEach( () => {
		cy.visit( postUrl );
		cy.viewport( 1280, 720 );
	} );

	after( () => {
		cy.login();
		cy.deleteAllTastyRecipes();
	} );

	it( 'should render recipe card with correct structure', () => {
		cy.get( '.tasty-recipes' ).should( 'exist' ).and( 'be.visible' );

		// Default template renders .tasty-recipes-title directly (no entry-header wrapper).
		cy.get( '.tasty-recipes .tasty-recipes-title' )
			.should( 'exist' )
			.and( 'contain.text', 'Chocolate Chip Cookies' );

		// Ensure main recipe body content exists regardless of selected template.
		cy.get(
			'.tasty-recipes .tasty-recipes-description, .tasty-recipes .tasty-recipes-ingredients'
		).should( 'exist' );
	} );

	it( 'should render description', () => {
		cy.get( '.tasty-recipes-description' )
			.should( 'exist' )
			.and( 'contain.text', 'Classic homemade chocolate chip cookies.' );
	} );

	it( 'should render ingredients section', () => {
		cy.get( '.tasty-recipes-ingredients' ).should( 'exist' );

		cy.get( '.tasty-recipes-ingredients-header' )
			.find( 'h3' )
			.should( 'contain.text', 'Ingredients' );

		cy.get( '.tasty-recipes-ingredients-body' ).within( () => {
			cy.get( 'li' ).should( 'have.length.at.least', 3 );
			cy.contains( 'li', '2 cups flour' ).should( 'exist' );
			cy.contains( 'li', '1 cup butter' ).should( 'exist' );
			cy.contains( 'li', '1 cup chocolate chips' ).should( 'exist' );
		} );
	} );

	it( 'should render instructions section', () => {
		cy.get( '.tasty-recipe-instructions' ).should( 'exist' );

		cy.get( '.tasty-recipes-instructions-header' )
			.find( 'h3' )
			.should( 'contain.text', 'Instructions' );

		cy.get( '.tasty-recipes-instructions-body' ).within( () => {
			cy.get( 'li' ).should( 'have.length.at.least', 4 );
			cy.contains( 'li', 'Cream butter and sugar.' ).should( 'exist' );
			cy.contains( 'li', 'Bake at 375°F for 10 minutes.' ).should(
				'exist'
			);
		} );
	} );

	it( 'should render recipe details', () => {
		cy.get( '.tasty-recipes-details' ).should( 'exist' );

		cy.get( '.tasty-recipes-details' ).within( () => {
			cy.contains( 'Test Chef' ).should( 'exist' );
			cy.contains( '15 minutes' ).should( 'exist' );
			cy.contains( '10 minutes' ).should( 'exist' );
			cy.contains( '25 minutes' ).should( 'exist' );
			cy.contains( '24 cookies' ).should( 'exist' );
		} );
	} );

	it( 'should render print button', () => {
		cy.get( 'a.tasty-recipes-print-button' )
			.should( 'exist' )
			.and( 'have.attr', 'href' )
			.and( 'include', 'print' );

		cy.get( 'a.tasty-recipes-print-button' ).should(
			'contain.text',
			'Print'
		);
	} );

	it( 'should render notes section', () => {
		cy.get( '.tasty-recipes-notes' ).should( 'exist' );

		cy.get( '.tasty-recipes-notes h3' ).should(
			'contain.text',
			'Notes'
		);

		cy.get( '.tasty-recipes-notes-body' ).should(
			'contain.text',
			'Store in an airtight container for up to 5 days.'
		);
	} );

	it( 'should include keywords in structured data', () => {
		cy.get( 'script[type="application/ld+json"]' )
			.should( 'exist' )
			.then( ( $scripts ) => {
				let foundKeywords = false;

				$scripts.each( ( _, el ) => {
					const json = JSON.parse( el.textContent );
					const graph = json[ '@graph' ] || [ json ];

					graph.forEach( ( item ) => {
						if ( item[ '@type' ] === 'Recipe' && item.keywords ) {
							foundKeywords = true;
							expect( String( item.keywords ).toLowerCase() ).to.include(
								'cookies'
							);
						}
					} );
				} );

				expect( foundKeywords ).to.be.true;
			} );
	} );

	it( 'should contain valid JSON-LD structured data', () => {
		cy.get( 'script[type="application/ld+json"]' )
			.should( 'exist' )
			.then( ( $scripts ) => {
				let foundRecipe = false;

				$scripts.each( ( _, el ) => {
					const json = JSON.parse( el.textContent );
					const graph = json[ '@graph' ] || [ json ];

					graph.forEach( ( item ) => {
						if ( item[ '@type' ] === 'Recipe' ) {
							foundRecipe = true;
							expect( item.name ).to.equal(
								'Chocolate Chip Cookies'
							);
							expect( item.recipeIngredient ).to.be.an(
								'array'
							);
							expect( item.recipeInstructions ).to.be.an(
								'array'
							);
						}
					} );
				} );

				expect( foundRecipe ).to.be.true;
			} );
	} );
} );
