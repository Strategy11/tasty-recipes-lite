import { useState, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export const RichTextarea = ( {
	initialValue,
	name,
	label,
	placeholder = '',
	actions = [],
	initialVideoSettingsValue,
	onChange = () => {},
} ) => {
	const [ value, setValue ] = useState( initialValue );
	const [ videoSettingsValue, setVideoSettingsValue ] = useState(
		initialVideoSettingsValue ? initialVideoSettingsValue.split( ' ' ) : []
	);

	const videoSettingsConfig = {
		settings: {
			autoplay: __( 'Autoplay', 'tasty-recipes-lite' ),
			mute: __( 'Mute', 'tasty-recipes-lite' ),
			loop: __( 'Loop', 'tasty-recipes-lite' ),
			'hide-controls': __( 'Hide Controls', 'tasty-recipes-lite' ),
		},
	};
	const textareaRef = useRef( null );
	const id = `tasty-recipes-recipe-${ name }`;
	const showVideoSettings =
		videoSettingsConfig &&
		value &&
		( value.includes( 'vimeo.com' ) ||
			value.includes( 'youtube.com' ) ||
			value.includes( 'youtu.be' ) );

	useEffect( () => {
		if ( typeof window.tinyMCE === 'undefined' ) {
			return;
		}

		const settings = Object.assign(
			{},
			window.tinyMCEPreInit.mceInit[ 'tasty-recipes-editor' ]
		);
		settings.selector = `#${ id }`;
		settings.statusbar = true;
		settings.placeholder = placeholder;
		settings.setup = ( ed ) => {
			ed.on( 'change', () => {
				const content = ed.getContent();
				setValue( content );
				onChange( content );
			} );
		};

		window.tinyMCE.init( settings );

		return () => {
			if ( typeof window.tinyMCE !== 'undefined' ) {
				window.tinyMCE.EditorManager.execCommand(
					'mceRemoveEditor',
					true,
					id
				);
			}
		};
		// We disable the exhaustive-deps rule because we want to ensure the editor is initialized only once.
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ id ] );

	const handleVideoSettingChange = ( settingKey, checked ) => {
		const newSettings = checked
			? [ ...videoSettingsValue, settingKey ]
			: videoSettingsValue.filter(
					( setting ) => setting !== settingKey
			  );

		setVideoSettingsValue( newSettings );
	};

	return (
		<fieldset>
			<label htmlFor={ id }>{ label }</label>
			<textarea
				ref={ textareaRef }
				id={ id }
				name={ name }
				className="tasty-recipes-tinymce"
				placeholder={ placeholder }
				defaultValue={ value || '' }
				onChange={ ( e ) => {
					setValue( e.target.value );
					onChange( e.target.value );
				} }
			/>
			{ showVideoSettings && (
				<div className="tasty-recipes-video-settings">
					<span>
						{ __( 'Video Settings:', 'tasty-recipes-lite' ) }
					</span>
					{ Object.entries( videoSettingsConfig.settings ).map(
						( [ key, innerLabel ] ) => (
							// eslint-disable-next-line jsx-a11y/label-has-associated-control
							<label key={ key }>
								<input
									type="checkbox"
									value={ key }
									checked={ videoSettingsValue.includes(
										key
									) }
									onChange={ ( e ) =>
										handleVideoSettingChange(
											key,
											e.target.checked
										)
									}
								/>
								{ innerLabel }
							</label>
						)
					) }
					<input
						type="hidden"
						name={ `${ name }_video_settings` }
						value={ videoSettingsValue.join( ' ' ) }
					/>
				</div>
			) }
			{ !! actions.length && (
				<div className="tasty-recipes-rich-textarea-actions">
					{ actions.map( ( action ) => (
						<button
							key={ action.label }
							type="button"
							className={ `tasty-recipes-rich-textarea-action tasty-recipes-rich-textarea-action--${ action.icon }` }
						>
							<span
								className="tasty-recipes-rich-textarea-action-icon"
								aria-hidden="true"
							></span>
							{ action.label }
						</button>
					) ) }
				</div>
			) }
		</fieldset>
	);
};
