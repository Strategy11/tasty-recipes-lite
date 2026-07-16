import { useState, useMemo, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { FieldGroup } from './FieldGroup';
import { ImprovedKeysNotice } from './ImprovedKeysNotice';
import { RichTextarea } from './RichTextarea';
import { ImageField } from './ImageField';

export const RecipeForm = ( { initialRecipe, config } ) => {
	const defaultRecipeType = config.defaultRecipeType || 'recipe';
	const initialRecipeState = applyFilters(
		'tasty_recipes_recipe_editor_initial_recipe',
		{
			recipe_type: defaultRecipeType,
			...( initialRecipe || {} ),
		},
		config
	);
	const [ recipe, setRecipe ] = useState( initialRecipeState );
	const [ isNoticeDismissed, setIsNoticeDismissed ] = useState(
		!! config.improvedKeysNoticeDismissed
	);

	const handleNoticeDismiss = () => {
		setIsNoticeDismissed( true );

		const formData = new FormData();
		formData.append(
			'action',
			'tasty_recipes_dismiss_improved_keys_notice'
		);
		formData.append( 'nonce', config.modifyNonce );

		fetch( window.ajaxurl, {
			method: 'POST',
			body: formData,
		} );
	};

	const recipeType = recipe.recipe_type || defaultRecipeType;
	const editorConfig = applyFilters(
		'tasty_recipes_recipe_editor_config',
		config,
		recipeType,
		recipe
	);
	const typeSettings = editorConfig?.typeSettings?.[ recipeType ] || {};
	const attributes = editorConfig.attributes;

	// For ImprovedKeysNotice positioning - split cooking attributes around 'category'
	const cookingAttributes = attributes.cooking;
	const cookingKeys = Object.keys( cookingAttributes );
	const splitIndex = cookingKeys.indexOf( 'category' );

	const cookingAttributesBefore = Object.fromEntries(
		cookingKeys
			.slice( 0, splitIndex > -1 ? splitIndex : cookingKeys.length )
			.map( ( key ) => [ key, cookingAttributes[ key ] ] )
	);

	const cookingAttributesAfter = Object.fromEntries(
		cookingKeys
			.slice( splitIndex > -1 ? splitIndex : cookingKeys.length )
			.map( ( key ) => [ key, cookingAttributes[ key ] ] )
	);

	const nutritionAttributes = useMemo( () => {
		const nutritionAttrs = attributes.nutrition;

		return Object.keys( nutritionAttrs )
			.filter( ( key ) => 'nutrifox_id' !== key )
			.reduce( ( obj, key ) => {
				obj[ key ] = nutritionAttrs[ key ];
				return obj;
			}, {} );
	}, [ attributes.nutrition ] );

	const nutrifoxLogoUrl =
		window.tastyRecipesEditorModalData.pluginURL +
		'/assets/images/nutrifox-logo.png';

	const handleRecipeChange = ( field, value ) =>
		setRecipe( ( prev ) => ( { ...prev, [ field ]: value } ) );

	const modalTitles = {
		create: __( 'Create Recipe', 'tasty-recipes-lite' ),
		edit: __( 'Edit Recipe', 'tasty-recipes-lite' ),
		...( typeSettings.modalTitles || {} ),
	};
	const buttonLabels = {
		create: __( 'Insert Recipe', 'tasty-recipes-lite' ),
		edit: __( 'Update Recipe', 'tasty-recipes-lite' ),
		...( typeSettings.buttonLabels || {} ),
	};
	const imageLabels = typeSettings.imageLabels || {};
	const richTextareas = [
		{
			name: 'description',
			label: __( 'Description', 'tasty-recipes-lite' ),
			placeholder: __(
				'Tell us about this recipe...',
				'tasty-recipes-lite'
			),
			initialValue: recipe.description,
			initialVideoSettingsValue: recipe.description_video_settings,
		},
		{
			name: 'ingredients',
			label: __( 'Ingredients', 'tasty-recipes-lite' ),
			placeholder: __(
				'Enter ingredient name or paste the full list to bulk add',
				'tasty-recipes-lite'
			),
			actions: [
				{
					label: __( 'Add Ingredient', 'tasty-recipes-lite' ),
					icon: 'add',
				},
				{
					label: __( 'Add Section Title', 'tasty-recipes-lite' ),
					icon: 'section',
				},
			],
			initialValue: recipe.ingredients,
			initialVideoSettingsValue: recipe.ingredients_video_settings,
		},
		{
			name: 'instructions',
			label: __( 'Instructions', 'tasty-recipes-lite' ),
			placeholder: __(
				'Enter instruction description or paste the full list to bulk add',
				'tasty-recipes-lite'
			),
			actions: [
				{
					label: __( 'Add Step', 'tasty-recipes-lite' ),
					icon: 'add',
				},
				{
					label: __( 'Add Section Title', 'tasty-recipes-lite' ),
					icon: 'section',
				},
			],
			initialValue: recipe.instructions,
			initialVideoSettingsValue: recipe.instructions_video_settings,
		},
		{
			name: 'notes',
			label: __( 'Notes', 'tasty-recipes-lite' ),
			placeholder: __(
				'Add any tips, substitutions, or storage notes...',
				'tasty-recipes-lite'
			),
			initialValue: recipe.notes,
			initialVideoSettingsValue: recipe.notes_video_settings,
		},
	];
	const richTextareaSettings = richTextareas
		.map( ( field ) => ( {
			...field,
			...( typeSettings.richTextareas?.[ field.name ] || {} ),
		} ) )
		.filter( ( field ) => false !== field.show );
	const descriptionField = richTextareaSettings.find(
		( field ) => 'description' === field.name
	);
	const remainingRichTextareas = richTextareaSettings.filter(
		( field ) => 'description' !== field.name
	);
	const detailsGroupSettings = typeSettings.groups?.details || {};
	const nutritionGroupSettings = typeSettings.groups?.nutrition || {};

	// Use split cooking attributes for recipe type to support ImprovedKeysNotice placement
	const isRecipeType = recipeType === 'recipe';
	const detailsFieldsBefore = isRecipeType
		? cookingAttributesBefore
		: detailsGroupSettings.fields || attributes.cooking;
	const detailsFieldsAfter = isRecipeType ? cookingAttributesAfter : null;

	const detailsFields = applyFilters(
		'tasty_recipes_recipe_editor_group_fields',
		detailsGroupSettings.fields || attributes.cooking,
		'details',
		recipe,
		recipeType
	);
	const detailsHeading = applyFilters(
		'tasty_recipes_recipe_editor_group_heading',
		detailsGroupSettings.heading || __( 'Details', 'tasty-recipes-lite' ),
		'details',
		recipe,
		recipeType
	);
	const showDetailsGroup = applyFilters(
		'tasty_recipes_recipe_editor_show_group',
		false !== detailsGroupSettings.show,
		'details',
		recipe,
		recipeType
	);
	const showAdditionalTime =
		false !== detailsGroupSettings.showAdditionalTime;
	const nutritionHeading = applyFilters(
		'tasty_recipes_recipe_editor_group_heading',
		__( 'Nutrition', 'tasty-recipes-lite' ),
		'nutrition',
		recipe,
		recipeType
	);
	const showNutritionGroup = applyFilters(
		'tasty_recipes_recipe_editor_show_group',
		false !== nutritionGroupSettings.show,
		'nutrition',
		recipe,
		recipeType
	);
	const typeSelector = applyFilters(
		'tasty_recipes_recipe_editor_type_selector',
		null,
		{
			recipe,
			recipeType,
			handleRecipeChange,
			editorConfig,
		}
	);

	useEffect( () => {
		const event = new CustomEvent( 'tasty-recipes-editor-labels-update', {
			detail: {
				recipeType,
				modalTitles,
				buttonLabels,
			},
		} );
		document.dispatchEvent( event );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [
		buttonLabels.create,
		buttonLabels.edit,
		modalTitles.create,
		modalTitles.edit,
		recipeType,
	] );

	return (
		<form className="tasty-framework">
			<input type="hidden" name="id" value={ recipe.id } />
			<input type="hidden" name="recipe_type" value={ recipeType } />
			{ typeSelector }
			<fieldset className="tasty-recipes-title-field">
				<label htmlFor="title">
					{ __( 'Recipe Title', 'tasty-recipes-lite' ) }
				</label>
				<input
					type="text"
					name="title"
					id="title"
					className="regular-text"
					placeholder={ __(
						'e.g. Lemon Ricotta Pancakes',
						'tasty-recipes-lite'
					) }
					value={ recipe.title || '' }
					onChange={ ( e ) =>
						handleRecipeChange( 'title', e.target.value )
					}
				/>
			</fieldset>
			{ descriptionField && (
				<RichTextarea
					{ ...descriptionField }
					onChange={ ( value ) =>
						handleRecipeChange( descriptionField.name, value )
					}
				/>
			) }
			<ImageField
				recipe={ recipe }
				handleRecipeChange={ handleRecipeChange }
				labels={ imageLabels }
			/>
			{ remainingRichTextareas.map( ( field ) => (
				<RichTextarea
					key={ field.name }
					{ ...field }
					onChange={ ( value ) =>
						handleRecipeChange( field.name, value )
					}
				/>
			) ) }
			<section className="tasty-recipes-details-section">
				{ showDetailsGroup && isRecipeType && (
					<>
						<FieldGroup
							className="tasty-recipes-details"
							heading={ detailsHeading }
							fields={ detailsFieldsBefore }
							initialFieldValues={ recipe }
							onFieldChange={ handleRecipeChange }
							showAdditionalTime={ showAdditionalTime }
							group="details"
							recipe={ recipe }
						/>
						<ImprovedKeysNotice
							isDismissed={ isNoticeDismissed }
							onDismiss={ handleNoticeDismiss }
						/>
						{ detailsFieldsAfter && (
							<FieldGroup
								className="tasty-recipes-details"
								fields={ detailsFieldsAfter }
								initialFieldValues={ recipe }
								onFieldChange={ handleRecipeChange }
								group="details"
								recipe={ recipe }
							/>
						) }
					</>
				) }
				{ showDetailsGroup && ! isRecipeType && (
					<FieldGroup
						className="tasty-recipes-details"
						heading={ detailsHeading }
						fields={ detailsFields }
						initialFieldValues={ recipe }
						onFieldChange={ handleRecipeChange }
						showAdditionalTime={ showAdditionalTime }
						group="details"
						recipe={ recipe }
					/>
				) }
				<FieldGroup
					className="tasty-recipes-video-url"
					fields={ {
						video_url: {
							label: __( 'Video URL', 'tasty-recipes-lite' ),
							placeholder: 'youtube.com/watch?v=...',
						},
					} }
					initialFieldValues={ recipe }
					onFieldChange={ handleRecipeChange }
					group="video"
					recipe={ recipe }
				/>
				{ editorConfig.after_video_url_markup && (
					<div
						className="tasty-recipes-after-video-url"
						dangerouslySetInnerHTML={ {
							__html: editorConfig.after_video_url_markup,
						} }
					/>
				) }
				<fieldset className="tasty-recipes-author-field">
					<label htmlFor="author_name">
						{ __( 'Author', 'tasty-recipes-lite' ) }
					</label>
					<input
						type="text"
						name="author_name"
						id="author_name"
						className="regular-text"
						value={ recipe.author_name || '' }
						onChange={ ( e ) =>
							handleRecipeChange( 'author_name', e.target.value )
						}
					/>
				</fieldset>
			</section>
			{ showNutritionGroup && (
				<FieldGroup
					className="tasty-recipes-nutrition"
					group="nutrition"
					heading={ nutritionHeading }
					fields={ nutritionAttributes }
					initialFieldValues={ recipe }
					onFieldChange={ handleRecipeChange }
					renderBeforeFields={ () => (
						<fieldset className="tasty-recipes-nutrifox-container">
							<div className="tasty-recipes-nutrifox-id-container">
								<label htmlFor="tasty-recipes-recipe-nutrifox_id">
									{ attributes.nutrition.nutrifox_id.label }
								</label>
								<input
									id="tasty-recipes-recipe-nutrifox_id"
									type="text"
									name="nutrifox_id"
									className="inline-field"
									value={ recipe.nutrifox_id || '' }
									onChange={ ( e ) => {
										handleRecipeChange(
											'nutrifox_id',
											e.target.value
										);
									} }
								/>

								{ recipe.nutrifox_id && (
									<a
										className="nutrifox-edit"
										href={ `https://nutrifox.com/recipes/${ parseInt(
											recipe.nutrifox_id
										) }/edit` }
										target="_blank"
										rel="noopener noreferrer"
									>
										<NutrifoxIcon />
										{ __(
											'Edit recipe in Nutrifox',
											'tasty-recipes-lite'
										) }
									</a>
								) }
							</div>
							<div className="tasty-recipes-nutrifox-logo-container">
								<a
									className="nutrifox-logo"
									href="https://nutrifox.com/?utm_source=tasty-recipes&utm_medium=dashboard&utm_campaign=recipe-editor"
									target="_blank"
									rel="noopener noreferrer"
								>
									<img
										alt="Nutrifox logo"
										src={ nutrifoxLogoUrl }
										width="96"
										height="22"
									/>
								</a>
							</div>
						</fieldset>
					) }
					recipe={ recipe }
				/>
			) }
		</form>
	);
};

const NutrifoxIcon = () => (
	<svg
		aria-hidden="true"
		focusable="false"
		style={ {
			marginRight: '4px',
		} }
		data-prefix="fas"
		data-icon="external-link-alt"
		className="svg-inline--fa fa-external-link-alt fa-w-16"
		role="img"
		xmlns="http://www.w3.org/2000/svg"
		viewBox="0 0 512 512"
	>
		<path
			fill="currentColor"
			d="M432,320H400a16,16,0,0,0-16,16V448H64V128H208a16,16,0,0,0,16-16V80a16,16,0,0,0-16-16H48A48,48,0,0,0,0,112V464a48,48,0,0,0,48,48H400a48,48,0,0,0,48-48V336A16,16,0,0,0,432,320ZM488,0h-128c-21.37,0-32.05,25.91-17,41l35.73,35.73L135,320.37a24,24,0,0,0,0,34L157.67,377a24,24,0,0,0,34,0L435.28,133.32,471,169c15,15,41,4.5,41-17V24A24,24,0,0,0,488,0Z"
		></path>
	</svg>
);
