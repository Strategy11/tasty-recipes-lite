import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import {
	ColorInput,
	SelectInput,
	PremiumLock,
	TabletSaveButton,
} from '@library';
import { templateFields, fieldLabels } from '../utils/templateFields.js';
import { getDefaultColors } from '../utils/defaultColors.js';
import { optionNames } from '../utils/optionNames.js';

const imagesUrl = window.tastyRecipesSettings.pluginUrl + '/assets/images/';

export const DesignSettings = ( {
	formData,
	selectedTemplate,
	handleChange,
	openModal,
	generateUpgradeUrl,
} ) => {
	const templateName = templateFields[ selectedTemplate.slug ]
		? selectedTemplate.slug
		: 'default';
	const parsedVariation = Number( selectedTemplate.variation );
	const safeVariation =
		Number.isFinite( parsedVariation ) && parsedVariation > 0
			? parsedVariation
			: 1;
	const defaultColors = getDefaultColors( templateName, selectedTemplate );

	const socialFooter = applyFilters(
		'tasty_recipes_design_social_footer',
		// eslint-disable-next-line jsx-a11y/click-events-have-key-events, jsx-a11y/no-noninteractive-element-interactions
		<img
			alt="Social Footer Preview"
			className="tasty-social-preview tasty-recipes-disabled"
			src={ imagesUrl + 'social-footer.png' }
			width="550"
			height="100"
			onClick={ () =>
				openModal( {
					id: 'tasty_recipes_upgrade_modal',
					title: __( 'Social Footers', 'tasty-recipes-lite' ),
					upgradeUrl: generateUpgradeUrl( {
						medium: 'tasty-recipes',
						content: 'social-footer',
					} ),
				} )
			}
		/>,
		formData,
		selectedTemplate,
		handleChange,
		defaultColors
	);

	return (
		<div className="tasty-recipes-card-customization-container">
			<input
				type="hidden"
				name={ optionNames.template }
				value={ selectedTemplate.slug }
			/>
			<input
				type="hidden"
				name={ optionNames.templateVariation }
				value={ safeVariation }
			/>
			<h2>{ __( 'Template Colors', 'tasty-recipes-lite' ) }</h2>
			<div className="tasty-two-columns-field-group">
				{ templateFields[ templateName ].map( ( field ) => (
					<ColorInput
						label={ fieldLabels[ field ] }
						id={ optionNames.customization + '-' + field }
						name={ optionNames.customization + '[' + field + ']' }
						color={ formData[ field ] }
						defaultColor={ defaultColors[ field ] }
						setColor={ ( color ) => {
							handleChange( field, color );
						} }
						key={ 'color-input-' + field }
					/>
				) ) }
			</div>
			<h3>{ __( 'Title (H2)', 'tasty-recipes-lite' ) }</h3>
			<div className="tasty-two-columns-field-group">
				<ColorInput
					label=""
					id={ optionNames.customization + "[h2_color]" }
					name={ optionNames.customization + "[h2_color]" }
					color={ formData.h2_color }
					defaultColor={ defaultColors.h2_color }
					setColor={ ( color ) => {
						handleChange( 'h2_color', color );
					} }
				/>
				<SelectInput
					label=""
					id={ optionNames.customization + "[h2_transform]" }
					name={ optionNames.customization + "[h2_transform]" }
					value={ formData.h2_transform }
					handleChange={ ( value ) => {
						handleChange( 'h2_transform', value );
					} }
					options={ [
						{
							label: __(
								'Default Capitalization',
								'tasty-recipes-lite'
							),
							value: '',
							selected: formData.h2_transform === '',
						},
						{
							label: __(
								'Uppercase Capitalization',
								'tasty-recipes-lite'
							),
							value: 'uppercase',
							selected: formData.h2_transform === 'uppercase',
						},
						{
							label: __(
								'Normal Capitalization',
								'tasty-recipes-lite'
							),
							value: 'initial',
							selected: formData.h2_transform === 'initial',
						},
						{
							label: __(
								'Lowercase Capitalization',
								'tasty-recipes-lite'
							),
							value: 'lowercase',
							selected: formData.h2_transform === 'lowercase',
						},
					] }
				/>
			</div>
			<h3>{ __( 'Subtitles (H3)', 'tasty-recipes-lite' ) }</h3>
			<div className="tasty-two-columns-field-group">
				<ColorInput
					label=""
					id={ optionNames.customization + "[h3_color]" }
					name={ optionNames.customization + "[h3_color]" }
					color={ formData.h3_color }
					defaultColor={ defaultColors.h3_color }
					setColor={ ( color ) => {
						handleChange( 'h3_color', color );
					} }
				/>
				<SelectInput
					label=""
					id={ optionNames.customization + "[h3_transform]" }
					name={ optionNames.customization + "[h3_transform]" }
					value={ formData.h3_transform }
					handleChange={ ( value ) => {
						handleChange( 'h3_transform', value );
					} }
					options={ [
						{
							label: __(
								'Default Capitalization',
								'tasty-recipes-lite'
							),
							value: '',
							selected: formData.h3_transform === '',
						},
						{
							label: __(
								'Uppercase Capitalization',
								'tasty-recipes-lite'
							),
							value: 'uppercase',
							selected: formData.h3_transform === 'uppercase',
						},
						{
							label: __(
								'Normal Capitalization',
								'tasty-recipes-lite'
							),
							value: 'initial',
							selected: formData.h3_transform === 'initial',
						},
						{
							label: __(
								'Lowercase Capitalization',
								'tasty-recipes-lite'
							),
							value: 'lowercase',
							selected: formData.h3_transform === 'lowercase',
						},
					] }
				/>
			</div>
			<h3>{ __( 'Body', 'tasty-recipes-lite' ) }</h3>
			<div className="tasty-two-columns-field-group">
				<ColorInput
					label=""
					id={ optionNames.customization + "[body_color]" }
					name={ optionNames.customization + "[body_color]" }
					color={ formData.body_color }
					defaultColor={ defaultColors.body_color }
					setColor={ ( color ) => {
						handleChange( 'body_color', color );
					} }
				/>
			</div>
			<h3>{ __( 'Star Ratings', 'tasty-recipes-lite' ) }</h3>
			<div className="tasty-wrap-bottom-padding tasty-bottom-border">
				<div className="tasty-two-columns-field-group tasty-no-bottom-border">
					<ColorInput
						label=""
						id={ optionNames.customization + "[star_color]" }
						name={ optionNames.customization + "[star_color]" }
						color={ formData.star_color }
						defaultColor={ defaultColors.star_color }
						setColor={ ( color ) => {
							handleChange( 'star_color', color );
						} }
					/>
					<SelectInput
						label=""
						id={ optionNames.customization + "[star_ratings_style]" }
						name={ optionNames.customization + "[star_ratings_style]" }
						value={ formData.star_ratings_style }
						handleChange={ ( value ) => {
							handleChange( 'star_ratings_style', value );
						} }
						options={ [
							{
								label: __( 'Solid', 'tasty-recipes-lite' ),
								value: 'solid',
								selected:
									! formData.star_ratings_style ||
									formData.star_ratings_style === 'solid',
							},
							{
								label: __( 'Outline', 'tasty-recipes-lite' ),
								value: 'outline',
								selected:
									formData.star_ratings_style === 'outline',
							},
						] }
					/>
				</div>
				<div className="tasty-recipes-nutrifox-field-container">
					<div>
						<h3>
							{ __( 'Nutrition Display', 'tasty-recipes-lite' ) }
						</h3>
						<div>
							<label htmlFor={ optionNames.customization + "-nutrifox-label" }>
								<input
									type="radio"
									id={ optionNames.customization + "-nutrifox-label" }
									name={ optionNames.customization + "[nutrifox_display_style]" }
									value="label"
									checked={
										formData.nutrifox_display_style ===
										'label'
									}
									onChange={ ( e ) => {
										handleChange(
											'nutrifox_display_style',
											e.target.value
										);
									} }
								/>
								{ __(
									'Insert Nutrifox label (iframe)',
									'tasty-recipes-lite'
								) }
							</label>
						</div>
						<div>
							<label htmlFor={ optionNames.customization + "-nutrifox-card" }>
								<input
									type="radio"
									id={ optionNames.customization + "-nutrifox-card" }
									name={ optionNames.customization + "[nutrifox_display_style]" }
									value="card"
									checked={
										formData.nutrifox_display_style ===
										'card'
									}
									onChange={ ( e ) => {
										handleChange(
											'nutrifox_display_style',
											e.target.value
										);
									} }
								/>
								{ __(
									'Insert as plain text',
									'tasty-recipes-lite'
								) }
							</label>
						</div>
					</div>
					<div className="nutrifox-cta">
						<a
							className="nutrifox-logo"
							href="https://nutrifox.com/?utm_source=tasty-recipes&utm_medium=dashboard&utm_campaign=design-tab"
							target="_blank"
							rel="noopener noreferrer"
						>
							<img
								alt="Nutrifox logo"
								src={ imagesUrl + 'nutrifox-logo.png' }
								width="96"
								height="22"
							/>
						</a>
						{ ! window.tastyRecipesSettings.isNutrifoxUser && (
							<a
								className="nutrifox-try"
								href="https://nutrifox.com/try?utm_source=tasty-recipes&utm_medium=dashboard&utm_campaign=design-tab&utm_content=try-cta"
								target="_blank"
								rel="noreferrer"
							>
								{ __(
									'Try Nutrifox free for 14 days',
									'tasty-recipes-lite'
								) }
							</a>
						) }
					</div>
				</div>
			</div>
			<h3>
				{ __( 'Social Footer', 'tasty-recipes-lite' ) }
				<PremiumLock
					upgradeUrl={ generateUpgradeUrl( {
						medium: 'tasty-recipes',
						campaign: 'upgrade',
						content: 'social-footer',
					} ) }
				/>
			</h3>
			{ socialFooter }
			<TabletSaveButton />
		</div>
	);
};
