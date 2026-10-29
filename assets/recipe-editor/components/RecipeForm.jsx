import {
	Fragment,
	useState,
	useMemo,
	useEffect,
	useRef,
} from '@wordpress/element';
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
	getDefaultSections,
	PasteFilledNotice,
	PasteRecipePanel,
} from './recipe-parser';
import { trackUsage } from '../track';

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

export const RecipeForm = ( {
	initialRecipe,
	config,
	initialView = 'form',
	entryPoint = 'unknown',
} ) => {
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
	const [ view, setView ] = useState(
		'paste' === initialView ? 'paste' : 'form'
	);
	// Rich text, row list and detail fields read their value once on mount,
	// so filling them from a paste remounts them with the new values.
	const [ formRevision, setFormRevision ] = useState( 0 );
	const [ pasteResult, setPasteResult ] = useState( null );

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

	const visibleFieldNames = richTextareaSettings.map( ( field ) => field.name );
	const fieldLabels = richTextareaSettings.reduce( ( labels, field ) => {
		labels[ field.name ] = field.label;
		return labels;
	}, {} );

	const pasteSections = applyFilters(
		'tasty_recipes_recipe_parser_sections',
		getDefaultSections()
			.filter( ( section ) => visibleFieldNames.includes( section.name ) )
			.map( ( section ) => ( {
				...section,
				label: fieldLabels[ section.name ] || section.label,
			} ) ),
		recipeType,
		recipe
	);
	// Locked fields aren't shown in the form, so a paste never fills them.
	const pasteDetailFields = Object.fromEntries(
		Object.entries( {
			title: { label: titleLabel },
			...( showDetailsGroup ? detailsFields : {} ),
			video_url: { label: __( 'Video URL', 'tasty-recipes-lite' ) },
			author_name: { label: __( 'Author', 'tasty-recipes-lite' ) },
			...( showNutritionGroup ? nutritionAttributes : {} ),
		} ).filter( ( [ , field ] ) => ! field.is_pro )
	);
	const handlePasteFill = ( parsedValues, { stats } ) => {
		const values = applyFilters(
			'tasty_recipes_recipe_parser_result',
			parsedValues,
			recipe,
			recipeType
		);
		const filled = Object.keys( values );
		// Some fields keep their value under another key (Total Time reads
		// `total_time_raw`), so store each value where its field reads it.
		const recipeValues = Object.fromEntries(
			filled.map( ( name ) => [
				pasteDetailFields[ name ]?.object_key || name,
				values[ name ],
			] )
		);

		trackUsage( 'recipe_paste_parsed', {
			...stats,
			entry_point: entryPoint,
			fields_filled: filled.length,
		} );

		classicEditorFieldsRef.current = {};
		setPasteResult( {
			count: filled.length,
			previous: filled.length ? recipe : null,
		} );
		setRecipe( { ...recipe, ...recipeValues } );
		setFormRevision( ( revision ) => revision + 1 );
		setView( 'form' );
	};

	const handlePasteUndo = () => {
		if ( ! pasteResult?.previous ) {
			return;
		}

		trackUsage( 'recipe_paste_undone', {
			fields_filled: pasteResult.count,
		} );
		classicEditorFieldsRef.current = {};
		setRecipe( pasteResult.previous );
		setPasteResult( null );
		setFormRevision( ( revision ) => revision + 1 );
	};

	// The modal footer (Cancel / Insert / Update) belongs to the form, so it is
	// hidden while the paste panel is open.
	useEffect( () => {
		const container = document.querySelector(
			'.tasty-recipes-modal-container'
		);

		container?.classList.toggle( 'tasty-recipes-is-pasting', 'paste' === view );

		// Switching views changes the content height; start each at the top.
		container
			?.querySelector( '.tasty-recipes-frame-content' )
			?.scrollTo( 0, 0 );

		return () => {
			container?.classList.remove( 'tasty-recipes-is-pasting' );
		};
	}, [ view ] );

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
		<form
			className={ `tasty-framework${
				'paste' === view ? ' is-pasting-recipe' : ''
			}` }
		>
			<input type="hidden" name="id" value={ recipe.id } />
			<input type="hidden" name="recipe_type" value={ recipeType } />
			{ 'paste' === view && (
				<PasteRecipePanel
					sections={ pasteSections }
					detailFields={ pasteDetailFields }
					onCancel={ () => setView( 'form' ) }
					onFill={ handlePasteFill }
				/>
			) }
			{ 'form' === view && pasteResult && (
				<PasteFilledNotice
					count={ pasteResult.count }
					onUndo={ handlePasteUndo }
					onDismiss={ () => setPasteResult( null ) }
				/>
			) }
			{ typeSelector }
			<Fragment key={ `fields-${ formRevision }` }>
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
					<FieldGroup
						className="tasty-recipes-nutrition"
						group="nutrition"
						heading={ nutritionHeading }
						fields={ nutritionAttributes }
						initialFieldValues={ recipe }
						onFieldChange={ handleRecipeChange }
						defaultCollapsed
						renderBeforeFields={ () => (
							<fieldset className="tasty-recipes-nutrifox-container">
								<div className="tasty-recipes-nutrifox-card-head">
									<label htmlFor="tasty-recipes-recipe-nutrifox_id">
										{ attributes.nutrition.nutrifox_id.label }
										<span
											className="tasty-tooltip-icon"
											tabIndex={ 0 }
											role="img"
											aria-label={ __(
												'What is this?',
												'tasty-recipes-lite'
											) }
											data-tasty-tooltip={ __(
												'Pull nutrition automatically from your existing Nutrifox recipe.',
												'tasty-recipes-lite'
											) }
										/>
									</label>
									<a
										className="nutrifox-logo"
										href="https://nutrifox.com/?utm_source=tasty-recipes&utm_medium=dashboard&utm_campaign=recipe-editor"
										target="_blank"
										rel="noopener noreferrer"
										aria-label={ __(
											'Nutrifox',
											'tasty-recipes-lite'
										) }
									>
										<img
											alt=""
											src={ nutrifoxLogoUrl }
											width="96"
											height="22"
										/>
									</a>
								</div>
								<input
									id="tasty-recipes-recipe-nutrifox_id"
									type="text"
									name="nutrifox_id"
									className="inline-field"
									placeholder={ __(
										'Paste your Nutrifox recipe ID',
										'tasty-recipes-lite'
									) }
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
							</fieldset>
						) }
						recipe={ recipe }
					/>
				) }
			</Fragment>
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
