const {PluginDocumentSettingPanel} = wp.editPost;
const { Button } = wp.components;
const { __ } = wp.i18n;

const TastyRecipesMetaBox = () => {
	const converters = window.tastyRecipesEditor.dismissedConverters;
	return (
		<PluginDocumentSettingPanel
			name="tasty-recipes-metabox"
			title="Tasty Recipes"
			className="tasty-recipes-metabox-wrapper"
			icon="tasty-recipes">
			<p className="components-form-token-field__help">{
				__( 'Conversion options for the following products will not be shown:', 'tasty-recipes' )
			}</p>
			{Object.keys(converters).map(converterKey => (
				<Button key={converterKey} variant="primary" href={converters[converterKey].url}>{converters[converterKey].title}</Button>
			))}
		</PluginDocumentSettingPanel>
	);
};

export default TastyRecipesMetaBox;
