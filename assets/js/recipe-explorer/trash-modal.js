import { createRoot } from '@wordpress/element';
import { TrashModal } from './components/TrashModal';

let isOpen = false;
let instance = null;
const body = document.querySelector( 'body' );

const closeTrashModal = () => {
	isOpen = false;
	instance.remove();
	instance = null;
};

export const openTrashModal = ( postUrl ) => {
	if ( isOpen ) {
		return;
	}

	isOpen = true;

	instance = document.createElement( 'div' );
	body.appendChild( instance );

	createRoot( instance ).render(
		<TrashModal postUrl={ postUrl } closeTrashModal={ closeTrashModal } />
	);
};
