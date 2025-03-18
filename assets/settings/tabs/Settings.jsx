import { createInterpolateElement } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	SettingsGroup,
	CheckInput,
	SelectInput,
	SelectGroup,
	ToggleInput,
	PremiumModal,
	TextInput,
	useForm,
	useModal,
	ShareASaleInput,
	TabletSaveButton,
} from '@library';

const globalSettings = window.tastyRecipesSettings;
const { settings } = globalSettings;

const recipeCardOptions = ( value ) => {
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
		},
		{
			value: 'mediavine',
			label: __( 'Mediavine Grow.me Save', 'tasty-recipes-lite' ),
			selected: value === 'mediavine',
		},
		{
			value: 'slickstream',
			label: __( 'Slickstream Favorites', 'tasty-recipes-lite' ),
			selected: value === 'slickstream',
		},
	];
};

export const Settings = () => {
	const { formData, handleChange } = useForm( settings );
	const { modalData, openModal, closeModal, generateUpgradeUrl } = useModal();

	const handleQuickLinksChange = ( checked, value ) => {
		handleChange(
			'quickLinks',
			checked
				? [ ...formData.quickLinks, value ]
				: formData.quickLinks.filter( ( link ) => link !== value )
		);
	};

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
			</SettingsGroup>
			<SelectInput
				label={ __( 'Quick Links Style', 'tasty-recipes-lite' ) }
				id="tasty_recipes_quick_links_style"
				name="tasty_recipes_quick_links_style"
				value={ formData.quickLinksStyle }
				disabled={ ! formData.quickLinksStyle }
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
			<SelectGroup
				groupId="tasty-recipes-card-buttons"
				groupLabel={ __( 'Recipe Card Buttons', 'tasty-recipes-lite' ) }
				selects={ [
					{
						id: 'tasty_recipes_card_buttons[first]',
						name: 'tasty_recipes_card_buttons[first]',
						value: formData.recipeCardButtons.first,
						handleChange: ( value ) => {
							handleChange( 'recipeCardButtons', value );
						},
						options: recipeCardOptions(
							formData.recipeCardButtons.first
						),
					},
					{
						id: 'tasty_recipes_card_buttons[second]',
						name: 'tasty_recipes_card_buttons[second]',
						value: formData.recipeCardButtons.second,
						handleChange: ( value ) => {
							handleChange( 'recipeCardButtons', value );
						},
						options: [
							{
								label: __( 'Off', 'tasty-recipes-lite' ),
								value: '',
								selected:
									! formData.recipeCardButtons.second ||
									formData.recipeCardButtons.second === 'off',
							},
							...recipeCardOptions(
								formData.recipeCardButtons.second
							),
						],
					},
				] }
			/>
			<ToggleInput
				onClick={ () => {
					if ( ! formData.unitConversion ) {
						openModal(
							'tasty_recipes_upgrade_modal',
							__( 'Unit Conversion', 'tasty-recipes-lite' ),
							generateUpgradeUrl( {
								medium: 'tasty-recipes',
								content: 'unit-conversion',
							} )
						);
					}
				} }
				label={ __( 'Unit Conversion', 'tasty-recipes-lite' ) }
				id="tasty_recipes_unit_conversion"
				name="tasty_recipes_unit_conversion"
				value={ formData.unitConversion ?? 0 }
				onValue="1"
				offValue="0"
				setValue={ ( value ) => {
					handleChange( 'unitConversion', value );
				} }
				disabled={ ! formData.unitConversion }
				helper={ __(
					'Allow visitors to convert between US Customary and Metric.',
					'tasty-recipes-lite'
				) }
			/>
			<ToggleInput
				onClick={ () => {
					if ( ! formData.ingredientCheckboxes ) {
						openModal(
							'tasty_recipes_upgrade_modal',
							__( 'Ingredient Checkboxes', 'tasty-recipes-lite' ),
							generateUpgradeUrl( {
								medium: 'tasty-recipes',
								content: 'ingredient-checkboxes',
							} )
						);
					}
				} }
				label={ __( 'Ingredient Checkboxes', 'tasty-recipes-lite' ) }
				id="tasty_recipes_ingredient_checkboxes"
				name="tasty_recipes_ingredient_checkboxes"
				value={ formData.ingredientCheckboxes ?? 0 }
				onValue="1"
				offValue="0"
				setValue={ ( value ) => {
					handleChange( 'ingredientCheckboxes', value );
				} }
				disabled={ ! formData.ingredientCheckboxes }
				helper={ __(
					'Allow visitors to check off recipe ingredients.',
					'tasty-recipes-lite'
				) }
			/>
			<ToggleInput
				onClick={ () => {
					if ( ! formData.cookMode ) {
						openModal(
							'tasty_recipes_upgrade_modal',
							__( 'Enable Cook Mode', 'tasty-recipes-lite' ),
							generateUpgradeUrl( {
								medium: 'tasty-recipes',
								content: 'cook-mode',
							} )
						);
					}
				} }
				label={ __( 'Enable Cook Mode', 'tasty-recipes-lite' ) }
				id="tasty_recipes_cook_mode"
				name="tasty_recipes_cook_mode"
				value={ formData.cookMode ?? 0 }
				onValue="1"
				offValue="0"
				setValue={ ( value ) => {
					handleChange( 'cookMode', value );
				} }
				disabled={ ! formData.cookMode }
				helper={ __(
					'Show Cook Mode toggle to let visitors prevent sleep mode for supported devices.',
					'tasty-recipes-lite'
				) }
			/>
			<ToggleInput
				onClick={ () => {
					if ( ! formData.cookMode ) {
						openModal(
							'tasty_recipes_upgrade_modal',
							__( 'Scaling', 'tasty-recipes-lite' ),
							generateUpgradeUrl( {
								medium: 'tasty-recipes',
								content: 'scaling',
							} )
						);
					}
				} }
				label={ __( 'Scaling', 'tasty-recipes-lite' ) }
				id="tasty_recipes_scaling"
				name="tasty_recipes_scaling"
				value={ formData.scaling ?? 0 }
				onValue="1"
				offValue="0"
				setValue={ ( value ) => {
					handleChange( 'scaling', value );
				} }
				disabled={ ! formData.scaling }
				helper={ __(
					'Allow visitors to scale the recipe 1x, 2x, or 3x.',
					'tasty-recipes-lite'
				) }
			/>
			<ToggleInput
				onClick={ () => {
					if ( ! formData.copyToClipboard ) {
						openModal(
							'tasty_recipes_upgrade_modal',
							__( 'Copy to Clipboard', 'tasty-recipes-lite' ),
							generateUpgradeUrl( {
								medium: 'tasty-recipes',
								content: 'copy-to-clipboard',
							} )
						);
					}
				} }
				label={ __( 'Copy to Clipboard', 'tasty-recipes-lite' ) }
				id="tasty_recipes_copy_to_clipboard"
				name="tasty_recipes_copy_to_clipboard"
				value={ formData.copyToClipboard ?? 0 }
				onValue="1"
				offValue="0"
				setValue={ ( value ) => {
					handleChange( 'copyToClipboard', value );
				} }
				disabled={ ! formData.copyToClipboard }
				helper={ __(
					'Allow visitors to copy ingredients to clipboard.',
					'tasty-recipes-lite'
				) }
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
			<ToggleInput
				onClick={ () => {
					if ( ! formData.instacartButton ) {
						openModal(
							'tasty_recipes_upgrade_modal',
							__( 'Instacart Button', 'tasty-recipes-lite' ),
							generateUpgradeUrl( {
								medium: 'tasty-recipes',
								content: 'instacart-button',
							} )
						);
					}
				} }
				label={ __( 'Instacart Button', 'tasty-recipes-lite' ) }
				id="tasty_recipes_instacart_button"
				name="tasty_recipes_instacart_button"
				value={ formData.instacartButton ?? 0 }
				onValue="1"
				offValue="0"
				setValue={ ( value ) => {
					handleChange( 'instacartButton', value );
				} }
				disabled={ ! formData.instacartButton }
				helper={ createInterpolateElement(
					__(
						'By turning this on, you agree to the <termsOfServiceLink>Instacart terms of service</termsOfServiceLink>, available in the US only at the moment.',
						'tasty-recipes-lite'
					),
					{
						termsOfServiceLink: (
							// eslint-disable-next-line jsx-a11y/anchor-has-content
							<a
								href="https://affiliate.instacart.com/terms"
								target="_blank"
								rel="noreferrer"
							/>
						),
					}
				) }
			/>
			<ShareASaleInput
				id="tasty-recipes-shareasale-affiliate-id"
				name="tasty_recipes_shareasale"
				value={ formData.shareasaleId }
				setValue={ ( value ) => {
					handleChange( 'shareasaleId', value );
				} }
			/>
			<TabletSaveButton />
		</div>
	);
};
