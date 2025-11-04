import domReady from '@wordpress/dom-ready';
import { Design, Settings, Converters } from './tabs';
import '../scss/settings.scss';
import { createRootCompat } from '../js/utils/reactCompat';

domReady( () => {
	const designTab = document.getElementById( 'tasty-recipes-design-tab' );
	if ( designTab ) {
		createRootCompat( designTab ).render( <Design /> );
	}

	const settingsTab = document.getElementById( 'tasty-recipes-settings-tab' );
	if ( settingsTab ) {
		createRootCompat( settingsTab ).render( <Settings /> );
	}

	const convertersTab = document.getElementById(
		'tasty-recipes-converters-tab'
	);
	if ( convertersTab ) {
		createRootCompat( convertersTab ).render( <Converters /> );
	}
} );
