describe('WP Tasty recipe page', () => {
	beforeEach(() => {
		cy.login();
		cy.visit('/wp-admin/edit.php?post_type=tasty_recipe');
		cy.viewport(1280, 720);
	});

	it('should validate recipes page content', () => {

		cy.log('Validating header elements');
		cy.get('.tasty-framework-header-logo img').should('be.visible')
		cy.get('.tasty-framework-header-title')
			.should('be.visible')
			.and('contain.text', 'Recipes');

		cy.get('.tasty-framework-header-support a')
			.should('have.attr', 'href')
			.and('include', 'wptasty.com/support');

		cy.log('Validating navigation tabs');
		const tabs = [
			{ label: 'Recipes', href: '/wp-admin/edit.php?post_type=tasty_recipe' },
			{ label: 'Design', href: 'admin.php?page=tasty-recipes&tab=design' },
			{ label: 'Settings', href: 'admin.php?page=tasty-recipes&tab=settings' },
			{ label: 'Converters', href: 'admin.php?page=tasty-recipes&tab=converters' },
			{ label: 'Get Started', href: 'admin.php?page=tasty-recipes&tab=about' }
		];

		cy.get('.tasty-nav-tab').should('have.length', tabs.length);

		tabs.forEach(tab => {
			cy.contains('.tasty-nav-tab', tab.label)
				.should('be.visible')
				.and('have.attr', 'href')
				.and('include', tab.href);
		});

		cy.log('Validating count of existing recipes');
		cy.get('.subsubsub .all a .count')
			.should('contain.text', '(0)');

		cy.log('Validate empty state text');
		cy.contains('.tasty-recipes-empty-state p', 'No recipes yet?')
			.should('have.text', 'No recipes yet? Click "Create Recipe" to start your culinary journey!');
		cy.contains('button', 'Create Recipe').should('be.visible');

	});

	it('validates Recipes Design page content', () => {

		cy.log('Navigate to Design tab');
		cy.contains('.tasty-nav-tab', 'Design').click();
		cy.url().should('include', 'admin.php?page=tasty-recipes&tab=design');

		cy.log('Validate header elements');
		cy.get('.tasty-tabs-submit button#tasty-nav-submit')
			.should('be.visible')
			.and('contain.text', 'Save Changes');

		cy.get('.tasty-recipes-card-chooser')
			.should('be.visible')
			.find('button')
			.its('length')
			.should('be.gt', 0);

		cy.contains('h3', 'Default (1/7)').should('be.visible');

		cy.log('Validate template preview');
		cy.get('#tasty-recipes-card-preview')
			.should('be.visible')
			.and('have.attr', 'title', 'Template Preview')
			.and('have.attr', 'src')
			.and('include', 'tasty_recipes_preview');

		cy.log('Validate color fields');
		const colorFields = [
			'Button Color',
			'Button Text Color',
			'Detail Label Color',
			'Detail Value Color'
		];

		colorFields.forEach(label => {
			cy.contains('label', label).should('be.visible');
		});

		cy.get('.tasty-swatch').should('have.length.at.least', colorFields.length);

		cy.contains('h3', 'Recipe Title (H2)').should('be.visible');

		const expectedOptions = [
			'Default Capitalization',
			'Uppercase Capitalization',
			'Normal Capitalization',
			'Lowercase Capitalization'
		];

		cy.log('Validate H2 Transform dropdown');
		cy.get('#tasty_recipes_customization\\[h2_transform\\] .tasty-react-select__control')
			.click({ force: true });

		cy.get('.tasty-react-select__menu .tasty-react-select__option')
			.should('have.length', expectedOptions.length)
			.each(($opt, i) => {
				cy.wrap($opt).should('have.text', expectedOptions[i]);
			});

		cy.contains('h3', 'Recipe Subtitles (H3)').should('be.visible');

		const expectedH3 = expectedOptions;

		cy.log('Validate H3 Transform dropdown');
		cy.get('#tasty_recipes_customization\\[h3_transform\\] .tasty-react-select__control')
			.click({ force: true });

		cy.get('.tasty-react-select__menu .tasty-react-select__option')
			.should('have.length', expectedH3.length)
			.each(($opt, i) => {
				cy.wrap($opt).should('have.text', expectedH3[i]);
			});

		cy.contains('h3', 'Body').should('be.visible');

		cy.get('input[name="tasty_recipes_customization[body_color]"]')
			.should('exist');

		cy.get('.tasty-color-wrap .tasty-swatch')
			.should('exist')
			.and('be.visible');

		cy.contains('h3', 'Star Ratings').should('be.visible');

		cy.log('Validate Star Ratings dropdown');
		cy.get('label[for="tasty_recipes_customization\\[star_ratings_style\\]"]')
			.parent()
			.find('.tasty-react-select__control')
			.click();

		const starStyleOptions = ['Solid', 'Outline'];

		cy.get('.tasty-react-select__option')
			.should('have.length', starStyleOptions.length)
			.each(($opt, i) => {
				cy.wrap($opt).should('have.text', starStyleOptions[i]);
			});
	});
});
