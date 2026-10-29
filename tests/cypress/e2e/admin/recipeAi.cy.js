describe( 'Recipe AI', () => {
	const aiRoute = ( name ) =>
		new RegExp( `tasty-recipes(/|%2F)v1(/|%2F)ai(/|%2F)${ name }` );

	const providers = [
		{
			id: 'openai',
			label: 'OpenAI',
			credentialsUrl: 'https://platform.openai.com/api-keys',
			hasConstantKey: false,
		},
		{
			id: 'anthropic',
			label: 'Anthropic',
			credentialsUrl: 'https://platform.claude.com/settings/keys',
			hasConstantKey: false,
		},
	];

	const connectedStatus = {
		provider: 'openai',
		model: 'gpt-4o',
		modelLabel: 'gpt-4o',
		modelIsAuto: false,
		recommended: '',
		connected: true,
		needsReconnect: false,
		keySource: 'database',
		keyHint: '0000',
		canEncrypt: true,
		providers,
	};

	const disconnectedStatus = {
		...connectedStatus,
		provider: '',
		model: '',
		modelLabel: '',
		connected: false,
		keySource: '',
		keyHint: '',
	};

	const models = [
		{ id: 'gpt-4.1', label: 'gpt-4.1' },
		{ id: 'gpt-4o', label: 'gpt-4o' },
	];

	// Stores an encrypted fake key so the editor offers the AI features. Every
	// request that would reach the provider is stubbed with cy.intercept.
	const seedConnection = () =>
		cy.wpDevEval( `
			$key = \\Tasty_Recipes\\AI\\Vault::encrypt( 'sk-cypress-fake-key-0000', 'tasty_recipes_ai_connection|openai' );
			update_option( 'tasty_recipes_ai_connection', array(
				'provider'   => 'openai',
				'model_auto' => false,
				'key'        => $key,
				'key_hint'   => '0000',
			), false );
			update_option( 'tasty_recipes_ai_model', 'gpt-4o' );
			set_transient( 'tasty_recipes_ai_models', json_decode( '${ JSON.stringify( models ) }', true ), HOUR_IN_SECONDS );
		` );

	const clearConnection = () =>
		cy.wpDevEval( `
			delete_option( 'tasty_recipes_ai_connection' );
			delete_option( 'tasty_recipes_ai_model' );
			delete_transient( 'tasty_recipes_ai_models' );
		` );

	const stubTracking = () =>
		cy.window().then( ( win ) => {
			win.tastyRecipesTrackEvent = cy.stub().as( 'track' );
		} );

	const trackedEvents = () =>
		cy
			.get( '@track' )
			.then( ( stub ) => stub.getCalls().map( ( call ) => call.args ) );

	const openPasteFromBlock = () => {
		cy.visit( '/wp-admin/post-new.php' );
		cy.closeWelcomeGuide();
		cy.insertBlock( 'wp-tasty/tasty-recipe', 'Tasty Recipe' );
		stubTracking();

		cy.getBlockEditor()
			.find( '.tasty-recipes-block-placeholder-buttons', {
				timeout: 10_000,
			} )
			.contains( 'button', 'Paste a recipe' )
			.click();

		cy.get( '.tasty-recipes-paste-panel', { timeout: 10_000 } ).should(
			'be.visible'
		);
	};

	const openCreateModal = () => {
		cy.visit( '/wp-admin/edit.php?post_type=tasty_recipe' );
		stubTracking();
		cy.get(
			'#tasty-create-recipe, .tasty-recipes-empty-actions .tasty-button-primary',
			{ timeout: 10_000 }
		)
			.first()
			.click();
		cy.get( '.tasty-recipes-frame-content input#title' ).should(
			'be.visible'
		);
	};

	const choosePhoto = () =>
		cy
			.get( '.tasty-recipes-paste-photo input[type="file"]' )
			.selectFile( 'tests/data/canola.jpg', { force: true } );

	before( () => {
		cy.loginCached();
		cy.deleteAllTastyRecipes();
	} );

	beforeEach( () => {
		cy.loginCached();
		cy.viewport( 1280, 800 );
	} );

	after( () => {
		clearConnection();
		cy.deleteAllTastyRecipes();
	} );

	describe( 'without a connection', () => {
		before( () => {
			clearConnection();
		} );

		it( 'should point to settings instead of reading photos', () => {
			openPasteFromBlock();

			cy.get( '.tasty-recipes-paste-photo .tasty-recipes-editor-image-placeholder' )
				.should( 'have.class', 'is-disabled' )
				.and( 'contain', 'Connect an AI provider' );
			cy.get( '.tasty-recipes-paste-photo button' ).should( 'be.disabled' );
		} );

		it( 'should not show the AI assistant in the editor', () => {
			openCreateModal();

			cy.get( '.tasty-recipes-ai-recipe-toggle' ).should( 'not.exist' );
		} );

		it( 'should connect, test and disconnect a provider in settings', () => {
			// apiFetch sends DELETE as POST with a method override header.
			cy.intercept( 'POST', aiRoute( 'connection' ), ( req ) => {
				if ( 'DELETE' === req.headers[ 'x-http-method-override' ] ) {
					req.alias = 'disconnect';
					req.reply( { statusCode: 200, body: disconnectedStatus } );
					return;
				}

				req.alias = 'connect';
				req.reply( {
					statusCode: 200,
					body: { status: connectedStatus, models },
				} );
			} );
			cy.intercept( 'GET', aiRoute( 'models' ), {
				statusCode: 200,
				body: { status: connectedStatus, models },
			} ).as( 'models' );
			cy.intercept( 'POST', aiRoute( 'test' ), {
				statusCode: 200,
				body: { success: true, durationMs: 1200 },
			} ).as( 'testConnection' );

			cy.visit( '/wp-admin/admin.php?page=tasty-recipes&tab=settings' );
			stubTracking();

			cy.get( 'input#tasty-recipes-ai-api-key', { timeout: 10_000 } )
				.scrollIntoView()
				.type( 'sk-cypress-typed-key-1234' );
			cy.contains( 'button', /^Connect$/ ).click();

			cy.wait( '@connect' )
				.its( 'request.body' )
				.should( 'deep.equal', {
					provider: 'openai',
					api_key: 'sk-cypress-typed-key-1234',
				} );
			cy.get( '.tasty-recipes-ai-connected-badge' ).should( 'contain', 'Connected' );
			cy.get( '.tasty-recipes-ai-connected-key' ).should( 'contain', '0000' );
			cy.get( 'input#tasty-recipes-ai-api-key' ).should( 'not.exist' );

			cy.contains( 'button', 'Test Connection' ).click();
			cy.wait( '@testConnection' );
			cy.get( '.tasty-recipes-ai-notice' ).should( 'contain', '1.2s' );

			cy.get( '.tasty-recipes-ai-disconnect' ).click();
			cy.wait( '@disconnect' );
			cy.get( 'input#tasty-recipes-ai-api-key' ).should( 'have.value', '' );

			trackedEvents().then( ( events ) => {
				expect( events.map( ( [ , props ] ) => props.action ) ).to.deep.equal( [
					'connect',
					'test',
					'disconnect',
				] );
				events.forEach( ( [ name, props ] ) => {
					expect( name ).to.equal( 'ai_connection_action' );
					expect( props.success ).to.equal( true );
					expect( JSON.stringify( props ) ).not.to.contain( 'sk-cypress' );
				} );
			} );
		} );

		it( 'should show a provider error when connecting fails', () => {
			cy.intercept( 'POST', aiRoute( 'connection' ), {
				statusCode: 400,
				body: {
					code: 'tasty_recipes_ai_invalid_key',
					message: 'OpenAI did not accept this API key.',
					data: { status: 400 },
				},
			} ).as( 'connect' );

			cy.visit( '/wp-admin/admin.php?page=tasty-recipes&tab=settings' );
			stubTracking();

			cy.get( 'input#tasty-recipes-ai-api-key', { timeout: 10_000 } )
				.scrollIntoView()
				.type( 'sk-cypress-bad-key-1234' );
			cy.contains( 'button', /^Connect$/ ).click();
			cy.wait( '@connect' );

			cy.get( '.tasty-recipes-ai-error' ).should(
				'contain',
				'did not accept this API key'
			);
			cy.get( '@track' ).should(
				'have.been.calledWithMatch',
				'ai_connection_action',
				{
					action: 'connect',
					success: false,
					error_code: 'tasty_recipes_ai_invalid_key',
				}
			);
		} );
	} );

	describe( 'with a connection', () => {
		before( () => {
			seedConnection();
		} );

		it( 'should fill the form from a recipe photo', () => {
			cy.intercept( 'POST', aiRoute( 'recipe-from-image' ), {
				statusCode: 200,
				body: {
					text: 'Lemon Pancakes\n\nIngredients\n1 cup flour\n2 eggs\n\nInstructions\n1. Whisk everything together.\n2. Cook on a hot griddle.',
				},
			} ).as( 'photo' );

			openPasteFromBlock();
			cy.get( '.tasty-recipes-paste-photo .tasty-recipes-editor-image-placeholder' )
				.should( 'not.have.class', 'is-disabled' )
				.and( 'contain', 'Handwritten or printed' );

			choosePhoto();
			cy.wait( '@photo' ).then( ( { request } ) => {
				expect( request.headers[ 'content-type' ] ).to.contain(
					'multipart/form-data'
				);
			} );

			cy.get( '.tasty-recipes-paste-panel' ).should( 'not.exist' );
			cy.get( '.tasty-recipes-paste-notice' ).should( 'be.visible' );
			cy.get( 'input#title' ).should( 'have.value', 'Lemon Pancakes' );
			cy.get( '.tasty-recipes-row-list[data-mode="ingredients"]' )
				.siblings( 'input[name="ingredients"]' )
				.should( 'have.value', '<ul><li>1 cup flour</li><li>2 eggs</li></ul>' );

			cy.get( '@track' ).should( 'have.been.calledWithMatch', 'recipe_photo_read', {
				success: true,
				recipe_type: 'recipe',
			} );
			cy.get( '@track' ).should( 'have.been.calledWithMatch', 'recipe_paste_parsed', {
				source: 'photo',
				entry_point: 'block_placeholder',
				recipe_type: 'recipe',
			} );
		} );

		it( 'should keep the panel open and show the error when a photo can’t be read', () => {
			cy.intercept( 'POST', aiRoute( 'recipe-from-image' ), {
				statusCode: 422,
				body: {
					code: 'tasty_recipes_ai_no_recipe',
					message: 'We couldn’t find a recipe in that photo.',
					data: { status: 422 },
				},
			} ).as( 'photo' );

			openPasteFromBlock();
			choosePhoto();
			cy.wait( '@photo' );

			cy.get( '.tasty-recipes-paste-panel' ).should( 'be.visible' );
			cy.get( '.tasty-recipes-paste-panel__error' ).should(
				'contain',
				'couldn’t find a recipe in that photo'
			);
			cy.get( '.tasty-recipes-paste-panel__input' ).should( 'be.visible' );
			cy.get( '@track' ).should( 'have.been.calledWithMatch', 'recipe_photo_read', {
				success: false,
				error_code: 'tasty_recipes_ai_no_recipe',
			} );
		} );

		it( 'should update the recipe from a prompt and undo it', () => {
			cy.intercept( 'POST', aiRoute( 'recipe-assist' ), {
				statusCode: 200,
				body: { fields: { title: 'Vegan Chili', yield: '8 servings' } },
			} ).as( 'assist' );

			openCreateModal();
			cy.get( 'input#title' ).type( 'Chili' );

			cy.get( '.tasty-recipes-ai-recipe-toggle' ).click();
			cy.get( '.tasty-recipes-ai-assist__input' ).type( 'Make it vegan' );
			cy.contains( '.tasty-recipes-ai-assist button', 'Generate' ).click();

			cy.wait( '@assist' )
				.its( 'request.body' )
				.then( ( body ) => {
					expect( body.request ).to.equal( 'Make it vegan' );
					expect( body.recipe.title ).to.equal( 'Chili' );
					expect( body.fields ).to.include( 'title' );
					expect( body.fields ).not.to.include( 'calories' );
				} );

			cy.get( '.tasty-recipes-ai-assist' ).should( 'not.exist' );
			cy.get( '.tasty-recipes-paste-notice' ).should( 'contain', 'Updated 2 fields.' );
			cy.get( 'input#title' ).should( 'have.value', 'Vegan Chili' );
			cy.get( '#tasty-recipes-recipe-yield' ).should( 'have.value', '8 servings' );

			cy.contains( '.tasty-recipes-paste-notice button', 'Undo' ).click();
			cy.get( 'input#title' ).should( 'have.value', 'Chili' );

			cy.get( '@track' ).should( 'have.been.calledWithMatch', 'recipe_ai_assist_used', {
				success: true,
				fields_changed: 2,
			} );
			cy.get( '@track' ).should( 'have.been.calledWithMatch', 'recipe_ai_assist_undone', {
				fields_filled: 2,
			} );
			cy.get( '@track' ).should( 'not.have.been.calledWith', 'recipe_paste_undone' );

			cy.get( '.tasty-recipes-modal-close' ).click( { force: true } );
		} );

		it( 'should keep the assistant open and show the error when it can’t help', () => {
			cy.intercept( 'POST', aiRoute( 'recipe-assist' ), {
				statusCode: 422,
				body: {
					code: 'tasty_recipes_ai_off_topic',
					message: 'The AI can only help with recipes and cooking.',
					data: { status: 422 },
				},
			} ).as( 'assist' );

			openCreateModal();
			cy.get( '.tasty-recipes-ai-recipe-toggle' ).click();
			cy.get( '.tasty-recipes-ai-assist__input' ).type( 'Write me a poem' );
			cy.contains( '.tasty-recipes-ai-assist button', 'Generate' ).click();
			cy.wait( '@assist' );

			cy.get( '.tasty-recipes-ai-assist__error' ).should(
				'contain',
				'only help with recipes'
			);
			cy.get( '.tasty-recipes-paste-notice' ).should( 'not.exist' );
			cy.get( '@track' ).should( 'have.been.calledWithMatch', 'recipe_ai_assist_used', {
				success: false,
				error_code: 'tasty_recipes_ai_off_topic',
			} );

			cy.get( '.tasty-recipes-modal-close' ).click( { force: true } );
		} );
	} );

	describe( 'paste tracking', () => {
		it( 'should track empty results and cancelling', () => {
			openPasteFromBlock();

			// A nutrition panel alone has nothing the Lite form can take.
			cy.get( '.tasty-recipes-paste-panel__input' ).type(
				'Nutrition Facts{enter}Amount per serving'
			);
			cy.contains( '.tasty-recipes-paste-panel button', 'Fill Recipe' ).click();
			cy.get( '.tasty-recipes-paste-panel__error' ).should( 'be.visible' );

			cy.contains( '.tasty-recipes-paste-panel button', 'Cancel' ).click();

			cy.get( '@track' ).should( 'have.been.calledWithMatch', 'recipe_paste_empty', {
				source: 'typed',
				entry_point: 'block_placeholder',
				recipe_type: 'recipe',
			} );
			cy.get( '@track' ).should( 'have.been.calledWithMatch', 'recipe_paste_cancelled', {
				entry_point: 'block_placeholder',
			} );
			// Nothing typed or pasted is ever sent.
			trackedEvents().then( ( events ) => {
				expect( JSON.stringify( events ) ).not.to.contain( 'Amount per serving' );
			} );
		} );
	} );
} );
