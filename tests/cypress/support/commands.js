import 'cypress-file-upload';

/**
 * Runs PHP code via WP-CLI against the development container (cli),
 * which shares the database with the Cypress baseUrl (port 3000).
 *
 * The built-in cy.wpCliEval() uses tests-cli (port 3001), creating
 * a database mismatch with the browser.
 *
 * The PHP code is base64-encoded so it can be passed through the shell
 * without escaping issues and without creating temporary files.
 *
 * @since x.x
 *
 * @param {string} command - PHP code to execute (without <?php prefix).
 * @returns {Cypress.Chainable<{ stdout: string }>}
 */
const wpDevCliEval = ( command ) => {
	const phpCode = command.replace( /^<\?php /, '' );

	// Encode as UTF-8 bytes, then base64-encode for safe shell passing.
	const encoded = Cypress.Buffer.from( phpCode, 'utf8' ).toString( 'base64' );

	return cy.exec(
		`npm --silent run env run cli -- wp eval 'eval(base64_decode("${ encoded }"));'`,
		{ timeout: 30000 }
	);
};

/**
 * Creates a tasty_recipe CPT via WP-CLI with sample data.
 *
 * @since x.x
 *
 * @param {Object} overrides - Optional overrides for recipe fields.
 * @returns {Cypress.Chainable<number>} The created recipe post ID.
 */
Cypress.Commands.add( 'createTastyRecipe', ( overrides = {} ) => {
	const defaults = {
		title: 'Cypress Test Recipe',
		author_name: 'admin',
		description: '<p>A delicious test recipe created by Cypress.</p>',
		ingredients:
			'<ul><li>1 cup flour</li><li>2 eggs</li><li>1/2 cup sugar</li></ul>',
		instructions:
			'<ol><li>Mix dry ingredients.</li><li>Add eggs and stir.</li><li>Bake at 350°F for 25 minutes.</li></ol>',
		notes: '<p>Let cool before serving.</p>',
		prep_time: '10 minutes',
		cook_time: '25 minutes',
		total_time: '35 minutes',
		yield: '4 servings',
		category: 'Dessert',
		cuisine: 'American',
		calories: '250',
		keywords: 'test, cypress, recipe',
	};

	const data = { ...defaults, ...overrides };

	const php = `
		$recipe_id = wp_insert_post( array(
			'post_type'   => 'tasty_recipe',
			'post_title'  => '${ data.title.replace( /'/g, "\\'" ) }',
			'post_status' => 'publish',
		) );
		if ( is_wp_error( $recipe_id ) ) {
			echo 'ERROR';
			exit;
		}
		$meta = array(
			'author_name'  => '${ data.author_name.replace( /'/g, "\\'" ) }',
			'description'  => '${ data.description.replace( /'/g, "\\'" ) }',
			'ingredients'  => '${ data.ingredients.replace( /'/g, "\\'" ) }',
			'instructions' => '${ data.instructions.replace( /'/g, "\\'" ) }',
			'notes'        => '${ data.notes.replace( /'/g, "\\'" ) }',
			'prep_time'    => '${ data.prep_time }',
			'cook_time'    => '${ data.cook_time }',
			'total_time'   => '${ data.total_time }',
			'yield'        => '${ data.yield }',
			'category'     => '${ data.category }',
			'cuisine'      => '${ data.cuisine }',
			'calories'     => '${ data.calories }',
			'keywords'     => '${ data.keywords }',
		);
		foreach ( $meta as $key => $value ) {
			update_post_meta( $recipe_id, $key, $value );
		}
		echo $recipe_id;
	`;

	return wpDevCliEval( php ).then( ( result ) => {
		const id = parseInt( result.stdout.trim(), 10 );
		expect( id ).to.be.greaterThan( 0 );
		return id;
	} );
} );

/**
 * Creates a published post with a recipe block embedded.
 *
 * @since x.x
 *
 * @param {number} recipeId      - The recipe post ID to embed.
 * @param {Object} postOverrides - Optional overrides for the post.
 * @returns {Cypress.Chainable<{ postId: number, postUrl: string }>}
 */
