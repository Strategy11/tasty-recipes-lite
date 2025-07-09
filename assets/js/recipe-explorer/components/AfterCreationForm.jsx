import { useEffect, useState } from '@wordpress/element';
import { Modal, useModal } from '@library';

export const AfterCreationForm = ( { recipe, closeEditorExtension } ) => {
	const [ pageState, setPageState ] = useState( {
		initialOpen: false,
		page: 'select',
	} );

	const { modalData, openModal, closeModal } = useModal();

	useEffect( () => {
		if ( ! pageState.initialOpen ) {
			openModal();

			setPageState( ( prev ) => {
				return {
					...prev,
					initialOpen: true,
				};
			} );
		}

		if ( pageState.initialOpen && ! modalData.open ) {
			closeEditorExtension();
		}
	}, [
		pageState.initialOpen,
		openModal,
		modalData.open,
		closeEditorExtension,
	] );

	return (
		<>
			<Modal modalData={ modalData } closeModal={ closeModal }>
				{ /* Add Modal Logic */ }
			</Modal>
		</>
	);
};
