import { PremiumLock, useModal } from '@library';

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
} ) => {
	const { generateUpgradeUrl } = useModal();
	const { title, index, total } = selectedTemplate;

	return (
		<div className="tasty-recipes-card-chooser">
			<button
				type="button"
				onClick={ () =>
					setSelectedTemplate(
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
				{ `${ title } (${ index + 1 }/${ total })` }
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
					setSelectedTemplate( iterateTemplate( selectedTemplate ) )
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
	);
};
