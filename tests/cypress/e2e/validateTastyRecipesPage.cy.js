describe( 'WP Tasty recipe page', () => {
	beforeEach( () => {
		cy.login();
		cy.visit( '/wp-admin/edit.php?post_type=tasty_recipe' );
		cy.viewport( 1280, 720 );
	} );

	it( 'should validate recipes page content', () => {
		cy.log( 'Validating header elements' );
		cy.get( '.tasty-framework-header-logo img' ).should( 'be.visible' );
		cy.get( '.tasty-framework-header-title' )
			.should( 'be.visible' )
			.and( 'contain.text', 'Recipes' );

		cy.get( '.tasty-framework-header-support a' )
			.should( 'have.attr', 'href' )
			.and( 'include', 'wptasty.com/support' );

		cy.log( 'Validating navigation tabs' );
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

		cy.get( '.tasty-nav-tab' ).should( 'have.length', tabs.length );

		tabs.forEach( ( tab ) => {
			cy.contains( '.tasty-nav-tab', tab.label )
				.should( 'be.visible' )
				.and( 'have.attr', 'href' )
				.and( 'include', tab.href );
		} );

		cy.log( 'Validating count of existing recipes' );
		cy.get( '.subsubsub .all a .count' ).should( 'contain.text', '(0)' );

		cy.log( 'Validate empty state text' );
		cy.contains( '.tasty-recipes-empty-state p', 'No recipes yet?' ).should(
			'have.text',
			'No recipes yet? Click "Create Recipe" to start your culinary journey!'
		);
		cy.contains( 'button', 'Create Recipe' ).should( 'be.visible' );
	} );

	it( 'should validate recipes Design page content', () => {
		cy.log( 'Navigate to Design tab' );
		cy.contains( '.tasty-nav-tab', 'Design' ).click();
		cy.url().should( 'include', 'admin.php?page=tasty-recipes&tab=design' );

		cy.log( 'Validate header elements' );
		cy.get( '.tasty-tabs-submit button#tasty-nav-submit' )
			.should( 'be.visible' )
			.and( 'contain.text', 'Save Changes' );

		cy.get( '.tasty-recipes-card-chooser' )
			.should( 'be.visible' )
			.find( 'button' )
			.its( 'length' )
			.should( 'be.gt', 0 );

		cy.contains( 'h3', 'Default (1/7)' ).should( 'be.visible' );

		cy.log( 'Validate template preview' );
		cy.get( '#tasty-recipes-card-preview' )
			.should( 'be.visible' )
			.and( 'have.attr', 'title', 'Template Preview' )
			.and( 'have.attr', 'src' )
			.and( 'include', 'tasty_recipes_preview' );

		cy.log( 'Validate color fields' );
		const colorFields = [
			'Button Color',
			'Button Text Color',
			'Detail Label Color',
			'Detail Value Color',
		];

		colorFields.forEach( ( label ) => {
			cy.contains( 'label', label ).should( 'be.visible' );
		} );

		cy.get( '.tasty-swatch' ).should(
			'have.length.at.least',
			colorFields.length
		);

		cy.contains( 'h3', 'Recipe Title (H2)' ).should( 'be.visible' );

		const expectedOptions = [
			'Default Capitalization',
			'Uppercase Capitalization',
			'Normal Capitalization',
			'Lowercase Capitalization',
		];

		cy.log( 'Validate H2 Transform dropdown' );
		cy.get(
			'#tasty_recipes_customization\\[h2_transform\\] .tasty-react-select__control'
		).click( { force: true } );

		cy.get( '.tasty-react-select__menu .tasty-react-select__option' )
			.should( 'have.length', expectedOptions.length )
			.each( ( $opt, i ) => {
				cy.wrap( $opt ).should( 'have.text', expectedOptions[ i ] );
			} );

		cy.contains( 'h3', 'Recipe Subtitles (H3)' ).should( 'be.visible' );

		const expectedH3 = expectedOptions;

		cy.log( 'Validate H3 Transform dropdown' );
		cy.get(
			'#tasty_recipes_customization\\[h3_transform\\] .tasty-react-select__control'
		).click( { force: true } );

		cy.get( '.tasty-react-select__menu .tasty-react-select__option' )
			.should( 'have.length', expectedH3.length )
			.each( ( $opt, i ) => {
				cy.wrap( $opt ).should( 'have.text', expectedH3[ i ] );
			} );

		cy.contains( 'h3', 'Body' ).should( 'be.visible' );

		cy.get(
			'input[name="tasty_recipes_customization[body_color]"]'
		).should( 'exist' );

		cy.get( '.tasty-color-wrap .tasty-swatch' )
			.should( 'exist' )
			.and( 'be.visible' );

		cy.contains( 'h3', 'Star Ratings' ).should( 'be.visible' );

		cy.log( 'Validate Star Ratings dropdown' );
		cy.get(
			'label[for="tasty_recipes_customization\\[star_ratings_style\\]"]'
		)
			.parent()
			.find( '.tasty-react-select__control' )
			.click();

		const starStyleOptions = [ 'Solid', 'Outline' ];

		cy.get( '.tasty-react-select__option' )
			.should( 'have.length', starStyleOptions.length )
			.each( ( $opt, i ) => {
				cy.wrap( $opt ).should( 'have.text', starStyleOptions[ i ] );
			} );
	} );

	it( 'should validate recipes Settings page content', () => {
		cy.log( 'Navigate to Settings tab' );
		cy.contains( '.tasty-nav-tab', 'Settings' ).click();
		cy.url().should(
			'include',
			'admin.php?page=tasty-recipes&tab=settings'
		);

		cy.log( 'Wait for Settings tab to render' );
		cy.get( '#tasty-recipes-settings-tab' )
			.should( 'be.visible' )
			.and( 'not.be.empty' );

		cy.log( 'Validate and update Quick Links checkboxes' );
		cy.contains( 'label', /Jump to Recipe/ ).should( 'exist' );
		cy.get( '#tasty-recipes-settings-tab' )
			.find( 'label[for="tasty-recipes-quick-links-jump"]' )
			.should( 'exist' )
			.parent()
			.find( 'input[type="checkbox"]' )
			.then( ( $el ) => {
				if ( ! $el.prop( 'checked' ) ) {
					cy.wrap( $el ).check( { force: true } );
				}
			} );

		cy.contains( 'label', /Print Recipe/ ).should( 'exist' );
		cy.get( '#tasty-recipes-settings-tab' )
			.find( 'label[for="tasty-recipes-quick-links-print"]' )
			.should( 'exist' )
			.next( 'label.tasty-checkbox-container' )
			.find( 'input[type="checkbox"]' )
			.then( ( $el ) => {
				if ( ! $el.prop( 'checked' ) ) {
					cy.wrap( $el ).check( { force: true } );
				}
			} );

		cy.contains( 'label', /Leave a Review/ ).should( 'exist' );
		cy.get( '#tasty-recipes-settings-tab' )
			.find( 'label[for="tasty-recipes-quick-links-rating"]' )
			.should( 'exist' )
			.parent( '.tasty-form-field' )
			.find( '.tasty-form-field-input-wrapper input[type="checkbox"]' )
			.then( ( $el ) => {
				if ( ! $el.prop( 'checked' ) ) {
					cy.wrap( $el ).check( { force: true } );
				}
			} );

		cy.log( 'Quick Links checkbox states updated successfully' );
		cy.get( 'input[name="tasty_recipes_quick_links[]"]:checked' ).then(
			( $els ) => {
				const values = [ ...$els ].map( ( el ) => el.value );
				expect( values ).to.include.members( [
					'jump',
					'print',
					'rating',
				] );
			}
		);

		cy.log( 'Validate Recipe Card Buttons dropdowns' );
		cy.get( '#tasty_recipes_card_buttons\\[first\\]' )
			.find( '.tasty-react-select__single-value' )
			.should( 'contain.text', 'Print' );

		cy.get( '#tasty_recipes_card_buttons\\[first\\]' )
			.find( '.tasty-react-select__control' )
			.click();

		cy.get( '#tasty_recipes_card_buttons\\[first\\]' )
			.find( '.tasty-react-select__menu' )
			.should( 'be.visible' )
			.within( () => {
				cy.contains(
					'.tasty-react-select__option:not(.tasty-react-select__option--is-disabled)',
					'Print'
				);

				cy.contains(
					'.tasty-react-select__option--is-disabled',
					'Pin this recipe'
				);
				cy.contains(
					'.tasty-react-select__option--is-disabled',
					'Mediavine Grow.me Save'
				);
				cy.contains(
					'.tasty-react-select__option--is-disabled',
					'Slickstream Favorites'
				);
			} );

		cy.get( 'input[name="tasty_recipes_card_buttons[first]"]' ).should(
			'have.value',
			'print'
		);

		cy.get( '#tasty_recipes_card_buttons\\[second\\]' )
			.find( '.tasty-react-select__single-value' )
			.should( 'contain.text', 'Off' );

		cy.get( '#tasty_recipes_card_buttons\\[second\\]' )
			.find( '.tasty-react-select__control' )
			.click();

		cy.get( '#tasty_recipes_card_buttons\\[second\\]' )
			.find( '.tasty-react-select__menu' )
			.should( 'be.visible' )
			.within( () => {
				cy.contains(
					'.tasty-react-select__option:not(.tasty-react-select__option--is-disabled)',
					'Off'
				);
				cy.contains(
					'.tasty-react-select__option:not(.tasty-react-select__option--is-disabled)',
					'Print'
				);
				cy.contains(
					'.tasty-react-select__option--is-disabled',
					'Pin this recipe'
				);
				cy.contains(
					'.tasty-react-select__option--is-disabled',
					'Mediavine Grow.me Save'
				);
				cy.contains(
					'.tasty-react-select__option--is-disabled',
					'Slickstream Favorites'
				);
			} );

		cy.log( 'Validate Default Author Link field' );
		cy.get( 'label[for="tasty-recipes-default-author-link"]' ).should(
			'contain.text',
			'Default Author Link'
		);

		cy.get( '#tasty-recipes-default-author-link' )
			.should( 'exist' )
			.should( 'have.attr', 'type', 'text' )
			.should( 'have.value', '' );

		cy.get( '#tasty-recipes-default-author-link' )
			.type( 'https://example.com/author' )
			.should( 'have.value', 'https://example.com/author' );

		cy.log( 'Validate ShareASale Affiliate ID field and helper links' );
		cy.get( '#tasty-recipes-shareasale-affiliate-id' )
			.closest( '.tasty-form-field' )
			.find( '.tasty-form-helper a' )
			.each( ( $a ) => {
				const href = $a.prop( 'href' );

				expect( href ).to.include( 'shareasale.com' );

				cy.request( href ).its( 'status' ).should( 'eq', 200 );
			} );

		cy.get( '.tasty-settings-banner-right a' )
			.should( 'have.text', 'Upgrade to PRO' )
			.should( 'have.attr', 'href' )
			.then( ( href ) => {
				cy.request( href ).its( 'status' ).should( 'eq', 200 );
			} );

		cy.get( '#tasty_recipes_quick_links_style' )
			.should( 'exist' )
			.find( '.tasty-react-select__control' )
			.should( 'have.attr', 'aria-disabled', 'true' )
			.and( 'have.class', 'tasty-react-select__control--is-disabled' );

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
			// {
			// 	label: 'Enable Cook Mode',
			// 	id: 'tasty_recipes_cook_mode',
			// 	helperText: 'Show Cook Mode toggle to let visitors prevent sleep mode for supported devices.'
			// },
			{
				label: 'Scaling',
				id: 'tasty_recipes_disable_scaling',
				helperText: 'Allow visitors to scale the recipe ½, 2x, or 3x.',
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

		premiumToggles.forEach( ( { label, id, helperText } ) => {
			cy.contains( 'label', label ).should( 'exist' );

			const field = cy.contains( '.tasty-form-field', label );

			field.should( 'contain.text', helperText );

			cy.get( `#${ id }` ).should( 'exist' ).and( 'be.disabled' );

			field.find( '.tasty-slider' ).first().click( { force: true } );

			cy.get( '.tasty-framework-modal-header' )
				.should( 'be.visible' )
				.and( 'contain.text', label );

			cy.get( '.tasty-framework-modal-body' )
				.should( 'contain.text', 'This is a premium feature' )
				.and( 'contain.text', 'Consider upgrading to a paid plan' );

			cy.get( '.tasty-framework-modal-body a' )
				.should( 'contain.text', 'Upgrade Now' )
				.and( 'have.attr', 'href' )
				.then( ( href ) => {
					cy.request( href ).its( 'status' ).should( 'eq', 200 );
				} );

			cy.get( '#tasty_recipes_upgrade_modal' )
				.find( '.tasty-framework-close-modal' )
				.click( { force: true } );
		} );
	} );

	it( 'should validate recipes Converters page content', () => {
		cy.log( 'Navigate to Converters tab' );
		cy.contains( '.tasty-nav-tab', 'Converters' ).click();
		cy.url().should(
			'include',
			'admin.php?page=tasty-recipes&tab=converters'
		);

		cy.log( 'Validating page header and description' );

		cy.get( '.tasty-recipes-converter-header' ).within( () => {
			cy.contains( 'h4', 'Import Existing Recipes' ).should(
				'be.visible'
			);

			cy.contains(
				'Just in case, we recommend backing up your database before converting your recipes in bulk to Tasty Recipes.'
			).should( 'be.visible' );

			cy.log( 'Validating Learn More link' );

			cy.contains( 'a', 'Learn more about backing up your database.' )
				.should( 'exist' )
				.and( 'have.attr', 'target', '_blank' )
				.and( 'have.attr', 'rel', 'noopener noreferrer' );
		} );

		cy.log( 'Validating helper text' );

		cy.get( '.tasty-recipes-converters' )
			.contains( 'Select an option below to start converting recipes.' )
			.should( 'be.visible' );

		cy.log( 'Validating converter buttons' );

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

		cy.get( '.tasty-recipes-converters-options button' )
			.should( 'have.length', converters.length )
			.each( ( $btn, index ) => {
				cy.wrap( $btn )
					.should( 'be.disabled' )
					.and( 'contain.text', converters[ index ] );
			} );
	} );

	it( 'should validate recipes Get Started page content', () => {
		cy.log( 'Navigate to Get Started tab' );
		cy.contains( '.tasty-nav-tab', 'Get Started' ).click();
		cy.url().should( 'include', 'admin.php?page=tasty-recipes&tab=about' );

		cy.contains( 'h2', 'Welcome to Tasty Recipes' ).should( 'be.visible' );
		cy.contains(
			'Tasty Recipes is a fast, simple, and SEO-optimized recipe plugin'
		).should( 'be.visible' );

		cy.contains( 'h2', 'Getting Started' ).should( 'be.visible' );
		cy.contains( 'Tasty Recipes is extremely easy to configure' ).should(
			'be.visible'
		);
		cy.contains( 'a', 'Visit Settings' )
			.should( 'be.visible' )
			.and( 'have.attr', 'href' )
			.then( ( href ) => {
				cy.request( href ).its( 'status' ).should( 'eq', 200 );
			} );

		cy.contains( 'h2', 'Convert Recipes' ).should( 'be.visible' );
		cy.contains(
			'Tasty Recipes converts recipes from many sources'
		).should( 'be.visible' );

		cy.log( 'Validate recipe conversion links' );

		cy.contains( 'a', 'converting recipes individually' )
			.should( 'be.visible' )
			.and( 'have.attr', 'href' )
			.then( ( href ) => {
				cy.request( href ).its( 'status' ).should( 'eq', 200 );
			} );

		cy.contains( 'a', 'converting recipes in bulk' )
			.should( 'be.visible' )
			.and( 'have.attr', 'href' )
			.then( ( href ) => {
				cy.request( href ).its( 'status' ).should( 'eq', 200 );
			} );

		cy.log( 'Validate Create New Recipes and Documentation sections' );

		cy.contains( 'h2', 'Create New Recipes' ).should( 'be.visible' );
		cy.contains( 'Creating new recipes is easy' ).should( 'be.visible' );

		cy.contains( 'h2', 'Visit Our Documentation' ).should( 'be.visible' );
		cy.contains( 'We pride ourselves on our plugin documentation' ).should(
			'be.visible'
		);

		cy.log( 'Validate documentation link' );

		cy.contains( 'a.tasty-button', 'Visit Documentation' )
			.should( 'be.visible' )
			.and( 'have.attr', 'href' )
			.then( ( href ) => {
				cy.request( href ).its( 'status' ).should( 'eq', 200 );
			} );

		cy.log( 'Validate onboarding consent checkbox' );
		cy.get( 'input[name="tasty_recipes_onboarding_consent_tracking"]' )
			.should( 'be.visible' )
			.check( { force: true } );
	} );
} );
