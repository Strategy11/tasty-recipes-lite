const { exec } = require( 'child_process' );
const { promisify } = require( 'util' );
const { defineConfig } = require( 'cypress' );

const execAsync = promisify( exec );

module.exports = defineConfig( {
	e2e: {
		baseUrl: 'http://localhost:3000',
		supportFile: 'tests/cypress/support/e2e.js',
		fixturesFolder: 'tests/cypress/fixtures',
		specPattern: 'tests/cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
		video: false,
		// WP 6.3+ loads the block canvas in a blob iframe; Electron/Chrome
		// block contentDocument access unless web security is disabled.
		chromeWebSecurity: false,
		retries: {
			runMode: 1,
		},
		setupNodeEvents( on, config ) {
			on( 'task', {
				shellExec( command ) {
					return execAsync( command, {
						timeout: 30000,
						maxBuffer: 10 * 1024 * 1024,
					} ).then(
						( { stdout, stderr } ) => ( {
							code: 0,
							stdout,
							stderr,
						} ),
						( error ) => {
							throw new Error(
								error.stderr || error.stdout || error.message
							);
						}
					);
				},
			} );

			return config;
		},
	},
} );
