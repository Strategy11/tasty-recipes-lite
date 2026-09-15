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

		// Asserted after each step so a no-op reorder cannot cancel out.
		cy.get( ingredients )
			.siblings( 'input[name="ingredients"]' )
			.should(
				'have.value',
				'<ul><li>Second ingredient</li><li>First ingredient</li></ul>'
			);

		cy.get( `${ ingredients } .tasty-recipes-row-input` )
			.eq( 1 )
			.trigger( 'keydown', { altKey: true, key: 'ArrowUp' } );

		cy.get( ingredients )
			.siblings( 'input[name="ingredients"]' )
			.should(
				'have.value',
				'<ul><li>First ingredient</li><li>Second ingredient</li></ul>'
			);

		cy.get( ingredients )
			.closest( 'fieldset' )
			.contains( 'button', 'Add Section Title' )
			.click();
		cy.get(
			`${ ingredients } .tasty-recipes-item-row--group .tasty-recipes-row-input`
		)
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

	describe( 'Row list fidelity', () => {
		const ingredients = '.tasty-recipes-row-list[data-mode="ingredients"]';
		const instructions =
			'.tasty-recipes-row-list[data-mode="instructions"]';

		const openEditRecipeModal = title => {
			cy.get( '.row-title.tasty-recipes-edit-recipe', {
				timeout: 10_000,
			} ).should( 'exist' );

			cy.contains( '.wp-list-table td', title )
				.closest( 'tr' )
				.find( '.row-actions .tasty-recipes-edit-recipe' )
				.click( { force: true } );

			cy.get( '.tasty-recipes-modal', { timeout: 10_000 } ).should(
				'be.visible'
			);
		};

		const getRecipeMeta = ( title, keys ) => {
			const echoed = keys
				.map( key => `get_post_meta( $recipe_id, '${ key }', true )` )
				.join( " . '~~' . " );
			const phpTitle = title.replaceAll( '\'', String.raw`\'` );

			return cy
				.wpDevEval(
					`
				$recipes = get_posts( array(
					'post_type'      => 'tasty_recipe',
					'posts_per_page' => 1,
					'post_status'    => 'any',
					'title'          => '${ phpTitle }',
				) );
				if ( empty( $recipes ) ) {
					echo 'missing';
					exit;
				}
				$recipe_id = $recipes[0]->ID;
				echo ${ echoed };
			`
				)
				.then( result => {
					const output = result.stdout.trim();
					expect( output ).to.not.equal( 'missing' );
					return output.split( '~~' );
				} );
		};

		const pasteText = ( el, win, text ) => {
			const data = new win.DataTransfer();
			data.setData( 'text', text );
			el.dispatchEvent(
				new win.ClipboardEvent( 'paste', {
					clipboardData: data,
					bubbles: true,
					cancelable: true,
				} )
			);
		};

		it( 'should keep image-only paragraphs outside numbered instruction steps', () => {
			const recipeTitle = `Cypress Process Photo ${ Date.now() }`;
			const legacyInstructions =
				'<p>Whip the cream until soft peaks form.</p>' +
				'<p><img src="/wp-content/uploads/process-one.jpg" alt="Process one" class="aligncenter size-full wp-image-11" /></p>' +
				'<p>Sift in the matcha.</p>' +
				'<p><img src="/wp-content/uploads/process-two.jpg" alt="Process two" class="aligncenter size-full wp-image-12" /></p>' +
				'<ol>' +
				'<li>Serve immediately.</li>' +
				'<li>Store leftovers.' +
				'<img src="/wp-content/uploads/in-step.jpg" alt="In step" class="wp-image-13" />' +
				'</li>' +
				'</ol>';

			cy.createTastyRecipe( {
				title: recipeTitle,
				instructions: legacyInstructions,
			} );

			cy.visit( '/wp-admin/edit.php?post_type=tasty_recipe' );
			openEditRecipeModal( recipeTitle );

			cy.get( `${ instructions } .tasty-recipes-item-row` ).should(
				'have.length',
				6
			);
			cy.get( `${ instructions } .tasty-recipes-row-image img` ).should(
				'have.length',
				3
			);
			cy.get(
				`${ instructions } .tasty-recipes-row-image img[src*="process-one.jpg"]`
			).should( 'exist' );
			cy.get(
				`${ instructions } .tasty-recipes-row-image img[src*="process-two.jpg"]`
			).should( 'exist' );
			cy.get(
				`${ instructions } .tasty-recipes-row-image img[src*="in-step.jpg"]`
			).should( 'exist' );

			cy.get( '.tasty-recipes-button-update' ).click();
			cy.get( '.tasty-recipes-modal', { timeout: 10_000 } ).should(
				'not.be.visible'
			);

			getRecipeMeta( recipeTitle, [ 'instructions' ] ).then(
				( [ savedInstructions ] ) => {
					expect( savedInstructions ).to.include( 'process-one.jpg' );
					expect( savedInstructions ).to.include( 'process-two.jpg' );
					expect( savedInstructions ).to.include( 'in-step.jpg' );
					expect( savedInstructions ).to.include(
						'Whip the cream until soft peaks form.'
					);
					expect( savedInstructions ).to.include(
						'Sift in the matcha.'
					);
					expect( savedInstructions ).to.include(
						'Serve immediately.'
					);
				}
			);
		} );

		it( 'should keep linked process photos outside numbered instruction steps', () => {
			const recipeTitle = `Cypress Linked Process Photo ${ Date.now() }`;
			const imageUrl = '/wp-content/uploads/process-linked.jpg';
			const legacyInstructions =
				'<p>Whip the cream until soft peaks form.</p>' +
				`<p><a href="${ imageUrl }"><img src="${ imageUrl }" alt="Process photo" /></a></p>` +
				'<ol><li>Serve immediately.</li></ol>';

			cy.createTastyRecipe( {
				title: recipeTitle,
				instructions: legacyInstructions,
			} );

			cy.visit( '/wp-admin/edit.php?post_type=tasty_recipe' );
			openEditRecipeModal( recipeTitle );

			cy.get( `${ instructions } .tasty-recipes-item-row` ).should(
				'have.length',
				3
			);
			cy.get(
				`${ instructions } .tasty-recipes-row-image img[src*="process-linked.jpg"]`
			).should( 'exist' );

			cy.get( '#tasty-recipes-recipe-yield' )
				.scrollIntoView()
				.clear()
				.type( '6 servings', { delay: 0 } );

			cy.get( '.tasty-recipes-button-update' ).click();
			cy.get( '.tasty-recipes-modal', { timeout: 10_000 } ).should(
				'not.be.visible'
			);

			getRecipeMeta( recipeTitle, [ 'instructions' ] ).then(
				( [ savedInstructions ] ) => {
					expect( savedInstructions ).to.include(
						'process-linked.jpg'
					);
					expect( savedInstructions ).to.match(
						/<img[^>]+src="[^"]*process-linked\.jpg"/
					);
					expect( savedInstructions ).to.match(
						/<a href="[^"]*process-linked\.jpg"[^>]*>[\s\S]*<img[^>]+src="[^"]*process-linked\.jpg"/
					);
					expect( savedInstructions ).to.not.match(
						/<a href="[^"]*process-linked\.jpg"><\/a>/
					);
					expect( savedInstructions ).to.include(
						'Whip the cream until soft peaks form.'
					);
					expect( savedInstructions ).to.include(
						'Serve immediately.'
					);
				}
			);
		} );

		it( 'should keep legacy markup when saving an untouched recipe', () => {
			const recipeTitle = `Cypress Legacy Recipe ${ Date.now() }`;
			const legacyIngredients =
				'<h2>For the dough</h2>' +
				'<ul>' +
				'<li>1 cup <a href="/go/flour" title="Buy flour">flour</a>' +
				'<ul><li>or 1 cup gluten-free blend</li></ul>' +
				'</li>' +
				'<li>2 eggs</li>' +
				'</ul>';
			const legacyInstructions =
				'<ol>' +
				'<li>Preheat oven.<br />Line the pan.</li>' +
				'<li>Mix well.<figure>' +
				'<img src="/wp-content/uploads/step-one.jpg" alt="Step one" class="wp-image-42" />' +
				'<figcaption>Mixing bowl</figcaption>' +
				'</figure></li>' +
				'<li>Bake.' +
				'<img src="/wp-content/uploads/step-two.jpg" alt="Step two" />' +
				'<img src="/wp-content/uploads/step-three.jpg" alt="Step three" />' +
				'</li>' +
				'</ol>';

			cy.createTastyRecipe( {
				title: recipeTitle,
				ingredients: legacyIngredients,
				instructions: legacyInstructions,
			} );

			cy.visit( '/wp-admin/edit.php?post_type=tasty_recipe' );
			openEditRecipeModal( recipeTitle );

			// Legacy markup of this shape belongs in the row list, not the
			// classic editor fallback.
			cy.get( ingredients ).should( 'exist' );
			cy.get( instructions ).should( 'exist' );

			cy.get( '.tasty-recipes-button-update' ).click();
			cy.get( '.tasty-recipes-modal', { timeout: 10_000 } ).should(
				'not.be.visible'
			);

			getRecipeMeta( recipeTitle, [ 'ingredients', 'instructions' ] ).then(
				( [ savedIngredients, savedInstructions ] ) => {
					expect( savedIngredients ).to.include(
						'<h2>For the dough</h2>'
					);
					expect( savedIngredients ).to.include( 'href="/go/flour"' );
					expect( savedIngredients ).to.include(
						'or 1 cup gluten-free blend'
					);
					expect( savedIngredients ).to.include( '2 eggs' );

					expect( savedInstructions ).to.include( 'Line the pan' );
					expect( savedInstructions ).to.match( /<br\s*\/?>/ );
					expect( savedInstructions ).to.include( 'Mixing bowl' );
					expect( savedInstructions ).to.include( 'step-one.jpg' );
					expect( savedInstructions ).to.include( 'step-two.jpg' );
					expect( savedInstructions ).to.include( 'step-three.jpg' );
				}
			);
		} );

		it( 'should bulk add pasted lines and keep the step image', () => {
			const recipeTitle = `Cypress Paste Recipe ${ Date.now() }`;

			cy.createTastyRecipe( {
				title: recipeTitle,
				instructions:
					'<ol><li>Whisk the eggs.' +
					'<img src="/wp-content/uploads/whisk.jpg" alt="Whisk" class="wp-image-7" />' +
					'</li></ol>',
			} );

			cy.visit( '/wp-admin/edit.php?post_type=tasty_recipe' );
			openEditRecipeModal( recipeTitle );

			cy.get( `${ instructions } .tasty-recipes-row-input` )
				.first()
				.scrollIntoView()
				.click();

			cy.window().then( win => {
				cy.get( `${ instructions } .tasty-recipes-row-input` )
					.first()
					.then( $el =>
						pasteText(
							$el[ 0 ],
							win,
							'Fold the batter\n1 - 2 tsp salt'
						)
					);
			} );

			cy.get( `${ instructions } .tasty-recipes-item-row` ).should(
				'have.length',
				3
			);

			cy.get( instructions )
				.siblings( 'input[name="instructions"]' )
				.invoke( 'val' )
				.then( value => {
					expect( value ).to.include( 'whisk.jpg' );
					expect( value ).to.include( '<li>Fold the batter</li>' );
					// The marker stripper must not eat the range's lower bound.
					expect( value ).to.include( '<li>1 - 2 tsp salt</li>' );
				} );
		} );

		it( 'should insert a line break in the same row on Shift+Enter', () => {
			openCreateRecipeModal();
			cy.get( '.tasty-recipes-frame-content input#title' ).type(
				`Cypress Shift Enter Recipe ${ Date.now() }`
			);

			cy.get( `${ ingredients } .tasty-recipes-row-input` )
				.first()
				.click()
				.type( 'First line' );
			cy.get( `${ ingredients } .tasty-recipes-row-input` )
				.first()
				.trigger( 'keydown', { key: 'Enter', shiftKey: true } );
			cy.get( `${ ingredients } .tasty-recipes-row-input` )
				.first()
				.type( 'Same row' );

			cy.get( `${ ingredients } .tasty-recipes-item-row` ).should(
				'have.length',
				1
			);
			cy.get( ingredients )
				.siblings( 'input[name="ingredients"]' )
				.invoke( 'val' )
				.then( value => {
					expect( value ).to.include( 'tasty-recipes-soft-break' );
					expect( value ).to.include( 'First line' );
					expect( value ).to.include( 'Same row' );
				} );

			cy.get( '.tasty-recipes-modal-close' ).click( { force: true } );
		} );

		it( 'should keep empty soft breaks after reopening a recipe', () => {
			const recipeTitle = `Cypress Soft Breaks ${ Date.now() }`;

			cy.createTastyRecipe( {
				title: recipeTitle,
				ingredients:
					'<ul><li>First line<br><br>After blanks</li></ul>',
				instructions:
					'<ol><li>Mix well<br><br></li></ol>',
			} );

			cy.visit( '/wp-admin/edit.php?post_type=tasty_recipe' );
			openEditRecipeModal( recipeTitle );

			cy.get( ingredients )
				.siblings( 'input[name="ingredients"]' )
				.invoke( 'val' )
				.then( value => {
					expect( value ).to.match(
						/First line.*tasty-recipes-soft-break.*tasty-recipes-soft-break.*After blanks/s
					);
				} );
			cy.get( instructions )
				.siblings( 'input[name="instructions"]' )
				.invoke( 'val' )
				.then( value => {
					expect( value ).to.match(
						/Mix well.*tasty-recipes-soft-break/s
					);
				} );
		} );

		it( 'should start a new row when pressing Enter', () => {
			openCreateRecipeModal();
			cy.get( '.tasty-recipes-frame-content input#title' ).type(
				`Cypress Enter Recipe ${ Date.now() }`
			);

			cy.get( `${ ingredients } .tasty-recipes-row-input` )
				.first()
				.click()
				.type( 'First line' );
			cy.get( `${ ingredients } .tasty-recipes-row-input` )
				.first()
				.trigger( 'keydown', { key: 'Enter' } );

			cy.get( `${ ingredients } .tasty-recipes-item-row` ).should(
				'have.length',
				2
			);

			cy.get( `${ ingredients } .tasty-recipes-row-input` )
				.eq( 1 )
				.type( 'Second line' );

			cy.get( ingredients )
				.siblings( 'input[name="ingredients"]' )
				.should(
					'have.value',
					'<ul><li>First line</li><li>Second line</li></ul>'
				);

			cy.get( '.tasty-recipes-modal-close' ).click( { force: true } );
		} );

		it( 'should reorder rows by dragging the grip', () => {
			openCreateRecipeModal();
			cy.get( '.tasty-recipes-frame-content input#title' ).type(
				`Cypress Drag Recipe ${ Date.now() }`
			);

			cy.get( `${ ingredients } .tasty-recipes-row-input` )
				.first()
				.click()
				.type( 'Top ingredient' );
			cy.get( ingredients )
				.closest( 'fieldset' )
				.contains( 'button', 'Add Ingredient' )
				.click();
			cy.get( `${ ingredients } .tasty-recipes-row-input` )
				.eq( 1 )
				.click()
				.type( 'Bottom ingredient' );

			cy.get( `${ ingredients } .tasty-recipes-item-row` ).then(
				$rows => {
					const first = $rows[ 0 ].getBoundingClientRect();
					const second = $rows[ 1 ].getBoundingClientRect();

					cy.wrap( $rows[ 0 ] )
						.find( '.tasty-recipes-drag-grip' )
						.trigger( 'pointerdown', {
							button: 0,
							pointerId: 1,
							clientX: first.left + 4,
							clientY: first.top + ( first.height / 2 ),
							force: true,
						} );

					cy.get( 'body' ).trigger( 'pointermove', {
						pointerId: 1,
						clientX: first.left + 4,
						clientY: second.bottom,
						force: true,
					} );

					// Reordering lands on the next frame, so the retried
					// assertion doubles as the wait for it.
					cy.get( ingredients )
						.siblings( 'input[name="ingredients"]' )
						.should(
							'have.value',
							'<ul><li>Bottom ingredient</li><li>Top ingredient</li></ul>'
						);

					cy.get( 'body' ).trigger( 'pointerup', {
						pointerId: 1,
						force: true,
					} );
				}
			);

			cy.get( ingredients )
				.siblings( 'input[name="ingredients"]' )
				.should(
					'have.value',
					'<ul><li>Bottom ingredient</li><li>Top ingredient</li></ul>'
				);

			cy.get( '.tasty-recipes-modal-close' ).click( { force: true } );
		} );

		const selectNodeContents = el => {
			const doc = el.ownerDocument;
			const range = doc.createRange();
			range.selectNodeContents( el );
			const selection = doc.defaultView.getSelection();
			selection.removeAllRanges();
			selection.addRange( range );
		};

		const assertRowActionsOnFocus = ( list, mode, lines ) => {
			openCreateRecipeModal();
			cy.get( '.tasty-recipes-frame-content input#title' ).type(
				`Cypress ${ mode } Toolbar ${ Date.now() }`
			);

			fillRowList( mode, lines );

			cy.get( `${ list } .tasty-recipes-item-row` )
				.eq( 0 )
				.scrollIntoView()
				.find( '.tasty-recipes-row-actions' )
				.should( 'not.be.visible' );
			cy.get( `${ list } .tasty-recipes-item-row` )
				.eq( 1 )
				.find( '.tasty-recipes-row-actions' )
				.should( 'be.visible' );

			cy.get( `${ list } .tasty-recipes-item-row` )
				.eq( 0 )
				.trigger( 'mouseover' )
				.find( '.tasty-recipes-row-actions' )
				.should( 'not.be.visible' );

			cy.get( `${ list } .tasty-recipes-row-input` )
				.first()
				.click();

			cy.get( `${ list } .tasty-recipes-item-row` )
				.eq( 0 )
				.find( '.tasty-recipes-row-actions' )
				.should( 'be.visible' );
			cy.get( `${ list } .tasty-recipes-item-row` )
				.eq( 1 )
				.find( '.tasty-recipes-row-actions' )
				.should( 'not.be.visible' );

			cy.get( `${ list } .tasty-recipes-row-input` )
				.first()
				.then( $el => selectNodeContents( $el[ 0 ] ) );

			cy.get( list ).should(
				'have.class',
				'tasty-recipes-row-list--has-selection'
			);
			cy.get( `${ list } .tasty-recipes-item-row` )
				.eq( 0 )
				.find( '.tasty-recipes-row-actions' )
				.should( 'not.be.visible' );
			cy.get( '.tasty-recipes-selection-toolbar' ).should( 'be.visible' );

			cy.get( `${ list } .tasty-recipes-row-input` )
				.first()
				.then( $el => {
					$el[ 0 ].ownerDocument.defaultView
						.getSelection()
						.collapseToEnd();
				} );

			cy.get( list ).should(
				'not.have.class',
				'tasty-recipes-row-list--has-selection'
			);
			cy.get( `${ list } .tasty-recipes-item-row` )
				.eq( 0 )
				.find( '.tasty-recipes-row-actions' )
				.should( 'be.visible' );
			cy.get( '.tasty-recipes-selection-toolbar' ).should( 'not.exist' );

			cy.get( '.tasty-recipes-modal-close' ).click( { force: true } );
		};

		it( 'should show ingredient row actions only on the focused unselected row', () => {
			assertRowActionsOnFocus( ingredients, 'ingredients', [
				'2 cups flour',
				'1 tsp salt',
			] );
		} );

		it( 'should show instruction row actions only on the focused unselected row', () => {
			assertRowActionsOnFocus( instructions, 'instructions', [
				'Mix the batter well.',
				'Bake until golden.',
			] );
		} );

		const openRowLinkPopover = list => {
			cy.get( `${ list } .tasty-recipes-row-input` )
				.first()
				.then( $el => {
					const link = $el.find( 'a' ).get( 0 );
					if ( link ) {
						cy.wrap( link ).click();
						return;
					}

					selectNodeContents( $el[ 0 ] );
					cy.get(
						'.tasty-recipes-selection-toolbar [aria-label="Add link"]'
					).click();
				} );
			cy.get( '.tasty-recipes-selection-link-popover' ).should(
				'be.visible'
			);
		};

		const assertLinkRelOption = ( label, checked ) => {
			cy.get( '.tasty-recipes-selection-link-popover' )
				.contains( 'label', label )
				.should( checked ? 'have.class' : 'not.have.class', 'is-checked' );
		};

		const toggleLinkRelOption = label => {
			cy.get( '.tasty-recipes-selection-link-popover' )
				.contains( 'label', label )
				.click();
		};

		it( 'should default link attributes off and persist sponsored and nofollow', () => {
			openCreateRecipeModal();
			cy.get( '.tasty-recipes-frame-content input#title' ).type(
				`Cypress Link Attrs ${ Date.now() }`
			);

			cy.get( `${ ingredients } .tasty-recipes-row-input` )
				.first()
				.click()
				.type( 'affiliate flour' );

			openRowLinkPopover( ingredients );
			assertLinkRelOption( 'Sponsored', false );
			assertLinkRelOption( 'Nofollow', false );

			cy.get( '.tasty-recipes-selection-link-popover input[type="text"]' )
				.clear()
				.type( 'https://example.com/flour' );
			toggleLinkRelOption( 'Sponsored' );
			assertLinkRelOption( 'Sponsored', true );
			assertLinkRelOption( 'Nofollow', false );
			cy.get( '.tasty-recipes-selection-link-popover' )
				.contains( 'button', 'Add' )
				.click();

			cy.get( ingredients )
				.siblings( 'input[name="ingredients"]' )
				.invoke( 'val' )
				.then( value => {
					expect( value ).to.include( 'https://example.com/flour' );
					expect( value ).to.include( 'sponsored' );
					expect( value ).to.not.include( 'nofollow' );
					expect( value ).to.include( 'noopener' );
					expect( value ).to.include( 'noreferrer' );
				} );

			openRowLinkPopover( ingredients );
			assertLinkRelOption( 'Sponsored', true );
			assertLinkRelOption( 'Nofollow', false );
			toggleLinkRelOption( 'Nofollow' );
			assertLinkRelOption( 'Nofollow', true );
			cy.get( '.tasty-recipes-selection-link-popover' )
				.contains( 'button', 'Update' )
				.click();

			cy.get( ingredients )
				.siblings( 'input[name="ingredients"]' )
				.invoke( 'val' )
				.then( value => {
					expect( value ).to.include( 'sponsored' );
					expect( value ).to.include( 'nofollow' );
				} );

			cy.get( `${ instructions } .tasty-recipes-row-input` )
				.first()
				.click()
				.type( 'Buy a mixer' );

			openRowLinkPopover( instructions );
			assertLinkRelOption( 'Sponsored', false );
			assertLinkRelOption( 'Nofollow', false );
			cy.get( '.tasty-recipes-selection-link-popover input[type="text"]' )
				.clear()
				.type( 'https://example.com/mixer' );
			toggleLinkRelOption( 'Nofollow' );
			cy.get( '.tasty-recipes-selection-link-popover' )
				.contains( 'button', 'Add' )
				.click();

			cy.get( instructions )
				.siblings( 'input[name="instructions"]' )
				.invoke( 'val' )
				.then( value => {
					expect( value ).to.include( 'https://example.com/mixer' );
					expect( value ).to.include( 'nofollow' );
					expect( value ).to.not.include( 'sponsored' );
				} );

			cy.get( '.tasty-recipes-modal-close' ).click( { force: true } );
		} );

		it( 'should restore saved link attributes when editing a recipe', () => {
			const recipeTitle = `Cypress Saved Link Attrs ${ Date.now() }`;

			cy.createTastyRecipe( {
				title: recipeTitle,
				ingredients:
					'<ul><li><a href="https://example.com/sugar" target="_blank" rel="noopener noreferrer sponsored nofollow">sugar</a></li></ul>',
			} );

			cy.visit( '/wp-admin/edit.php?post_type=tasty_recipe' );
			openEditRecipeModal( recipeTitle );

			openRowLinkPopover( ingredients );
			assertLinkRelOption( 'Sponsored', true );
			assertLinkRelOption( 'Nofollow', true );
			cy.get( '.tasty-recipes-selection-link-popover input[type="text"]' )
				.should( 'have.value', 'https://example.com/sugar' );

			cy.get( '.tasty-recipes-modal-close' ).click( { force: true } );
		} );

		it( 'should open link settings when clicking a recipe editor link', () => {
			const recipeTitle = `Cypress Click Link ${ Date.now() }`;

			cy.createTastyRecipe( {
				title: recipeTitle,
				ingredients:
					'<ul><li><a href="https://example.com/sugar" target="_blank" rel="noopener noreferrer sponsored">sugar</a></li></ul>',
			} );

			cy.visit( '/wp-admin/edit.php?post_type=tasty_recipe' );
			openEditRecipeModal( recipeTitle );

			cy.get( `${ ingredients } .tasty-recipes-row-input a` )
				.should( 'be.visible' )
				.click();

			cy.get( '.tasty-recipes-selection-link-popover' ).should(
				'be.visible'
			);
			cy.get( '.tasty-recipes-selection-link-popover input[type="text"]' )
				.should( 'have.value', 'https://example.com/sugar' );
			assertLinkRelOption( 'Sponsored', true );
			assertLinkRelOption( 'Nofollow', false );
			cy.get( '.tasty-recipes-selection-link-popover' )
				.contains( 'button', 'Update' )
				.should( 'be.visible' );

			cy.get( '.tasty-recipes-modal-close' ).click( { force: true } );
		} );

		it( 'should apply bulk edit headings from the toolbar', () => {
			const selectFirstText = el => {
				const doc = el.ownerDocument;
				const walker = doc.createTreeWalker(
					el,
					doc.defaultView.NodeFilter.SHOW_TEXT
				);
				const text = walker.nextNode();
				if ( ! text ) {
					return;
				}

				const range = doc.createRange();
				range.selectNodeContents( text );
				const selection = doc.defaultView.getSelection();
				selection.removeAllRanges();
				selection.addRange( range );
			};

			openCreateRecipeModal();
			cy.get( '.tasty-recipes-frame-content input#title' ).type(
				`Cypress Bulk Edit ${ Date.now() }`
			);

			cy.get( ingredients )
				.closest( 'fieldset' )
				.contains( 'button', 'Bulk Edit' )
				.click();

			cy.get( `${ ingredients }` ).should( 'not.exist' );
			cy.get( '.tasty-recipes-bulk-toolbar' ).should( 'be.visible' );
			cy.get( '.tasty-recipes-bulk-toolbar [aria-label="Bold"]' ).should(
				'be.visible'
			);
			cy.get(
				'.tasty-recipes-bulk-toolbar [aria-label="Heading"]'
			).should( 'be.visible' );
			cy.get(
				'.tasty-recipes-bulk-toolbar [aria-label="Add image"]'
			).should( 'not.exist' );

			cy.get( '.tasty-recipes-row-list-bulk' )
				.first()
				.should( 'have.attr', 'contenteditable', 'true' )
				.click()
				.type(
					'{selectall}{backspace}For the dressing{enter}2 anchovy fillets{enter}1 tsp Dijon mustard'
				);

			cy.get( '.tasty-recipes-row-list-bulk' )
				.first()
				.then( $el => selectFirstText( $el[ 0 ] ) );

			cy.get(
				'.tasty-recipes-bulk-toolbar [aria-label="Heading"]'
			).click();

			cy.contains( 'button', 'Done' ).click();

			cy.get( `${ ingredients } .tasty-recipes-item-row--group` )
				.first()
				.should( 'contain', 'For the dressing' );
			cy.get( ingredients )
				.siblings( 'input[name="ingredients"]' )
				.should(
					'have.value',
					'<h4>For the dressing</h4><ul><li>2 anchovy fillets</li><li>1 tsp Dijon mustard</li></ul>'
				);

			cy.get( '.tasty-recipes-modal-close' ).click( { force: true } );
		} );

		it( 'should not insert a blank line after a bulk heading', () => {
			openCreateRecipeModal();
			cy.get( '.tasty-recipes-frame-content input#title' ).type(
				`Cypress Bulk Heading Gap ${ Date.now() }`
			);

			fillRowList( 'ingredients', [ 'test', 'test 2', 'test 3' ] );

			cy.get( ingredients )
				.closest( 'fieldset' )
				.contains( 'button', 'Add Section Title' )
				.click();
			cy.get(
				`${ ingredients } .tasty-recipes-item-row--group .tasty-recipes-row-input`
			)
				.last()
				.type( 'For serving' );

			cy.get( `${ ingredients } .tasty-recipes-item-row` )
				.eq( 0 )
				.find( 'button[aria-label="Convert to section title"]' )
				.click( { force: true } );

			cy.get( ingredients )
				.closest( 'fieldset' )
				.contains( 'button', 'Bulk Edit' )
				.click();

			cy.get( '.tasty-recipes-row-list-bulk' )
				.first()
				.should( 'have.attr', 'contenteditable', 'true' )
				.invoke( 'html' )
				.then( html => {
					expect( html ).to.not.match( /<\/h[1-6]>\s*<br\s*\/?>/i );
				} );

			cy.contains( 'button', 'Done' ).click();
			cy.get( ingredients )
				.closest( 'fieldset' )
				.contains( 'button', 'Bulk Edit' )
				.click();

			cy.get( '.tasty-recipes-row-list-bulk' )
				.first()
				.invoke( 'html' )
				.then( html => {
					expect( html ).to.not.match( /<\/h[1-6]>\s*<br\s*\/?>/i );
				} );

			cy.get( '.tasty-recipes-modal-close' ).click( { force: true } );
		} );

		it( 'should keep Shift+Enter as a line break in bulk edit', () => {
			openCreateRecipeModal();
			cy.get( '.tasty-recipes-frame-content input#title' ).type(
				`Cypress Bulk Shift Enter ${ Date.now() }`
			);

			cy.get( ingredients )
				.closest( 'fieldset' )
				.contains( 'button', 'Bulk Edit' )
				.click();

			cy.get( '.tasty-recipes-row-list-bulk' )
				.first()
				.click()
				.type( '{selectall}{backspace}First line' )
				.trigger( 'keydown', { key: 'Enter', shiftKey: true } )
				.type( 'Same item' );

			cy.contains( 'button', 'Done' ).click();

			cy.get( `${ ingredients } .tasty-recipes-item-row` ).should(
				'have.length',
				1
			);
			cy.get( ingredients )
				.siblings( 'input[name="ingredients"]' )
				.invoke( 'val' )
				.then( value => {
					expect( value ).to.include( 'tasty-recipes-soft-break' );
					expect( value ).to.include( 'First line' );
					expect( value ).to.include( 'Same item' );
				} );

			cy.get( '.tasty-recipes-modal-close' ).click( { force: true } );
		} );

		it( 'should keep typed hashes as item text in bulk edit', () => {
			openCreateRecipeModal();
			cy.get( '.tasty-recipes-frame-content input#title' ).type(
				`Cypress Bulk Hash ${ Date.now() }`
			);

			cy.get( ingredients )
				.closest( 'fieldset' )
				.contains( 'button', 'Bulk Edit' )
				.click();

			cy.get( '.tasty-recipes-row-list-bulk' )
				.first()
				.click()
				.type(
					'{selectall}{backspace}## Not a heading{enter}Salt'
				);

			cy.contains( 'button', 'Done' ).click();

			cy.get( `${ ingredients } .tasty-recipes-item-row--group` ).should(
				'not.exist'
			);
			cy.get( ingredients )
				.siblings( 'input[name="ingredients"]' )
				.should(
					'have.value',
					'<ul><li>## Not a heading</li><li>Salt</li></ul>'
				);

			cy.get( '.tasty-recipes-modal-close' ).click( { force: true } );
		} );

		it( 'should format bulk edit text and show the image control for instructions', () => {
			const selectFirstText = el => {
				const doc = el.ownerDocument;
				const walker = doc.createTreeWalker(
					el,
					doc.defaultView.NodeFilter.SHOW_TEXT
				);
				const text = walker.nextNode();
				if ( ! text ) {
					return;
				}

				const range = doc.createRange();
				range.selectNodeContents( text );
				const selection = doc.defaultView.getSelection();
				selection.removeAllRanges();
				selection.addRange( range );
			};

			openCreateRecipeModal();
			cy.get( '.tasty-recipes-frame-content input#title' ).type(
				`Cypress Bulk Format ${ Date.now() }`
			);

			cy.get( instructions )
				.closest( 'fieldset' )
				.contains( 'button', 'Bulk Edit' )
				.click();

			cy.get(
				'.tasty-recipes-bulk-toolbar [aria-label="Add image"]'
			).should( 'be.visible' );

			cy.get( '.tasty-recipes-row-list-bulk' )
				.first()
				.click()
				.type( '{selectall}{backspace}Whisk the eggs' );

			cy.get( '.tasty-recipes-row-list-bulk' )
				.first()
				.then( $el => selectFirstText( $el[ 0 ] ) );

			cy.get( '.tasty-recipes-bulk-toolbar [aria-label="Bold"]' ).click();
			cy.get(
				'.tasty-recipes-bulk-toolbar [aria-label="Add link"]'
			).click();
			cy.get( '.tasty-recipes-bulk-toolbar-link input[type="text"]' )
				.clear()
				.type( 'https://example.com' );
			cy.get( '.tasty-recipes-bulk-toolbar-link' )
				.contains( 'button', 'Add' )
				.click();

			cy.contains( 'button', 'Done' ).click();

			cy.get( instructions )
				.siblings( 'input[name="instructions"]' )
				.invoke( 'val' )
				.then( value => {
					expect( value ).to.match( /<(b|strong)>/ );
					expect( value ).to.include( 'https://example.com' );
					expect( value ).to.include( 'Whisk the eggs' );
				} );

			cy.get( '.tasty-recipes-modal-close' ).click( { force: true } );
		} );

		it( 'should apply sponsored and nofollow from the bulk link popover', () => {
			const selectFirstText = el => {
				const doc = el.ownerDocument;
				const walker = doc.createTreeWalker(
					el,
					doc.defaultView.NodeFilter.SHOW_TEXT
				);
				const text = walker.nextNode();
				if ( ! text ) {
					return;
				}

				const range = doc.createRange();
				range.selectNodeContents( text );
				const selection = doc.defaultView.getSelection();
				selection.removeAllRanges();
				selection.addRange( range );
			};

			openCreateRecipeModal();
			cy.get( '.tasty-recipes-frame-content input#title' ).type(
				`Cypress Bulk Link Attrs ${ Date.now() }`
			);

			cy.get( instructions )
				.closest( 'fieldset' )
				.contains( 'button', 'Bulk Edit' )
				.click();

			cy.get( '.tasty-recipes-row-list-bulk' )
				.first()
				.click()
				.type( '{selectall}{backspace}Whisk the eggs' );

			cy.get( '.tasty-recipes-row-list-bulk' )
				.first()
				.then( $el => selectFirstText( $el[ 0 ] ) );

			cy.get(
				'.tasty-recipes-bulk-toolbar [aria-label="Add link"]'
			).click();
			cy.get( '.tasty-recipes-bulk-toolbar-link' ).should( 'be.visible' );
			cy.get( '.tasty-recipes-bulk-toolbar-link' )
				.contains( 'label', 'Sponsored' )
				.should( 'not.have.class', 'is-checked' );
			cy.get( '.tasty-recipes-bulk-toolbar-link' )
				.contains( 'label', 'Nofollow' )
				.should( 'not.have.class', 'is-checked' );

			cy.get( '.tasty-recipes-bulk-toolbar-link input[type="text"]' )
				.clear()
				.type( 'https://example.com' );
			cy.get( '.tasty-recipes-bulk-toolbar-link' )
				.contains( 'label', 'Sponsored' )
				.click();
			cy.get( '.tasty-recipes-bulk-toolbar-link' )
				.contains( 'label', 'Nofollow' )
				.click();
			cy.get( '.tasty-recipes-bulk-toolbar-link' )
				.contains( 'button', 'Add' )
				.click();

			cy.contains( 'button', 'Done' ).click();

			cy.get( instructions )
				.siblings( 'input[name="instructions"]' )
				.invoke( 'val' )
				.then( value => {
					expect( value ).to.include( 'https://example.com' );
					expect( value ).to.include( 'sponsored' );
					expect( value ).to.include( 'nofollow' );
					expect( value ).to.include( 'Whisk the eggs' );
				} );

			cy.get( '.tasty-recipes-modal-close' ).click( { force: true } );
		} );

		it( 'should keep instruction images visible through bulk edit', () => {
			const recipeTitle = `Cypress Bulk Image Keep ${ Date.now() }`;

			cy.createTastyRecipe( {
				title: recipeTitle,
				instructions:
					'<ol><li>Whisk the eggs.' +
					'<img src="/wp-content/uploads/whisk.jpg" alt="Whisk" class="wp-image-7" />' +
					'</li></ol>',
			} );

			cy.visit( '/wp-admin/edit.php?post_type=tasty_recipe' );
			openEditRecipeModal( recipeTitle );

			cy.get( instructions )
				.closest( 'fieldset' )
				.contains( 'button', 'Bulk Edit' )
				.click();

			cy.get( '.tasty-recipes-row-list-bulk img' )
				.should( 'be.visible' )
				.and( 'have.attr', 'src' )
				.and( 'include', 'whisk.jpg' );
			cy.get(
				'.tasty-recipes-row-list-bulk .tasty-recipes-row-image__remove'
			).should( 'exist' );

			cy.contains( 'button', 'Done' ).click();

			cy.get( instructions )
				.siblings( 'input[name="instructions"]' )
				.invoke( 'val' )
				.then( value => {
					expect( value ).to.include( 'whisk.jpg' );
					expect( value ).to.include( 'Whisk the eggs' );
				} );
		} );

		it( 'should remove an instruction image from bulk edit', () => {
			const recipeTitle = `Cypress Bulk Image ${ Date.now() }`;

			cy.createTastyRecipe( {
				title: recipeTitle,
				instructions:
					'<ol><li>Whisk the eggs.' +
					'<img src="/wp-content/uploads/whisk.jpg" alt="Whisk" class="wp-image-7" />' +
					'</li></ol>',
			} );

			cy.visit( '/wp-admin/edit.php?post_type=tasty_recipe' );
			openEditRecipeModal( recipeTitle );

			cy.get( instructions )
				.closest( 'fieldset' )
				.contains( 'button', 'Bulk Edit' )
				.click();

			cy.get( '.tasty-recipes-row-list-bulk img' )
				.should( 'be.visible' )
				.and( 'have.attr', 'src' )
				.and( 'include', 'whisk.jpg' );

			cy.get(
				'.tasty-recipes-row-list-bulk .tasty-recipes-row-image__remove'
			).click( { force: true } );

			cy.get( '.tasty-recipes-row-list-bulk img' ).should( 'not.exist' );

			cy.contains( 'button', 'Done' ).click();

			cy.get( instructions )
				.siblings( 'input[name="instructions"]' )
				.invoke( 'val' )
				.then( value => {
					expect( value ).to.not.include( 'whisk.jpg' );
					expect( value ).to.include( 'Whisk the eggs' );
				} );
		} );

		it( 'should attach an inserted image to the current bulk instruction line', () => {
			openCreateRecipeModal();
			cy.get( '.tasty-recipes-frame-content input#title' ).type(
				`Cypress Bulk Attach ${ Date.now() }`
			);

			cy.get( instructions )
				.closest( 'fieldset' )
				.contains( 'button', 'Bulk Edit' )
				.click();

			cy.get( '.tasty-recipes-row-list-bulk' )
				.first()
				.click()
				.type( '{selectall}{backspace}Whisk the eggs' );

			cy.get( '.tasty-recipes-row-list-bulk' )
				.first()
				.then( $el => {
					const el = $el[ 0 ];
					el.focus();
					el.ownerDocument.execCommand(
						'insertHTML',
						false,
						'<img src="/wp-content/uploads/step.jpg" alt="Step" class="wp-image-8" />'
					);
					el.dispatchEvent( new Event( 'input', { bubbles: true } ) );
				} );

			cy.contains( 'button', 'Done' ).click();

			cy.get( `${ instructions } .tasty-recipes-item-row` ).should(
				'have.length',
				1
			);
			cy.get( instructions )
				.siblings( 'input[name="instructions"]' )
				.invoke( 'val' )
				.then( value => {
					expect( value ).to.include( 'Whisk the eggs' );
					expect( value ).to.include( 'step.jpg' );
				} );

			cy.get( '.tasty-recipes-modal-close' ).click( { force: true } );
		} );
	} );
} );
