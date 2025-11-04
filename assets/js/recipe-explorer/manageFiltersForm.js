import { FiltersForm } from './components/FiltersForm';
import { createRootCompat } from '../utils/reactCompat';

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

	root = createRootCompat( instance );
	root.render( <FiltersForm closeFiltersForm={ closeFiltersForm } /> );
};
