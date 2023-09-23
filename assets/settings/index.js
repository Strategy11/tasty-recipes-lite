import Vue from 'vue';

// Entire views
Vue.component('tasty-recipes-design-settings', require('./views/Design.vue').default);

if (document.querySelector('.tasty-recipes-design-settings-app')) {
	new Vue({
		el: '.tasty-recipes-design-settings-app',
	});
}
