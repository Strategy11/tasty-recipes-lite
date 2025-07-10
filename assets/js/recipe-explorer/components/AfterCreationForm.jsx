import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Modal, useModal } from '@library';
import { SelectPage } from './pages/SelectPage';
import { EmbedPage } from './pages/EmbedPage';
import { CreatePage } from './pages/CreatePage';

export const AfterCreationForm = ( { recipe, closeEditorExtension } ) => {
	const [ pageState, setPageState ] = useState( {
		initialOpen: false,
		page: 'select',
	} );

	const { modalData, openModal, closeModal } = useModal();

	useEffect( () => {
		if ( ! pageState.initialOpen ) {
			openModal( {
				title: __( 'Embed Recipe', 'tasty-recipes-lite' ),
			} );

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

	const setPage = ( page ) => {
		setPageState( ( prev ) => {
			return {
				...prev,
				page,
			};
		} );
	};

	return (
		<>
			<Modal modalData={ modalData } closeModal={ closeModal }>
				{ pageState.page === 'select' && (
					<SelectPage setPage={ setPage } />
				) }
				{ pageState.page === 'embed' && (
					<EmbedPage recipe={ recipe } setPage={ setPage } />
				) }
				{ pageState.page === 'create' && (
					<CreatePage recipe={ recipe } setPage={ setPage } />
				) }
			</Modal>
		</>
	);
};
