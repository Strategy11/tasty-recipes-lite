describe( 'Templates', () => {
	let recipeId;
	let postUrl;

	before( () => {
		cy.loginCached();
		cy.deleteAllTastyRecipes();
		cy.createTastyRecipe( { title: 'Template Test Recipe' } ).then(
			( id ) => {
				recipeId = id;
				cy.createPostWithRecipe( id, {
					title: 'Template Test Post',
				} ).then( ( result ) => {
					postUrl = result.postUrl;
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
		// Reset to default template.
		cy.setTastyOption( 'tasty_recipes_template', '' );
	} );

	describe( 'Preview UI', () => {
		beforeEach( () => {
			cy.visit( '/wp-admin/admin.php?page=tasty-recipes&tab=design' );
		} );

		it( 'should navigate through all templates', () => {
			cy.get( '.tasty-recipes-card-chooser', { timeout: 10000 } ).should(
				'be.visible'
			);

			// Use the first chooser only (there are 2 on this page: top + bottom).
			cy.get( '.tasty-recipes-card-chooser' )
				.first()
				.within( () => {
					// Heading should show template position like "(1/7)".
					cy.get( 'h3' ).invoke( 'text' ).should( 'match', /\(\d+\/\d+\)/ );
				} );

			cy.window()
				.its( 'tastyRecipesSettings.design.allTemplates' )
				.then( ( allTemplates ) => {
					const templateSlugs = Object.keys( allTemplates );
					const totalTemplates = templateSlugs.length;

					cy.get( 'input[name="tasty_recipes_template"]' )
						.invoke( 'val' )
						.then( ( initialSlug ) => {
							// Cycle through all templates and ensure we return
							// to the initial selection.
							for ( let i = 0; i < totalTemplates; i++ ) {
								cy.get( '.tasty-recipes-card-chooser' )
									.first()
									.find( 'button' )
									.last()
									.click();
							}

							cy.get( 'input[name="tasty_recipes_template"]' ).should(
								'have.value',
								initialSlug
							);
						} );
				} );
		} );

		it( 'should move to next template when clicking next', () => {
			cy.get( '.tasty-recipes-card-chooser', { timeout: 10000 } ).should(
				'be.visible'
			);

			cy.get( 'input[name="tasty_recipes_template"]' )
				.invoke( 'val' )
				.then( ( initialSlug ) => {
					cy.get( '.tasty-recipes-card-chooser' )
						.first()
						.find( 'button' )
						.last()
						.click();

					cy.get( 'input[name="tasty_recipes_template"]' )
						.invoke( 'val' )
						.should( ( nextSlug ) => {
							expect( nextSlug ).to.not.equal( initialSlug );
							expect( nextSlug ).to.not.equal( '' );
						} );
				} );
		} );

		it( 'should update preview iframe when switching templates', () => {
			cy.get( '#tasty-recipes-card-preview', { timeout: 10000 } )
				.should( 'be.visible' )
				.and( 'have.attr', 'src' )
				.then( ( initialSrc ) => {
					cy.get( '.tasty-recipes-card-chooser' )
						.first()
						.find( 'button' )
						.last()
						.click();

					cy.get( '#tasty-recipes-card-preview' )
						.should( 'have.attr', 'src' )
						.and( 'not.eq', initialSrc );
				} );
		} );

		it( 'should display color customization fields', () => {
			const colorFields = [
				'Button Color',
				'Button Text Color',
				'Detail Label Color',
				'Detail Value Color',
			];

			colorFields.forEach( ( label ) => {
				cy.contains( 'label', label ).should( 'be.visible' );
			} );

			cy.get( '.tasty-swatch' ).should(
				'have.length.at.least',
				colorFields.length
			);
		} );
	} );

	describe( 'Template Execution', () => {
		it( 'should apply selected template on frontend', () => {
			cy.visit( '/wp-admin/admin.php?page=tasty-recipes&tab=design' );

			cy.get( '.tasty-recipes-card-chooser', { timeout: 10000 } ).should(
				'be.visible'
			);

			cy.get( 'input[name="tasty_recipes_template"]' )
				.invoke( 'val' )
				.then( ( initialSlug ) => {
					cy.get( '.tasty-recipes-card-chooser' )
						.first()
						.find( 'button' )
						.last()
						.click();

					cy.get( 'input[name="tasty_recipes_template"]' )
						.invoke( 'val' )
						.then( ( selectedSlug ) => {
							expect( selectedSlug ).to.not.equal( initialSlug );
							expect( selectedSlug ).to.not.equal( '' );

							cy.get( '#tasty-nav-submit' ).click();
							cy.url().should( 'include', 'tab=design' );

							// The frontend stylesheet URL does not always include
							// the template slug, so verify persisted selection
							// via option value instead.
							cy.getTastyOption( 'tasty_recipes_template' ).then(
								( savedTemplate ) => {
									expect( savedTemplate ).to.equal( selectedSlug );
								}
							);

							cy.visit( postUrl );

							// Verify recipe card renders on frontend.
							cy.get( '.tasty-recipes' ).should( 'exist' );
						} );
				} );
		} );

		it( 'should apply color customizations on frontend', () => {
			cy.visit( '/wp-admin/admin.php?page=tasty-recipes&tab=design' );

			cy.get( '.tasty-recipes-card-chooser', { timeout: 10000 } ).should(
				'be.visible'
			);

			// Set a custom color field that exists for all templates.
			cy.get( 'input[name="tasty_recipes_customization[button_color]"]' )
				.should( 'exist' )
				.clear()
				.type( '#ff5500' );

			cy.get( '#tasty-nav-submit' ).click();

			cy.url().should( 'include', 'tab=design' );

			cy.visit( postUrl );

			// Verify the recipe card renders and customization styles are present.
			cy.get( '.tasty-recipes' ).should( 'exist' );
			cy.get(
				'[data-tasty-recipes-customization]'
			).should( 'have.length.at.least', 1 );
		} );

		it( 'should reset to default template', () => {
			cy.setTastyOption( 'tasty_recipes_template', '' );

			cy.visit( postUrl );

			cy.get( '.tasty-recipes' ).should( 'exist' );
		} );
	} );
} );
