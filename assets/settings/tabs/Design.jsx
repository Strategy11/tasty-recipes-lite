import { useState } from '@wordpress/element';
import { useForm, useModal, PremiumModal } from '@library';
import { DesignExample } from '../partials/DesignExample';
import { DesignSettings } from '../partials/DesignSettings';

const globalSettings = window.tastyRecipesSettings;
const { design } = globalSettings;
const { customization, template, variation } = design;
const allTemplates = design.allTemplates;
const templateSlugs = Object.keys( allTemplates );
const parsedVariation = Number( variation );
const initialVariation =
	Number.isFinite( parsedVariation ) && parsedVariation > 0
		? parsedVariation
		: 1;
const initialTemplateSlug = templateSlugs.includes( template )
	? template
	: 'default';

export const Design = () => {
	const { formData, handleChange } = useForm( customization );
	const { modalData, openModal, closeModal, generateUpgradeUrl } = useModal();

	const [ selectedTemplate, setSelectedTemplate ] = useState( {
		slug: initialTemplateSlug,
		variation: initialVariation,
	} );

	const activeTemplate =
		templateSlugs.includes( selectedTemplate.slug )
			? selectedTemplate
			: {
					...selectedTemplate,
					slug: initialTemplateSlug,
					variation: initialVariation,
			  };

	const templateMeta = {
		slug: activeTemplate.slug,
		variation: activeTemplate.variation,
		index: templateSlugs.indexOf( activeTemplate.slug ),
		title: allTemplates[ activeTemplate.slug ],
		total: templateSlugs.length,
	};

	return (
		<>
			<PremiumModal modalData={ modalData } closeModal={ closeModal } />
			<div className="tasty-settings-two-columns">
				<div className="tasty-settings-column">
					<DesignExample
						formData={ formData }
						template={ templateMeta }
						setSelectedTemplate={ setSelectedTemplate }
						openModal={ openModal }
						generateUpgradeUrl={ generateUpgradeUrl }
					/>
				</div>
				<div className="tasty-settings-column">
					<DesignSettings
						formData={ formData }
						selectedTemplate={ activeTemplate }
						handleChange={ handleChange }
						openModal={ openModal }
						generateUpgradeUrl={ generateUpgradeUrl }
					/>
				</div>
			</div>
		</>
	);
};
