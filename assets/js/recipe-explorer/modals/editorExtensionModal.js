import { createRoot } from '@wordpress/element';
import { AfterCreationForm } from '../components/AfterCreationForm';

let isOpen = false;
let instance = null;
let root = null;
const body = document.body;

const closeEditorExtension = () => {
	isOpen = false;
	instance.remove();
	root.unmount();
	instance = null;
	root = null;
};

export const openEditorExtension = ( recipe ) => {
	if ( isOpen ) {
		return;
	}

	isOpen = true;

	instance = document.createElement( 'div' );
	body.appendChild( instance );

	root = createRoot( instance );
	root.render(
		<AfterCreationForm
			recipe={ recipe }
			closeEditorExtension={ closeEditorExtension }
		/>
	);
};
