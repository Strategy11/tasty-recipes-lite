import { createRoot } from '@wordpress/element';
import { FiltersForm } from './components/FiltersForm';

let isOpen = false;
let instance = null;
let root = null;
const parentElement = document.getElementById(
	'tasty-recipes-filters-button-container'
);

const closeFiltersForm = () => {
	if ( instance && root ) {
		isOpen = false;
		root.unmount();
		instance.remove();
		instance = null;
		root = null;
	}
};

export const toggleFiltersForm = () => {
	if ( isOpen ) {
		closeFiltersForm();
		return;
	}

	isOpen = true;

	instance = document.createElement( 'div' );
	parentElement.appendChild( instance );

	root = createRoot( instance );
	root.render( <FiltersForm closeFiltersForm={ closeFiltersForm } /> );
};
