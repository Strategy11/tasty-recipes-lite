const { defineConfig } = require( 'cypress' );

module.exports = defineConfig( {
	e2e: {
		baseUrl: 'http://localhost:3000',
		supportFile: 'tests/cypress/support/e2e.js',
		specPattern: 'tests/cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
		video: false,
		retries: {
			runMode: 1,
		},
		setupNodeEvents( on, config ) {
			// implement node event listeners here
		},
	},
} );
