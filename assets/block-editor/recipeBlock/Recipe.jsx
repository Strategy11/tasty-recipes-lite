import { applyFilters } from '@wordpress/hooks';
import {
	ToggleControl,
	PanelBody,
	TextControl,
	ToolbarGroup,
	ToolbarButton,
	SelectControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {
	BlockControls,
	InspectorControls,
	useBlockProps,
} from '@wordpress/block-editor';

// We use the wp global object because the NPM package is crashing the build.
const { serverSideRender: ServerSideRender } = wp;

export const Recipe = ( { attributes, setAttributes, handleModalClose } ) => {
	const blockProps = useBlockProps();

	const existingRecipe =
		window.tastyRecipesBlockEditor.recipeDataStore[ attributes.id ];

	const existingOnClickHandler = () => {
		if ( typeof existingRecipe === 'undefined' ) {
			return;
		}

		window.tastyRecipesEditorModal.open( existingRecipe, handleModalClose );
	};

	const disableJSON = applyFilters(
		'tasty_recipes_lite_disable_json',
		<ToggleControl
			label={ __(
				'This is a non-food recipe (craft, cleaning product, etc)',
				'tasty-recipes-lite'
			) }
			disabled
			__nextHasNoMarginBottom
		/>,
		attributes,
		setAttributes
	);

	const unitConversion = applyFilters(
		'tasty_recipes_lite_conversion',
		<ToggleControl
			label={ __(
				'Disable unit conversion for this recipe',
				'tasty-recipes-lite'
			) }
			disabled
			__nextHasNoMarginBottom
		/>,
		attributes,
		setAttributes
	);

	const disableScaling = applyFilters(
		'tasty_recipes_lite_disable_scaling',
		<ToggleControl
			label={ __(
				'Disable scaling for this recipe',
				'tasty-recipes-lite'
			) }
			disabled
			__nextHasNoMarginBottom
		/>,
		attributes,
		setAttributes
	);

	return (
		<div { ...blockProps }>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						className="components-edit-tasty-recipe-button"
						icon="edit"
						label={ __(
							'Edit Tasty Recipe',
							'tasty-recipes-lite'
						) }
						onClick={ existingOnClickHandler }
					></ToolbarButton>
				</ToolbarGroup>
			</BlockControls>
			<div
				role="button"
				tabIndex={ 0 }
				onClick={ existingOnClickHandler }
				onKeyDown={ existingOnClickHandler }
			>
				<ServerSideRender
					block="wp-tasty/tasty-recipe"
					attributes={ attributes }
				/>
			</div>
			<InspectorControls>
				<PanelBody
					title={ __( 'Select Recipe', 'tasty-recipes-lite' ) }
					initialOpen={ true }
				>
					<SelectControl
						options={ window.tastyRecipesBlockEditor.recipeIds }
						value={ attributes.id }
						onChange={ ( value ) => {
							setAttributes( { id: Number( value ) } );
						} }
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
				</PanelBody>
				<PanelBody
					title={ __( 'Recipe options', 'tasty-recipes-lite' ) }
					initialOpen={ true }
				>
					{ disableJSON }
					{ unitConversion }
					{ disableScaling }
					<ToggleControl
						label={ __(
							'Override author link',
							'tasty-recipes-lite'
						) }
						checked={ attributes.override_author_link }
						onChange={ ( checked ) =>
							setAttributes( {
								override_author_link: checked,
							} )
						}
						__nextHasNoMarginBottom
					/>
					{ attributes.override_author_link && (
						<TextControl
							value={ attributes.author_link }
							onChange={ ( value ) => {
								setAttributes( {
									author_link: value,
								} );
							} }
							__next40pxDefaultSize
							__nextHasNoMarginBottom
						/>
					) }
				</PanelBody>
			</InspectorControls>
		</div>
	);
};
