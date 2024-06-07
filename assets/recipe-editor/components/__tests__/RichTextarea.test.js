import { mount } from "@vue/test-utils";
import RichTextarea from "../RichTextarea.vue";

describe("richTextarea", () => {
	it("mounts", () => {
		expect.assertions(1);
		const wrapper = mount(RichTextarea, {
			propsData: {
				name: "description",
				label: "Description"
			}
		});
		expect(wrapper.text()).toContain('Description');
	});
});
