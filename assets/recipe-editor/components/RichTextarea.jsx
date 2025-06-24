import { useState, useEffect, useRef } from '@wordpress/element';

const RichTextarea = ({ 
	initialValue, 
	name, 
	label, 
	initialVideoSettingsValue, 
	videoSettingsConfig,
	onChange 
}) => {
	const [value, setValue] = useState(initialValue);
	const [videoSettingsValue, setVideoSettingsValue] = useState(
		initialVideoSettingsValue ? initialVideoSettingsValue.split(' ') : []
	);
	const textareaRef = useRef(null);

	const id = `tasty-recipes-recipe-${name}`;

	const showVideoSettings = videoSettingsConfig && value && 
		(value.includes('vimeo.com') || value.includes('youtube.com') || value.includes('youtu.be'));

	useEffect(() => {
		if (typeof window.tinyMCE !== 'undefined') {
			const settings = Object.assign({}, window.tinyMCEPreInit.mceInit['tasty-recipes-editor']);
			settings.selector = `#${id}`;
			settings.setup = (ed) => {
				ed.on('change', () => {
					const content = ed.getContent();
					setValue(content);
					if (onChange) {
						onChange(content);
					}
				});
			};
			
			setTimeout(() => {
				window.tinyMCE.init(settings);
			}, 1);
		}

		return () => {
			if (typeof window.tinyMCE !== 'undefined') {
				window.tinyMCE.EditorManager.execCommand('mceRemoveEditor', true, id);
			}
		};
	}, [id, onChange]);

	const handleVideoSettingChange = (settingKey, checked) => {
		const newSettings = checked 
			? [...videoSettingsValue, settingKey]
			: videoSettingsValue.filter(setting => setting !== settingKey);
		
		setVideoSettingsValue(newSettings);
	};

	return (
		<fieldset>
			<label htmlFor={id}>{label}</label>
			<textarea 
				ref={textareaRef}
				id={id} 
				name={name} 
				className="tasty-recipes-tinymce" 
				value={value || ''} 
				onChange={(e) => {
					setValue(e.target.value);
					if (onChange) {
						onChange(e.target.value);
					}
				}}
			/>
			{showVideoSettings && (
				<div className="tasty-recipes-video-settings">
					<span>{videoSettingsConfig.label}</span>
					{Object.entries(videoSettingsConfig.settings).map(([key, label]) => (
						<label key={key}>
							<input 
								type="checkbox" 
								value={key} 
								checked={videoSettingsValue.includes(key)}
								onChange={(e) => handleVideoSettingChange(key, e.target.checked)}
							/>
							{label}
						</label>
					))}
					<input 
						type="hidden" 
						name={`${name}_video_settings`} 
						value={videoSettingsValue.join(' ')} 
					/>
				</div>
			)}
		</fieldset>
	);
};

export default RichTextarea;