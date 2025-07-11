import { createRoot } from '@wordpress/element';
import { TrashModal } from './components/TrashModal';

let isOpen = false;
let instance = null;
const body = document.querySelector( 'body' );

export const openTrashModal = () => {
	if ( isOpen ) {
		return;
	}

	isOpen = true;

	instance = document.createElement( 'div' );
	body.appendChild( instance );

	createRoot( instance ).render( <TrashModal /> );
};
