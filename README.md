# Tasty Recipes #
**Contributors:** [wptasty](https://profiles.wordpress.org/wptasty/), [danielbachhuber](https://profiles.wordpress.org/danielbachhuber/)  
**Tags:** food blogging, recipes  
**Requires at least:** 4.6  
**Tested up to:** 6.5.3  
**Requires PHP:** 5.6  
**Stable tag:** 3.12.3  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Tasty Recipes is the easiest way to publish recipes on your WordPress blog.

## Description ##

Tasty Recipes makes it possible for anyone to publish their favorite recipes to the world. Your recipes will automatically be optimized for search engines and match your theme's design.

### Optimized for humans and machines ###

In the WordPress editor, embed a recipe into your post, page or custom post type by using the "Add Recipe" button. When the recipe is inserted into the content, you'll see a preview of the rendered recipe so you know exactly what it's going to look like when published.

On the frontend of your website, your published recipe uses standard markup to fit in naturally with the design of your theme. Readers can click the "Print" button to open a version of the recipe formatted specifically for print.

### Schema.org microdata and JSON+LD ###

Tasty Recipes automatically includes Schema.org microdata and JSON+LD to make it easy for Google and other search engines to read your recipes. When entering in your recipe's ingredients or instructions, make sure to create an ordered or unordered list. The list will signal to Tasty Recipes that these items should be marked up with Schema.org microdata and JSON+LD.

### Pain-free switching from EasyRecipe ###

EasyRecipe user? Tasty Recipes can fully convert your existing EasyRecipe recipes into the Tasty Recipes format. First, perform a couple of test conversions on existing posts. If your posts support revisions, Tasty Recipes creates new revisions so you can safely roll back to the EasyRecipe markup. Once you've verified your recipes convert as expected, use the batch conversion tool in "Settings" -> "Tasty Recipes" to migrate any remaining existing EasyRecipe recipes.

## Installation ##

The Tasty Recipes plugin can be installed much like any other WordPress plugin.

1. Upload the plugin ZIP archive file via "Plugins" -> "Add New" in the WordPress admin, or extract the files and upload them via FTP.
2. Activate the Tasty Recipes plugin through the "Plugins" list in the WordPress admin.

With Tasty Recipes, there aren't any confusing settings to configure or customizations you need to worry about. You can now share your favorite recipes with the world!

## Changelog ##
### 3.12.3 ###
* Enhancement: Move CSS to the head to avoid any content shifts for better SEO.
* Enhancement: After a new comment is added, clear the BigScoots page cache for that page so the new comment will appear when the page is refreshed.
* Fix: Remove all Hubbub tools on the recipe print page.
* Fix: Remove the nonce checking for logged out users when leaving a review. This prevents page caching from causing issues when leaving a review.
* Fix: Updates for valid HTML. This includes removing type="script" and type="style" and trailing slashes.
* Fix: The comments were not always showing if any anonymous comments were included.
* Fix: If there are extra empty lines in the notes, don't add numbers for the empty lines in the recipe.
* Fix: Recipe rating stars disappeared in the comment form when the post content contained the words "tasty-recipes-ID-jump-target"
* Code: Updates for more recent standards and consistency.

### 3.12.2 ###
* Enhancement: Turn off anonymous ratings by default.
* Fix: Comment now shows in comment field when updating a rating/comment.

### 3.12.1 ###
* Fix: Ratings with no comment were being flagged as invalid structured data.
* Fix: Wrong star appearance after leaving a rating in some cases.
* Fix: Wrong star appearance when the average rating was x.9.

### 3.12 ###
* New: One-click ratings to improve leaving ratings directly from the recipe card.
* New: Administrators can now update star ratings in the backend.
* New: Remove keywords from recipe card frontend and keep it only in recipe schema.
* Enhancement: Star ratings now have better styling and improved interactions on hover and click/tap.
* Fix: Some ratings didn't work properly if comments were disabled.
* Fix: Rating star spacing was wrong in some cases due to CSS minification.
* Fix: In some themes using blocks, ratings didn't show properly in the comments section.
* 3rd party compatibility: Fix Console error when activating the HTML minification feature in SiteGround plugin.

### 3.11.1 ###
* Enhancement: When comments are disabled, you'll no longer see ratings links.
* Fix: The video schema was generating errors. This update matches the new date format for videos.
* Fix: Empty lines in instructions were sometimes causing issues with the recipe schema
* Fix: Added compatibility with SiteGround optimizer HTML minification
* Fix: Some people were seeing warnings if the button color wasn't set
* Fix: When a license check to wptasty.com fails, the license will show "Error" instead of "Expired". This will now recheck after 1 minute instead of 2 days.
* Enhancement: "Leave a review" quicklink is now "Leave a Review" for consistency
* Fix: The converter metabox in the edit post sidebar wasn't showing up

### 3.11 ###
* New: The jump links at the top of the page can now be formatted as buttons too!
* New: Improve the printed recipes. The options show by default, the font size option changes all the text on the page, the result looks more professional without borders and background colors.
* New: Recipe times now support multiple languages. Just make sure the time strings for Tasty Recipes are translated on your site, and the time schema and total times will now be correct.
* Fix: The icons are back. We were a little too aggressive with our security update.
* Fix: Multiple canonical tags were showing on the print page when Yoast was installed.
* Fix: The Snap template sometimes had a blank page when printing from Firefox.

### 3.10.1 ###
* Fix: Console error related to recipe pin button.

### 3.10 ###
* New: New admin menu for all tasty plugins.
* New: New license manager page to make it easier to download/install/activate all tasty plugins.
* Enhancement: Made the "Bold" template notes section look better on mobile; ordered lists now have rounded circles around them, exactly like on desktop.
* Enhancement: Made recipe pin button crawlable to fix lighthouse warning "Links are not crawable"
* Enhancement: Made instacart button responsive.
* 3rd party compatibility: Now more compatible with Elementor page builder by fixing deprecation errors.
* 3rd party compatibility: Added compatibility with Akismet plugin, now ratings without comments are not considered spam.
* Fix: Thrive Architect now shows quick links.
* Fix: Added the ability for the post/recipe content to have the words "Jump to recipe"
* Fix: Comment form no longer prompts for a required comment for 5-star ratings on themes that don't support HTML5.

### 3.9.1 ###
* Hotfix: Enhance the way we handle review rating stars in custom themes.

### 3.9 ###
* New: Don't require a comment for a 4 or 5 star review.
* New: Add an option to include average rating or "leave a review" link in quick links.
* New: If the recipe has no reviews yet, show "no reviews" in the card with a link to leave a review.
* New: Add a color setting to set the stars a different color to help them stand out.
* Fix: There was an error when adding reviews to the top of the post using a php code example.
* Fix: The icons were too big when printing a recipe using hte Snap template.
* Fix: The wrong ingredient was crossed out if there were two that are exactly the same.

### 3.8.1 (March 22, 2023) ###
* Fix: Recipe Card not showing up in some cases with the latest release.

### 3.8.0 (March 21, 2023) ###
* New: Added an Instacart button to recipe cards for shoppable recipes.
* New: Added a Cook mode toggle to keep the screen from going dark.
* New: Save the post id with the recipes for easier integrations.
* New: Disable all notices at once to convert recipes from another plugin.
* Fix: Extra whitespace in the ingredients list or instructions was creating invalid recipe schema.

### 3.7.4 (January 11, 2023) ###
* New: The printed Snap template now includes recipe URL, author, and other extra details.
* Fix: Added compatibility for last version of Yoast SEO. Without this update, the recipe schema may not be included for Google on the page when using Yoast SEO 19.14+.
* Fix: When printed, the Snap template sometimes included empty space for a page break.
* Fix: Make "Recipe rating" translatable.
* Fix: Add screenreader support for selecting a star rating for a review.

### 3.7.3 (October 6, 2022) ###
* Introduces a `tasty_recipes_print_view_buttons` filter for changing option buttons on print views.
* Improves the print view to include previously selected unit and scale values.
* Use the medium image size when converting WP Recipe Maker instructions.
* Remove reliance on the PHP mbstring extension.

### 3.7.2 (June 21, 2022) ###
* Fixes issue in some fractional conversions between metric and US customary.

### 3.7.1 (May 24, 2022) ###
* Updates placement of 'Description Color' in settings page.
* Fixes issue with video input field not displaying the video on Snap recipe card template.
* Adds support for primary, secondary, and icon color setting on Snap recipe card template.

### 3.7.0 (May 11, 2022) ###
* Introduces Snap recipe card template.
* Handles pure numbers in the unit amount parser.
* Adds a ‘Tasty Recipes Print View Defaults’ filter for changing defaults.
* Adds Tasty Recipes integration with the Rank Math Content Analysis API.
* Adds support to properly display ‘1/6’ when ‘1/3’ is halved.

### 3.6.4 (April 5, 2022) ###
* Adds support for embedding YouTube Shorts URLs.

### 3.6.3 (March 30, 2022) ###
* Updates WP Recipe Maker converter to properly migrate recipe videos.
* Introduces a `tasty_recipes_quick_links` filter to allow modifying Quick Links.
* Adds more specific rating styles to avoid theme conflicts.
* Fixes issue where percentages would be matched as units in the scaling buttons.

### 3.6.2 (February 14, 2022) ###
* Ensures 'calories' is always appended in nutrition schema output, regardless of where it's from.

### 3.6.1 (February 10, 2022) ###
* Appends 'calories' to Nutrifox nutrition schema output to fix Google Search Console warning.
* Allows '8' as the denominator when rounding if the value is '1/8'.
* Ignores `trash` posts when finding posts to convert.

### 3.6.0 (January 10, 2022) ###
* Refactors 'Copy to Clipboard' to use ingredients listed in the browser, to ensure scaling and unit conversion are applied.
* Adds a checkbox to enable automatic conversion of older recipes.
* Detects Mediavine videos in blocks in the post for inclusion in JSON+LD schema.
* Reintroduces scaling and unit conversion in print preview.
* Adds `style="display: none !important;"` to ensure script and style tags aren't ever displayed.
* Introduces a `tasty_recipes_default_author_name` filter to allow the default author name to be modified.
* Introduces a `tasty_recipes_scalable_amounts` filter to allow the scale amounts to be modified.
* Introduces a `tasty_recipes_json_ld_image_sizes` filter to allow JSON+LD image sizes to be modified.
* Introduces a `tasty_recipes_card_top_details` filter to allow Bold card top details to be configurable.
* Introduces a `tasty_recipes_customization_settings` filter to allow changing customization settings based on context (e.g. a different color for print).

### 3.5.0 (October 13, 2021) ###
* Adds ingredient checkboxes.
* Adds improved print controls and card styles for print.
* Switches to `body-color.color` on all recipe cards for inline nutrition details.
* Bold card changes: hides the 'Description' heading; moves Cook Time, Prep Time, Additional Time to 'Other Details'
* Recalculates recipe ratings after wpDiscuz comment submission.
* Introduces a `tasty_recipes_nutrifox_display_style` filter to make it possible to change the Nutrifox display style when a custom card is used.
* Introduces a `tasty_recipes_use_yoast_schema` filter to allow Tasty Recipes schema to be used even when Yoast is active.

### 3.4.0 (August 30, 2021) ###
* Adds unit conversion support to all recipe card templates.
* Adds button support to all recipe card templates.
* Adds support for displaying Nutrifox data directly in the recipe card, instead of as a label.
* Persists the original fraction type (vulgar vs. standard) when using unit conversion UX.
* Adds settings and per-recipe controls for enabling/disabling scaling.
* Adds per-recipe control for enabling/disabling unit conversion.

### 3.3.1 (August 3, 2021) ###
* Includes ratings data in the Create conversion process.
* Enhances Thrive theme compatibility.
* Properly format amounts as fractions when only unit conversion is used.
* Avoids appending the unit for the second quantity.
* Ensures the secondary color applies to the background color on the Modern Compact card.

### 3.3.0 (June 4, 2021) ###
* Adds US customary to metric conversion.
* Adds recipe converter for Mediavine Create.
* Fixes editing and the print button when a Tasty Recipe block is nested in a parent block.
* Saves the Tasty Recipes rating when Mediavine Trellis submits a comment.
* Hides the print button in the editor.

### 3.2.3 (May 12, 2021) ###
* Adds an `id` attribute to the tasty-recipe-video-embed element.

### 3.2.2 (April 1, 2021) ###
* Fixes issue where changes to prep time could be lost when clicking '+ Time' to add an additional time.
* Hides the copy icon for print view.
* Adds toolbar buttons for Creating and Editing in the block editor.
* Fixes deprecation notice for `wp.serverSideRender`.

### 3.2.1 (March 29, 2021) ###
* Improves the WP Recipe Maker converter to process times as '2 hours 5 minutes' instead of '165 minutes'.

### 3.2.0 (March 22, 2021) ###
* Adds an Elementor widget.
* Adds basic Thrive Architect compatibility by enabling 'Add Recipe' button in WordPress Content Module.
* Adds 'Additional Time' feature.

### 3.1.1 (February 18, 2021) ###
* Fixes placement of clipboard copy success message on Modern Compact.

### 3.1.0 (February 17, 2021) ###
* Adds card button settings, with Slickstream and Grow, for the Bold and Fresh templates.
* Adds an Outline star rating style, configurable in the Customizer.
* Adds copy to clipboard feature.
* Makes sure the recipe card thumbnail always appears with Jetpack.
* Makes Quick Links available as a shortcode: `[tasty-recipes-quick-links]`.
* Moves the jump target to above the recipe card.
* Enables the TinyMCE 'removeformat' button to make it easy to remove formatting.
* Ensures Quick Links only render on blog posts, not the homepage or archives.

### 3.0.2 (December 18, 2020) ###
* Ensures our 'Pin' button works when Tasty Pins is active.
* Adds support for migrating old WPRM data.

### 3.0.1 (November 30, 2020) ###
* Introduces a `tasty_recipes_updated_recipe_rating` action which fires when a recipe's rating is updated.
* Fixes issue where a backslash would be added to a recipe card social description with apostrophes.
* Ensures rating stars appear when the comment form doesn't have a `.comment-form` class.
* Ensures 'Jump To Recipe' quick links don't render for excerpts.
* Ensures paragraph tags are removed from the schema description.
* Minifies SVG sprite and CSS when printing with recipe card.
* Adds a `title=""` attribute to the Nutrifox iframe.
* Supports another Mediavine endpoint.

### 3.0.0 (November 3, 2020) ###
* Adds a recipe card template customizer to the settings page.
* Various recipe card design cleanup.
* Switches print template to use `noindex, follow`.
* Has the 'Pin' button open a bookmarklet popup.
* Allows Convert Recipe notices to be dismissed.
* Allows the print URL to be modified with the `tasty_recipes_print_url` filter.

### 2.8.2 (August 26, 2020) ###
* Fixes the regex matching Tasty Recipes shortcodes to not pick up others (i.e. pagebuilders).
* Processes `<b>` elements as the Guided Recipe 'name' too.
* Ensures 'Total Time' field is empty until it's actually used.
* Display Bold and Fresh recipe card icons from one SVG sprite.

### 2.8.1 (July 28, 2020) ###
* Only `wp_enqueue_media()` in Manage Posts to avoid breaking block editor.

### 2.8.0 (July 22, 2020) ###
* Adds an 'Edit Tasty Recipe' button to Quick Edit.
* Guided Recipes: Generates unique URLs for each recipe instruction step.
* Guided Recipes: Generates recipe instruction step 'name' field based on bolded text.
* When Yoast SEO is active, filters Yoast's `<meta name="robots">` on print to avoid duplicate tags.
* Adds `rel="noreferrer noopener"` to Instagram footer link.
* Fixes print view for non-food Tasty Recipe blocks.
* Applies the print endpoint at the right spot for permalinks w/ query args.
* Hides SVGs from screen readers.
* Updates recipe card images to avoid stretching.
* Updates the EDD Updater to v1.7.1.

### 2.7.1 (June 2, 2020) ###
* Fixes `</div>` placement for Default, Elegant, and Simple recipe cards.

### 2.7.0 (May 26, 2020) ###
* Improves Yoast SEO integration to nest Recipe under the Article schema.
* Accessibility and markup improvements to the video on/off toggle.
* Disables the force pin image feature by default; can be re-enabled with the `tasty_recipes_force_pin_image_url` filter.
* Styling tweaks to the Bold card.
* Allows users to add custom nutrition attributes with the `tasty_recipes_nutrition_attributes` filter.
* Adds a class to the recipe title for improved compatibility with Feast advanced jump to links.
* Adds a TTL to YouTube and Vimeo oEmbed data so it refreshes every seven days.
* Supports scaling ingredients with grams.
* Uses SVGs throughout Bold and Fresh templates.
* Uses the localized version of the Diet when displaying on the frontend.
* Adds alt text to the Tasty Recipes logo.
* Splits `recipeYield` schema value into an array when it's not numeric to address Google warning.

### 2.6.1 (May 20, 2020) ###
* Uses the player page URL as the `contentUrl` for YT and Vimeo to avoid Google Rich Snippet error.

### 2.6.0 (March 4, 2020) ###
* Adds support for adding videos to recipe instructions (with player controls, JSON+LD schema integration, and and on/off switch on the frontend).
* Also parses images within recipe instructions, to include in JSON+LD schema.
* Adds a 'Diet' field to recipes and renders in the card.
* Introduces `tasty_recipes_only_recipe_in_yoast_schema` filter to disable the article schema when a recipe schema is present.
* Introduces `tasty_recipes_human_time_formats` filter for modifying the formats used for displaying cooking, prep, and total time.
* Includes a 'large' image size in the 'Pin Recipe' share URL so that the recipe is shared with an image even when images are no-pinned on the post.
* Updates WPUR converter to handle shortcodes inside the shortcode block.
* Fixes issue where negative ratings could appear in JSON+LD schema.
* Updates EDD updater to v1.6.19.

### 2.5.2 (January 27, 2020) ###
* Fixes print page compatibility issue with Beaver Builder.

### 2.5.1 (January 21, 2020) ###
* Fixes print page compatibility issue with Genesis.

### 2.5.0 (November 4, 2019) ###
* Enriches YouTube video schema with with description, duration, and upload date.
* Includes seconds in video schema durations.
* Improves Nutrifox embedding methodology to make the nutrition card load faster.
* Adds rel="canonical" to the print template.
* Fixes an issue where recipe scaling didn't work on sites that minified their frontend code.

### 2.4.0 (October 1, 2019) ###
* Correctly displays Nutrifox labels in the block editor.
* Removes 'nofollow' from print template robots tag.
* Only adds trailing slash to print permalink when enabled for site to avoid unexpected redirect.
* Fixes scaling for an ingredient with a number immediately next to a vulgar fraction.
* Fixes incorrect counting for unordered lists within ordered lists in the Fresh and Bold templates.

### 2.3.1 (July 30, 2019) ###
* Fixes "Jump To" button priority so it appears below all other added items.
* Properly supports the 'Additional CSS class' feature in the block editor.
* Fixes parsing the description from the AdThrive shortcode.
* Fixes duplicate `<hr>` displayed in Fresh card.

### 2.3.0 (May 8, 2019) ###
* Integrates JSON+LD with Yoast SEO when Yoast is active.
* Adds a few integration points for Tasty Links equipment support.
* Ignores empty WPRM blocks when identifying posts to convert.
* Updates Cookbook converter to also convert shortcode instances.
* Fixes JavaScript error experienced post-conversion.

### 2.2.0 (April 10, 2019) ###
* Introduces 'Default Author Link' with a per-recipe override.
* Makes a few improvements to the ingredient scaling algorithm.
* Refactors instruction schema generation to use 'HowToSection' and 'HowToStep' types.

### 2.1.0 (March 11, 2019) ###
* Introduces ingredient scaling to recipes; can be disabled with `tasty_recipes_scalable` filter.
* Allow recipes to be converted individually within the block editor.
* Removes the inline metadata for nutrition markup in the Bold card.
* Updates WP Recipe Maker converter to support WPRM blocks.
* Updates Zip Recipe converter to support ZR blocks.
* Casts JSON+LD rating as strings to avoid PHP 7.1 `json_encode()` bug.
* Appends PHP and plugin version to EDD update requests.

### 2.0.0 (January 15, 2019) ###
* Updates Block Editor (Gutenberg) integration for Tasty Links compatibility.
* Ignores nutrition fields in WPRM converter when no calories are present.
* Correctly parses ingredients and instructions with multiple `<li>` on the same line.
* Fixes PHP notice when 'external_plugins' isn't yet set in TinyMCE configuration.

### 1.9.1 (December 11, 2018) ###
* Fixes issue where Tasty Recipe couldn't be edited in Gutenberg after the page had reloaded.

### 1.9.0 (December 3, 2018) ###
* Script dependency updates for Gutenberg 4.5.0 compatibility.
* Auto-fills default recipe title in Gutenberg.
* Adds option to disable the Google JSON LD markup for a non-food recipe in Gutenberg.
* Introduces `tasty_recipes_limit_schema_reviews` filter for limiting the number of reviews in Google JSON LD markup.
* In Google JSON LD markup, if no author is set for the recipe, the post author is used instead.
* Adds 'fitvidsignore' to responsive video iframes by default.
* Fixes floats in Modern Compact stylesheet.

### 1.8.1 (October 11, 2018) ###
* Limits 'Jump to Recipe' and 'Print Recipe' to only display on single post views; introduces `tasty_recipes_should_prepend_jump_to` filter for more granular control.

### 1.8.0 (October 8, 2018) ###
* Adds Tasty Recipes affiliate functionality which automatically adds UI below the recipe card.
* Introduces 'Jump to Recipe' and 'Print Recipe' buttons which can be enabled from backend.
* Processes comment and non-comment ratings from Zip Recipes Pro.
* Removes license nag from Post editor because of layout conflicts.
* Fixes fatal error when using Tasty Recipes Gutenberg block.

### 1.7.0 (September 5, 2018) ###
* Includes reviews in Google JSON LD markup.
* Includes post publish date as `datePublished` in Google JSON LD markup.
* Switches from type 'Thing' to type 'Person' in Google JSON LD markup author attribute.
* Supports a `disable-json-ld` shortcode flag for disabling JSON LD (e.g. non-food recipe posts).
* Displays Cookbook, Simple Recipe Pro, and WP Recipe Maker comment ratings when they exist.
* Also matches single quotes when processing `<iframe>` embeds for videos.

### 1.6.1 (August 1, 2018) ###
* Fixes issue where recipe print template wouldn't load (strict variable comparison for the loss).

### 1.6.0 (July 31, 2018) ###
* Adds a Tasty Recipes block for Gutenberg compatibility.
* Introduces `tasty_recipes_enable_responsive_iframes` filter for permitting responsive iframes to be disabled.

### 1.5.0 (July 12, 2018) ###
* Generates JSON+LD metadata for AdThrive, Mediavine, Vimeo, and YouTube videos within post content.
* Only applies ratings hover behavior on desktop, to ensure compatibility with mobile.
* Fixes issue with Cookbook converter where nutrition could be set to `0` values.

### 1.4.1 (June 19, 2018) ###
* Ensures AdThrive video embeds report a `embedUrl` to JSON+LD markup.

### 1.4.0 (June 14, 2018) ###
* Adds support for embedding a video into the recipe card, and incuding the video's metadata in JSON+LD structured data.
* Displays keywords in all recipe card templates, for SEO best practices.
* Updates Simple Recipe Pro converter to handle ingredients stored as objects.
* Rearranges details fields to improve the UI.

### 1.3.0 (May 9, 2018) ###
* Includes full capatibility with Tasty Links.
* Adds a "Keywords" field and includes the data in Google Schema output.
* Introduces `tasty_recipes_convert_easyrecipe_image_id` filter for modifying the image ID when converting Easy Recipe recipes.

### 1.2.1 (Mar. 28, 2018) ###
* Fixes PHP fatal error caused by `get_current_screen()` not being defined in certain contexts.

### 1.2.0 (Mar. 21, 2018) ###
* Adds recipe converter for WordPress.com.
* Updates ZipList converter to also handle Zip Recipes.
* Persists Simple Recipe Pro ratings data when it exists, and includes in recipe rating calculation.
* Introduces `tasty_recipes_google_schema` filter for modifying Google Schema output.
* Always includes the full size image in Google Schema output.
* Allows translation for all templates.
* Includes `data-pin-nopin` attribute to thumbnail and Instagram logo on all templates.
* Fixes issue where Tasty Recipes had a conflict with the WordPress Text Widget.
* Fixes PHP notice that occasionally happened when posting a comment.

### 1.1.0 (Feb. 26, 2018) ###
* Adds recipe conversion tool for Simple Recipe Pro.
* Generates custom 1x1, 4x3, and 16x9 image sizes for use in JSON+LD markup.
* Includes several bundled recipe card designs that can be selected from the backend.
* Improves WP Recipe Maker converter to persist rating data and ignore nutrition data it can't handle.
* Fixes issue where print button didn't correctly pull up the print dialog.

### 1.0.0 (Jan. 8, 2018) ###
* Greatly improves UI for batch recipe converters.
* Adds recipe converters for Cookbook and Yummly.
* Handles images in instruction and ingredient fields when migrating Meal Planner Pro, Yumprint, and ZipList recipes.
* Introduces `tasty_recipes_pre_import_image` for modifying image importing behavior.
* Ensures images with captions are correctly rendered.
* Removes all microdata markup from templates, as it was found to be causing SEO issues with https sites.

### 0.9.0 (Nov. 14, 2017) ###
* Improves Yumprint converter to only include nutrition data when enabled for theme and a recipe has servings.
* Uses `https` for Schema.org URLs.
* Fixes issues where revisions of EasyRecipe posts would be counted in total posts to convert.
* Adds a call to `load_plugin_textdomain` to load actual localization files, enabling language-specific translations to work as expected.
* Attempt to find parent image in conversation process when a thumbnail was used in the template.

### 0.8.0 (Sept. 26, 2017) ###
* Adds recipe conversion tool for WP Recipe Maker.
* Introduces `tasty_recipes_enable_ratings` filter for enabling/disabling ratings.
* Introduces `tasty_recipes_recipe_template_vars` filter for modifying template variables before rendering.
* Permits a custom value to be entered for the recipe total time.
* Properly calculates durations for times over 24 hours.
* Updates EasyRecipe converter to correctly process another very old recipe format.
* Fixes nofollow attribute for a link when "Open in a new window" is also checked.

### 0.7.0 (Jul. 26, 2017) ###
* Adds support for inserting images into notes, descriptions, and other text fields.
* Introduces `tasty_recipes_comment_form_rating_position` filter to change the location of the rating buttons (default is 'before').
* Displays error in the admin when a Nutrifox API request fails.
* Fixes button style for WP 4.8 by ditching `button-link` class.
* Adds `mce-view` as a dependency for editor JS, to fix JavaScript errors in certain scenarios.
* Corrects Windows-style line endings to their cross-platform compatible equivalent during the conversion process, to ensure line breaks are appropriately respected.
* Updates EDD updater to version 1.6.12.

### 0.6.0 (Apr. 26, 2017) ###
* Adds recipe conversion tool for WP Ultimate Recipe.
* Updates EasyRecipe converter to correctly process very old recipe format.
* Only includes first cuisine, category or method in JSON+LD when multiple are provided (comma-separated).
* Conditionally displays Nutrifox data as microdata, depending if nutrient is present in embed.
* Ensures recipe editor only renders once when TinyMCE is used multiple times in the admin.

### 0.5.0 (Mar. 27, 2017) ###
* Magically uses a `tasty-recipes.php` file in the active theme directory as the recipe card template.
* Makes print button font color CSS more specific, to increase the likelihood it will display as white.
* Introduces `tasty_recipes_print_query_var` filter to modify the print URL keyword (default is 'print').
* Only renders recipe description microdata when there's a description for the recipe.
* Adds `.tasty-recipes-print-view` class to the `<body>` element when printing a recipe, for targeting CSS to the print view.
* Strips slashes from escaped characters in EasyRecipe conversion process.
* Transforms `[i]` and other styling markup when converting Meal Planner Pro recipe subheadings.

### 0.4.0 (Feb. 15, 2017) ###
* Uses full image size in JSON+LD and microdata, instead of the thumbnail size, to ensure image meets minimum size recommended by search engines.
* Falls back to the post's first inline image for JSON+LD and microdata when recipe doesn't have an assigned image.
* Adds `rel="nofollow"` checkbox to WordPress link editor, making it possible to add `rel="nofollow"` to links in a recipe.
* Only includes `author` in JSON data when author is present, to ensure validator errors when it's not.
* Meal Planner Pro converter: handles `[b]` and `[i]` markup; strips empty erroneous lines.
* Warns user when post revisions are disabled, to ensure a backup is made prior to performing conversions.

### 0.3.1 (Feb. 9, 2017) ###
* Fixes Meal Planner Pro recipe conversion tool to use the correct recipe in the conversion process.
* Errors early in the recipe batch conversion process to make it more obvious when support should help with diagnosis.

### 0.3.0 (Jan. 18, 2017) ###
* Adds recipe conversion tools for Meal Planner Pro, Yumprint Recipe Card, and ZipList.
* Includes a template matching EasyRecipe HTML, for sites with existing CSS for styling EasyRecipe.
* Changes the default print button color to Tasty Slate, instead of an intense red.
* Introduces custom `tasty_recipes_the_content` filter to apply text formatting without third-party share buttons, etc.
* Ensures clicking the "Print" button on the print view for a recipe opens the computer's print window.
* Uses the post's featured image for Google Schema when recipe doesn't have an assigned image.
* Clips display of star rating based on rating percentage (e.g. 4.8 stars will display if the rating is 4.8).
* Includes `noindex,nofollow` SEO metadata on the recipe print view.
* Adds a `<h4>` section heading button to the recipe editor.
* EasyRecipe converter: Wraps section headings with `<h4>` upon transformation; fixes parsing of old EasyRecipe data; fixes recipe rating calculation.
* Updates EDD Software Licensing updater class to v1.6.8 from v1.6.5.
* EDD Software Licensing integration correctly registers site with WP Tasty, and deregisters when license key is removed.

### 0.2.0 (December 7th, 2016) ###
* Fixed a variety of bugs in the EDD Software Licensing update check.
* Ensures slashed data doesn't save with slashes to the database.

### 0.1.0 (November 23rd, 2016) ###
* Initial release.
