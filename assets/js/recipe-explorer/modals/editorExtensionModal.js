import { AfterCreationForm } from '../components/AfterCreationForm';
import { createRootCompat } from '../../utils/reactCompat';

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

	root = createRootCompat( instance );
	root.render(
		<AfterCreationForm
			recipe={ recipe }
			closeEditorExtension={ closeEditorExtension }
		/>
	);
};
