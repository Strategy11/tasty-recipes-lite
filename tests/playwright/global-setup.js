const path = require( 'node:path' );
const { execFile } = require( 'node:child_process' );

const runCommand = ( command, args ) =>
	new Promise( ( resolve, reject ) => {
		execFile(
			command,
			args,
			{
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

const getWordPressPlaywrightUtils = () => {
	const packagePath = require.resolve(
		'@wordpress/e2e-test-utils-playwright/package.json'
	);

	return require( path.join( path.dirname( packagePath ), 'build' ) );
};

module.exports = async config => {
	const project = config.projects[ 0 ];
	const baseURL = project.use.baseURL;
	const storageStatePath = project.use.storageState;

	process.env.WP_BASE_URL = baseURL;

	await runCommand( 'pnpm', [ 'run', 'env', 'start' ] );

	const { RequestUtils } = getWordPressPlaywrightUtils();
	const requestUtils = await RequestUtils.setup( {
		baseURL,
		storageStatePath,
	} );

	await requestUtils.setupRest();
	await requestUtils.request.dispose();
};
