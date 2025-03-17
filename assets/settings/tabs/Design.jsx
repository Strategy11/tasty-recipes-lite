import { useState } from '@wordpress/element';
import { useForm, useModal, PremiumModal } from '@library';
import { DesignExample } from '../partials/DesignExample';
import { DesignSettings } from '../partials/DesignSettings';

const globalSettings = window.tastyRecipesSettings;
const { design } = globalSettings;
const { customization, template } = design;

export const Design = () => {
	const { formData, handleChange } = useForm( customization );
	const { modalData, openModal, closeModal, generateUpgradeUrl } = useModal();

	const [ selectedTemplate, setSelectedTemplate ] = useState(
		template ?? 'default'
	);

	const templateMeta = {
		slug: selectedTemplate,
		index: Object.keys(
			window.tastyRecipesSettings.design.allTemplates
		).indexOf( selectedTemplate ),
		title: window.tastyRecipesSettings.design.allTemplates[
			selectedTemplate
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
