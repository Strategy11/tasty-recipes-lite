import { createRoot } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';
import { Design } from './tabs';
import '../scss/settings.scss';

domReady( () => {
	const designSettings = document.getElementById(
		'tasty-recipes-design-settings'
	);

	if ( designSettings ) {
		const root = createRoot( designSettings );
		root.render( <Design /> );
	}
} );
