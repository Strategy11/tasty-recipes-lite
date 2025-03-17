import { useEffect, useRef } from '@wordpress/element';
import { TemplateSelector } from './TemplateSelector';

const recipeCardPreviewUrlBase =
	window.tastyRecipesSettings.design.recipeCardPreviewUrlBase;

const getTemplatePreviewUrl = (
	template,
	settings,
	include = [ 'nutrifox_display_style', 'star_ratings_style' ]
) => {
	const params = Object.entries( settings )
		.filter( ( [ key ] ) => include.includes( key ) ) // Only include keys that are in the "include" array
		.map(
			( [ key, value ] ) => `${ key }=${ encodeURIComponent( value ) }`
		)
		.join( '&' );

	return recipeCardPreviewUrlBase + '&template=' + template + '&' + params;
};

export const DesignExample = ( {
	formData,
	template,
	setSelectedTemplate,
} ) => {
	const iframeRef = useRef( null );

	// Handle iframe resize.
	useEffect( () => {
		const handleMessage = ( event ) => {
			try {
				const data = JSON.parse( event.data );
				if ( data.event === 'setRecipeCardSize' && iframeRef.current ) {
					iframeRef.current.style.height = `${ data.height }px`;
				}
			} catch ( e ) {
				// Ignore invalid JSON messages
			}
		};

		window.addEventListener( 'message', handleMessage );
		return () => window.removeEventListener( 'message', handleMessage );
	}, [] );

	// Send customization updates when formData changes
	useEffect( () => {
		if ( iframeRef.current?.contentWindow ) {
			const data = {
				event: 'updateCustomization',
				data: {
					...formData,
				},
			};
			iframeRef.current.contentWindow.postMessage(
				JSON.stringify( data ),
				'*'
			);
		}
	}, [ formData ] );

	return (
		<div className="tasty-recipes-card-preview-container">
			<TemplateSelector
				selectedTemplate={ template }
				setSelectedTemplate={ setSelectedTemplate }
			/>
			<iframe
				ref={ iframeRef }
				title="Template Preview"
				src={ getTemplatePreviewUrl( template.slug, formData ) }
				style={ {
					minHeight: '200px',
				} }
			></iframe>
			<TemplateSelector
				selectedTemplate={ template }
				setSelectedTemplate={ setSelectedTemplate }
			/>
		</div>
	);
};
