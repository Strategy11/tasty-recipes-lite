import { BlockControls, useBlockProps } from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Icon } from '@wordpress/icons';
import { useState } from '@wordpress/element';
import { RecipeIcon } from '../icons';
import Select from 'react-select';

export const RecipePlaceholder = ( { handleModalClose, setAttributes } ) => {
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
							<Select
								className="tasty-recipes-block-placeholder-select"
								placeholder={ __(
									'Select existing recipe',
									'tasty-recipes-lite'
								) }
								options={
									window.tastyRecipesBlockEditor.recipeIds
								}
								onChange={ ( selectedOption ) => {
									if ( selectedOption ) {
										setAttributes( {
											id: Number( selectedOption.value ),
										} );
									}
								} }
								styles={ {
									control: ( provided, state ) => ( {
										...provided,
										border: '1px solid #ccc',
										borderRadius: '4px',
										minHeight: '40px',
										boxShadow: 'none',
										outline: 'none',
										background: '#fff',
										fontSize: '16px',
										padding: '0',
										width: '70%',
										borderColor: state.isFocused ? '#888' : '#ccc',
									} ),
									input: ( provided ) => ( {
										...provided,
										outline: 'none',
										boxShadow: 'none',
									} ),
									valueContainer: ( provided ) => ( {
										...provided,
										padding: '2px 12px',
									} ),
									placeholder: ( provided ) => ( {
										...provided,
										color: '#666',
										fontSize: '15px',
									} ),
									indicatorSeparator: () => ( {
										display: 'none',
									} ),
									dropdownIndicator: ( provided ) => ( {
										...provided,
										color: '#333',
										paddingRight: '10px',
									} ),
									menu: ( provided ) => ( {
										...provided,
										borderRadius: '4px',
										boxShadow: '0 2px 8px rgba(0,0,0,0.08)',
										fontSize: '15px',
										border: '1px solid #ccc',
										width: '70%',
									} ),
									option: ( provided, state ) => {
										let backgroundColor = '#fff';

										if ( state.isSelected ) {
											backgroundColor = '#f0f0f0';
										}

										if ( state.isFocused ) {
											backgroundColor = '#f8f8f8';
										}
										return {
											...provided,
											padding: '8px 12px',
											cursor: 'pointer',
											backgroundColor,
											color: '#222',
										};
									},
								} }
							/>
						</div>
					) }
				</div>
			</div>
		</div>
	);
};
