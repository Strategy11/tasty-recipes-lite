import { createRoot } from '@wordpress/element';
import { BulkEditModal } from '../components/bulk-edit/BulkEditModal';

let isOpen = false;
let instance = null;
let root = null;

const closeBulkEditModal = () => {
	isOpen = false;
	root.unmount();
	instance.remove();
	instance = null;
	root = null;
};

export const openBulkEditModal = ( ids ) => {
	if ( isOpen ) {
		return;
	}

	isOpen = true;

	instance = document.createElement( 'div' );
	document.body.appendChild( instance );

	root = createRoot( instance );
	root.render(
		<BulkEditModal ids={ ids } onClose={ closeBulkEditModal } />
	);
};

export const registerBulkEdit = () => {
	const config = window.tastyRecipesExplorerPage?.bulkEdit;
	const applyButton = document.getElementById( 'doaction' );
	const actionSelect = document.getElementById( 'bulk-action-selector-top' );

	if ( ! config || ! applyButton || ! actionSelect ) {
		return;
	}

	// The bottom "Apply" button is forwarded to this one by core.
	applyButton.addEventListener( 'click', ( e ) => {
		if ( actionSelect.value !== config.action ) {
			return;
		}

		const ids = Array.from(
			document.querySelectorAll(
				'#posts-filter .wp-list-table tbody .check-column input[type="checkbox"]:checked'
			)
		)
			.map( ( input ) => parseInt( input.value, 10 ) )
			.filter( Boolean );

		// Let core show its "select at least one item" notice.
		if ( ! ids.length ) {
			return;
		}

		e.preventDefault();
		openBulkEditModal( ids );
	} );
};
