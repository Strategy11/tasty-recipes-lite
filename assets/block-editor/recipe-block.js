/**
 * WordPress dependencies
 */
const {
	InspectorControls,
	BlockControls
} = wp.blockEditor;

const {
	TextControl,
	PanelBody,
	ToolbarGroup,
	ToolbarButton
} = wp.components;

const { serverSideRender: ServerSideRender } = wp;

const { Fragment } = wp.element;

const { __ } = wp.i18n;

const handleEditorModalUpdate = (props, response) => {
	const date = new Date();
	window.tastyRecipesBlockEditor.recipeDataStore[response.recipe.id] =
		response.recipe;
	props.setAttributes({
		id: response.recipe.id,
		lastUpdated: date.getTime()
	});
	if ( typeof wp.hooks !== 'undefined' ) {
		wp.hooks.doAction('tasty_recipes_updated_recipe', response.recipe);
	}
};

const edit = props => {
	const handleModalClose = response => {
		handleEditorModalUpdate(props, response);
	};

	const defaultOnClickHandler = () => {
		window.tastyRecipesEditorModal.open(
			{
				author_name:
					window.tastyRecipesEditorModalData.defaultAuthorName,
				title: wp.data
						.select("core/editor")
						.getEditedPostAttribute("title"),
				diet: '',
			},
			handleModalClose
		);
	};

	function CreateRecipeButton() {
		return (
			<ToolbarButton
				className="components-edit-tasty-recipe-button"
				icon="edit"
				label={ __( 'Create Tasty Recipe', 'tasty-recipes-lite' ) }
				onClick={ defaultOnClickHandler }
			>
			</ToolbarButton>
		);
	}

	if (
		typeof props.attributes.id === "undefined" ||
		0 === props.attributes.id
	) {
		if (typeof props.attributes.id === "undefined") {
			window.tastyRecipesEditorModal.open(
				{
					author_name:
						window.tastyRecipesEditorModalData.defaultAuthorName,
					title: wp.data
						.select("core/editor")
						.getEditedPostAttribute("title"),
					diet: '',
				},
				handleModalClose
			);
			props.setAttributes({ id: 0 });
		}
		return (
			<Fragment>
				<BlockControls>
					<ToolbarGroup>
						<CreateRecipeButton></CreateRecipeButton>
					</ToolbarGroup>
				</BlockControls>
				<div
					role="button"
					tabIndex={0}
					onClick={defaultOnClickHandler}
					onKeyDown={defaultOnClickHandler}
				>
					<p>
						{__(
							"No Tasty Recipe created yet. Click here to start one.",
							"tasty-recipes"
						)}
					</p>
				</div>
			</Fragment>
		);
	}

	const existingOnClickHandler = () => {
		if (
			typeof window.tastyRecipesBlockEditor.recipeDataStore[
				props.attributes.id
			] === "undefined"
		) {
			return;
		}
		window.tastyRecipesEditorModal.open(
			window.tastyRecipesBlockEditor.recipeDataStore[props.attributes.id],
			handleModalClose
		);
	};

	function EditRecipeButton() {
		return (
			<ToolbarButton
				className="components-edit-tasty-recipe-button"
				icon="edit"
				label={ __( 'Edit Tasty Recipe', 'tasty-recipes-lite' ) }
				onClick={ existingOnClickHandler }
			>
			</ToolbarButton>
		);
	}

	return (
		<Fragment>
			<BlockControls>
				<ToolbarGroup>
					<EditRecipeButton></EditRecipeButton>
				</ToolbarGroup>
			</BlockControls>
			<div
				role="button"
				tabIndex={0}
				onClick={existingOnClickHandler}
				onKeyDown={existingOnClickHandler}
			>
				<ServerSideRender
					block="wp-tasty/tasty-recipe"
					attributes={props.attributes}
				/>
			</div>
			<InspectorControls>
				<PanelBody
					title={__("Recipe options", "tasty-recipes")}
					initialOpen={true}
				>
					<TextControl
						label={__("Override Author Link", "tasty-recipes")}
						value={props.attributes.author_link}
						onChange={value => {
							props.setAttributes({
								author_link: value
							});
						}}
					/>
				</PanelBody>
			</InspectorControls>
		</Fragment>
	);
};

/**
 * Renders dynamically on the frontend.
 */
const save = () => {
	return null;
};

const blockRegistration = {
	title: window.tastyRecipesBlockEditor.recipeBlockTitle,
	icon: "carrot",
	category: "common",
	html: false,
	attributes: {
		id: {
			type: "number"
		},
		lastUpdated: {
			type: "number"
		},
		author_link: {
			type: "string",
			default: ""
		},
	},
	edit,
	save,
	transforms: {
		from: [
			{
				type: "shortcode",
				tag: "tasty-recipe",
				attributes: {
					id: {
						type: "number",
						shortcode: props => {
							if (typeof props.named.id === "undefined") {
								return 0;
							}
							return parseInt(props.named.id);
						}
					}
				}
			}
		]
	}
};

export default blockRegistration;
