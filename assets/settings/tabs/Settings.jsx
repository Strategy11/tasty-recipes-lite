import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import {
	SettingsGroup,
	CheckInput,
	SelectInput,
	SelectGroup,
	ToggleInput,
	PremiumModal,
	useForm,
	useModal,
	ShareASaleInput,
	TabletSaveButton,
	TextInput,
} from '@library';
import { UpgradeBanner } from '../partials/UpgradeBanner';
import { CookMode } from '../partials/CookMode';
import { InstacartButton } from '../partials/InstacartButton';
import { UnitConversion } from '../partials/UnitConversion';
import { isSettingDisabled } from '../utils/helpers';

const globalSettings = window.tastyRecipesSettings;
const { settings } = globalSettings;

const recipeCardOptions = ( value ) => {
	const isPro = applyFilters( 'tasty_recipes_is_pro', false );

	return [
		{
			value: 'print',
			label: __( 'Print', 'tasty-recipes-lite' ),
			selected: value === 'print',
		},
		{
			value: 'pin',
			label: __( 'Pin this recipe', 'tasty-recipes-lite' ),
			selected: value === 'pin',
			isDisabled: ! isPro,
		},
		{
			value: 'mediavine',
			label: __( 'Mediavine Grow.me Save', 'tasty-recipes-lite' ),
			selected: value === 'mediavine',
			isDisabled: ! isPro,
		},
		{
			value: 'slickstream',
			label: __( 'Slickstream Favorites', 'tasty-recipes-lite' ),
			selected: value === 'slickstream',
			isDisabled: ! isPro,
		},
	];
};

