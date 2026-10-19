describe( 'Recipe Paste', () => {
	const rowListValue = ( mode ) =>
		cy
			.get( `.tasty-recipes-row-list[data-mode="${ mode }"]` )
			.siblings( `input[name="${ mode }"]` );

	const openPasteFromBlock = () => {
		cy.visit( '/wp-admin/post-new.php' );
		cy.closeWelcomeGuide();
		cy.insertBlock( 'wp-tasty/tasty-recipe', 'Tasty Recipe' );

		cy.getBlockEditor()
			.find( '.tasty-recipes-block-placeholder-buttons', {
				timeout: 10_000,
			} )
			.contains( 'button', 'Paste a recipe' )
			.click();

		cy.get( '.tasty-recipes-modal', { timeout: 10_000 } ).should(
			'be.visible'
		);
		cy.get( '.tasty-recipes-paste-panel__input' ).should( 'be.visible' );
	};

	const pasteFixture = ( name, { html = true } = {} ) => {
		cy.fixture( `paste/${ name }.txt` ).then( ( text ) => {
			const withHtml = html
				? cy.fixture( `paste/${ name }.html` )
				: cy.wrap( '' );

			withHtml.then( ( markup ) => {
				cy.window().then( ( win ) => {
					const clipboardData = new win.DataTransfer();
					if ( markup ) {
						clipboardData.setData( 'text/html', markup );
					}
					clipboardData.setData( 'text/plain', text );

					cy.get( '.tasty-recipes-paste-panel__input' ).trigger(
						'paste',
						{ clipboardData }
					);
				} );
			} );
		} );

		cy.get( '.tasty-recipes-paste-panel__input' ).should(
			'not.have.value',
			''
		);
	};

	const fillRecipe = () => {
		cy.contains( '.tasty-recipes-paste-panel button', 'Fill Recipe' ).click();
		cy.get( '.tasty-recipes-paste-panel' ).should( 'not.exist' );
		cy.get( '.tasty-recipes-paste-notice' ).should( 'be.visible' );
	};

	before( () => {
		cy.loginCached();
		cy.deleteAllTastyRecipes();
	} );

	beforeEach( () => {
		cy.loginCached();
		cy.viewport( 1280, 800 );
	} );

	after( () => {
		cy.deleteAllTastyRecipes();
	} );

	it( 'should open the paste panel without the modal footer and return on cancel', () => {
		openPasteFromBlock();

		cy.get( '.tasty-recipes-frame-content input#title' ).should(
			'not.be.visible'
		);
		cy.get( '.tasty-recipes-frame-toolbar' ).should( 'not.be.visible' );
		cy.contains( '.tasty-recipes-paste-panel button', 'Fill Recipe' ).should(
			'be.disabled'
		);

		cy.contains( '.tasty-recipes-paste-panel button', 'Cancel' ).click();

		cy.get( '.tasty-recipes-paste-panel' ).should( 'not.exist' );
		cy.get( '.tasty-recipes-frame-content input#title' ).should(
			'be.visible'
		);
		cy.get( '.tasty-recipes-frame-toolbar' ).should( 'be.visible' );
	} );

	it( 'should not offer pasting when creating a recipe from the recipes list', () => {
		cy.visit( '/wp-admin/edit.php?post_type=tasty_recipe' );
		cy.get(
			'#tasty-create-recipe, .tasty-recipes-empty-actions .tasty-button-primary',
			{ timeout: 10_000 }
		)
			.first()
			.click();

		cy.get( '.tasty-recipes-frame-content input#title' ).should(
			'be.visible'
		);
		cy.get( '.tasty-recipes-paste-panel' ).should( 'not.exist' );
		cy.get( '.tasty-recipes-frame-title button' ).should( 'not.exist' );
	} );

	it( 'should fill the form directly from a Google Docs paste', () => {
		openPasteFromBlock();
		pasteFixture( 'google-docs' );
		fillRecipe();

		cy.get( 'input#title' ).should( 'have.value', 'Weeknight Turkey Chili' );
		cy.get( '#tasty-recipes-recipe-prep_time' ).should(
			'have.value',
			'10 minutes'
		);
		cy.get( '#tasty-recipes-recipe-yield' ).should( 'have.value', '6' );
		rowListValue( 'ingredients' ).should(
			'have.value',
			'<ul><li>1 lb ground turkey</li><li>1 onion, diced</li><li>2 cans kidney beans, drained</li></ul><h4>For the topping</h4><ul><li>1 cup shredded cheddar</li></ul>'
		);
		rowListValue( 'instructions' ).should(
			'have.value',
			'<ol><li>Brown the turkey and onion in a large pot.</li><li>Add the beans and simmer for 30 minutes.</li></ol>'
		);
		cy.get( '#tasty-recipes-recipe-notes' )
			.should( 'contain', 'Freeze for up to 3 months.' )
			.and( 'not.contain', 'It is cozy' );
	} );

	it( 'should keep Google Docs line-broken text out of the title', () => {
		openPasteFromBlock();
		pasteFixture( 'google-docs-line-breaks' );
		fillRecipe();

		cy.get( 'input[name="description"]' ).should(
			'have.value',
			'<p>This is the description</p>'
		);
		rowListValue( 'ingredients' ).should(
			'have.value',
			'<ul><li>1 liter of water</li><li>200 grams of flour</li><li>20 grams of sugar</li></ul>'
		);
		cy.get( 'input[name="notes"]' ).should(
			'have.value',
			'<p>Mix them all together</p>'
		);
	} );

	const formValues = () =>
		cy
			.get( '.tasty-recipes-frame-content form' )
			.then( ( $form ) =>
				Object.fromEntries( new FormData( $form[ 0 ] ).entries() )
			);

	it( 'should put Method and Video lines in their own fields', () => {
		openPasteFromBlock();
		pasteFixture( 'google-docs-video' );
		fillRecipe();

		cy.get( '#tasty-recipes-recipe-notes' )
			.should( 'not.contain', 'Video' )
			.and( 'not.contain', 'Method' );
		formValues().should( 'deep.include', {
			description: '<p>This is the description</p>',
			ingredients:
				'<ul><li>1 liter of water</li><li>200 grams of flour</li><li>20 grams of sugar</li></ul>',
			instructions: '<ol><li>Have fun while doing it</li></ol>',
			notes: '<p>Mix them all together</p>',
			method: 'Baking',
			video_url: 'https://google.com',
		} );
	} );

	it( 'should fill every editor field from a Label: value paste', () => {
		openPasteFromBlock();
		pasteFixture( 'all-fields', { html: false } );
		fillRecipe();

		formValues().should( 'deep.include', {
			title: 'Lemon Bars',
			description: '<p>Bright and tangy.</p>',
			author_name: 'Jane Doe',
			prep_time: '15 minutes',
			cook_time: '30 minutes',
			total_time: '45 minutes',
			yield: '16 bars',
			category: 'Dessert',
			method: 'Baking',
			cuisine: 'American',
			diet: 'Vegetarian',
			keywords: 'lemon, bars, citrus',
			video_url:
				'https://www.youtube.com/watch?v=dQw4w9WgXcQ&list=PL1234567890abcdef&index=3',
			ingredients: '<ul><li>1 cup flour</li><li>2 lemons</li></ul>',
			instructions:
				'<ol><li>Mix everything.</li><li>Bake until set.</li></ol>',
			notes: '<p>Chill before slicing.</p>',
			serving_size: '1 bar',
			calories: '150',
			sugar: '12g',
			sodium: '40mg',
			fat: '7g',
			saturated_fat: '4g',
			unsaturated_fat: '2g',
			trans_fat: '0g',
			carbohydrates: '20g',
			fiber: '1g',
			protein: '2g',
			cholesterol: '30mg',
		} );
	} );

	it( 'should join hard-wrapped lines from a PDF paste', () => {
		openPasteFromBlock();
		pasteFixture( 'pdf', { html: false } );
		fillRecipe();

		cy.get( 'input#title' ).should( 'have.value', 'Lemon Ricotta Pancakes' );
		cy.get( '#tasty-recipes-recipe-yield' ).should(
			'have.value',
			'8 pancakes'
		);
		rowListValue( 'instructions' ).should(
			'have.value',
			'<ol><li>Whisk the flour and sugar together in a large bowl.</li><li>Fold in the ricotta and eggs until just combined.</li><li>Cook on a hot griddle until golden.</li></ol>'
		);
	} );

	it( 'should parse a Word paste', () => {
		openPasteFromBlock();
		pasteFixture( 'word' );
		fillRecipe();

		cy.get( 'input#title' ).should( 'have.value', 'Banana Bread' );
		rowListValue( 'ingredients' ).should(
			'have.value',
			'<ul><li>3 ripe bananas</li><li>1 ½ cups flour</li></ul>'
		);
	} );

	it( 'should parse a web recipe card paste', () => {
		openPasteFromBlock();
		pasteFixture( 'web' );
		fillRecipe();

		cy.get( 'input#title' ).should( 'have.value', 'Easy Beef Tacos' );
		cy.get( '#tasty-recipes-recipe-cook_time' ).should(
			'have.value',
			'15 minutes'
		);
	} );

	it( 'should undo a fill', () => {
		openPasteFromBlock();
		cy.get( '.tasty-recipes-frame-content input#title' )
			.invoke( 'val' )
			.then( ( titleBefore ) => {
				pasteFixture( 'google-docs' );
				fillRecipe();

				cy.get( 'input#title' ).should(
					'have.value',
					'Weeknight Turkey Chili'
				);

				cy.contains( '.tasty-recipes-paste-notice button', 'Undo' ).click();

				cy.get( '.tasty-recipes-paste-notice' ).should( 'not.exist' );
				cy.get( 'input#title' ).should( 'have.value', titleBefore );
				rowListValue( 'ingredients' ).should( 'have.value', '' );
			} );
	} );

	it( 'should insert a recipe filled from a paste into the block', () => {
		openPasteFromBlock();
		pasteFixture( 'google-docs' );
		fillRecipe();

		cy.get( '.tasty-recipes-button-insert' ).should( 'be.visible' ).click();
		cy.get( '.tasty-recipes-modal', { timeout: 10_000 } ).should(
			'not.be.visible'
		);

		cy.getBlockEditor()
			.find( '.tasty-recipes', { timeout: 15_000 } )
			.should( 'contain', 'ground turkey' );
	} );
} );
