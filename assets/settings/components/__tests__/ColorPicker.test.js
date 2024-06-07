import { mount } from '@vue/test-utils';
import ColorPicker from '../ColorPicker.vue';

describe('colorPicker', () => {
	it('emits input event when updated', async () => {
		expect.assertions(3);
		const wrapper = mount( ColorPicker );
		expect(wrapper.emitted().input).toBeUndefined();
		await wrapper.find('input').setValue('#FFF');
		expect(wrapper.emitted().input).toHaveLength(1);
		expect(wrapper.emitted().input[0]).toStrictEqual(['#FFF']);
	});
});
