import { createRoot } from '@wordpress/element';
import { FiltersForm } from './components/FiltersForm';

let isOpen = false;
let instance = null;
const parentElement = document.getElementById(
	'tasty-recipes-filters-button-container'
);

const closeFiltersForm = () => {
	if ( instance ) {
		isOpen = false;
		instance.remove();
		instance = null;
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

	createRoot( instance ).render( <FiltersForm /> );
};
