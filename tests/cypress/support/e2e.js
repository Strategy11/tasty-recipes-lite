// ***********************************************************
// This example support/e2e.js is processed and
// loaded automatically before your test files.
//
// This is a great place to put global configuration and
// behavior that modifies Cypress.
//
// You can change the location of this file or turn off
// automatically serving support files with the
// 'supportFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/configuration
// ***********************************************************

import '@10up/cypress-wp-utils';
import './commands';

// Never let a test browser reach PostHog, even if the snippet loads.
beforeEach( () => {
	cy.intercept( { hostname: /(^|\.)posthog\.com$/ }, { statusCode: 204 } );
} );

Cypress.on( 'uncaught:exception', ( err ) => {
	if ( err.message.includes( 'No route was found' ) ) {
		return false;
	}

	// The recipe explorer JS may throw when tearing down UI elements
	// that are no longer in the DOM (e.g. after a modal closes).
	if ( err.message.includes( 'removeClass' ) ) {
		return false;
	}

	// TinyMCE fromDom throws when the editor iframe body is gone during
	// recipe modal open. Unrelated to the row-list assertions under test.
	if (
		err.message.includes( 'Node cannot be null or undefined' ) &&
		String( err.stack || '' ).includes( 'tinymce' )
	) {
		return false;
	}
} );
