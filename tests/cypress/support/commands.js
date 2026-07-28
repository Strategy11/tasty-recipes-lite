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

Cypress.Commands.add( 'typeTastyInlineEditor', ( fieldId, text ) => {
	cy.get( `#${ fieldId }.mce-content-body`, {
		timeout: 10_000,
	} )
		.should( 'be.visible' )
		.click()
		.type( text, { delay: 0 } );
} );
