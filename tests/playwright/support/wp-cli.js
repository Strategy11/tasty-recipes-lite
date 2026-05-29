const { execFile } = require( 'node:child_process' );

const escapePhpString = value =>
	String( value ).replace( /\\/g, '\\\\' ).replace( /'/g, "\\'" );

const runWpCli = ( args, options = {} ) =>
	new Promise( ( resolve, reject ) => {
		execFile(
			'pnpm',
			[ 'run', 'env', 'run', 'cli', '--', 'wp', ...args ],
			{
				timeout: options.timeout || 30000,
				maxBuffer: 1024 * 1024,
			},
			( error, stdout, stderr ) => {
				if ( error ) {
					error.stdout = stdout;
					error.stderr = stderr;
					reject( error );
					return;
				}

				resolve( { stdout, stderr } );
			}
		);
	} );

const wpDevEval = async ( command, options = {} ) => {
	const phpCode = command.replace( /^<\?php /, '' );
	const encoded = Buffer.from( phpCode, 'utf8' ).toString( 'base64' );

	return runWpCli(
		[ 'eval', `eval(base64_decode("${ encoded }"));` ],
		options
	);
};

const createTastyRecipe = async ( overrides = {} ) => {
	const defaults = {
		title: 'Playwright Test Recipe',
		author_name: 'admin',
		description:
			'<p>A delicious test recipe created by Playwright.</p>',
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
		keywords: 'test, playwright, recipe',
	};
	const data = { ...defaults, ...overrides };

	const php = `
		$recipe_id = wp_insert_post( array(
			'post_type'   => 'tasty_recipe',
			'post_title'  => '${ escapePhpString( data.title ) }',
			'post_status' => 'publish',
		) );
		if ( is_wp_error( $recipe_id ) ) {
			echo 'ERROR';
			exit;
		}
		$meta = array(
			'author_name'  => '${ escapePhpString( data.author_name ) }',
			'description'  => '${ escapePhpString( data.description ) }',
			'ingredients'  => '${ escapePhpString( data.ingredients ) }',
			'instructions' => '${ escapePhpString( data.instructions ) }',
			'notes'        => '${ escapePhpString( data.notes ) }',
			'prep_time'    => '${ escapePhpString( data.prep_time ) }',
			'cook_time'    => '${ escapePhpString( data.cook_time ) }',
			'total_time'   => '${ escapePhpString( data.total_time ) }',
			'yield'        => '${ escapePhpString( data.yield ) }',
			'category'     => '${ escapePhpString( data.category ) }',
			'cuisine'      => '${ escapePhpString( data.cuisine ) }',
			'calories'     => '${ escapePhpString( data.calories ) }',
			'keywords'     => '${ escapePhpString( data.keywords ) }',
		);
		foreach ( $meta as $key => $value ) {
			update_post_meta( $recipe_id, $key, $value );
		}
		echo $recipe_id;
	`;
	const result = await wpDevEval( php );
	const id = Number.parseInt( result.stdout.trim(), 10 );

	if ( ! id ) {
		throw new Error( `Unable to create tasty recipe: ${ result.stdout }` );
	}

	return id;
};

const createPostWithRecipe = async ( recipeId, postOverrides = {} ) => {
	const defaults = {
		title: 'Playwright Test Post With Recipe',
		status: 'publish',
	};
	const data = { ...defaults, ...postOverrides };
	const blockMarkup = `<!-- wp:wp-tasty/tasty-recipe {"id":${ recipeId }} /-->`;
	const php = `
		$post_id = wp_insert_post( array(
			'post_type'    => 'post',
			'post_title'   => '${ escapePhpString( data.title ) }',
			'post_status'  => '${ escapePhpString( data.status ) }',
			'post_content' => '${ escapePhpString( blockMarkup ) }',
		) );
		if ( is_wp_error( $post_id ) ) {
			echo 'ERROR';
			exit;
		}
		add_post_meta( ${ recipeId }, '_tasty_recipe_parents', $post_id );
		echo $post_id . '|' . get_permalink( $post_id );
	`;
	const result = await wpDevEval( php );
	const parts = result.stdout.trim().split( '|' );
	const postId = Number.parseInt( parts[ 0 ], 10 );
	const rawUrl = parts.slice( 1 ).join( '|' );
	let postUrl = rawUrl;

	try {
		const parsed = new URL( rawUrl );
		postUrl = parsed.pathname + parsed.search;
	} catch {
		postUrl = rawUrl;
	}

	if ( ! postId ) {
		throw new Error( `Unable to create post: ${ result.stdout }` );
	}

	return { postId, postUrl };
};

const optionValuePhp = value => {
	if ( typeof value === 'object' && value !== null ) {
		return `json_decode( '${ escapePhpString(
			JSON.stringify( value )
		) }', true )`;
	}

	return `'${ escapePhpString( value ) }'`;
};

const setTastyOption = ( optionName, value ) =>
	wpDevEval(
		`update_option( '${ escapePhpString( optionName ) }', ${ optionValuePhp(
			value
		) } );`
	);

const setTastyOptions = ( options = {} ) => {
	const optionPhp = Object.entries( options )
		.map(
			( [ optionName, value ] ) =>
				`update_option( '${ escapePhpString(
					optionName
				) }', ${ optionValuePhp( value ) } );`
		)
		.join( '\n' );

	return optionPhp ? wpDevEval( optionPhp ) : Promise.resolve( { stdout: '' } );
};

const getTastyOption = async optionName => {
	const result = await wpDevEval(
		`echo json_encode( get_option( '${ escapePhpString(
			optionName
		) }', '' ) );`
	);

	return JSON.parse( result.stdout.trim() );
};

const deleteAllTastyRecipes = () =>
	wpDevEval( `
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
	` );

module.exports = {
	createPostWithRecipe,
	createTastyRecipe,
	deleteAllTastyRecipes,
	escapePhpString,
	getTastyOption,
	setTastyOption,
	setTastyOptions,
	wpDevEval,
};
