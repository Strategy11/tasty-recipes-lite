import { useState, useMemo } from '@wordpress/element';
import FieldGroup from './FieldGroup';
import RichTextarea from './RichTextarea';

const RecipeForm = ({ initialRecipe, config }) => {
	const [recipe, setRecipe] = useState({ ...initialRecipe });
	const [recipeThumbnail, setRecipeThumbnail] = useState(
		initialRecipe.image_sizes ? initialRecipe.image_sizes.thumbnail : null
	);

	const labels = config.labels;
	const attributes = config.attributes;
	const videoSettingsConfig = config.video_settings;

	const nutritionAttributes = useMemo(() => {
		const nutritionAttrs = attributes.nutrition;
		const denied = ['nutrifox_id'];
		return Object.keys(nutritionAttrs)
			.filter(key => !denied.includes(key))
			.reduce((obj, key) => {
				obj[key] = nutritionAttrs[key];
				return obj;
			}, {});
	}, [attributes.nutrition]);

	const nutrifoxLogoUrl = window.tastyRecipesEditorModalData.pluginURL + '/assets/images/nutrifox-logo.png';

	const handleRecipeChange = (field, value) => {
		setRecipe(prev => ({ ...prev, [field]: value }));
	};

	const handleClickSelectImage = () => {
		const frame = wp.media({
			title: window.tastyRecipesEditorModalData.i18n.frameTitle,
			multiple: false,
			library: {
				type: 'image',
			},
			button: {
				text: window.tastyRecipesEditorModalData.i18n.frameButton,
			},
		});

		frame.on('open', () => {
			const selection = frame.state().get('selection');
			const selected = recipe.image_id;
			if (selected) {
				const attachment = wp.media.model.Attachment.get(selected);
				attachment.fetch();
				selection.add(attachment);
			}
		});

		frame.on('select', () => {
			const attachments = frame.state().get('selection').toJSON();
			if (attachments.length) {
				handleRecipeChange('image_id', attachments[0].id);
				setRecipeThumbnail(attachments[0].sizes.thumbnail);
			}
		});

		frame.open();
	};

	const handleClickRemoveImage = () => {
		handleRecipeChange('image_id', 0);
		setRecipeThumbnail(null);
	};

	return (
		<form className="tasty-framework">
			<input type="hidden" name="id" value={recipe.id} />

			<div className={`tasty-recipes-image ${!recipeThumbnail ? 'tasty-recipes-image-with-placeholder' : ''}`}>
				<input type="hidden" name="image_id" value={recipe.image_id} />
				<div className="tasty-recipes-image-preview" onClick={handleClickSelectImage}>
					{recipeThumbnail ? (
						<img data-pin-nopin="true" src={recipeThumbnail.url} alt="" />
					) : (
						<div className="tasty-recipes-image-preview-placeholder"></div>
					)}
				</div>
				<span className="tasty-recipes-select-image button" onClick={handleClickSelectImage}>
					{labels.select_image}
				</span>
				<span className="tasty-recipes-remove-image button" onClick={handleClickRemoveImage}>
					<span className="dashicons dashicons-no-alt"></span>
				</span>
			</div>

			<fieldset>
				<label htmlFor="title">{attributes.general.title.label}</label>
				<input 
					type="text" 
					name="title" 
					className="regular-text" 
					value={recipe.title || ''} 
					onChange={(e) => handleRecipeChange('title', e.target.value)} 
				/>
			</fieldset>

			<fieldset>
				<label htmlFor="author_name">{attributes.general.author_name.label}</label>
				<input 
					type="text" 
					name="author_name" 
					className="regular-text" 
					value={recipe.author_name || ''} 
					onChange={(e) => handleRecipeChange('author_name', e.target.value)} 
				/>
			</fieldset>

			<RichTextarea
				name="description"
				label={attributes.general.description.label}
				initialValue={recipe.description}
				videoSettingsConfig={videoSettingsConfig}
				initialVideoSettingsValue={recipe.description_video_settings}
				onChange={(value) => handleRecipeChange('description', value)}
			/>

			<RichTextarea
				name="ingredients"
				label={attributes.general.ingredients.label}
				initialValue={recipe.ingredients}
				videoSettingsConfig={videoSettingsConfig}
				initialVideoSettingsValue={recipe.ingredients_video_settings}
				onChange={(value) => handleRecipeChange('ingredients', value)}
			/>

			<RichTextarea
				name="instructions"
				label={attributes.general.instructions.label}
				initialValue={recipe.instructions}
				videoSettingsConfig={videoSettingsConfig}
				initialVideoSettingsValue={recipe.instructions_video_settings}
				onChange={(value) => handleRecipeChange('instructions', value)}
			/>

			<RichTextarea
				name="notes"
				label={attributes.general.notes.label}
				initialValue={recipe.notes}
				videoSettingsConfig={videoSettingsConfig}
				initialVideoSettingsValue={recipe.notes_video_settings}
				onChange={(value) => handleRecipeChange('notes', value)}
			/>

			<FieldGroup
				className="tasty-recipes-details"
				heading={labels.details_heading}
				fields={attributes.cooking}
				extraLabels={labels.additional_time}
				initialFieldValues={recipe}
				onFieldChange={handleRecipeChange}
			/>

			<FieldGroup
				className="tasty-recipes-video-url"
				heading={labels.video_url_heading}
				fields={{ 'video_url': attributes.general.video_url }}
				showLabels={false}
				initialFieldValues={recipe}
				onFieldChange={handleRecipeChange}
			/>

			{config.after_video_url_markup && (
				<div dangerouslySetInnerHTML={{ __html: config.after_video_url_markup }} />
			)}

			<FieldGroup
				className="tasty-recipes-nutrition"
				group="nutrition"
				heading={labels.nutrition_heading}
				fields={nutritionAttributes}
				initialFieldValues={recipe}
				onFieldChange={handleRecipeChange}
				renderBeforeFields={({ fieldValues }) => (
					<fieldset className="tasty-recipes-nutrifox-container">
						<span>
							<label htmlFor="tasty-recipes-recipe-nutrifox_id">
								{attributes.nutrition.nutrifox_id.label}
							</label>
							<input 
								id="tasty-recipes-recipe-nutrifox_id" 
								type="text" 
								name="nutrifox_id" 
								className="inline-field" 
								value={fieldValues['nutrifox_id'] || ''} 
								onChange={(e) => handleRecipeChange('nutrifox_id', e.target.value)}
							/>
						</span>
						{fieldValues['nutrifox_id'] && (
							<a 
								className="nutrifox-edit" 
								href={`https://nutrifox.com/recipes/${parseInt(fieldValues['nutrifox_id'])}/edit`} 
								target="_blank" 
								rel="noopener noreferrer"
							>
								Edit recipe in Nutrifox
								<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="external-link-alt" className="svg-inline--fa fa-external-link-alt fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
									<path fill="currentColor" d="M432,320H400a16,16,0,0,0-16,16V448H64V128H208a16,16,0,0,0,16-16V80a16,16,0,0,0-16-16H48A48,48,0,0,0,0,112V464a48,48,0,0,0,48,48H400a48,48,0,0,0,48-48V336A16,16,0,0,0,432,320ZM488,0h-128c-21.37,0-32.05,25.91-17,41l35.73,35.73L135,320.37a24,24,0,0,0,0,34L157.67,377a24,24,0,0,0,34,0L435.28,133.32,471,169c15,15,41,4.5,41-17V24A24,24,0,0,0,488,0Z"></path>
								</svg>
							</a>
						)}
						<a 
							className="nutrifox-logo" 
							href="https://nutrifox.com/?utm_source=tasty-recipes&utm_medium=dashboard&utm_campaign=recipe-editor" 
							target="_blank" 
							rel="noopener noreferrer"
						>
							<img alt="Nutrifox logo" src={nutrifoxLogoUrl} width="96" height="22" />
						</a>
					</fieldset>
				)}
			/>
		</form>
	);
};

export default RecipeForm;