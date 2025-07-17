import { createRoot } from '@wordpress/element';
import { AfterCreationForm } from '../components/AfterCreationForm';

let isOpen = false;
let instance = null;
const body = document.body;

const closeEditorExtension = () => {
	isOpen = false;
	instance.remove();
	instance = null;
};

export const openEditorExtension = ( recipe ) => {
	if ( isOpen ) {
		return;
	}

	isOpen = true;

	instance = document.createElement( 'div' );
	body.appendChild( instance );

	createRoot( instance ).render(
		<AfterCreationForm
			recipe={ recipe }
			closeEditorExtension={ closeEditorExtension }
		/>
	);
};
