import { useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { TemplateSelector } from './TemplateSelector';

const recipeCardPreviewUrlBase =
	window.tastyRecipesSettings.design.recipeCardPreviewUrlBase;

const getTemplatePreviewUrl = ( template, settings ) => {
	const include = applyFilters(
		'tasty_recipes_design_example_includes',
		[ 'nutrifox_display_style', 'star_ratings_style' ],
		settings
	);

	const params = include
		.filter( ( key ) => include.includes( key ) ) // Only include keys that are in the "include" array
		.map( ( key ) => `${ key }=${ encodeURIComponent( settings[ key ] ) }` )
		.join( '&' );

	return recipeCardPreviewUrlBase + '&template=' + template + '&' + params;
};

export const DesignExample = ( {
	formData,
	template,
	setSelectedTemplate,
	openModal,
	generateUpgradeUrl,
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
			setTimeout( () => {
				iframeRef.current.contentWindow.postMessage(
					JSON.stringify( data ),
					'*'
				);
			}, 100 ); // We wait for the new template url to be loaded.
		}
	}, [ formData, template ] );

	// Handle pro image clicked event.
	useEffect( () => {
		window.addEventListener( 'message', ( event ) => {
			try {
				const data = JSON.parse( event.data );
				if ( data.event === 'proImageClicked' ) {
					openModal(
						'tasty_recipes_upgrade_modal',
						__( 'Premium Template', 'tasty-recipes-lite' ),
						generateUpgradeUrl( {
							medium: 'tasty-recipes',
							content: 'template',
						} )
					);
				}
			} catch ( e ) {
				// Ignore invalid JSON messages
			}
		} );
	}, [ generateUpgradeUrl, openModal ] );

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
