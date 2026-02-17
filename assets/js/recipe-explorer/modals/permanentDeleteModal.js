import { createRoot } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { MessageModal } from '../components/MessageModal';

let isOpen = false;
let instance = null;
let root = null;
const body = document.body;

const closePermanentDeleteModal = () => {
	isOpen = false;
	instance.remove();
	root.unmount();
	instance = null;
	root = null;
};

export const openPermanentDeleteModal = ( postId, name ) => {
	if ( isOpen ) {
		return;
	}

	isOpen = true;

	instance = document.createElement( 'div' );
	body.appendChild( instance );

	const deleteRecipe = async () => {
		await apiFetch( {
			path: `/tasty-recipes/v1/recipe-explorer/delete/${ postId }`,
			method: 'POST',
		} );

		window.location.reload();
	};

	const button = (
		<button className="tasty-button-primary" onClick={ deleteRecipe }>
			{ __( 'Delete', 'tasty-recipes-lite' ) }
		</button>
	);

	const modalName = name.length > 0 ? `"${ name }"` : '';

	root = createRoot( instance );
	root.render(
		<MessageModal
			title={ __( 'Permanently Delete Recipe', 'tasty-recipes-lite' ) }
			description={ sprintf(
				/* translators: %s: recipe name */
				__(
					'Are you sure you want to permanently delete the %s recipe? This action cannot be undone.',
					'tasty-recipes-lite'
				),
				modalName
			) }
			button={ button }
			closeMessageModal={ closePermanentDeleteModal }
			closeLabel={ __( 'Cancel', 'tasty-recipes-lite' ) }
		/>
	);
};
