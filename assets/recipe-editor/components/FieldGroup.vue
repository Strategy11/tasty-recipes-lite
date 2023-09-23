<template>
	<div>
		<h3 v-if="heading">{{ heading }}</h3>
		<slot name="before-fields" v-bind:field-values="fieldValues"></slot>
		<fieldset
			v-for="(field, name) in fields"
			:class="'tasty-recipes-recipe-' + name + '-container'"
			:key="name"
			v-show="showField(name)">
			<span>
				<label :for="'tasty-recipes-recipe-' + name" v-if="showLabels">{{ field.label }}<a v-if="'prep_time' === name" class="tasty-recipes-add-additional-time button secondary-button" type="button" @click.prevent="isAdditionalTimeOpen = !isAdditionalTimeOpen" :title="!isAdditionalTimeOpen ? extraLabels.add_button_title : extraLabels.remove_button_title ">{{ ! isAdditionalTimeOpen ? extraLabels.add_button_text : extraLabels.remove_button_text }}</a></label>
				<select v-if="field.options" :id="'tasty-recipes-recipe-' + name" :name="name" v-model="fieldValues[field.object_key || name]" class="inline-field">
					<option v-for="(label, value) in field.options" :value="value" :key="value">{{ label }}</option>
				</select>
				<input v-if="!field.options" :id="'tasty-recipes-recipe-' + name" type="text" :placeholder="field.placeholder" :name="name" class="inline-field" v-model="fieldValues[field.object_key || name]" />
			</span><span v-if="'prep_time' === name" :style="{'display': isAdditionalTimeOpen ? 'inline-block' : 'none', 'margin-left': '2px'}">
				<input id="tasty-recipes-recipe-additional-time-label" type="text" name="additional_time_label" :placeholder="extraLabels.label_placeholder" class="inline-field" v-model="fieldValues.additional_time_label" /><br>
				<input id="tasty-recipes-recipe-additional-time-value" type="text" name="additional_time_value" :placeholder="extraLabels.value_placeholder" class="inline-field" v-model="fieldValues.additional_time_value" />
			</span><span v-if="'prep_time' === name && !isAdditionalTimeOpen" style="display:none;">
				<input id="tasty-recipes-recipe-additional-time-label" type="hidden" name="additional_time_label" /><input id="tasty-recipes-recipe-additional-time-value" type="hidden" name="additional_time_value" />
			</span>
		</fieldset>
	</div>
</template>
<script>
export default {
	props: {
		heading: {
			type: String,
		},
		group: {
			type: String,
			default: '',
		},
		extraLabels: {
			type: Object,
		},
		fields: {
			type: Object,
			required: true,
		},
		initialFieldValues: {
			type: Object,
			default: () => {},
		},
		showLabels: {
			type: Boolean,
			default: true,
		},
	},
	data() {
		return {
			fieldValues: JSON.parse(JSON.stringify(this.initialFieldValues)),
			isAdditionalTimeOpen: false,
		};
	},
	mounted() {
		if ( ( typeof this.fieldValues.additional_time_label !== 'undefined'
			&& this.fieldValues.additional_time_label )
			|| ( typeof this.fieldValues.additional_time_value !== 'undefined'
			&& this.fieldValues.additional_time_value ) ) {
			this.isAdditionalTimeOpen = true;
		}
	},
	methods: {
		showField() {
			if ('nutrition' === this.group && this.fieldValues.nutrifox_id) {
				return false;
			}
			return true;
		}
	},
	watch: {
		isAdditionalTimeOpen(val) {
			if (!val) {
				this.fieldValues.additional_time_label = '';
				this.fieldValues.additional_time_value = '';
			}
		},
	},
};
</script>
