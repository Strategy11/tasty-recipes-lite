const path = require( 'node:path' );

const packagePath = require.resolve(
	'@wordpress/e2e-test-utils-playwright/package.json'
);
const { test: base, expect } = require( path.join(
	path.dirname( packagePath ),
	'build'
) );

const wpCli = require( './wp-cli' );

const test = base.extend( {
	wpCli: async ( {}, use ) => {
		await use( wpCli );
	},
} );

module.exports = {
	expect,
	test,
};
