import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Modal, useModal } from '@library';

export const MessageModal = ( {
	closeMessageModal,
	title,
	description,
	button,
} ) => {
	const { openModal, modalData, closeModal } = useModal();
	const [ pageState, setPageState ] = useState( {
		initialOpen: false,
	} );

	useEffect( () => {
		if ( ! pageState.initialOpen ) {
			openModal( { title } );
			setPageState( {
				initialOpen: true,
			} );
		}

		if ( pageState.initialOpen && ! modalData.open ) {
			closeMessageModal();
		}
	}, [
		closeMessageModal,
		modalData.open,
		openModal,
		pageState.initialOpen,
		title,
	] );

	return (
		<Modal modalData={ modalData } closeModal={ closeModal }>
			<p>{ description }</p>
			<div className="tasty-recipes-modal-actions">
				<button
					className="tasty-secondary-button"
					onClick={ () => closeModal() }
				>
					{ __( 'Close', 'tasty-recipes-lite' ) }
				</button>
				{ button }
			</div>
		</Modal>
	);
};