export const Settings = () => {
	const { formData, handleChange } = useForm( settings );
	const { modalData, openModal, closeModal, generateUpgradeUrl } = useModal();
	const [ recipeCardButtons, setRecipeCardButtons ] = useState(
		formData.recipeCardButtons
	);

	const handleQuickLinksChange = ( checked, value ) => {
		handleChange(
			'quickLinks',
			checked
				? [ ...formData.quickLinks, value ]
				: formData.quickLinks.filter( ( link ) => link !== value )
		);
	};

	const scalingSetting = applyFilters(
		'tasty_recipes_scaling_setting',
		<ToggleInput
			onClick={ () => {
				if ( isSettingDisabled( formData.scaling ) ) {
					openModal( {
						id: 'tasty_recipes_upgrade_modal',
						title: __( 'Scaling', 'tasty-recipes-lite' ),
						upgradeUrl: generateUpgradeUrl( {
							medium: 'tasty-recipes',
							content: 'scaling',
						} ),
					} );
				}
			} }
			label={ __( 'Scaling', 'tasty-recipes-lite' ) }
			id="tasty_recipes_disable_scaling"
			name="tasty_recipes_disable_scaling"
			value={ String( formData.scaling ?? 1 ) }
			onValue="0"
			offValue="1"
			setValue={ ( value ) => {
				handleChange( 'scaling', value );
			} }
			disabled={ isSettingDisabled( formData.scaling ) }
			helper={ __(
				'Allow visitors to scale the recipe ½, 2x, or 3x.',
				'tasty-recipes-lite'
			) }
		/>,
		formData,
		handleChange
	);

	const EnableHowTos = applyFilters(
		'tasty_recipes_enable_how_tos',
		<ToggleInput
			onClick={ () => {
				if ( isSettingDisabled( formData.enableHowTos ) ) {
					openModal( {
						id: 'tasty_recipes_upgrade_modal',
						title: __(
							'Enable How-Tos',
							'tasty-recipes-lite'
						),
						upgradeUrl: generateUpgradeUrl( {
							medium: 'tasty-recipes',
							content: 'enable-how-tos',
						} ),
					} );
				}
			} }
			label={ __( 'Enable How-Tos', 'tasty-recipes-lite' ) }
			isNew
			id="tasty_recipes_enable_how_tos"
			name="tasty_recipes_enable_how_tos"
			value={ String( formData.enableHowTos ?? 0 ) }
			onValue="1"
			offValue="0"
			setValue={ ( value ) => {
				handleChange( 'enableHowTos', value );
			} }
			disabled={ isSettingDisabled( formData.enableHowTos ) }
			helper={ __(
				'When enabled, you can create and manage How-Tos  alongside Recipes.',
				'tasty-recipes-lite'
			) }
		/>,
		formData,
		handleChange
	);

	return (
		<div>
			<PremiumModal modalData={ modalData } closeModal={ closeModal } />
			<SettingsGroup label={ __( 'Quick Links', 'tasty-recipes-lite' ) }>
				<CheckInput
					id="tasty-recipes-quick-links-jump"
					name="tasty_recipes_quick_links[]"
					label={ __(
						"'Jump to Recipe' link",
						'tasty-recipes-lite'
					) }
					value="jump"
					isChecked={
						formData.quickLinks.includes( 'jump' ) ||
						formData.quickLinks.includes( 'both' )
					}
					setValue={ ( checked ) =>
						handleQuickLinksChange( checked, 'jump' )
					}
				/>
				<CheckInput
					id="tasty-recipes-quick-links-print"
					name="tasty_recipes_quick_links[]"
					label={ __( "'Print Recipe' link", 'tasty-recipes-lite' ) }
					value="print"
					isChecked={
						formData.quickLinks.includes( 'print' ) ||
						formData.quickLinks.includes( 'both' )
					}
					setValue={ ( checked ) =>
						handleQuickLinksChange( checked, 'print' )
					}
				/>
				<CheckInput
					id="tasty-recipes-quick-links-rating"
					name="tasty_recipes_quick_links[]"
					className="tasty-form-field-no-helper-margin"
					label={ __(
						"'Leave a Review' link",
						'tasty-recipes-lite'
					) }
					value="rating"
					isChecked={ formData.quickLinks.includes( 'rating' ) }
					setValue={ ( checked ) =>
						handleQuickLinksChange( checked, 'rating' )
					}
					helper={ __(
						'Enable or disable quick links below the post title.',
						'tasty-recipes-lite'
					) }
				/>
				<CheckInput
					id="tasty-recipes-quick-links-trusted-source"
					name="tasty_recipes_quick_links[]"
					label={ __(
						"'Trust With Google' link",
						'tasty-recipes-lite'
					) }
					value="trusted_source"
					isChecked={ formData.quickLinks.includes(
						'trusted_source'
					) }
					setValue={ ( checked ) =>
						handleQuickLinksChange( checked, 'trusted_source' )
					}
					helper={ __(
						'Adds a link that lets visitors set your site as a Google Preferred Source.',
						'tasty-recipes-lite'
					) }
				/>
			</SettingsGroup>
			<SelectGroup
				groupId="tasty-recipes-card-buttons"
				groupLabel={ __( 'Recipe Card Buttons', 'tasty-recipes-lite' ) }
				selects={ [
					{
						id: 'tasty_recipes_card_buttons[first]',
						name: 'tasty_recipes_card_buttons[first]',
						value: recipeCardButtons.first,
						handleChange: ( value ) => {
							setRecipeCardButtons( ( prev ) => {
								return {
									...prev,
									first: value,
								};
							} );
						},
						options: recipeCardOptions( recipeCardButtons.first ),
					},
					{
						id: 'tasty_recipes_card_buttons[second]',
						name: 'tasty_recipes_card_buttons[second]',
						value: recipeCardButtons.second,
						handleChange: ( value ) => {
							setRecipeCardButtons( ( prev ) => {
								return {
									...prev,
									second: value,
								};
							} );
						},
						options: [
							{
								label: __( 'Off', 'tasty-recipes-lite' ),
								value: '',
								selected:
									! recipeCardButtons.second ||
									recipeCardButtons.second === 'off',
							},
							...recipeCardOptions( recipeCardButtons.second ),
						],
					},
				] }
			/>
			<TextInput
				label={ __( 'Default Author Link', 'tasty-recipes-lite' ) }
				id="tasty-recipes-default-author-link"
				name="tasty_recipes_default_author_link"
				value={ formData.defaultAuthorLink }
				setValue={ ( value ) => {
					handleChange( 'defaultAuthorLink', value );
				} }
			/>
			<ShareASaleInput
				id="tasty-recipes-shareasale-affiliate-id"
				name="tasty_recipes_shareasale"
				value={ formData.shareasaleId }
				setValue={ ( value ) => {
					handleChange( 'shareasaleId', value );
				} }
			/>
			<ToggleInput
				label={ __( 'Taxonomy Key Links', 'tasty-recipes-lite' ) }
				id="tasty_recipes_enable_taxonomy_links"
				name="tasty_recipes_enable_taxonomy_links"
				value={ String( formData.enableTaxonomyLinks ?? 0 ) }
				onValue="1"
				offValue="0"
				setValue={ ( value ) => {
					handleChange( 'enableTaxonomyLinks', value );
				} }
				helper={ __(
					'Allow users to click a taxonomy key and see all recipes that are tagged similarly',
					'tasty-recipes-lite'
				) }
			/>
			<ToggleInput
				label={ __( 'AI Scraper Prevention', 'tasty-recipes-lite' ) }
				id="tasty_recipes_ai_scraper_prevention"
				name="tasty_recipes_ai_scraper_prevention"
				value={ String( formData.enableAiScraperPrevention ?? 0 ) }
				onValue="1"
				offValue="0"
				setValue={ ( value ) => {
					handleChange( 'enableAiScraperPrevention', value );
				} }
				helper={ __(
					'Prevents compliant AI crawlers that honor robots.txt from scraping your site.',
					'tasty-recipes-lite'
				) }
				isNew
			/>
			<UpgradeBanner generateUpgradeUrl={ generateUpgradeUrl } />
			<SelectInput
				onClick={ () => {
					if ( isSettingDisabled( formData.quickLinksStyle ) ) {
						openModal( {
							id: 'tasty_recipes_upgrade_modal',
							title: __(
								'Quick Links Style',
								'tasty-recipes-lite'
							),
							upgradeUrl: generateUpgradeUrl( {
								medium: 'tasty-recipes',
								content: 'quick-links-style',
							} ),
							previewImage:
								globalSettings.pluginUrl +
								'/assets/images/quick-links-preview.png',
						} );
					}
				} }
				label={ __( 'Quick Links Style', 'tasty-recipes-lite' ) }
				id="tasty_recipes_quick_links_style"
				name="tasty_recipes_quick_links_style"
				value={ formData.quickLinksStyle }
				disabled={ isSettingDisabled( formData.quickLinksStyle ) }
				handleChange={ ( value ) => {
					handleChange( 'quickLinksStyle', value );
				} }
				options={ [
					{
						label: __( 'Buttons', 'tasty-recipes-lite' ),
						value: 'buttons',
						selected:
							formData.quickLinksStyle &&
							formData.quickLinksStyle === 'buttons',
					},
					{
						label: __( 'Links', 'tasty-recipes-lite' ),
						value: 'links',
						selected:
							! formData.quickLinksStyle ||
							formData.quickLinksStyle === 'links',
					},
				] }
			/>
			<UnitConversion
				formData={ formData }
				handleChange={ handleChange }
				openModal={ openModal }
				generateUpgradeUrl={ generateUpgradeUrl }
			/>
			<ToggleInput
				onClick={ () => {
					if ( isSettingDisabled( formData.ingredientCheckboxes ) ) {
						openModal( {
							id: 'tasty_recipes_upgrade_modal',
							title: __(
								'Ingredient Checkboxes',
								'tasty-recipes-lite'
							),
							upgradeUrl: generateUpgradeUrl( {
								medium: 'tasty-recipes',
								content: 'ingredient-checkboxes',
							} ),
						} );
					}
				} }
				label={ __( 'Ingredient Checkboxes', 'tasty-recipes-lite' ) }
				id="tasty_recipes_ingredient_checkboxes"
				name="tasty_recipes_ingredient_checkboxes"
				value={ String( formData.ingredientCheckboxes ?? 0 ) }
				onValue="1"
				offValue="0"
				setValue={ ( value ) => {
					handleChange( 'ingredientCheckboxes', value );
				} }
				disabled={ isSettingDisabled( formData.ingredientCheckboxes ) }
				helper={ __(
					'Allow visitors to check off recipe ingredients.',
					'tasty-recipes-lite'
				) }
			/>
			<CookMode
				formData={ formData }
				handleChange={ handleChange }
				openModal={ openModal }
				generateUpgradeUrl={ generateUpgradeUrl }
			/>
			{ scalingSetting }
			<ToggleInput
				onClick={ () => {
					if ( isSettingDisabled( formData.copyToClipboard ) ) {
						openModal( {
							id: 'tasty_recipes_upgrade_modal',
							title: __(
								'Copy to Clipboard',
								'tasty-recipes-lite'
							),
							upgradeUrl: generateUpgradeUrl( {
								medium: 'tasty-recipes',
								content: 'copy-to-clipboard',
							} ),
						} );
					}
				} }
				label={ __( 'Copy to Clipboard', 'tasty-recipes-lite' ) }
				id="tasty_recipes_copy_to_clipboard"
				name="tasty_recipes_copy_to_clipboard"
				value={ String( formData.copyToClipboard ?? 0 ) }
				onValue="1"
				offValue="0"
				setValue={ ( value ) => {
					handleChange( 'copyToClipboard', value );
				} }
				disabled={ isSettingDisabled( formData.copyToClipboard ) }
				helper={ __(
					'Allow visitors to copy ingredients to clipboard.',
					'tasty-recipes-lite'
				) }
			/>
			{ applyFilters(
				'tasty_recipes_save_recipe_newsletter',
				<ToggleInput
					onClick={ () => {
						if (
							isSettingDisabled(
								formData.saveRecipeNewsletter
							)
						) {
							openModal( {
								id: 'tasty_recipes_upgrade_modal',
								title: __(
									'Save Recipe/Newsletter',
									'tasty-recipes-lite'
								),
								upgradeUrl: generateUpgradeUrl( {
									medium: 'tasty-recipes',
									content: 'save-recipe-newsletter',
								} ),
							} );
						}
					} }
					label={ __(
						'Save Recipe/Newsletter',
						'tasty-recipes-lite'
					) }
					isNew
					id="tasty_recipes_save_recipe_newsletter"
					name="tasty_recipes_save_recipe_newsletter"
					value={ String(
						formData.saveRecipeNewsletter ?? 0
					) }
					onValue="1"
					offValue="0"
					setValue={ ( value ) => {
						handleChange(
							'saveRecipeNewsletter',
							value
						);
					} }
					disabled={ isSettingDisabled(
						formData.saveRecipeNewsletter
					) }
					helper={ __(
						'Allow visitors to send a recipe via email.',
						'tasty-recipes-lite'
					) }
				/>,
				formData,
				handleChange
			) }
			<InstacartButton
				formData={ formData }
				handleChange={ handleChange }
				openModal={ openModal }
				generateUpgradeUrl={ generateUpgradeUrl }
			/>
			{ EnableHowTos }
			<TabletSaveButton />
		</div>
	);
};
