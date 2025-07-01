import { applyFilters } from '@wordpress/hooks';
import {
	CheckboxControl,
	PanelBody,
	TextControl,
	ToolbarGroup,
	ToolbarButton,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {
	BlockControls,
	InspectorAdvancedControls,
	useBlockProps,
} from '@wordpress/block-editor';

// We use the wp global object because the NPM package is crashing the build.
const { serverSideRender: ServerSideRender } = wp;

export const Recipe = ( { attributes, setAttributes, handleModalClose } ) => {
	const blockProps = useBlockProps();

	const existingOnClickHandler = () => {
		if (
			typeof window.tastyRecipesBlockEditor.recipeDataStore[
				attributes.id
			] === 'undefined'
		) {
			return;
		}
		window.tastyRecipesEditorModal.open(
			window.tastyRecipesBlockEditor.recipeDataStore[ attributes.id ],
			handleModalClose
		);
	};

	const disableJSON = applyFilters(
		'tasty_recipes_lite_disable_json',
		<CheckboxControl
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
		<CheckboxControl
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
		<CheckboxControl
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
			<InspectorAdvancedControls>
				<PanelBody
					title={ __( 'Recipe options', 'tasty-recipes-lite' ) }
					initialOpen={ true }
				>
					{ disableJSON }
					{ unitConversion }
					{ disableScaling }
					<TextControl
						label={ __(
							'Override Author Link',
							'tasty-recipes-lite'
						) }
						value={ attributes.author_link }
						onChange={ ( value ) => {
							setAttributes( {
								author_link: value,
							} );
						} }
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorAdvancedControls>
		</div>
	);
};
