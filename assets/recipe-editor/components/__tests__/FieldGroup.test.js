import { mount } from "@vue/test-utils";
import FieldGroup from "../FieldGroup.vue";

describe("fieldGroup", () => {
	it("mounts", () => {
		expect.assertions(3);
		const wrapper = mount(FieldGroup, {
			propsData: {
				fields: {
					nutrifox_id: {
						label: "Nutrifox ID",
						sanitize_callback: "intval"
					},
					serving_size: {
						label: "Serving Size",
						property: "servingSize",
						nutrifox_key: "serving_size"
					},
					calories: {
						label: "Calories",
						property: "calories",
						nutrifox_key: "ENERC_KCAL"
					},
					sugar: {
						label: "Sugar",
						property: "sugarContent",
						nutrifox_key: "SUGAR"
					},
					sodium: {
						label: "Sodium",
						property: "sodiumContent",
						nutrifox_key: "NA"
					},
					fat: {
						label: "Fat",
						property: "fatContent",
						nutrifox_key: "FAT"
					},
					saturated_fat: {
						label: "Saturated Fat",
						property: "saturatedFatContent",
						nutrifox_key: "FASAT"
					},
					unsaturated_fat: {
						label: "Unsaturated Fat",
						property: "unsaturatedFatContent"
					},
					trans_fat: {
						label: "Trans Fat",
						property: "transFatContent",
						nutrifox_key: "FATRN"
					},
					carbohydrates: {
						label: "Carbohydrates",
						property: "carbohydrateContent",
						nutrifox_key: "CHOCDF"
					},
					fiber: {
						label: "Fiber",
						property: "fiberContent",
						nutrifox_key: "FIBTG"
					},
					protein: {
						label: "Protein",
						property: "proteinContent",
						nutrifox_key: "PROCNT"
					},
					cholesterol: {
						label: "Cholesterol",
						property: "cholesterolContent",
						nutrifox_key: "CHOLE"
					}
				},
				heading: "Nutrition",
				initialFieldValues: {
					id: 288,
					title: "",
					title_rendered: "",
					author_name: "daniel",
					image_id: 0,
					description: "",
					description_rendered: "",
					ingredients: "",
					ingredients_rendered: "",
					instructions: "",
					instructions_rendered: "",
					notes: "",
					notes_rendered: "",
					keywords: "",
					nutrifox_id: "",
					video_url: "",
					average_rating: "",
					total_reviews: "",
					prep_time: "",
					cook_time: "",
					total_time: "",
					yield: "",
					category: "",
					method: "",
					cuisine: "",
					diet: "",
					serving_size: "1 cup",
					calories: "",
					sugar: "",
					sodium: "",
					fat: "",
					saturated_fat: "",
					unsaturated_fat: "",
					trans_fat: "",
					carbohydrates: "",
					fiber: "",
					protein: "",
					cholesterol: "",
					total_time_raw: "",
					image_sizes: [],
					tasty_links_equipment: ""
				}
			}
		});
		expect(wrapper.find("h3").text()).toBe("Nutrition");
		expect(wrapper.find("label").text()).toBe("Nutrifox ID");
		expect(wrapper.findAll("select")).toHaveLength(0);
	});
	it("shows a <select> when a field has options", () => {
		expect.assertions(3);
		const wrapper = mount(FieldGroup, {
			propsData: {
				extraLabels: {"add_button_text":"+ Time","add_button_title":"Add an additional time field","label_placeholder":"Additional Time Label","value_placeholder":"Additional Time Value"},
				fields: {
					prep_time: { label: "Prep Time", property: "prepTime" },
					cook_time: { label: "Cook Time", property: "cookTime" },
					total_time: {
						label: "Total Time",
						property: "totalTime",
						object_key: 'total_time_raw',
						placeholder: "Default to prep + cook."
					},
					yield: { label: "Yield", property: "recipeYield" },
					category: { label: "Category", property: "recipeCategory" },
					method: { label: "Method", property: "cookingMethod" },
					cuisine: { label: "Cuisine", property: "recipeCuisine" },
					diet: {
						label: "Diet",
						property: "suitableForDiet",
						options: {
							"": "N/A",
							Diabetic: "Diabetic",
							"Gluten Free": "Gluten Free",
							Halal: "Halal",
							Hindu: "Hindu",
							Kosher: "Kosher",
							"Low Calorie": "Low Calorie",
							"Low Fat": "Low Fat",
							"Low Lactose": "Low Lactose",
							"Low Salt": "Low Salt",
							Vegan: "Vegan",
							Vegetarian: "Vegetarian"
						}
					},
					keywords: {
						label: "Keywords",
						sanitize_callback: "sanitize_text_field"
					}
				},
				heading: "Details",
				initialFieldValues: {
					id: 288,
					title: "",
					title_rendered: "",
					author_name: "daniel",
					image_id: 0,
					description: "",
					description_rendered: "",
					ingredients: "",
					ingredients_rendered: "",
					instructions: "",
					instructions_rendered: "",
					notes: "",
					notes_rendered: "",
					keywords: "",
					nutrifox_id: "92941",
					video_url: "",
					average_rating: "",
					total_reviews: "",
					prep_time: "",
					cook_time: "",
					total_time: "",
					yield: "",
					category: "",
					method: "",
					cuisine: "Indian",
					diet: "",
					serving_size: "1 cup",
					calories: "",
					sugar: "",
					sodium: "",
					fat: "",
					saturated_fat: "",
					unsaturated_fat: "",
					trans_fat: "",
					carbohydrates: "",
					fiber: "",
					protein: "",
					cholesterol: "",
					total_time_raw: "",
					image_sizes: [],
					tasty_links_equipment: ""
				}
			}
		});
		expect(wrapper.find("h3").text()).toBe("Details");
		expect(wrapper.find("label").text()).toContain("Prep Time");
		expect(wrapper.findAll("select")).toHaveLength(1);
	});
});