Cypress.Commands.add( 'createPostWithRecipe', ( recipeId, postOverrides = {} ) => {
	const defaults = {
		title: 'Cypress Test Post With Recipe',
		status: 'publish',
	};

	const data = { ...defaults, ...postOverrides };
	const blockMarkup = `<!-- wp:wp-tasty/tasty-recipe {"id":${ recipeId }} /-->`;

	const php = `
		$post_id = wp_insert_post( array(
			'post_type'    => 'post',
			'post_title'   => '${ data.title.replace( /'/g, "\\'" ) }',
			'post_status'  => '${ data.status }',
			'post_content' => '${ blockMarkup }',
		) );
		if ( is_wp_error( $post_id ) ) {
			echo 'ERROR';
			exit;
		}
		add_post_meta( ${ recipeId }, '_tasty_recipe_parents', $post_id );
		echo $post_id . '|' . get_permalink( $post_id );
	`;

	return wpDevCliEval( php ).then( ( result ) => {
		const parts = result.stdout.trim().split( '|' );
		const postId = parseInt( parts[ 0 ], 10 );
		const rawUrl = parts.slice( 1 ).join( '|' );

		// Strip origin to get a relative path, keeping the query string
		// (plain permalinks use ?p=123).
		let postUrl;
		try {
			const parsed = new URL( rawUrl );
			postUrl = parsed.pathname + parsed.search;
		} catch {
			postUrl = rawUrl;
		}

		expect( postId ).to.be.greaterThan( 0 );
		return { postId, postUrl };
	} );
} );

/**
 * Sets a Tasty Recipes option via WP-CLI.
 *
 * @since x.x
 *
 * @param {string}          optionName - The option name.
 * @param {string|string[]} value      - The option value. Arrays are serialized.
 */
Cypress.Commands.add( 'setTastyOption', ( optionName, value ) => {
	let php;

	if ( typeof value === 'object' && value !== null ) {
		const serialized = JSON.stringify( value );
		php = `update_option( '${ optionName }', json_decode( '${ serialized }', true ) );`;
	} else {
		php = `update_option( '${ optionName }', '${ String( value ).replace( /'/g, "\\'" ) }' );`;
	}

	return wpDevCliEval( php );
} );

/**
 * Sets multiple Tasty Recipes options in a single WP-CLI call.
 *
 * @since x.x
 *
 * @param {Object<string, string|Array|Object>} options - Option map keyed by option name.
 * @returns {Cypress.Chainable<{ stdout: string }>}
 */
