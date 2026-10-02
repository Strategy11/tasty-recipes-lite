import { useState, useMemo, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { FieldGroup } from './FieldGroup';
import { DescriptionFormatTip } from './DescriptionFormatTip';
import {
	hasUnsupportedRowContent,
	IngredientsList,
	InstructionsList,
} from './recipe-row-list';
import { RichTextarea } from './RichTextarea';
import { ImageField } from './ImageField';
import {
	getNutrifoxConfig,
	NutrifoxPanel,
	useLabelActions,
	useNutrifoxConnection,
} from './nutrifox';

const DefaultNutritionSource = ( { renderGroup, renderNutrifoxPanel } ) =>
	renderGroup( { beforeFields: renderNutrifoxPanel(), showFields: true } );

const FormatTipField = ( {
	field,
	isFormatTipDismissed,
	isRevealed,
	modifyNonce,
	onDismiss,
	onFocus,
	onValueChange,
} ) => (
	<div className="tasty-recipes-format-tip-field">
		<RichTextarea
			{ ...field }
			bubbleToolbar
			onChange={ value => onValueChange( field.name, value ) }
			onFocus={ onFocus }
		/>
		<DescriptionFormatTip
			isRevealed={ isRevealed }
			initialDismissed={ isFormatTipDismissed }
			onDismiss={ onDismiss }
			modifyNonce={ modifyNonce }
		/>
	</div>
);

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
	const [ formatTipAnchor, setFormatTipAnchor ] = useState( null );
	const [ isFormatTipDismissed, setIsFormatTipDismissed ] = useState(
		!! config.descriptionFormatTipDismissed
	);
	const classicEditorFieldsRef = useRef( {} );

	const shouldUseClassicEditor = ( field ) => {
		const decisions = classicEditorFieldsRef.current;
		if ( ! ( field.name in decisions ) ) {
			decisions[ field.name ] = hasUnsupportedRowContent(
				field.initialValue
			);
		}

		return decisions[ field.name ];
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
	const nutrifoxConfig = getNutrifoxConfig();
	const [ hasOpenedNutrition, setHasOpenedNutrition ] = useState( false );
	const nutrifox = useNutrifoxConnection( {
		checkStatus: hasOpenedNutrition,
	} );
	const NutritionSource = useMemo(
		() =>
			applyFilters(
				'tasty_recipes_recipe_editor_nutrition_source',
				DefaultNutritionSource
			),
		[]
	);

	const handleRecipeChange = ( field, value ) =>
		setRecipe( ( prev ) => ( { ...prev, [ field ]: value } ) );
	const nutrifoxLabel = useLabelActions( {
		restBase: nutrifoxConfig.restBase,
		recipe,
		isConnected: nutrifox.isConnected,
		isVisible: hasOpenedNutrition,
		onChange: handleRecipeChange,
	} );

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
	const titleLabel =
		typeSettings.title?.label ||
		__( 'Recipe Title', 'tasty-recipes-lite' );
	const titlePlaceholder =
		typeSettings.title?.placeholder ||
		__( 'e.g. Lemon Ricotta Pancakes', 'tasty-recipes-lite' );
	const richTextareas = [
		{
			name: 'description',
			label: __( 'Description', 'tasty-recipes-lite' ),
			placeholder: __(
				'Tell us about this recipe…',
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
			initialValue: recipe.instructions,
			initialVideoSettingsValue: recipe.instructions_video_settings,
		},
		{
			name: 'notes',
			label: __( 'Notes', 'tasty-recipes-lite' ),
			placeholder: __(
				'Add any tips, substitutions, or storage notes…',
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
					{ titleLabel }
				</label>
				<input
					type="text"
					name="title"
					id="title"
					className="regular-text"
					placeholder={ titlePlaceholder }
					value={ recipe.title || '' }
					onChange={ ( e ) =>
						handleRecipeChange( 'title', e.target.value )
					}
				/>
			</fieldset>
			{ descriptionField && (
				<FormatTipField
					field={ descriptionField }
					isFormatTipDismissed={ isFormatTipDismissed }
					isRevealed={ 'description' === formatTipAnchor }
					modifyNonce={ config.modifyNonce }
					onDismiss={ () => setIsFormatTipDismissed( true ) }
					onFocus={ () => setFormatTipAnchor( 'description' ) }
					onValueChange={ handleRecipeChange }
				/>
			) }
			<ImageField
				recipe={ recipe }
				handleRecipeChange={ handleRecipeChange }
				labels={ imageLabels }
			/>
			{ remainingRichTextareas.map( ( field ) => {
				const isNotes = 'notes' === field.name;
				const isIngredients = 'ingredients' === field.name;
				const isInstructions = 'instructions' === field.name;
				const useClassicEditor =
					( isIngredients || isInstructions ) &&
					shouldUseClassicEditor( field );

				// Legacy media/embeds cannot round-trip through the row list —
				// keep the classic TinyMCE field (with video settings) intact.
				if ( useClassicEditor ) {
					return (
						<RichTextarea
							key={ field.name }
							{ ...field }
							onChange={ ( value ) =>
								handleRecipeChange( field.name, value )
							}
						/>
					);
				}

				if ( isIngredients ) {
					return (
						<IngredientsList
							key={ field.name }
							name={ field.name }
							label={ field.label }
							placeholder={ field.placeholder }
							initialValue={ field.initialValue }
							addItemLabel={ field.addItemLabel }
							onChange={ ( value ) =>
								handleRecipeChange( field.name, value )
							}
						/>
					);
				}

				if ( isInstructions ) {
					return (
						<InstructionsList
							key={ field.name }
							name={ field.name }
							label={ field.label }
							placeholder={ field.placeholder }
							initialValue={ field.initialValue }
							onChange={ ( value ) =>
								handleRecipeChange( field.name, value )
							}
						/>
					);
				}

				if ( isNotes ) {
					return (
						<FormatTipField
							key={ field.name }
							field={ field }
							isFormatTipDismissed={ isFormatTipDismissed }
							isRevealed={ 'notes' === formatTipAnchor }
							modifyNonce={ config.modifyNonce }
							onDismiss={ () => setIsFormatTipDismissed( true ) }
							onFocus={ () => setFormatTipAnchor( 'notes' ) }
							onValueChange={ handleRecipeChange }
						/>
					);
				}

				return (
					<RichTextarea
						key={ field.name }
						{ ...field }
						bubbleToolbar
						onChange={ ( value ) =>
							handleRecipeChange( field.name, value )
						}
					/>
				);
			} ) }
			<section className="tasty-recipes-details-section">
				{ showDetailsGroup && (
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
							type: 'url',
							label: __( 'Video URL', 'tasty-recipes-lite' ),
							placeholder: __(
								'youtube.com/watch?v=…',
								'tasty-recipes-lite'
							),
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
				<NutritionSource
					initialRecipe={ initialRecipeState }
					fields={ nutritionAttributes }
					isConnected={ nutrifox.isConnected }
					renderNutrifoxPanel={ () => (
						<NutrifoxPanel
							connection={ nutrifox.connection }
							canConnect={ !! nutrifoxConfig.canConnect }
							isConnecting={ nutrifox.isConnecting }
							notice={ nutrifox.notice }
							onConnect={ nutrifox.start }
							trialUrl={ nutrifoxConfig.trialUrl }
							nutrifoxUrl={ nutrifoxConfig.nutrifoxUrl }
							logoUrl={ nutrifoxLogoUrl }
							label={ nutrifoxLabel }
						/>
					) }
					renderGroup={ ( { beforeFields, showFields } ) => (
						<FieldGroup
							className="tasty-recipes-nutrition"
							group="nutrition"
							heading={ nutritionHeading }
							fields={ nutritionAttributes }
							initialFieldValues={ recipe }
							onFieldChange={ handleRecipeChange }
							defaultCollapsed
							onCollapseChange={ collapsed => {
								if ( ! collapsed ) {
									setHasOpenedNutrition( true );
								}
							} }
							showFields={ showFields }
							renderBeforeFields={ () => (
								<div className="tasty-recipes-nutrition-source-container">
									{ beforeFields }
								</div>
							) }
							recipe={ recipe }
						/>
					) }
				/>
			) }
		</form>
	);
};
