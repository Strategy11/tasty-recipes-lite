import { mount } from "@vue/test-utils";
import Design from "../Design.vue";

const initialOptions = {
	template: "simple",
	customization: {
		primary_color: "",
		secondary_color: "",
		icon_color: "",
		button_color: "",
		detail_label_color: "",
		detail_value_color: "",
		h2_color: "",
		h2_transform: "",
		h3_color: "",
		h3_transform: "",
		body_color: "",
		footer_social_platform: "",
		footer_icon_color: "",
		footer_heading: "Did you make this recipe?",
		footer_heading_color: "",
		footer_description:
			"<p>Share a photo and tag us — we can't wait to see what you've made!</p>",
		footer_description_color: "",
	},
	locked: ['bold'],
	upgrade_url: "https://www.wptasty.com/tasty-recipes",
};
const recipeCardPreviewUrlBase =
	'"http://tastyrecipes.test/wp-admin/admin-ajax.php?action=tasty_recipes_preview_recipe_card&nonce=6c8f2158d1"';
const templateOptions = {
	"": "Default",
	simple: "Simple",
	snap: "Snap",
};

describe("design", () => {
	it("shows only some color options for the Simple template", async () => {
		expect.assertions(7);
		const wrapper = mount(Design, {
			propsData: {
				initialOptions,
				recipeCardPreviewUrlBase,
				templateOptions,
			},
		});
		wrapper.setData({ currentTemplate: "simple" });
		await wrapper.vm.$nextTick();
		expect(
			wrapper
				.find("fieldset.tasty-recipes-primary-color-fieldset")
				.isVisible()
		).toBe(true);
		expect(
			wrapper
				.find("fieldset.tasty-recipes-secondary-color-fieldset")
				.isVisible()
		).toBe(true);
		expect(
			wrapper
				.find("fieldset.tasty-recipes-icon-color-fieldset")
				.isVisible()
		).toBe(false);
		expect(
			wrapper
				.find("fieldset.tasty-recipes-button-color-fieldset")
				.isVisible()
		).toBe(true);
		expect(
			wrapper
				.find("fieldset.tasty-recipes-button-text-color-fieldset")
				.isVisible()
		).toBe(true);
		expect(
			wrapper
				.find("fieldset.tasty-recipes-detail-label-color-fieldset")
				.isVisible()
		).toBe(true);
		expect(
			wrapper
				.find("fieldset.tasty-recipes-detail-value-color-fieldset")
				.isVisible()
		).toBe(true);
	});
});
