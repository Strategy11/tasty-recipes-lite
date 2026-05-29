const path = require( 'node:path' );

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

	const { RequestUtils } = getWordPressPlaywrightUtils();
	const requestUtils = await RequestUtils.setup( {
		baseURL,
		storageStatePath,
	} );

	await requestUtils.setupRest();
	await requestUtils.request.dispose();
};
