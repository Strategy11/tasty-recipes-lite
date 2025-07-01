import { BlockControls, useBlockProps } from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Icon } from '@wordpress/icons';
import { useState } from '@wordpress/element';
import { RecipeIcon } from '../icons';
import AsyncSelect from 'react-select/async';

export const RecipePlaceholder = ( { handleModalClose } ) => {
	const [ pageState, setPageState ] = useState( 'placeholder' );
	const blockProps = useBlockProps();

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
		<div { ...blockProps }>
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
			<div className="tasty-recipes-block">
				<div className="tasty-recipes-block-placeholder">
					<div className="tasty-recipes-block-placeholder-heading">
						<Icon icon={ RecipeIcon } width="20" height="24" />
						<h2>{ __( 'Tasty Recipes', 'tasty-recipes-lite' ) }</h2>
					</div>
					<p>
						{ __(
							'Select an already created recipe to embed or create a new one from scratch.',
							'tasty-recipes-lite'
						) }
					</p>
					{ pageState === 'placeholder' && (
						<div className="tasty-recipes-block-placeholder-buttons">
							<Button
								variant="primary"
								onClick={ defaultOnClickHandler }
							>
								{ __(
									'Create new recipe',
									'tasty-recipes-lite'
								) }
							</Button>
							<Button
								variant="tertiary"
								onClick={ () => setPageState( 'embed' ) }
							>
								{ __(
									'Embed existing recipe',
									'tasty-recipes-lite'
								) }
							</Button>
						</div>
					) }
					{ pageState === 'embed' && (
						<div className="tasty-recipes-block-placeholder-embed">
							<AsyncSelect
								placeholder={ __(
									'Select existing recipe',
									'tasty-recipes-lite'
								) }
							/>
						</div>
					) }
				</div>
			</div>
		</div>
	);
};
