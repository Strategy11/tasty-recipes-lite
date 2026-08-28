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

const getTinyMceMajorVersion = () => {
	const raw = window.tinyMCE?.majorVersion;
	const parsed = Number.parseInt( raw, 10 );

	return Number.isNaN( parsed ) ? 0 : parsed;
};

const stripEmptyEdgeBlocks = ed => {
	const body = ed.getBody();
	if ( ! body ) {
		return;
	}

	const removeEmptyEdge = side => {
		const node =
			'start' === side ? body.firstChild : body.lastChild;
		if (
			! node ||
			1 === body.childNodes.length ||
			! ed.dom.isEmpty( node )
		) {
			return false;
		}

		body.removeChild( node );
		return true;
	};

	while ( removeEmptyEdge( 'start' ) ) {
		// Strip leading empty paragraphs left by placeholder/paste.
	}
	while ( removeEmptyEdge( 'end' ) ) {
		// Strip trailing empty paragraphs left by paste.
	}
};

const sanitizePastedNode = node => {
	if ( ! node?.querySelectorAll ) {
		return;
	}

	node.querySelectorAll( 'h1,h2,h3,h4,h5,h6' ).forEach( heading => {
		const paragraph = node.ownerDocument.createElement( 'p' );
		paragraph.innerHTML = heading.innerHTML;
		heading.replaceWith( paragraph );
	} );

	node.querySelectorAll( '[style]' ).forEach( element => {
		element.style.fontSize = '';
		element.style.fontFamily = '';
		element.style.lineHeight = '';
		if ( ! element.getAttribute( 'style' )?.trim() ) {
			element.removeAttribute( 'style' );
		}
	} );
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
			// inlite was removed in TinyMCE 5. Keep it only for WP < 6.3.
			if ( getTinyMceMajorVersion() < 5 ) {
				settings.theme = 'inlite';
			} else {
				delete settings.theme;
			}
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
			settings.paste_webkit_styles = 'none';
			settings.paste_remove_styles_if_webkit = true;
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

			ed.on( 'PastePostProcess', event => {
				sanitizePastedNode( event.node );
				window.setTimeout( () => {
					stripEmptyEdgeBlocks( ed );
					syncPlaceholderAttr();
					syncContent();
				}, 0 );
			} );

			ed.on( 'init', () => {
				if ( bubbleToolbar && initialValue ) {
					ed.setContent( initialValue );
					stripEmptyEdgeBlocks( ed );
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
