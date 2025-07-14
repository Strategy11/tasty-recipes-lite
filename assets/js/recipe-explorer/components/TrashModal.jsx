import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Modal, useModal } from '@library';

export const TrashModal = ( { postUrl, closeTrashModal } ) => {
	const { openModal, modalData, closeModal } = useModal();
	const [ pageState, setPageState ] = useState( {
		initialOpen: false,
	} );

	useEffect( () => {
		if ( ! pageState.initialOpen ) {
			openModal( {
				title: __( 'Recipe Can’t be Deleted', 'tasty-recipes-lite' ),
			} );
			setPageState( {
				initialOpen: true,
			} );
		}

		if ( pageState.initialOpen && ! modalData.open ) {
			closeTrashModal();
		}
	}, [ closeTrashModal, modalData.open, openModal, pageState.initialOpen ] );

	return (
		<Modal modalData={ modalData } closeModal={ closeModal }>
			<p>
				{ __(
					"This recipe can't be deleted since it is embedded in your site. Please remove the recipe from your page first.",
					'tasty-recipes-lite'
				) }
			</p>
			<div className="tasty-recipes-modal-actions">
				<button
					className="tasty-secondary-button"
					onClick={ () => closeModal() }
				>
					{ __( 'Close', 'tasty-recipes-lite' ) }
				</button>
				<a className="tasty-button-primary" href={ postUrl }>
					{ __( 'Go to Post', 'tasty-recipes-lite' ) }
				</a>
			</div>
		</Modal>
	);
};
