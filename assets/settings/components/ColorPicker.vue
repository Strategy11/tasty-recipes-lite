<template>
	<div class="color-picker" ref="colorpicker">
		<span class="color-picker-container">
			<span
				class="current-color"
				:style="'background: ' + colorValue"
				@click="!disabled ? togglePicker() : null"></span>
			<chrome-picker :value="colors" @input="updateFromPicker" v-if="displayPicker" />
		</span>
		<input type="text" class="form-control" v-model="colorValue" @focus="showPicker()" @input="updateFromInput" :disabled="disabled" :name="! disabled ? name : ''" />
		<input v-if="disabled" type="hidden" v-model="colorValue" :name="disabled ? name : ''" />
		<button v-if="colorValue" class="color-picker-clear" type="button" @click.prevent="colorValue = ''">
			<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="times-circle" class="svg-inline--fa fa-times-circle fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm121.6 313.1c4.7 4.7 4.7 12.3 0 17L338 377.6c-4.7 4.7-12.3 4.7-17 0L256 312l-65.1 65.6c-4.7 4.7-12.3 4.7-17 0L134.4 338c-4.7-4.7-4.7-12.3 0-17l65.6-65-65.6-65.1c-4.7-4.7-4.7-12.3 0-17l39.6-39.6c4.7-4.7 12.3-4.7 17 0l65 65.7 65.1-65.6c4.7-4.7 12.3-4.7 17 0l39.6 39.6c4.7 4.7 4.7 12.3 0 17L312 256l65.6 65.1z"></path></svg>
		</button>
	</div>
</template>
<script>
import { Chrome } from 'vue-color'
export default {
	components: {
		'chrome-picker': Chrome,
	},
	props: {
		disabled: {
			type: Boolean,
			default: false,
		},
		value: {
			type: String,
		},
		name: {
			type: String,
			default: '',
		}
	},
	data() {
		return {
			colors: {
				hex: '',
			},
			colorValue: '',
			displayPicker: false,
		}
	},
	mounted() {
		this.setColor(this.value || '');
	},
	methods: {
		setColor(color) {
			this.updateColors(color);
			this.colorValue = color;
		},
		updateColors(color) {
			if(color.slice(0, 1) === '#') {
				this.colors = {
					hex: color
				};
			}
			else if(color.slice(0, 4) === 'rgba') {
				const rgba = color.replace(/^rgba?\(|\s+|\)$/g,'').split(','),
					// eslint-disable-next-line
					hex = '#' + ((1 << 24) + (parseInt(rgba[0]) << 16) + (parseInt(rgba[1]) << 8) + parseInt(rgba[2])).toString(16).slice(1);
				this.colors = {
					hex,
					a: rgba[3],
				}
			}
		},
		showPicker() {
			document.addEventListener('click', this.documentClick);
			this.displayPicker = true;
		},
		hidePicker() {
			document.removeEventListener('click', this.documentClick);
			this.displayPicker = false;
		},
		togglePicker() {
			if ( this.displayPicker ) {
				this.hidePicker()
			} else {
				this.showPicker();
			}
		},
		updateFromInput() {
			this.updateColors(this.colorValue);
		},
		updateFromPicker(color) {
			this.colors = color;
			if(color.rgba.a === 1) {
				this.colorValue = color.hex;
			}
			else {
				this.colorValue = 'rgba(' + color.rgba.r + ', ' + color.rgba.g + ', ' + color.rgba.b + ', ' + color.rgba.a + ')';
			}
		},
		documentClick(e) {
			const el = this.$refs.colorpicker,
				target = e.target;
			if(el !== target && !el.contains(target)) {
				this.hidePicker()
			}
		}
	},
	watch: {
		colorValue(val) {
			this.updateColors(val);
			this.$emit('input', val);
		}
	},
};
</script>

