<template>
	<div class="tasty-recipes-design-settings tasty-settings tasty-flex tasty-items-start">
		<div
			:class="{
				'tasty-recipes-card-preview-container': true,
				'tasty-recipes-card-locked': isLocked
			}"
		>
			<div class="tasty-recipes-card-chooser">
				<button type="button" @click.prevent="goToPreviousTemplate">
					<svg
						aria-hidden="true"
						focusable="false"
						data-prefix="fas"
						data-icon="chevron-left"
						class="svg-inline--fa fa-chevron-left fa-w-10"
						role="img"
						xmlns="http://www.w3.org/2000/svg"
						viewBox="0 0 320 512"
					>
						<path
							fill="currentColor"
							d="M34.52 239.03L228.87 44.69c9.37-9.37 24.57-9.37 33.94 0l22.67 22.67c9.36 9.36 9.37 24.52.04 33.9L131.49 256l154.02 154.75c9.34 9.38 9.32 24.54-.04 33.9l-22.67 22.67c-9.37 9.37-24.57 9.37-33.94 0L34.52 272.97c-9.37-9.37-9.37-24.57 0-33.94z"
						></path>
					</svg>
				</button>
				<h3 v-html="currentTemplateLabel"></h3>
				<button type="button" @click.prevent="goToNextTemplate">
					<svg
						aria-hidden="true"
						focusable="false"
						data-prefix="fas"
						data-icon="chevron-right"
						class="svg-inline--fa fa-chevron-right fa-w-10"
						role="img"
						xmlns="http://www.w3.org/2000/svg"
						viewBox="0 0 320 512"
					>
						<path
							fill="currentColor"
							d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"
						></path>
					</svg>
				</button>
				<input
					type="hidden"
					name="tasty_recipes_template"
					v-model="currentTemplate"
				/>
			</div>
			<iframe
				:class="{
					'tasty-shadow-lg': ! isLocked
				}"
				width="600"
				:height="iframeHeight"
				ref="previewIframe"
				:src="recipeCardPreviewUrl"
				@load="sendCustomizationToIframe"
			></iframe>
			<div v-show="iframeHeight" class="tasty-recipes-card-chooser">
				<button type="button" @click.prevent="goToPreviousTemplate">
					<svg
						aria-hidden="true"
						focusable="false"
						data-prefix="fas"
						data-icon="chevron-left"
						class="svg-inline--fa fa-chevron-left fa-w-10"
						role="img"
						xmlns="http://www.w3.org/2000/svg"
						viewBox="0 0 320 512"
					>
						<path
							fill="currentColor"
							d="M34.52 239.03L228.87 44.69c9.37-9.37 24.57-9.37 33.94 0l22.67 22.67c9.36 9.36 9.37 24.52.04 33.9L131.49 256l154.02 154.75c9.34 9.38 9.32 24.54-.04 33.9l-22.67 22.67c-9.37 9.37-24.57 9.37-33.94 0L34.52 272.97c-9.37-9.37-9.37-24.57 0-33.94z"
						></path>
					</svg>
				</button>
				<h3 v-html="currentTemplateLabel"></h3>
				<button type="button" @click.prevent="goToNextTemplate">
					<svg
						aria-hidden="true"
						focusable="false"
						data-prefix="fas"
						data-icon="chevron-right"
						class="svg-inline--fa fa-chevron-right fa-w-10"
						role="img"
						xmlns="http://www.w3.org/2000/svg"
						viewBox="0 0 320 512"
					>
						<path
							fill="currentColor"
							d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"
						></path>
					</svg>
				</button>
				<input
					type="hidden"
					name="tasty_recipes_template"
					v-model="currentTemplate"
				/>
			</div>
		</div>
		<div class="tasty-recipes-card-customization-container">
			<div
				class="tasty-recipes-card-customization-sticky"
				:style="{ top: currentStickyTop + 'px' }"
			>
				<h3>Template Colors</h3>
				<div class="tasty-recipes-card-field-container tasty-flex">
					<fieldset
						v-show="canCustomizeColor('primary')"
						class="tasty-recipes-primary-color-fieldset"
					>
						<label>Primary color</label>
						<color-picker
							v-model="customization.primary_color"
							name="tasty_recipes_customization[primary_color]"
							:disabled="!canCustomizeColor('primary')"
						></color-picker>
					</fieldset>
					<fieldset
						v-show="canCustomizeColor('secondary')"
						class="tasty-recipes-secondary-color-fieldset"
					>
						<label>Secondary color</label>
						<color-picker
							v-model="customization.secondary_color"
							name="tasty_recipes_customization[secondary_color]"
							:disabled="!canCustomizeColor('secondary')"
						></color-picker>
					</fieldset>
					<fieldset
						v-show="canCustomizeColor('icon')"
						class="tasty-recipes-icon-color-fieldset"
					>
						<label>Icon color</label>
						<color-picker
							v-model="customization.icon_color"
							name="tasty_recipes_customization[icon_color]"
							:disabled="!canCustomizeColor('icon')"
						></color-picker>
					</fieldset>
					<fieldset
						v-show="canCustomizeColor('button')"
						class="tasty-recipes-button-color-fieldset"
					>
						<label>Button color</label>
						<color-picker
							v-model="customization.button_color"
							name="tasty_recipes_customization[button_color]"
							:disabled="!canCustomizeColor('button')"
						></color-picker>
					</fieldset>
					<fieldset
						v-show="canCustomizeColor('button-text')"
						class="tasty-recipes-button-text-color-fieldset"
					>
						<label>Button text color</label>
						<color-picker
							v-model="customization.button_text_color"
							name="tasty_recipes_customization[button_text_color]"
							:disabled="!canCustomizeColor('button-text')"
						></color-picker>
					</fieldset>
					<fieldset class="tasty-recipes-detail-label-color-fieldset">
						<label>Detail label color</label>
						<color-picker
							v-model="customization.detail_label_color"
							name="tasty_recipes_customization[detail_label_color]"
						></color-picker>
					</fieldset>
					<fieldset class="tasty-recipes-detail-value-color-fieldset">
						<label>Detail value color</label>
						<color-picker
							v-model="customization.detail_value_color"
							name="tasty_recipes_customization[detail_value_color]"
						></color-picker>
					</fieldset>
				</div>
				<hr />
				<h3>Recipe Title (h2)</h3>
				<div class="tasty-recipes-card-field-container tasty-flex">
					<fieldset>
						<label>Color</label>
						<color-picker
							v-model="customization.h2_color"
							name="tasty_recipes_customization[h2_color]"
						></color-picker>
					</fieldset>
					<fieldset>
						<label>Capitalization</label>
						<select
							v-model="customization.h2_transform"
							name="tasty_recipes_customization[h2_transform]"
						>
							<option value="">Default</option>
							<option value="uppercase">Uppercase</option>
							<option value="initial">Normal</option>
							<option value="lowercase">Lowercase</option>
						</select>
					</fieldset>
				</div>
				<h3>Recipe Subtitles (h3)</h3>
				<div class="tasty-recipes-card-field-container tasty-flex">
					<fieldset>
						<label>Color</label>
						<color-picker
							v-model="customization.h3_color"
							name="tasty_recipes_customization[h3_color]"
						></color-picker>
					</fieldset>
					<fieldset>
						<label>Capitalization</label>
						<select
							v-model="customization.h3_transform"
							name="tasty_recipes_customization[h3_transform]"
						>
							<option value="">Default</option>
							<option value="uppercase">Uppercase</option>
							<option value="initial">Normal</option>
							<option value="lowercase">Lowercase</option>
						</select>
					</fieldset>
				</div>
				<h3>Body Copy</h3>
				<div class="tasty-recipes-card-field-container tasty-flex">
					<fieldset>
						<label>Color</label>
						<color-picker
							v-model="customization.body_color"
							name="tasty_recipes_customization[body_color]"
						></color-picker>
					</fieldset>
				</div>
				<hr />
				<div class="tasty-recipes-nutrifox-field-container">
					<div>
						<h3>Nutrifox Display</h3>
						<fieldset>
							<label
								><input
									type="radio"
									v-model="
										customization.nutrifox_display_style
									"
									name="tasty_recipes_customization[nutrifox_display_style]"
									value="label"
								/>
								Insert Nutrifox label (iframe)</label
							>
							<label
								><input
									type="radio"
									v-model="
										customization.nutrifox_display_style
									"
									name="tasty_recipes_customization[nutrifox_display_style]"
									value="card"
								/>
								Insert as plain text</label
							>
						</fieldset>
					</div>
					<div class="nutrifox-cta">
						<a
							class="nutrifox-logo"
							href="https://nutrifox.com/?utm_source=tasty-recipes&utm_medium=dashboard&utm_campaign=design-tab"
							target="_blank"
							rel="noopener"
							><img
								alt="Nutrifox logo"
								:src="nutrifoxLogoUrl"
								width="96"
								height="22"
						/></a>
						<a
							class="nutrifox-try"
							v-show="!isNutrifoxUser"
							:href="'https://nutrifox.com/try?utm_source=tasty-recipes&utm_medium=dashboard&utm_campaign=design-tab&utm_content=try-cta'"
							target="_blank"
							>Try Nutrifox free for 14 days<svg
								aria-hidden="true"
								focusable="false"
								data-prefix="fas"
								data-icon="external-link-alt"
								class="svg-inline--fa fa-external-link-alt fa-w-16"
								role="img"
								xmlns="http://www.w3.org/2000/svg"
								viewBox="0 0 512 512"
							>
								<path
									fill="currentColor"
									d="M432,320H400a16,16,0,0,0-16,16V448H64V128H208a16,16,0,0,0,16-16V80a16,16,0,0,0-16-16H48A48,48,0,0,0,0,112V464a48,48,0,0,0,48,48H400a48,48,0,0,0,48-48V336A16,16,0,0,0,432,320ZM488,0h-128c-21.37,0-32.05,25.91-17,41l35.73,35.73L135,320.37a24,24,0,0,0,0,34L157.67,377a24,24,0,0,0,34,0L435.28,133.32,471,169c15,15,41,4.5,41-17V24A24,24,0,0,0,488,0Z"
								></path></svg
						></a>
					</div>
				</div>
				<slot name="after-style-settings">
					<hr/>
					<h3>Social Icons <span v-html="getProBadge()"></span></h3>
					<img
						alt="Social Footer Preview"
						class="tasty-social-preview tasty-show-modal tasty-recipes-disabled"
						:src="getImage('social-footer.png')"
						width="550"
						height="100"
						data-modal_id="tasty_recipes_upgrade_modal"
						:data-replacements="getSocialModal"
					/>
				</slot>

				<p class="submit">
					<input
						type="submit"
						name="submit"
						id="submit"
						value="Save Changes"
						class="button button-primary"
					/>
				</p>
			</div>
		</div>
	</div>
