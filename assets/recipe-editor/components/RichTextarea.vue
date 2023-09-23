<template>
	<fieldset>
		<label :for="id">{{ label }}</label>
		<textarea :id="id" :name="name" class="tasty-recipes-tinymce" :value="value"></textarea>
		<div v-if="showVideoSettings" class="tasty-recipes-video-settings">
			<span>{{ videoSettingsConfig.label }}</span>
			<label v-for="(label, key) in videoSettingsConfig.settings" :key="key">
				<input type="checkbox" :value="key" v-model="videoSettingsValue">
				{{ label }}
			</label>
			<input type="hidden" :name="name + '_video_settings'" :value="videoSettingsValue.join( ' ' )" />
		</div>
	</fieldset>
</template>
<script>
export default {
	props: {
		initialValue: {
		},
		name: {
			type: String,
			required: true,
		},
		label: {
			type: String,
			required: true,
		},
		initialVideoSettingsValue: {
			type: String,
		},
		videoSettingsConfig: {
			type: Object,
		},
	},
	data() {
		return {
			value: this.initialValue,
			videoSettingsValue: this.initialVideoSettingsValue ? this.initialVideoSettingsValue.split( ' ' ) : [],
		};
	},
	computed: {
		id() {
			return 'tasty-recipes-recipe-' + this.name;
		},
		showVideoSettings() {
			if ( ! this.videoSettingsConfig ||
				! this.value ) {
				return false;
			}
			// Remove what might be links to videos.
			const content = this.value.replace(/<a\b[^>]*>/i,'').replace(/<\/a>/i, '');
			return -1 !== content.indexOf( 'vimeo.com' ) ||
				-1 !== content.indexOf( 'youtube.com' ) ||
				-1 !== content.indexOf( 'youtu.be' );
		},
	},
	mounted() {
		Vue.nextTick( () => {
			if ( typeof window.tinyMCE !== 'undefined' ) {
				const settings = Object.assign( {}, window.tinyMCEPreInit.mceInit[ 'tasty-recipes-editor' ] );
				settings.selector = '#' + this.id;
				settings.setup = ( ed ) => {
					ed.on( 'change', () => {
						this.value = ed.getContent();
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
