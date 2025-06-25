import { useState, useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { FieldGroup } from './FieldGroup';
import { RichTextarea } from './RichTextarea';
import { ImageField } from './ImageField';

export const RecipeForm = ( { initialRecipe, config } ) => {
	const [ recipe, setRecipe ] = useState( initialRecipe );

	const attributes = config.attributes;
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

	return (
		<form className="tasty-framework">
			<input type="hidden" name="id" value={ recipe.id } />
			<fieldset className="tasty-recipes-modal-form-row">
				<div>
					<label htmlFor="title">
						{ __( 'Title', 'tasty-recipes-lite' ) }
					</label>
					<input
						type="text"
						name="title"
						id="title"
						className="regular-text"
						value={ recipe.title || '' }
						onChange={ ( e ) =>
							handleRecipeChange( 'title', e.target.value )
						}
					/>
				</div>
				<div>
					<label htmlFor="author_name">
						{ __( 'Author Name', 'tasty-recipes-lite' ) }
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
				</div>
			</fieldset>
			<ImageField
				recipe={ recipe }
				handleRecipeChange={ handleRecipeChange }
			/>
			<RichTextarea
				name="description"
				label={ __( 'Description', 'tasty-recipes-lite' ) }
				initialValue={ recipe.description }
				initialVideoSettingsValue={ recipe.description_video_settings }
				onChange={ ( value ) =>
					handleRecipeChange( 'description', value )
				}
			/>
			<RichTextarea
				name="ingredients"
				label={ __( 'Ingredients', 'tasty-recipes-lite' ) }
				initialValue={ recipe.ingredients }
				initialVideoSettingsValue={ recipe.ingredients_video_settings }
				onChange={ ( value ) =>
					handleRecipeChange( 'ingredients', value )
				}
			/>
			<RichTextarea
				name="instructions"
				label={ __( 'Instructions', 'tasty-recipes-lite' ) }
				initialValue={ recipe.instructions }
				initialVideoSettingsValue={ recipe.instructions_video_settings }
				onChange={ ( value ) =>
					handleRecipeChange( 'instructions', value )
				}
			/>
			<RichTextarea
				name="notes"
				label={ __( 'Notes', 'tasty-recipes-lite' ) }
				initialValue={ recipe.notes }
				initialVideoSettingsValue={ recipe.notes_video_settings }
				onChange={ ( value ) => handleRecipeChange( 'notes', value ) }
			/>
			<FieldGroup
				className="tasty-recipes-details"
				heading={ __( 'Details', 'tasty-recipes-lite' ) }
				fields={ attributes.cooking }
				initialFieldValues={ recipe }
				onFieldChange={ handleRecipeChange }
			/>
			<FieldGroup
				className="tasty-recipes-video-url"
				heading={ __( 'Video URL', 'tasty-recipes-lite' ) }
				fields={ {
					video_url: {
						label: __( 'Video URL', 'tasty-recipes-lite' ),
					},
				} }
				showLabels={ false }
				initialFieldValues={ recipe }
				onFieldChange={ handleRecipeChange }
			/>
			{ config.after_video_url_markup && (
				<div
					dangerouslySetInnerHTML={ {
						__html: config.after_video_url_markup,
					} }
				/>
			) }
			<FieldGroup
				className="tasty-recipes-nutrition"
				group="nutrition"
				heading={ __( 'Nutrition', 'tasty-recipes-lite' ) }
				fields={ nutritionAttributes }
				initialFieldValues={ recipe }
				onFieldChange={ handleRecipeChange }
				renderBeforeFields={ () => (
					<fieldset className="tasty-recipes-nutrifox-container">
						<span>
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
						</span>
						{ recipe.nutrifox_id && (
							<a
								className="nutrifox-edit"
								href={ `https://nutrifox.com/recipes/${ parseInt(
									recipe.nutrifox_id
								) }/edit` }
								target="_blank"
								rel="noopener noreferrer"
							>
								{ __(
									'Edit recipe in Nutrifox',
									'tasty-recipes-lite'
								) }
								<svg
									aria-hidden="true"
									focusable="false"
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
							</a>
						) }
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
					</fieldset>
				) }
			/>
		</form>
	);
};
