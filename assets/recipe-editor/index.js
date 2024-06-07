import Vue from "vue";

window.Vue = Vue;

let instance;

window.tastyRecipesMountRecipeEditor = (el, initialRecipe) => {
	instance = new Vue({
		el,
		components: {
			RecipeForm: require("./components/RecipeForm").default
		},
		data: {
			initialRecipe,
			config: window.tastyRecipesRecipeEditorData
		},
		template: `<recipe-form :initial-recipe="initialRecipe" :config="config"></recipe-form>`
	});
};

window.tastyRecipesDestroyRecipeEditor = () => {
	instance.$destroy();
	instance.$el.remove();
	instance = null;
};