Cypress.Commands.add( 'setTastyOptions', ( options = {} ) => {
	const optionEntries = Object.entries( options );

	if ( optionEntries.length === 0 ) {
		return cy.wrap( { stdout: '' } );
	}

	const optionPhp = optionEntries
		.map( ( [ optionName, value ] ) => {
			if ( typeof value === 'object' && value !== null ) {
				const serialized = JSON.stringify( value ).replace( /'/g, "\\'" );
				return `update_option( '${ optionName }', json_decode( '${ serialized }', true ) );`;
			}

			return `update_option( '${ optionName }', '${ String( value ).replace( /'/g, "\\'" ) }' );`;
		} )
		.join( "\n" );

	return wpDevCliEval( optionPhp );
} );

/**
 * Gets a Tasty Recipes option value via WP-CLI.
 *
 * @since x.x
 *
 * @param {string} optionName - The option name.
 * @returns {Cypress.Chainable<string>} The option value.
 */
Cypress.Commands.add( 'getTastyOption', ( optionName ) => {
	const php = `echo json_encode( get_option( '${ optionName }', '' ) );`;

	return wpDevCliEval( php ).then( ( result ) => {
		return JSON.parse( result.stdout.trim() );
	} );
} );

/**
 * Deletes all tasty_recipe posts via WP-CLI.
 *
 * @since x.x
 */
Cypress.Commands.add( 'deleteAllTastyRecipes', () => {
	const php = `
		$recipes = get_posts( array(
			'post_type'      => 'tasty_recipe',
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		) );
		foreach ( $recipes as $id ) {
			wp_delete_post( $id, true );
		}
		echo count( $recipes );
	`;

	return wpDevCliEval( php ).then( ( result ) => {
		const count = parseInt( result.stdout.trim(), 10 );
		cy.log( `Deleted ${ count } recipes` );
	} );
} );

/**
 * Executes arbitrary PHP via WP-CLI against the dev container.
 *
 * @since x.x
 *
 * @param {string} php - PHP code to execute (without <?php prefix).
 * @returns {Cypress.Chainable<{ stdout: string }>}
 */
Cypress.Commands.add( 'wpDevEval', ( php ) => {
	return wpDevCliEval( php );
} );

/**
 * Logs into WordPress admin once per run via Cypress session cache.
 *
 * @since x.x
 *
 * @returns {void}
 */
Cypress.Commands.add( 'loginCached', () => {
	cy.session( 'wp-admin-login', () => {
		cy.login();
	} );
} );

/**
 * Types into a Tasty Recipes inline TinyMCE field.
 *
 * @since x.x
 *
 * @param {string} fieldId Field element id.
 * @param {string} text    Text to type.
 *
 * @returns {void}
 */
Cypress.Commands.add( 'typeTastyInlineEditor', ( fieldId, text ) => {
	cy.get( `#${ fieldId }.mce-content-body`, {
		timeout: 10_000,
	} )
		.scrollIntoView()
		.should( 'be.visible' )
		.click()
		.type( text, { delay: 0 } );
} );

/**
 * Close the Gutenberg welcome guide without chaining `.find()` on the
 * iframed editor body, which detaches when the canvas remounts.
 *
 * @since x.x
 *
 * @returns {void}
 */
Cypress.Commands.overwrite( 'closeWelcomeGuide', ( originalFn ) => {
	void originalFn;

	cy.get( '.editor-visual-editor, .edit-post-visual-editor', {
		timeout: 15000,
	} ).should( 'exist' );

	cy.window().then( ( win ) => {
		const data = win.wp?.data;
		if ( ! data ) {
			return;
		}

		if (
			data.select( 'core/edit-post' )?.isFeatureActive?.( 'welcomeGuide' )
		) {
			data.dispatch( 'core/edit-post' ).toggleFeature( 'welcomeGuide' );
		}

		if (
			data.select( 'core/preferences' )?.get?.(
				'core/edit-post',
				'welcomeGuide'
			)
		) {
			data.dispatch( 'core/preferences' ).set(
				'core/edit-post',
				'welcomeGuide',
				false
			);
		}

		if (
			data.select( 'core/preferences' )?.get?.( 'core', 'welcomeGuide' )
		) {
			data.dispatch( 'core/preferences' ).set(
				'core',
				'welcomeGuide',
				false
			);
		}
	} );

	cy.get( 'body' ).then( ( $body ) => {
		const closeButton = $body.find(
			'.edit-post-welcome-guide .components-modal__header button, .editor-welcome-guide .components-modal__header button'
		);

		if ( closeButton.length ) {
			cy.wrap( closeButton.first() ).click();
		}
	} );
} );

/**
 * Yield the block editor canvas body, including the iframed editor.
 *
 * Replaces the 10up helper so Cypress retries iframe document access
 * instead of wrapping a jQuery body with an undefined selector.
 *
 * @since x.x
 *
 * @returns {Cypress.Chainable<JQuery<HTMLElement>>}
 */
Cypress.Commands.overwrite( 'getBlockEditor', ( originalFn ) => {
	void originalFn;

	cy.get( '.editor-visual-editor, .edit-post-visual-editor', {
		timeout: 15000,
	} ).should( 'exist' );

	return cy.get( 'body' ).then( ( $body ) => {
		if ( ! $body.find( 'iframe[name="editor-canvas"]' ).length ) {
			return cy.wrap( $body );
		}

		return cy
			.get( 'iframe[name="editor-canvas"]' )
			.its( '0.contentDocument.body' )
			.should( 'not.be.empty' )
			.then( ( body ) => cy.wrap( body ) );
	} );
} );

/**
 * Open the editor settings sidebar and select a tab.
 *
 * Avoids 10up's jQuery `:contains()` selectors, which Cypress 15 no
 * longer supports in `cy.get()`.
 *
 * @since x.x
 *
 * @param {string} tab Sidebar tab label. Defaults to "Post".
 *
 * @returns {void}
 */
Cypress.Commands.overwrite( 'openDocumentSettingsSidebar', ( originalFn, tab = 'Post' ) => {
	void originalFn;

	cy.get( 'body' ).then( ( $body ) => {
		const settingsButton = $body.find(
			'button[aria-expanded="false"][aria-label="Settings"], button[aria-expanded="false"][aria-label="Editor settings"]'
		);

		if ( settingsButton.length ) {
			cy.wrap( settingsButton.first() ).click();
		}
	} );

	cy.contains(
		'[role="tablist"] button, .editor-sidebar__panel-tab, .edit-post-sidebar__panel-tabs button',
		tab
	).click();
} );
