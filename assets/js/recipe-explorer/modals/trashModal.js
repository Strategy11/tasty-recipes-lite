import { createRoot } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { MessageModal } from '../components/MessageModal';
import { applyFilters } from '@wordpress/hooks';

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

export const openTrashModal = ( postUrl, recipeType = 'recipe' ) => {
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

	const labels = {
		title: applyFilters( 'tasty_recipes_trash_modal_title', __( "Recipe Can't be Deleted", 'tasty-recipes-lite' ), recipeType ),
		description: applyFilters( 'tasty_recipes_trash_modal_description', __(
			"This recipe can't be deleted since it is embedded in your site. Please remove the recipe from your page first.",
			'tasty-recipes-lite'
		), recipeType ),
	};

	root = createRoot( instance );
	root.render(
		<MessageModal
			title={ labels.title }
			description={ labels.description }
			button={ button }
			closeMessageModal={ closeTrashModal }
		/>
	);
};
