import { useEffect, useState, useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { SelectPage } from './pages/SelectPage';
import { EmbedPage } from './pages/EmbedPage';
import { CreatePage } from './pages/CreatePage';
import { Modal, useModal } from '@library';

export const AfterCreationForm = ( { recipe, closeEditorExtension } ) => {
	const [ pageState, setPageState ] = useState( {
		initialOpen: false,
		page: 'select',
	} );

	const { modalData, openModal, closeModal } = useModal();

	const recipeType = recipe?.recipe_type || 'recipe';

	const labels = useMemo( () => {
		const defaultLabels = {
			modalTitle: __( 'Embed Recipe', 'tasty-recipes-lite' ),
			embedInExisting: __(
				'Embed in existing post',
				'tasty-recipes-lite'
			),
			embedDescription: __(
				'Embed your recipe in an existing post.',
				'tasty-recipes-lite'
			),
			createNewPost: __( 'Create new post', 'tasty-recipes-lite' ),
			createDescription: __(
				'Create a new post and embed your recipe.',
				'tasty-recipes-lite'
			),
			skipStep: __(
				'<link>Skip this step</link> and add the recipe later from inside a post or page.',
				'tasty-recipes-lite'
			),
		};

		return applyFilters(
			'tasty_recipes_embed_modal_labels',
			defaultLabels,
			recipeType,
			recipe
		);
	}, [ recipeType, recipe ] );

	useEffect( () => {
		if ( ! pageState.initialOpen ) {
			openModal( {
				title: labels.modalTitle,
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
		// eslint-disable-next-line react-hooks/exhaustive-deps
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
					<SelectPage setPage={ setPage } labels={ labels } />
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
