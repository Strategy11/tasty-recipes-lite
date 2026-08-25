import { useState, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { SelectionToolbar } from './recipe-row-list/SelectionToolbar';

const CUSTOM_PLUGINS = [ 'tr_heading', 'tr_image', 'tr_video' ];

const cloneMceSettings = source => {
	if ( ! source || 'object' !== typeof source ) {
		return {};
	}

	try {
		return structuredClone( source );
	} catch ( error ) {
		return { ...source };
	}
};

const filterExternalPlugins = ( externalPlugins, allowlist ) => {
	let parsed = externalPlugins;

	if ( 'string' === typeof parsed ) {
		try {
			parsed = JSON.parse( parsed );
		} catch ( error ) {
			parsed = {};
		}
	}

	if ( ! parsed || 'object' !== typeof parsed ) {
		return {};
	}

	return allowlist.reduce( ( next, name ) => {
		if ( parsed[ name ] ) {
			next[ name ] = parsed[ name ];
		}
		return next;
	}, {} );
};

const normalizeFormatClasses = formats => {
	if ( ! formats || 'object' !== typeof formats ) {
		return formats;
	}

	Object.keys( formats ).forEach( key => {
		const format = formats[ key ];
		const items = Array.isArray( format ) ? format : [ format ];

		items.forEach( item => {
			if ( item && Array.isArray( item.classes ) ) {
				item.classes = item.classes.join( ' ' );
			}
		} );
	} );

	return formats;
};

const stripCustomPlugins = settings => {
	const next = { ...settings };

	next.plugins = 'paste,lists';
	next.external_plugins = {};

	return next;
};

export const RichTextarea = ( {
	initialValue,
	name,
	label,
	placeholder = '',
	bubbleToolbar = false,
	initialVideoSettingsValue,
	onChange = () => {},
	onFocus = () => {},
} ) => {
	const [ value, setValue ] = useState( initialValue );
	// Frozen at mount so React never rewrites the element TinyMCE owns.
	const [ seedHtml ] = useState( initialValue || '' );
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
	const hiddenInputRef = useRef( null );
	const bubbleSurfaceRef = useRef( null );
	const id = `tasty-recipes-recipe-${ name }`;
	const showVideoSettings =
		videoSettingsConfig &&
		value &&
		( value.includes( 'vimeo.com' ) ||
			value.includes( 'youtube.com' ) ||
			value.includes( 'youtu.be' ) );

	useEffect( () => {
		if ( window.tinyMCE === undefined ) {
			return;
		}

		let settings = cloneMceSettings(
			window.tinyMCEPreInit?.mceInit?.[ 'tasty-recipes-editor' ]
		);
		settings.formats = normalizeFormatClasses( settings.formats );
		settings.selector = `#${ id }`;
		settings.placeholder = placeholder;

		if ( bubbleToolbar ) {
			settings = stripCustomPlugins( settings );
			settings.theme = 'inlite';
			settings.inline = true;
			settings.content_css = false;
			settings.selection_toolbar = '';
			settings.insert_toolbar = '';
			settings.menubar = false;
			settings.statusbar = false;
			settings.toolbar = false;
			settings.toolbar1 = '';
			settings.toolbar2 = '';
			settings.toolbar3 = '';
			settings.toolbar4 = '';
		} else {
			settings.external_plugins = filterExternalPlugins(
				settings.external_plugins,
				CUSTOM_PLUGINS
			);
			settings.statusbar = true;
		}

		settings.setup = ed => {
			const syncContent = () => {
				const content = ed.getContent();
				if ( bubbleToolbar && hiddenInputRef.current ) {
					hiddenInputRef.current.value = content;
				}
				setValue( content );
				onChange( content );
			};

			ed.on( 'focus click', () => {
				onFocus();
			} );

			const syncPlaceholderAttr = () => {
				const body = ed.getBody();
				if ( ! body || ! placeholder ) {
					return;
				}

				if ( ed.dom.isEmpty( body ) ) {
					body.dataset.mcePlaceholder = placeholder;
				} else {
					delete body.dataset.mcePlaceholder;
				}
			};

			ed.on( 'init', () => {
				if ( bubbleToolbar && initialValue ) {
					ed.setContent( initialValue );
					syncContent();
				}
				syncPlaceholderAttr();
			} );
			ed.on(
				'SetContent keyup change NodeChange blur',
				syncPlaceholderAttr
			);

			ed.on( 'change keyup input SetContent blur', syncContent );
		};

		window.tinyMCE.init( settings );

		return () => {
			if ( window.tinyMCE !== undefined ) {
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
				setting => setting !== settingKey
			);

		setVideoSettingsValue( newSettings );
	};

	return (
		<fieldset>
			<label htmlFor={ id }>{ label }</label>
			{ bubbleToolbar ? (
				<>
					{ /* Seeded so the value stays readable if TinyMCE or its
					inline theme fails to load. */ }
					<div ref={ bubbleSurfaceRef }>
						<div
							id={ id }
							className="tasty-recipes-inline-editor"
							data-placeholder={ placeholder }
							dangerouslySetInnerHTML={ { __html: seedHtml } }
						/>
					</div>
					<input
						ref={ hiddenInputRef }
						type="hidden"
						name={ name }
						defaultValue={ value || '' }
					/>
					<SelectionToolbar
						containerRef={ bubbleSurfaceRef }
						surfaceSelector=".tasty-recipes-inline-editor"
					/>
				</>
			) : (
				<textarea
					ref={ textareaRef }
					id={ id }
					name={ name }
					className="tasty-recipes-tinymce"
					placeholder={ placeholder }
					defaultValue={ value || '' }
					onFocus={ onFocus }
					onChange={ e => {
						setValue( e.target.value );
						onChange( e.target.value );
					} }
				/>
			) }
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
									onChange={ e =>
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
		</fieldset>
	);
};
