import { useState, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const CUSTOM_PLUGINS = [ 'tr_heading', 'tr_image', 'tr_video' ];

/**
 * Strip Lite custom TinyMCE plugins that target the classic toolbar.
 *
 * @param {Object} settings TinyMCE init settings.
 * @return {Object} Settings without custom plugins.
 */
const stripCustomPlugins = settings => {
	const next = { ...settings };

	if ( typeof next.plugins === 'string' ) {
		next.plugins = next.plugins
			.split( ',' )
			.map( plugin => plugin.trim() )
			.filter( plugin => plugin && ! CUSTOM_PLUGINS.includes( plugin ) )
			.join( ',' );
	}

	let externalPlugins = next.external_plugins;
	if ( typeof externalPlugins === 'string' ) {
		try {
			externalPlugins = JSON.parse( externalPlugins );
		} catch ( error ) {
			externalPlugins = {};
		}
	}

	if ( externalPlugins && typeof externalPlugins === 'object' ) {
		const filtered = { ...externalPlugins };
		CUSTOM_PLUGINS.forEach( plugin => {
			delete filtered[ plugin ];
		} );
		next.external_plugins = filtered;
	}

	return next;
};

export const RichTextarea = ( {
	initialValue,
	name,
	label,
	placeholder = '',
	actions = [],
	bubbleToolbar = false,
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
	const inlineEditorRef = useRef( null );
	const hiddenInputRef = useRef( null );
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

		let settings = Object.assign(
			{},
			window.tinyMCEPreInit.mceInit[ 'tasty-recipes-editor' ]
		);
		settings.selector = `#${ id }`;
		settings.placeholder = placeholder;

		if ( bubbleToolbar ) {
			settings = stripCustomPlugins( settings );
			settings.theme = 'inlite';
			settings.inline = true;
			settings.selection_toolbar = 'bold italic underline | quicklink';
			settings.insert_toolbar = '';
			settings.menubar = false;
			settings.statusbar = false;
			settings.toolbar = false;
			settings.toolbar1 = '';
			settings.toolbar2 = '';
			settings.toolbar3 = '';
			settings.toolbar4 = '';
		} else {
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

			// Keep last non-collapsed selection while toolbar buttons steal focus.
			let lastSelectionRect = null;
			let positionFrame = 0;
			const positionTimeouts = [];

			/**
			 * Pin the inlite bubble above the selection in viewport space.
			 *
			 * @param {DOMRect|ClientRect|null} selectionRect Selection bounds.
			 */
			const pinBubbleAbove = selectionRect => {
				const panel = document.querySelector(
					'.tasty-recipes-modal-open .mce-tinymce-inline.mce-floatpanel'
				);
				if (
					! panel ||
					getComputedStyle( panel ).display === 'none'
				) {
					return;
				}

				if ( ! selectionRect || ! selectionRect.height ) {
					return;
				}

				const panelRect = panel.getBoundingClientRect();
				const gap = 8;
				const top = selectionRect.top - panelRect.height - gap;
				const left =
					selectionRect.left +
					( selectionRect.width - panelRect.width ) / 2;

				panel.style.setProperty( 'margin', '0', 'important' );
				panel.style.setProperty( 'position', 'fixed', 'important' );
				panel.style.setProperty(
					'top',
					`${ Math.max( 8, top ) }px`,
					'important'
				);
				panel.style.setProperty(
					'left',
					`${ Math.max( 8, left ) }px`,
					'important'
				);

				panel
					.querySelectorAll( '[title]' )
					.forEach( el => el.removeAttribute( 'title' ) );
			};

			/**
			 * Prefer live selection bounds; fall back while toolbar steals focus.
			 *
			 * @return {Object|null} Viewport rect snapshot.
			 */
			const getSelectionRectSnapshot = () => {
				if ( ! ed.selection.isCollapsed() ) {
					const rect = ed.selection.getBoundingClientRect();
					if ( rect && rect.height ) {
						lastSelectionRect = {
							top: rect.top,
							left: rect.left,
							width: rect.width,
							height: rect.height,
							bottom: rect.bottom,
							right: rect.right,
						};
					}
				}

				return lastSelectionRect;
			};

			/**
			 * Schedule pins after TinyMCE's own async reposition finishes.
			 */
			const positionBubbleAboveSelection = () => {
				if ( ! bubbleToolbar ) {
					return;
				}

				const pin = () => {
					pinBubbleAbove( getSelectionRectSnapshot() );
				};

				pin();

				window.cancelAnimationFrame( positionFrame );
				positionTimeouts.forEach( timeoutId =>
					window.clearTimeout( timeoutId )
				);
				positionTimeouts.length = 0;

				positionFrame = window.requestAnimationFrame( pin );
				// Inlite repositions again after ExecCommand / button clicks.
				[ 0, 16, 50, 100 ].forEach( delay => {
					positionTimeouts.push( window.setTimeout( pin, delay ) );
				} );
			};

			if ( bubbleToolbar && initialValue ) {
				ed.on( 'init', () => {
					ed.setContent( initialValue );
					syncContent();
				} );
			}

			let quicklinkObserver = null;
			let isStylingQuicklink = false;

			/**
			 * Match prototype: keep toolbar above selection, float link chip below.
			 * TinyMCE inlite swaps toolbar for form — undo that and restyle.
			 */
			const styleQuicklinkForm = () => {
				if ( isStylingQuicklink ) {
					return;
				}

				const panel = document.querySelector(
					'.mce-tinymce-inline.mce-floatpanel'
				);
				const form = panel?.querySelector( '.mce-form' );
				const input = form?.querySelector( '.mce-textbox' );
				if ( ! panel || ! form || ! input ) {
					return;
				}

				const formVisible =
					getComputedStyle( form ).display !== 'none';
				if ( ! formVisible ) {
					return;
				}

				isStylingQuicklink = true;

				const panelBody = Array.from( panel.children ).find( child =>
					child.classList.contains( 'mce-container-body' )
				);
				const body = form.querySelector( '.mce-container-body' );
				const filepicker = form.querySelector( '.mce-filepicker' );
				const primary = form.querySelector( '.mce-btn.mce-primary' );
				const unlink = form.querySelector(
					'.mce-btn:has(.mce-i-unlink)'
				);
				const primaryButton = primary?.querySelector( 'button' );
				const selectionToolbar = Array.from(
					panel.querySelectorAll( '.mce-toolbar' )
				).find(
					toolbar =>
						toolbar.querySelector( '.mce-i-bold' ) ||
						toolbar.querySelector( '.mce-i-link' )
				);
				const selectionRect = getSelectionRectSnapshot();
				const formWidth = 283;
				const formGap = 8;

				input.setAttribute( 'placeholder', 'https://example.com' );
				input.setAttribute( 'aria-label', 'https://example.com' );

				// Panel stays the toolbar bubble — not the link chip.
				panel.style.setProperty( 'width', 'auto', 'important' );
				panel.style.setProperty( 'min-width', '0', 'important' );
				panel.style.setProperty( 'max-width', 'none', 'important' );
				panel.style.setProperty( 'height', 'auto', 'important' );
				panel.style.setProperty( 'overflow', 'visible', 'important' );

				if ( panelBody ) {
					panelBody.style.setProperty( 'width', 'auto', 'important' );
					panelBody.style.setProperty(
						'max-width',
						'none',
						'important'
					);
					panelBody.style.setProperty( 'height', 'auto', 'important' );
					panelBody.style.setProperty(
						'overflow',
						'visible',
						'important'
					);
				}

				if ( selectionToolbar ) {
					selectionToolbar.style.setProperty(
						'display',
						'block',
						'important'
					);
					selectionToolbar.style.setProperty(
						'position',
						'relative',
						'important'
					);
					selectionToolbar.style.setProperty(
						'left',
						'auto',
						'important'
					);
					selectionToolbar.style.setProperty(
						'top',
						'auto',
						'important'
					);
					selectionToolbar.style.setProperty(
						'width',
						'auto',
						'important'
					);
					selectionToolbar.style.setProperty(
						'height',
						'auto',
						'important'
					);
				}

				// Independent chip below selection (prototype link-popover).
				form.style.setProperty( 'position', 'fixed', 'important' );
				form.style.setProperty( 'z-index', '10201', 'important' );
				form.style.setProperty( 'width', `${ formWidth }px`, 'important' );
				form.style.setProperty(
					'max-width',
					`${ formWidth }px`,
					'important'
				);
				form.style.setProperty( 'height', 'auto', 'important' );
				form.style.setProperty( 'overflow', 'visible', 'important' );

				if ( selectionRect ) {
					const left = Math.max(
						8,
						selectionRect.left +
							( selectionRect.width - formWidth ) / 2
					);
					const top = selectionRect.bottom + formGap;
					form.style.setProperty( 'left', `${ left }px`, 'important' );
					form.style.setProperty( 'top', `${ top }px`, 'important' );
				}

				if ( body ) {
					body.style.setProperty(
						'width',
						`${ formWidth }px`,
						'important'
					);
					body.style.setProperty(
						'max-width',
						`${ formWidth }px`,
						'important'
					);
					body.style.setProperty( 'height', 'auto', 'important' );
					body.style.setProperty( 'display', 'flex', 'important' );
					body.style.setProperty(
						'overflow',
						'visible',
						'important'
					);
				}

				if ( filepicker ) {
					filepicker.style.setProperty(
						'width',
						'auto',
						'important'
					);
					filepicker.style.setProperty(
						'min-width',
						'0',
						'important'
					);
					filepicker.style.setProperty( 'left', 'auto', 'important' );
					filepicker.style.setProperty( 'top', 'auto', 'important' );
					filepicker.style.setProperty(
						'position',
						'relative',
						'important'
					);
					filepicker.style.setProperty(
						'flex',
						'1 1 0%',
						'important'
					);
				}

				input.style.setProperty( 'width', '100%', 'important' );

				if ( primary ) {
					primary.style.setProperty( 'left', 'auto', 'important' );
					primary.style.setProperty( 'top', 'auto', 'important' );
					primary.style.setProperty(
						'position',
						'relative',
						'important'
					);
					primary.style.setProperty(
						'flex',
						'0 0 auto',
						'important'
					);
					primary.style.setProperty( 'width', 'auto', 'important' );
					primary.style.setProperty(
						'min-width',
						'45px',
						'important'
					);
					primary.style.setProperty(
						'overflow',
						'visible',
						'important'
					);
				}

				if ( primaryButton ) {
					primaryButton
						.querySelectorAll( '.mce-ico' )
						.forEach( icon => icon.remove() );
					primaryButton.textContent = 'Add';
					primaryButton.setAttribute( 'aria-label', 'Add' );
					primaryButton.removeAttribute( 'title' );
					primaryButton.style.setProperty(
						'overflow',
						'visible',
						'important'
					);
				}

				if ( unlink ) {
					unlink.style.setProperty( 'left', 'auto', 'important' );
					unlink.style.setProperty( 'top', 'auto', 'important' );
					unlink.style.setProperty(
						'position',
						'relative',
						'important'
					);
				}

				// Toolbar bubble stays above selection while form sits below.
				pinBubbleAbove( selectionRect );

				if ( ! quicklinkObserver ) {
					quicklinkObserver = new window.MutationObserver( () => {
						if ( isStylingQuicklink ) {
							return;
						}
						window.requestAnimationFrame( styleQuicklinkForm );
					} );
					quicklinkObserver.observe( panel, {
						attributes: true,
						attributeFilter: [ 'style' ],
						subtree: true,
						childList: true,
					} );
				}

				window.requestAnimationFrame( () => {
					isStylingQuicklink = false;
				} );
			};

			const scheduleQuicklinkFormStyle = () => {
				[ 0, 16, 50, 100, 200, 400 ].forEach( delay => {
					window.setTimeout( styleQuicklinkForm, delay );
				} );
			};

			const onBubbleMouseDown = event => {
				if (
					! event.target.closest(
						'.mce-tinymce-inline.mce-floatpanel'
					)
				) {
					return;
				}
				positionBubbleAboveSelection();
				scheduleQuicklinkFormStyle();
			};

			if ( bubbleToolbar ) {
				ed.on(
					'mouseup keyup NodeChange ExecCommand',
					positionBubbleAboveSelection
				);
				ed.on( 'ExecCommand NodeChange', scheduleQuicklinkFormStyle );

				// Toolbar button mousedown can collapse selection briefly.
				document.addEventListener( 'mousedown', onBubbleMouseDown, true );
				ed.on( 'remove', () => {
					document.removeEventListener(
						'mousedown',
						onBubbleMouseDown,
						true
					);
					window.cancelAnimationFrame( positionFrame );
					positionTimeouts.forEach( timeoutId =>
						window.clearTimeout( timeoutId )
					);
					if ( quicklinkObserver ) {
						quicklinkObserver.disconnect();
						quicklinkObserver = null;
					}
				} );
			}

			ed.on( 'change keyup input SetContent blur', syncContent );
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
			{ bubbleToolbar ? (
				<>
					<div
						ref={ inlineEditorRef }
						id={ id }
						className="tasty-recipes-inline-editor"
						data-placeholder={ placeholder }
					/>
					<input
						ref={ hiddenInputRef }
						type="hidden"
						name={ name }
						defaultValue={ value || '' }
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
					onChange={ ( e ) => {
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
