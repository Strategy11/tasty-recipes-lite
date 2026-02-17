import { sprintf, __ } from '@wordpress/i18n';
import { PremiumLock, useModal } from '@library';
import classNames from 'classnames';

const allTemplates = window.tastyRecipesSettings.design.allTemplates;

const iterateTemplate = ( currentTemplate, operator = '+' ) => {
	const templateKeys = Object.keys( allTemplates );
	const totalTemplates = templateKeys.length;
	const currentIndex = templateKeys.indexOf( currentTemplate.slug );

	const offset = operator === '-' ? -1 : 1;
	const newIndex =
		( currentIndex + offset + totalTemplates ) % totalTemplates;

	return templateKeys[ newIndex ];
};

const lockedTemplates = window.tastyRecipesSettings.design.locked;

export const TemplateSelector = ( {
	selectedTemplate,
	setSelectedTemplate,
	variationSelector = true,
} ) => {
	const { generateUpgradeUrl } = useModal();
	const { title, index, total } = selectedTemplate;
	const safeTitle = title || {
		name: __( 'Template', 'tasty-recipes-lite' ),
		variation_count: 0,
	};
	const parsedVariationCount = Number( safeTitle.variation_count );
	const variationCount = Number.isFinite( parsedVariationCount )
		? parsedVariationCount
		: 0;

	const variations = [];

	if ( variationCount > 0 ) {
		for ( let i = 1; i <= variationCount; i++ ) {
			variations.push( i );
		}
	}

	const handleTemplateChange = ( slug ) => {
		setSelectedTemplate( {
			...selectedTemplate,
			slug,
		} );
	};

	const handleVariationChange = ( variation ) => {
		setSelectedTemplate( {
			...selectedTemplate,
			variation,
		} );
	};

	return (
		<>
			<div className="tasty-recipes-card-chooser">
				<button
					type="button"
					onClick={ () =>
						handleTemplateChange(
							iterateTemplate( selectedTemplate, '-' )
						)
					}
				>
					<svg
						xmlns="http://www.w3.org/2000/svg"
						width="5"
						height="8"
						fill="none"
					>
						<path
							stroke="currentColor"
							strokeLinecap="round"
							strokeLinejoin="round"
							strokeWidth="1.5"
							d="M4.25.75.75 4l3.5 3.25"
						/>
					</svg>
				</button>
				<h3>
					{ `${ safeTitle.name } (${ index + 1 }/${ total })` }
					{ lockedTemplates.includes( selectedTemplate.slug ) && (
						<PremiumLock
							upgradeUrl={ generateUpgradeUrl( {
								medium: 'tasty-recipes',
								campaign: 'upgrade',
								content: 'template',
							} ) }
						/>
					) }
				</h3>
				<button
					type="button"
					onClick={ () =>
						handleTemplateChange(
							iterateTemplate( selectedTemplate )
						)
					}
				>
					<svg
						xmlns="http://www.w3.org/2000/svg"
						width="5"
						height="8"
						fill="none"
					>
						<path
							stroke="currentColor"
							strokeLinecap="round"
							strokeLinejoin="round"
							strokeWidth="1.5"
							d="M.75 7.25 4.25 4 .75.75"
						/>
					</svg>
				</button>
			</div>
			{ variationSelector && variationCount > 0 && (
					<div className="tasty-recipes-template-variations">
						{ variations.map( ( variation ) => (
							<button
								key={ 'variation-' + variation }
								type="button"
								onClick={ () =>
									handleVariationChange( variation )
								}
								className={ classNames( {
									'tasty-recipes-template-variation-active':
										variation ===
										selectedTemplate.variation,
								} ) }
							>
								{ sprintf(
									/* translators: %d is the variation number */
									__( 'Variation %d', 'tasty-recipes-lite' ),
									variation
								) }
							</button>
						) ) }
					</div>
				) }
		</>
	);
};
