/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import initializeOnboardingWizard from './initializeOnboardingWizard';
import '../../scss/onboarding-wizard.scss';

domReady( () => {
	initializeOnboardingWizard();
} );
