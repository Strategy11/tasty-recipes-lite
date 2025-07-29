import { createRoot } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { MessageModal } from '../components/MessageModal';

let isOpen = false;
let instance = null;
let root = null;
const body = document.body;

const closeTrashModal = () => {
	isOpen = false;
	instance.remove();
	root.unmount();
	instance = null;
	root = null;
};

export const openTrashModal = ( postUrl ) => {
	if ( isOpen ) {
		return;
	}

	isOpen = true;

	instance = document.createElement( 'div' );
	body.appendChild( instance );

	const button = (
		<a className="tasty-button-primary" href={ postUrl }>
			{ __( 'Go to Post', 'tasty-recipes-lite' ) }
		</a>
	);

	root = createRoot( instance ).render(
		<MessageModal
			title={ __( 'Recipe Can’t be Deleted', 'tasty-recipes-lite' ) }
			description={ __(
				"This recipe can't be deleted since it is embedded in your site. Please remove the recipe from your page first.",
				'tasty-recipes-lite'
			) }
			button={ button }
			closeMessageModal={ closeTrashModal }
		/>
	);
};
