import { useState } from '@wordpress/element';
import { useForm, useModal, PremiumModal } from '@library';
import { DesignExample } from '../partials/DesignExample';
import { DesignSettings } from '../partials/DesignSettings';

const globalSettings = window.tastyRecipesSettings;
const { design } = globalSettings;
const { customization, template, variation } = design;

export const Design = () => {
	const { formData, handleChange } = useForm( customization );
	const { modalData, openModal, closeModal, generateUpgradeUrl } = useModal();

	const [ selectedTemplate, setSelectedTemplate ] = useState( {
		slug: template ?? 'default',
		variation: Number( variation ) ?? 1,
	} );

	const templateMeta = {
		slug: selectedTemplate.slug,
		variation: selectedTemplate.variation,
		index: Object.keys(
			window.tastyRecipesSettings.design.allTemplates
		).indexOf( selectedTemplate.slug ),
		title: window.tastyRecipesSettings.design.allTemplates[
			selectedTemplate.slug
		],
		total: Object.keys( window.tastyRecipesSettings.design.allTemplates )
			.length,
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
						selectedTemplate={ selectedTemplate }
						handleChange={ handleChange }
						openModal={ openModal }
						generateUpgradeUrl={ generateUpgradeUrl }
					/>
				</div>
			</div>
		</>
	);
};
