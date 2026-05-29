const { expect, test } = require( '../../support/test' );

test.describe.configure( { mode: 'serial' } );

test.describe( 'WP Tasty recipe page', () => {
	test.beforeEach( async ( { admin } ) => {
		await admin.visitAdminPage( 'edit.php?post_type=tasty_recipe' );
	} );

	test( 'should validate recipes page content', async ( { page } ) => {
		await expect(
			page.locator( '.tasty-nav-tab', { hasText: 'Recipes' } )
		).toBeVisible();
		await expect(
			page.locator( '.tasty-framework-header-logo img' )
		).toBeVisible();
		await expect(
			page.locator( '.tasty-framework-header-title' )
		).toContainText( 'Recipes' );
		await expect(
			page.locator( '.tasty-framework-header-support a' )
		).toHaveAttribute( 'href', /wptasty\.com\/support/ );

		const tabs = [
			{
				label: 'Recipes',
				href: '/wp-admin/edit.php?post_type=tasty_recipe',
			},
			{
				label: 'Design',
				href: 'admin.php?page=tasty-recipes&tab=design',
			},
			{
				label: 'Settings',
				href: 'admin.php?page=tasty-recipes&tab=settings',
			},
			{
				label: 'Converters',
				href: 'admin.php?page=tasty-recipes&tab=converters',
			},
			{
				label: 'Get Started',
				href: 'admin.php?page=tasty-recipes&tab=about',
			},
		];

		await expect( page.locator( '.tasty-nav-tab' ) ).toHaveCount(
			tabs.length
		);
		for ( const tab of tabs ) {
			await expect(
				page.locator( '.tasty-nav-tab', { hasText: tab.label } )
			).toHaveAttribute( 'href', new RegExp( escapeRegExp( tab.href ) ) );
		}

		await expect( page.locator( '.subsubsub .all a .count' ) ).toHaveText(
			/^\(\d+\)$/
		);
		await expect(
			page.locator( '.tasty-recipes-empty-state p' ).first()
		).toHaveText(
			'No recipes yet? Click "Create Recipe" to start your culinary journey!'
		);
		await expect(
			page.getByRole( 'button', { name: 'Create Recipe' } )
		).toBeVisible();
	} );

	test( 'should validate recipes Design page content', async ( { page } ) => {
		await page.locator( '.tasty-nav-tab', { hasText: 'Design' } ).click();
		await expect( page ).toHaveURL( /admin\.php\?page=tasty-recipes&tab=design/ );
		await page.waitForLoadState( 'networkidle' );

		await expect(
			page.locator( '.tasty-tabs-submit button#tasty-nav-submit' )
		).toContainText( 'Save Changes' );
		await expect(
			page.locator( '.tasty-recipes-card-chooser' ).first()
		).toBeVisible();
		await expect(
			page.locator( '.tasty-recipes-card-chooser' ).first().locator( 'h3' )
		).toContainText( /\(\d+\/\d+\)/ );
		await expect( page.locator( '#tasty-recipes-card-preview' ) )
			.toHaveAttribute( 'title', 'Template Preview' );
		await expect( page.locator( '#tasty-recipes-card-preview' ) )
			.toHaveAttribute( 'src', /tasty_recipes_preview/ );

		const colorFields = [
			'Button Color',
			'Button Text Color',
			'Detail Label Color',
			'Detail Value Color',
		];
		for ( const label of colorFields ) {
			await expect( page.locator( 'label', { hasText: label } ) ).toBeVisible();
		}
		await expect( page.locator( '.tasty-swatch' ) ).toHaveCount(
			await page.locator( '.tasty-swatch' ).count()
		);
		expect( await page.locator( '.tasty-swatch' ).count() ).toBeGreaterThanOrEqual(
			colorFields.length
		);
		await expect(
			page.locator( 'input[name="tasty_recipes_customization[body_color]"]' )
		).toBeAttached();

		const expectedOptions = [
			'Default Capitalization',
			'Uppercase Capitalization',
			'Normal Capitalization',
			'Lowercase Capitalization',
		];

		await openReactSelect(
			page,
			'#tasty_recipes_customization\\[h2_transform\\] .tasty-react-select__control'
		);
		await expectSelectOptions( page, expectedOptions );

		await expect(
			page.locator(
				'#tasty_recipes_customization\\[h3_transform\\] .tasty-react-select__control'
			)
		).toBeAttached();
		await openReactSelect(
			page,
			'#tasty_recipes_customization\\[h3_transform\\] .tasty-react-select__control'
		);
		await expectSelectOptions( page, expectedOptions );

		await expect( page.locator( 'h3', { hasText: 'Body' } ) ).toBeVisible();
		await expect(
			page.locator( 'input[name="tasty_recipes_customization[body_color]"]' )
		).toBeAttached();
		await expect(
			page.locator( '.tasty-color-wrap .tasty-swatch' ).first()
		).toBeVisible();
		await expect(
			page.locator( 'h3', { hasText: 'Star Ratings' } )
		).toBeVisible();

		await openReactSelect(
			page,
			'label[for="tasty_recipes_customization\\[star_ratings_style\\]"] >> xpath=.. >> .tasty-react-select__control'
		);
		await expectSelectOptions( page, [ 'Solid', 'Outline' ] );
	} );

	test( 'should validate recipes Settings page content', async ( { page } ) => {
		await page.locator( '.tasty-nav-tab', { hasText: 'Settings' } ).click();
		await expect( page ).toHaveURL(
			/admin\.php\?page=tasty-recipes&tab=settings/
		);
		await page.waitForLoadState( 'networkidle' );
		await expect( page.locator( '#tasty-recipes-settings-tab' ) ).toBeVisible();

		await checkIfUnchecked(
			page.locator( 'label[for="tasty-recipes-quick-links-jump"]' )
				.locator( 'xpath=..' )
				.locator( 'input[type="checkbox"]' )
		);
		await checkIfUnchecked(
			page.locator( 'label[for="tasty-recipes-quick-links-print"]' )
				.locator( 'xpath=..' )
				.locator( 'input[type="checkbox"]' )
		);
		await checkIfUnchecked(
			page.locator( 'label[for="tasty-recipes-quick-links-rating"]' )
				.locator( 'xpath=..' )
				.locator( 'input[type="checkbox"]' )
		);

		const values = await page
			.locator( 'input[name="tasty_recipes_quick_links[]"]:checked' )
			.evaluateAll( inputs => inputs.map( input => input.value ) );
		expect( values ).toEqual( expect.arrayContaining( [ 'jump', 'print', 'rating' ] ) );

		for ( const fieldName of [
			'tasty_recipes_card_buttons[first]',
			'tasty_recipes_card_buttons[second]',
		] ) {
			const value = await page
				.locator( `input[name="${ fieldName }"]` )
				.inputValue();
			expect( [ '', 'print', 'pin', 'mediavine', 'slickstream' ] ).toContain(
				value
			);
		}

		await expect(
			page.locator( 'label[for="tasty-recipes-default-author-link"]' )
		).toContainText( 'Default Author Link' );
		const authorLink = page.locator( '#tasty-recipes-default-author-link' );
		await expect( authorLink ).toHaveAttribute( 'type', 'text' );
		expect( await authorLink.inputValue() ).toMatch( /^$|^https?:\/\// );
		await authorLink.fill( 'https://example.com/author' );
		await expect( authorLink ).toHaveValue( 'https://example.com/author' );

		const shareLinks = await page
			.locator( '#tasty-recipes-shareasale-affiliate-id' )
			.locator( 'xpath=..' )
			.locator( '.tasty-form-helper a' )
			.evaluateAll( links => links.map( link => link.href ) );
		expect( shareLinks.some( href => href.includes( 'wptasty.com' ) ) )
			.toBe( true );
		await expect( page.locator( '.tasty-settings-banner-right a' ) )
			.toHaveText( 'Upgrade to PRO' );
		await expect( page.locator( '.tasty-settings-banner-right a' ) )
			.toHaveAttribute( 'href', /wptasty\.com/ );
		await expect(
			page.locator( '#tasty_recipes_quick_links_style .tasty-react-select__control' )
		).toHaveAttribute( 'aria-disabled', 'true' );

		const premiumToggles = [
			{
				label: 'Unit Conversion',
				id: 'tasty_recipes_unit_conversion',
				helperText:
					'Allow visitors to convert between US Customary and Metric.',
			},
			{
				label: 'Ingredient Checkboxes',
				id: 'tasty_recipes_ingredient_checkboxes',
				helperText: 'Allow visitors to check off recipe ingredients.',
			},
			{
				label: 'Scaling',
				id: 'tasty_recipes_disable_scaling',
				helperText: /Allow visitors to scale the recipe (?:1\/2|½), 2x, or 3x\./,
			},
			{
				label: 'Copy to Clipboard',
				id: 'tasty_recipes_copy_to_clipboard',
				helperText: 'Allow visitors to copy ingredients to clipboard.',
			},
			{
				label: 'Instacart Button',
				id: 'tasty_recipes_instacart',
				helperText: 'Instacart terms of service',
			},
		];

		for ( const { label, id, helperText } of premiumToggles ) {
			const field = page.locator( '.tasty-form-field', {
				has: page.locator( `label[for="${ id }"]` ),
			} );
			await expect( field ).toContainText( helperText );
			await expect( page.locator( `#${ id }` ) ).toBeDisabled();
			await field.locator( '.tasty-slider' ).first().click( { force: true } );
			await expect(
				page.locator( '.tasty-framework-modal-header' )
			).toContainText( label );
			await expect(
				page.locator( '.tasty-framework-modal-body' )
			).toContainText( 'This is a premium feature' );
			await expect(
				page.locator( '.tasty-framework-modal-body' )
			).toContainText( 'Consider upgrading to a paid plan' );
			await expect( page.locator( '.tasty-framework-modal-body a' ) )
				.toHaveAttribute( 'href', /wptasty\.com/ );
			await page
				.locator( '#tasty_recipes_upgrade_modal .tasty-framework-close-modal' )
				.click( { force: true } );
		}
	} );

	test( 'should validate recipes Converters page content', async ( { page } ) => {
		await page.locator( '.tasty-nav-tab', { hasText: 'Converters' } ).click();
		await expect( page ).toHaveURL(
			/admin\.php\?page=tasty-recipes&tab=converters/
		);
		await page.waitForLoadState( 'networkidle' );

		const header = page.locator( '.tasty-recipes-converter-header' );
		await expect( header.locator( 'h4' ) ).toContainText(
			'Import Existing Recipes'
		);
		await expect( header ).toContainText(
			'Just in case, we recommend backing up your database before converting your recipes in bulk to Tasty Recipes.'
		);
		await expect(
			header.locator( 'a', { hasText: 'Learn more about backing up your database.' } )
		).toHaveAttribute( 'rel', 'noopener noreferrer' );
		await expect( page.locator( '.tasty-recipes-converters' ) ).toContainText(
			'Select an option below to start converting recipes.'
		);

		const converters = [
			'Cookbook',
			'Mediavine Create',
			'EasyRecipe',
			'Meal Planner Pro',
			'Simple Recipe Pro',
			'WordPress.com',
			'WP Recipe Maker',
			'WP Ultimate Recipe',
			'Yummly',
			'YumPrint Recipe',
			'ZipList (or Zip Recipes)',
		];
		await expect( page.locator( '.tasty-recipes-converters-options button' ) )
			.toHaveCount( converters.length );
		for ( const converter of converters ) {
			await expect(
				page.locator( '.tasty-recipes-converters-options button', {
					hasText: converter,
				} )
			).toBeDisabled();
		}
	} );

	test( 'should validate recipes Get Started page content', async ( { page } ) => {
		await page.locator( '.tasty-nav-tab', { hasText: 'Get Started' } ).click();
		await expect( page ).toHaveURL( /admin\.php\?page=tasty-recipes&tab=about/ );
		await page.waitForLoadState( 'networkidle' );

		await expect( page.locator( 'h2', { hasText: 'Welcome to Tasty Recipes' } ) )
			.toBeVisible();
		await expect( page.locator( 'body' ) ).toContainText(
			'Tasty Recipes is a fast, simple, and SEO-optimized recipe plugin'
		);
		await expect( page.locator( 'h2', { hasText: 'Getting Started' } ) )
			.toBeVisible();
		await expect( page.locator( 'body' ) ).toContainText(
			'Tasty Recipes is extremely easy to configure'
		);
		await expect( page.locator( 'a', { hasText: 'Visit Settings' } ) )
			.toHaveAttribute( 'href', /admin\.php\?page=tasty-recipes/ );
		await expect( page.locator( 'h2', { hasText: 'Convert Recipes' } ) )
			.toBeVisible();
		await expect( page.locator( 'body' ) ).toContainText(
			'Tasty Recipes converts recipes from many sources'
		);

		for ( const linkText of [
			'converting recipes individually',
			'converting recipes in bulk',
		] ) {
			await expect( page.locator( 'a', { hasText: linkText } ) )
				.toHaveAttribute( 'href', /wptasty\.com/ );
		}

		await expect( page.locator( 'h2', { hasText: 'Create New Recipes' } ) )
			.toBeVisible();
		await expect( page.locator( 'body' ) ).toContainText(
			'Creating new recipes is easy'
		);
		await expect( page.locator( 'h2', { hasText: 'Visit Our Documentation' } ) )
			.toBeVisible();
		await expect( page.locator( 'body' ) ).toContainText(
			'We pride ourselves on our plugin documentation'
		);
		await expect( page.locator( 'a.tasty-button', { hasText: 'Visit Documentation' } ) )
			.toHaveAttribute( 'href', /wptasty\.com/ );
		await page
			.locator( 'input[name="tasty_recipes_onboarding_consent_tracking"]' )
			.check( { force: true } );
	} );
} );

const checkIfUnchecked = async locator => {
	if ( ! ( await locator.isChecked() ) ) {
		await locator.evaluate( input => {
			input.checked = true;
			input.dispatchEvent( new Event( 'input', { bubbles: true } ) );
			input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		} );
	}
};

const escapeRegExp = value =>
	String( value ).replace( /[.*+?^${}()|[\]\\]/g, '\\$&' );

const openReactSelect = async ( page, selector ) => {
	const control = page.locator( selector ).first();
	const indicator = control.locator( '.tasty-react-select__dropdown-indicator' );

	await control.scrollIntoViewIfNeeded();
	if ( await indicator.count() ) {
		await indicator.click( { force: true } );
	} else {
		await control.click( { force: true } );
	}

	if ( await page.locator( '.tasty-react-select__option' ).count() ) {
		return;
	}

	await page.keyboard.press( 'ArrowDown' );
};

const expectSelectOptions = async ( page, expectedOptions ) => {
	const options = page.locator( '.tasty-react-select__option' );
	await expect( options ).toHaveCount( expectedOptions.length );

	for ( const [ index, expectedOption ] of expectedOptions.entries() ) {
		await expect( options.nth( index ) ).toHaveText( expectedOption );
	}

	await page.keyboard.press( 'Escape' );
};
