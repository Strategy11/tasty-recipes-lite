<template>
	<form>
		<input type="hidden" name="id" :value="recipe.id" />

		<div :class="{ 'tasty-recipes-image': true, 'tasty-recipes-image-with-placeholder': ! recipeThumbnail }">
			<input type="hidden" name="image_id" v-model="recipe.image_id" />
			<div class="tasty-recipes-image-preview" @click="handleClickSelectImage">
				<img data-pin-nopin="true" v-if="recipeThumbnail" :src="recipeThumbnail.url" />
				<div v-else class="tasty-recipes-image-preview-placeholder"></div>
			</div>
			<span class="tasty-recipes-select-image button" @click="handleClickSelectImage">{{ labels.select_image }}</span>
			<span class="tasty-recipes-remove-image button" @click="handleClickRemoveImage"><span class="dashicons dashicons-no-alt"></span></span>
		</div>

		<fieldset>
			<label for="title">{{ attributes.general.title.label }}</label>
			<input type="text" name="title" class="regular-text" v-model="recipe.title" />
		</fieldset>

		<fieldset>
			<label for="author_name">{{ attributes.general.author_name.label }}</label>
			<input type="text" name="author_name" class="regular-text" v-model="recipe.author_name" />
		</fieldset>

		<rich-textarea
			name="description"
			:label="attributes.general.description.label"
			:initial-value="recipe.description"
			:video-settings-config="videoSettingsConfig"
			:initial-video-settings-value="recipe.description_video_settings"></rich-textarea>

		<rich-textarea
			name="ingredients"
			:label="attributes.general.ingredients.label"
			:initial-value="recipe.ingredients"
			:video-settings-config="videoSettingsConfig"
			:initial-video-settings-value="recipe.ingredients_video_settings"></rich-textarea>

		<rich-textarea
			name="instructions"
			:label="attributes.general.instructions.label"
			:initial-value="recipe.instructions"
			:video-settings-config="videoSettingsConfig"
			:initial-video-settings-value="recipe.instructions_video_settings"></rich-textarea>

		<rich-textarea
			name="notes"
			:label="attributes.general.notes.label"
			:initial-value="recipe.notes"
			:video-settings-config="videoSettingsConfig"
			:initial-video-settings-value="recipe.notes_video_settings"></rich-textarea>

		<field-group
			class="tasty-recipes-details"
			:heading="labels.details_heading"
			:fields="attributes.cooking"
			:extra-labels="labels.additional_time"
			:initial-field-values="recipe"></field-group>

		<field-group
			class="tasty-recipes-video-url"
			:heading="labels.video_url_heading"
			:fields="{'video_url': attributes.general.video_url}"
			:show-labels="false"
			:initial-field-values="recipe"></field-group>

		<div v-if="config.after_video_url_markup" v-html="config.after_video_url_markup"></div>

		<field-group
			class="tasty-recipes-nutrition"
			group="nutrition"
			:heading="labels.nutrition_heading"
			:fields="nutritionAttributes"
			:initial-field-values="recipe">
			<template v-slot:before-fields="{ fieldValues }">
				<fieldset class="tasty-recipes-nutrifox-container">
					<span>
						<label for="tasty-recipes-recipe-nutrifox_id">{{ attributes.nutrition.nutrifox_id.label }}</label>
						<input id="tasty-recipes-recipe-nutrifox_id" type="text" name="nutrifox_id" class="inline-field" v-model="fieldValues['nutrifox_id']" />
					</span>
					<a class="nutrifox-edit" v-show="fieldValues['nutrifox_id']" :href="'https://nutrifox.com/recipes/' + parseInt(fieldValues['nutrifox_id']) + '/edit'" target="_blank">Edit recipe in Nutrifox<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="external-link-alt" class="svg-inline--fa fa-external-link-alt fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M432,320H400a16,16,0,0,0-16,16V448H64V128H208a16,16,0,0,0,16-16V80a16,16,0,0,0-16-16H48A48,48,0,0,0,0,112V464a48,48,0,0,0,48,48H400a48,48,0,0,0,48-48V336A16,16,0,0,0,432,320ZM488,0h-128c-21.37,0-32.05,25.91-17,41l35.73,35.73L135,320.37a24,24,0,0,0,0,34L157.67,377a24,24,0,0,0,34,0L435.28,133.32,471,169c15,15,41,4.5,41-17V24A24,24,0,0,0,488,0Z"></path></svg></a>
					<a class="nutrifox-logo" href="https://nutrifox.com/?utm_source=tasty-recipes&utm_medium=dashboard&utm_campaign=recipe-editor" target="_blank" rel="noopener"><img alt="Nutrifox logo" :src="nutrifoxLogoUrl" width="96" height="22"></a>
				</fieldset>
			</template>
		</field-group>
	</form>
</template>
<script>
export default {
	components: {
		FieldGroup: require( './FieldGroup' ).default,
		RichTextarea: require( './RichTextarea' ).default,
	},
	props: {
		initialRecipe: {
			type: Object,
		},
		config: {
			type: Object,
			required: true,
		},
	},
	data() {
		return {
			recipe: {
				...this.initialRecipe,
			},
			recipeThumbnail: this.initialRecipe.image_sizes ? this.initialRecipe.image_sizes.thumbnail : null,
			labels: {
				...this.config.labels,
			},
			attributes: {
				...this.config.attributes,
			},
			videoSettingsConfig: {
				...this.config.video_settings,
			},
		};
	},
	computed: {
		nutritionAttributes() {
			const attributes = this.attributes.nutrition;
			const denied = ['nutrifox_id'];
			return Object.keys(attributes)
				.filter(key => !denied.includes(key))
				.reduce((obj, key) => {
					obj[key] = attributes[key];
					return obj;
			}, {});
		},
		nutrifoxLogoUrl() {
			return window.tastyRecipesEditorModalData.pluginURL + '/assets/images/nutrifox-logo.png';
		}
	},
	methods: {
		handleClickSelectImage() {
			const frame = wp.media( {
				title: window.tastyRecipesEditorModalData.i18n.frameTitle,
				multiple: false,
				library: {
					type: 'image',
				},
				button: {
					text: window.tastyRecipesEditorModalData.i18n.frameButton,
				},
			} );

			// Open the media manager
			frame.on( 'open', () => {
				const selection = frame.state().get( 'selection' );
				const selected = this.recipe.image_id;
				if ( selected ) {
					const attachment = wp.media.model.Attachment.get( selected );
					attachment.fetch();
					selection.add( attachment );
				}
			} );

			// Handle results from media manager.
			frame.on( 'select', () => {
				const attachments = frame.state().get( 'selection' ).toJSON();
				if ( attachments.length ) {
					this.recipe.image_id = attachments[ 0 ].id;
					this.recipeThumbnail = attachments[ 0 ].sizes.thumbnail;
				}
			} );

			frame.open();
		},
		handleClickRemoveImage() {
			this.recipe.image_id = 0;
			this.recipeThumbnail = null;
		},
	},
};
</script>
