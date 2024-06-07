import { mount } from "@vue/test-utils";
import RecipeForm from "../RecipeForm.vue";

describe("recipeForm", () => {
	it("mounts", () => {
		expect.assertions(1);
		const wrapper = mount(RecipeForm, {
			propsData: {
				initialRecipe: {},
				config: window.tastyRecipesRecipeEditorData
			}
		});
		expect(wrapper.text()).toContain('Ingredients');
	});
});
