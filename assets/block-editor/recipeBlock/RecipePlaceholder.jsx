import { BlockControls } from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export const RecipePlaceholder = ( { handleModalClose } ) => {
	const defaultOnClickHandler = () => {
		window.tastyRecipesEditorModal.open(
			{
				author_name:
					window.tastyRecipesEditorModalData.defaultAuthorName,
				title: wp.data
					.select( 'core/editor' )
					.getEditedPostAttribute( 'title' ),
				diet: '',
			},
			handleModalClose
		);
	};

	return (
		<>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						className="components-edit-tasty-recipe-button"
						icon="edit"
						label={ __(
							'Create Tasty Recipe',
							'tasty-recipes-lite'
						) }
						onClick={ defaultOnClickHandler }
					></ToolbarButton>
				</ToolbarGroup>
			</BlockControls>
			<div
				role="button"
				tabIndex={ 0 }
				onClick={ defaultOnClickHandler }
				onKeyDown={ defaultOnClickHandler }
			>
				<p>
					{ __(
						'No Tasty Recipe created yet. Click here to start one.',
						'tasty-recipes-lite'
					) }
				</p>
			</div>
		</>
	);
};
