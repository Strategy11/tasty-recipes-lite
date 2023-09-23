/*global tinymce:true */

tinymce.PluginManager.add('tr_heading', function(editor) {
	var name = 'h4';
	editor.addButton('tr_heading', {
		image: tastyRecipesEditor.pluginURL + '/assets/images/header-icon.svg',
		tooltip: 'Heading',
		onClick: function() {
			editor.execCommand( 'mceToggleFormat', false, 'h4' );
		},
		onPostRender: function() {
			var self = this, setup = function() {
			editor.formatter.formatChanged(name, function(state) {
				self.active(state);
				});
			};
			editor.formatter ? setup() : editor.on('init', setup);
		}
	});
});
