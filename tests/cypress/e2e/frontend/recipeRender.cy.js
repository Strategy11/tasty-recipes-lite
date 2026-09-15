describe( 'Recipe Render', () => {
	let postUrl;

	before( () => {
		cy.deleteAllTastyRecipes();
		cy.setTastyOption( 'tasty_recipes_template', '' );

		cy.createTastyRecipe( {
			title: 'Chocolate Chip Cookies',
			author_name: 'Chef Cypress',
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
		} ).then( id => {
			cy.createPostWithRecipe( id, {
				title: 'Recipe Render Test',
			} ).then( result => {
				postUrl = result.postUrl;
			} );
		} );
	} );

	after( () => {
		cy.deleteAllTastyRecipes();
		cy.setTastyOptions( {
			tasty_recipes_quick_links: [ 'jump', 'print' ],
			tasty_recipes_card_buttons: '',
			tasty_recipes_default_author_link: '',
			tasty_recipes_shareasale: '',
			tasty_recipes_poweredby: '',
		} );
	} );

	describe( 'Recipe Card', () => {
		beforeEach( () => {
			cy.visit( postUrl );
			cy.viewport( 1280, 720 );
		} );

		it( 'should render recipe card with correct structure', () => {
			cy.get( '.tasty-recipes' )
				.should( 'exist' )
				.and( 'be.visible' );

			cy.get( '.tasty-recipes .tasty-recipes-title' )
				.should( 'exist' )
				.and( 'contain.text', 'Chocolate Chip Cookies' );

			cy.get(
				'.tasty-recipes .tasty-recipes-description, .tasty-recipes .tasty-recipes-ingredients'
			).should( 'exist' );
		} );

		it( 'should render description', () => {
			cy.get( '.tasty-recipes-description' )
				.should( 'exist' )
				.and(
					'contain.text',
					'Classic homemade chocolate chip cookies.'
				);
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
				cy.contains( 'li', '1 cup chocolate chips' ).should(
					'exist'
				);
			} );
		} );

		it( 'should render instructions section', () => {
			cy.get( '.tasty-recipe-instructions' ).should( 'exist' );

			cy.get( '.tasty-recipes-instructions-header' )
				.find( 'h3' )
				.should( 'contain.text', 'Instructions' );

			cy.get( '.tasty-recipes-instructions-body' ).within( () => {
				cy.get( 'li' ).should( 'have.length.at.least', 4 );
				cy.contains( 'li', 'Cream butter and sugar.' ).should(
					'exist'
				);
				cy.contains(
					'li',
					'Bake at 375°F for 10 minutes.'
				).should( 'exist' );
			} );
		} );

		it( 'should render recipe details', () => {
			cy.get( '.tasty-recipes-details' ).should( 'exist' );

			cy.get( '.tasty-recipes-details' ).within( () => {
				cy.contains( 'Chef Cypress' ).should( 'exist' );
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
				.then( $scripts => {
					let foundKeywords = false;

					$scripts.each( ( _, el ) => {
						const json = JSON.parse( el.textContent );
						const graph = json[ '@graph' ] || [ json ];

						graph.forEach( item => {
							if (
								item[ '@type' ] === 'Recipe' &&
								item.keywords
							) {
								foundKeywords = true;
								expect(
									String( item.keywords ).toLowerCase()
								).to.include( 'cookies' );
							}
						} );
					} );

					expect( foundKeywords ).to.equal( true );
				} );
		} );

		it( 'should contain valid JSON-LD structured data', () => {
			cy.get( 'script[type="application/ld+json"]' )
				.should( 'exist' )
				.then( $scripts => {
					let foundRecipe = false;

					$scripts.each( ( _, el ) => {
						const json = JSON.parse( el.textContent );
						const graph = json[ '@graph' ] || [ json ];

						graph.forEach( item => {
							if ( item[ '@type' ] === 'Recipe' ) {
								foundRecipe = true;
								expect( item.name ).to.equal(
									'Chocolate Chip Cookies'
								);
								expect( item.recipeIngredient ).to.be.an(
									'array'
								);
								expect(
									item.recipeInstructions
								).to.be.an( 'array' );
							}
						} );
					} );

					expect( foundRecipe ).to.equal( true );
				} );
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

			cy.get( '.tasty-recipes .tasty-recipes-buttons' ).should(
				'exist'
			);
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
			cy.setTastyOption(
				'tasty_recipes_default_author_link',
				''
			);

			cy.visit( postUrl );

			cy.get( 'span.tasty-recipes-author-name' )
				.should( 'exist' )
				.and( 'contain.text', 'Chef Cypress' );

			cy.get( 'a.tasty-recipes-author-name' ).should(
				'not.exist'
			);
		} );
	} );

	describe( 'Ingredient and instruction link attributes', () => {
		it( 'should render sponsored and nofollow on recipe card links', () => {
			cy.createTastyRecipe( {
				title: `Link Attribute Recipe ${ Date.now() }`,
				ingredients:
					'<ul><li><a href="https://example.com/flour" target="_blank" rel="noopener noreferrer sponsored nofollow">flour</a></li></ul>',
				instructions:
					'<ol><li>Use <a href="https://example.com/pan" target="_blank" rel="noopener noreferrer sponsored">the pan</a>.</li></ol>',
			} ).then( id => {
				cy.createPostWithRecipe( id, {
					title: 'Link Attribute Render Test',
				} ).then( result => {
					cy.visit( result.postUrl );

					cy.get(
						'.tasty-recipes-ingredients-body a[href="https://example.com/flour"]'
					)
						.should( 'have.attr', 'rel' )
						.and( 'include', 'sponsored' )
						.and( 'include', 'nofollow' )
						.and( 'include', 'noopener' );

					cy.get(
						'.tasty-recipes-instructions-body a[href="https://example.com/pan"]'
					)
						.should( 'have.attr', 'rel' )
						.and( 'include', 'sponsored' )
						.and( 'include', 'noopener' )
						.then( $link => {
							expect( $link.attr( 'rel' ) ).to.not.include(
								'nofollow'
							);
						} );
				} );
			} );
		} );
	} );

	describe( 'WP Tasty Affiliate Link', () => {
		it( 'should show powered-by with affiliate link when ID is set', () => {
			cy.setTastyOption( 'tasty_recipes_shareasale', '12345' );

			cy.visit( postUrl );

			cy.get( '.tasty-recipes-plug' ).should( 'exist' );

			cy.get( '.tasty-recipes-plug a' )
				.should( 'have.attr', 'href' )
				.and( 'include', 'wptasty.com/ref/12345' );
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
