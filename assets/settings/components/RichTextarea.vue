<template>
	<fieldset>
		<label :for="id">{{ label }}</label>
		<textarea :id="id" :name="name" class="tasty-recipes-tinymce" :value="currentValue"></textarea>
	</fieldset>
</template>
<script>
export default {
	props: {
		value: {
		},
		name: {
			type: String,
			required: true,
		},
		label: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			currentValue: this.value,
		};
	},
	computed: {
		id() {
			return 'tasty-recipes-settings-' + this.label.toLowerCase();
		},
	},
	mounted() {
		const docReady = (fn) => {
			// see if DOM is already available
			if (document.readyState === "complete" || document.readyState === "interactive") {
				// call on next available tick
				setTimeout(fn, 1);
			} else {
				document.addEventListener("DOMContentLoaded", fn);
			}
		}
		docReady( () => {
			if ( typeof window.tinyMCE !== 'undefined' ) {
				const settings = Object.assign( {}, window.tinyMCEPreInit.mceInit[ 'tasty-recipes-settings' ] );
				settings.selector = '#' + this.id;
				settings.setup = ( ed ) => {
					ed.on( 'change', () => {
						this.currentValue = ed.getContent();
						this.$emit('input', this.currentValue);
					} );
					ed.on( 'keyup', () => {
						this.currentValue = ed.getContent();
						this.$emit('input', this.currentValue);
					} );
				};
				setTimeout( () => {
					window.tinyMCE.init( settings );
				}, 1 );
			}
		} );
	},
	beforeDestroy() {
		if ( typeof window.tinyMCE !== 'undefined' ) {
			window.tinyMCE.EditorManager.execCommand( 'mceRemoveEditor', true, this.id );
		}
	},
};
</script>
