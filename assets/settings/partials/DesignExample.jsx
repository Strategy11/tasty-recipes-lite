import { useCallback, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { TemplateSelector } from './TemplateSelector';

const recipeCardPreviewUrlBase =
	window.tastyRecipesSettings.design.recipeCardPreviewUrlBase;

const getTemplatePreviewUrl = ( template, settings, variation ) => {
	const include = applyFilters(
		'tasty_recipes_design_example_includes',
		[ 'nutrifox_display_style', 'star_ratings_style' ],
		settings
	);

	const params = include
		.filter( ( key ) => include.includes( key ) ) // Only include keys that are in the "include" array
		.map( ( key ) => `${ key }=${ encodeURIComponent( settings[ key ] ) }` )
		.join( '&' );

	const parsedVariation = Number( variation );
	const safeVariation =
		Number.isFinite( parsedVariation ) && parsedVariation > 0
			? parsedVariation
			: 1;

	return (
		recipeCardPreviewUrlBase +
		'&template=' +
		template +
		'&variation=' +
		safeVariation +
		'&' +
		params
	);
};

export const DesignExample = ( {
	formData,
	template,
	setSelectedTemplate,
	openModal,
	generateUpgradeUrl,
} ) => {
	const iframeRef = useRef( null );

	const sendCustomizationUpdate = useCallback( () => {
		if ( ! iframeRef.current?.contentWindow ) {
			return;
		}

		const starColorUsesDetailValue = applyFilters(
			'tasty_recipes_star_color_uses_detail_value',
			true,
			template.slug
		);
		const starColorFallback = applyFilters(
			'tasty_recipes_star_color_preview_fallback',
			'',
			template.slug,
			formData
		);

		const data = {
			event: 'updateCustomization',
			data: {
				...formData,
				_starColorUsesDetailValue: starColorUsesDetailValue,
				_starColorFallback: starColorFallback,
			},
		};

		setTimeout( () => {
			iframeRef.current.contentWindow.postMessage(
				JSON.stringify( data ),
				'*'
			);
		}, 100 ); // We wait for the new template url to be loaded.
	}, [ formData, template.slug ] );

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
		if ( ! iframeRef.current ) {
			return;
		}

		const handleLoad = () => {
			sendCustomizationUpdate();
		};

		sendCustomizationUpdate();
		iframeRef.current.onload = handleLoad;
	}, [ sendCustomizationUpdate, template ] );

	// Handle pro image clicked event.
	useEffect( () => {
		const handleMessage = ( event ) => {
			try {
				const data = JSON.parse( event.data );
				if ( data.event === 'proImageClicked' ) {
					openModal( {
						id: 'tasty_recipes_upgrade_modal',
						title: __( 'Premium Template', 'tasty-recipes-lite' ),
						upgradeUrl: generateUpgradeUrl( {
							medium: 'tasty-recipes',
							content: 'template',
						} ),
					} );
				}
			} catch ( e ) {
				// Ignore invalid JSON messages
			}
		};

		window.addEventListener( 'message', handleMessage );
		return () => {
			window.removeEventListener( 'message', handleMessage );
		};
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
				id="tasty-recipes-card-preview"
				src={ getTemplatePreviewUrl(
					template.slug,
					formData,
					template.variation
				) }
				style={ {
					minHeight: '200px',
				} }
			></iframe>
			<TemplateSelector
				selectedTemplate={ template }
				setSelectedTemplate={ setSelectedTemplate }
				variationSelector={ false }
			/>
		</div>
	);
};