</template>
<script>
export default {
	components: {
		ColorPicker: require("../components/ColorPicker.vue").default,
	},
	props: {
		initialOptions: {
			type: Object,
			required: true,
		},
		templateOptions: {
			required: true,
			type: Object,
		},
		recipeCardPreviewUrlBase: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			currentStickyTop: 0,
			currentTemplate: this.initialOptions.template,
			customization: {
				...this.initialOptions.customization,
			},
			originalStickyTop: -300,
			iframeHeight: 0,
			recipeCardPreviewUrl: this.getRecipeCardPreviewUrl(
				this.initialOptions.template,
				this.initialOptions.customization,
				this.getPreviewSettings()
			),
			prevScrollY: 0,
		};
	},
	mounted() {
		window.addEventListener("message", this.resizeIframe);
		window.addEventListener("scroll", this.handleScroll);
	},
	destroyed() {
		window.removeEventListener("message", this.resizeIframe);
		window.removeEventListener("scroll", this.handleScroll);
	},
	computed: {
		currentNutrifoxDisplayStyle() {
			return this.customization.nutrifox_display_style;
		},
		currentTemplateLabel() {
			// Show the template count, and use the index for the current template.
			const keys = Object.keys( this.templateOptions );

			return this.templateOptions[this.currentTemplate] +
				" ("
				+ (keys.indexOf(this.currentTemplate) + 1) + "/"
				+ keys.length
				+ ")"
				+ (this.isLocked ? this.getProBadge() : "");
		},
		nutrifoxLogoUrl() {
			return this.getImage( 'nutrifox-logo.png' );
		},
		isNutrifoxUser() {
			return window.tastyRecipesSettings.isNutrifoxUser;
		},
		isLocked() {
			return this.initialOptions.locked.includes(
				this.currentTemplate
			);
		},
		getSocialModal() {
			return JSON.stringify({
				modal_title: 'Social Footers',
				setting_utm_content: 'social-footer'
			});
		},
	},
	methods: {
		canCustomizeColor(color) {
			switch (this.currentTemplate) {
				case "bold":
				case "fresh":
				case "snap":
					if (
						-1 !==
						[
							"primary",
							"secondary",
							"icon",
							"button",
							"button-text",
						].indexOf(color)
					) {
						return true;
					}
					break;
				case "simple":
					if (
						-1 !==
						[
							"primary",
							"secondary",
							"button",
							"button-text",
						].indexOf(color)
					) {
						return true;
					}
					break;
				case "modern-compact":
					if (
						-1 !==
						[
							"primary",
							"secondary",
							"button",
							"button-text",
						].indexOf(color)
					) {
						return true;
					}
					break;
				case "elegant":
					if (
						-1 !==
						["primary", "button", "button-text"].indexOf(color)
					) {
						return true;
					}
					break;
				default:
					if (-1 !== ["button", "button-text"].indexOf(color)) {
						return true;
					}
					break;
			}
			return false;
		},
		goToPreviousTemplate() {
			const keys = Object.keys(this.templateOptions);
			const index = keys.indexOf(this.currentTemplate);
			if (0 === index) {
				this.currentTemplate = keys[keys.length - 1];
			} else {
				this.currentTemplate = keys[index - 1];
			}
		},
		goToNextTemplate() {
			const keys = Object.keys(this.templateOptions);
			const index = keys.indexOf(this.currentTemplate);
			if (keys.length - 1 === index) {
				this.currentTemplate = keys[0];
			} else {
				this.currentTemplate = keys[index + 1];
			}
		},
		getRecipeCardPreviewUrl(template, settings, include) {
			const params = Object.entries(settings)
				.filter(([key]) => include.includes(key)) // Only include keys that are in the "include" array
				.map(([key, value]) => `${key}=${encodeURIComponent(value)}`)
				.join('&');

			return (
				this.recipeCardPreviewUrlBase +
				"&template=" +
				template +
				"&" +
				params
			);
		},
		getPreviewSettings() {
			return ['nutrifox_display_style', 'star_ratings_style'];
		},
		getProBadge() {
			if ( ! window.TastyFramework || ! window.TastyFramework.f ) {
				return '';
			}
			let badge = window.TastyFramework.f.getProBadge();
			badge = badge.replace( /href=""/, `href="${this.initialOptions.upgrade_url}"` );
			return badge;
		},
		getImage( image ) {
			return (
				window.tastyRecipesSettings.pluginUrl +
				"/assets/images/" + image
			);
		},
		handleScroll() {
			const currentScrollY = window.scrollY;
			if (this.prevScrollY < currentScrollY) {
				this.currentStickyTop = Math.max(
					this.originalStickyTop,
					this.currentStickyTop - 10
				);
			} else {
				this.currentStickyTop = Math.min(0, this.currentStickyTop + 10);
			}
			this.prevScrollY = currentScrollY;
		},
		resizeIframe(event) {
			if (
				window.location.origin !== event.origin ||
				typeof event.data !== "string"
			) {
				return;
			}
			const payload = JSON.parse(event.data);
			switch (payload.event) {
				case "setRecipeCardSize":
					this.iframeHeight = payload.height;
					break;
			}
		},
		sendCustomizationToIframe() {
			const data = {
				event: "updateCustomization",
				data: {
					...this.customization,
				},
			};
			this.$refs.previewIframe.contentWindow.postMessage(
				JSON.stringify(data)
			);
		},
		updateRecipeCardPreviewUrl() {
			this.recipeCardPreviewUrl = this.getRecipeCardPreviewUrl(
				this.currentTemplate,
				this.customization,
				this.getPreviewSettings()
			);
		},
	},
	watch: {
		currentNutrifoxDisplayStyle() {
			this.updateRecipeCardPreviewUrl();
		},
		currentStarRatingsStyle() {
			this.updateRecipeCardPreviewUrl();
		},
		currentTemplate() {
			this.updateRecipeCardPreviewUrl();
		},
		customization: {
			deep: true,
			handler() {
				this.sendCustomizationToIframe();
			},
		},
	},
};
</script>
