import { BlockControls, useBlockProps } from '@wordpress/block-editor';
import {
	ToolbarGroup,
	ToolbarButton,
	Button,
	ComboboxControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Icon } from '@wordpress/icons';
import { useState } from '@wordpress/element';
import { select } from '@wordpress/data';
import { RecipeIcon } from '../icons';

const getRecipeOptions = () =>
	( window.tastyRecipesBlockEditor?.recipeIds || [] ).map( item => ( {
		label: item.label,
		value: String( item.value ),
	} ) );

export const RecipePlaceholder = ( { handleModalClose, setAttributes } ) => {
	const [ pageState, setPageState ] = useState( 'placeholder' );
	const blockProps = useBlockProps();

	const defaultOnClickHandler = () => {
		window.tastyRecipesEditorModal.open(
			{
				author_name:
					window.tastyRecipesEditorModalData.defaultAuthorName,
				title: select( 'core/editor' ).getEditedPostAttribute(
					'title'
				),
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
							<ComboboxControl
								className="tasty-recipes-block-placeholder-select"
								label={ __(
									'Embed recipe',
									'tasty-recipes-lite'
								) }
								placeholder={ __(
									'Select existing recipe',
									'tasty-recipes-lite'
								) }
								options={ getRecipeOptions() }
								onChange={ recipeId => {
									if ( recipeId ) {
										setAttributes( {
											id: Number( recipeId ),
										} );
									}
								} }
							/>
						</div>
					) }
				</div>
			</div>
		</div>
	);
};
