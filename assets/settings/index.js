import { createRoot } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';
import { Design, Settings, Converters } from './tabs';
import '../scss/settings.scss';

domReady( () => {
	const designTab = document.getElementById( 'tasty-recipes-design-tab' );
	if ( designTab ) {
		const root = createRoot( designTab );
		root.render( <Design /> );
	}

	const settingsTab = document.getElementById( 'tasty-recipes-settings-tab' );
	if ( settingsTab ) {
		const root = createRoot( settingsTab );
		root.render( <Settings /> );
	}

	const convertersTab = document.getElementById(
		'tasty-recipes-converters-tab'
	);
	if ( convertersTab ) {
		const root = createRoot( convertersTab );
		root.render( <Converters /> );
	}
} );
