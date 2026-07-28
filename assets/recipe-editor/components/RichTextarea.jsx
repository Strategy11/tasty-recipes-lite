import { useState, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	hideKbdTooltip,
	repositionKbdTooltip,
	scheduleKbdTooltipFromAnchor,
} from './kbd-tooltip';
import { getModifierKeySymbol } from './platform';

const CUSTOM_PLUGINS = [ 'tr_heading', 'tr_image', 'tr_video' ];

// Not translatable: a translated URL would render an invalid example.
const LINK_PLACEHOLDER_URL = 'https://example.com';

// Shared across Description/Notes so only one scroll handler moves the toolbar.
let activeBubbleEditorId = null;

// Panels this component parks are hidden with visibility, not display, so both
// have to be checked or the other editor's toolbar counts as the visible one.
const getVisibleFloatPanel = () => {
	const panels = document.querySelectorAll(
		'.tasty-recipes-modal-open .mce-tinymce-inline.mce-floatpanel'
	);

	return (
		Array.from( panels ).find( panel => {
			const styles = getComputedStyle( panel );
			return 'none' !== styles.display && 'hidden' !== styles.visibility;
		} ) || null
	);
};

const getBubbleToolbarHelpers = () => {
	const mod = getModifierKeySymbol();

	return [
		{
			icon: 'mce-i-bold',
			label: __( 'Bold', 'tasty-recipes-lite' ),
			keys: [ mod, 'B' ],
		},
		{
			icon: 'mce-i-italic',
			label: __( 'Italic', 'tasty-recipes-lite' ),
			keys: [ mod, 'I' ],
		},
		{
			icon: 'mce-i-underline',
			label: __( 'Underline', 'tasty-recipes-lite' ),
			keys: [ mod, 'U' ],
		},
		{
			icon: 'mce-i-link',
			label: __( 'Add link', 'tasty-recipes-lite' ),
			keys: [ mod, 'K' ],
		},
	];
};

const getHelperAriaLabel = ( { label, keys } ) =>
	`${ label } (${ keys.join( '+' ) })`;

const applyBubbleToolbarHelpers = panel => {
	if ( ! panel ) {
		return;
	}

	getBubbleToolbarHelpers().forEach( helper => {
		const iconEl = panel.querySelector( `.${ helper.icon }` );
		if ( ! iconEl ) {
			return;
		}

		const button = iconEl.closest( 'button' );
		const btnWrap = iconEl.closest( '.mce-btn' );
		const ariaLabel = getHelperAriaLabel( helper );
		const keysValue = helper.keys.join( '\u0001' );

		// Kill native browser + TinyMCE tooltips; we use the shared KbdTooltip.
		button?.removeAttribute( 'title' );
		btnWrap?.removeAttribute( 'title' );
		button?.setAttribute( 'aria-label', ariaLabel );
		button?.setAttribute( 'data-tasty-helper-label', helper.label );
		button?.setAttribute( 'data-tasty-helper-keys', keysValue );
		btnWrap?.setAttribute( 'aria-label', ariaLabel );
		btnWrap?.setAttribute( 'data-tasty-helper-label', helper.label );
		btnWrap?.setAttribute( 'data-tasty-helper-keys', keysValue );

		if ( btnWrap?.id && window.tinymce?.ui?.Control?.getById ) {
			const control = window.tinymce.ui.Control.getById( btnWrap.id );
			if ( control?.settings ) {
				control.settings.tooltip = '';
			}
		}
	} );
};

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

			ed.on( 'focus click', () => {
				onFocus();
			} );

			// Keep last non-collapsed range while toolbar buttons steal focus.
			let lastSelectionRange = null;
			let positionFrame = 0;
			let scrollFrame = 0;
			const positionTimeouts = [];

			const getModalHeaderBottom = () => {
				const title = document.querySelector(
					'.tasty-recipes-modal-open .tasty-recipes-frame-title'
				);
				if ( ! title ) {
					return 0;
				}

				return title.getBoundingClientRect().bottom;
			};

			const getModalFooterTop = () => {
				const footer = document.querySelector(
					'.tasty-recipes-modal-open .tasty-recipes-frame-toolbar'
				);
				if ( ! footer ) {
					const modal = document.querySelector(
						'.tasty-recipes-modal-open .tasty-recipes-modal'
					);
					return modal
						? modal.getBoundingClientRect().bottom
						: Number.POSITIVE_INFINITY;
				}

				return footer.getBoundingClientRect().top;
			};

			const setFloatingUiVisible = ( panel, visible ) => {
				if ( ! panel ) {
					return;
				}

				panel.style.setProperty(
					'visibility',
					visible ? 'visible' : 'hidden',
					'important'
				);
				panel.style.setProperty(
					'pointer-events',
					visible ? 'auto' : 'none',
					'important'
				);
			};

			const pinBubbleAbove = selectionRect => {
				const panel = getVisibleFloatPanel();
				if ( ! panel ) {
					return;
				}

				if ( ! selectionRect || ! selectionRect.height ) {
					setFloatingUiVisible( panel, false );
					return;
				}

				const panelRect = panel.getBoundingClientRect();
				const gap = 8;
				const headerBottom = getModalHeaderBottom();
				const footerTop = getModalFooterTop();
				const top = selectionRect.top - panelRect.height - gap;
				const panelBottom = top + panelRect.height;
				const left =
					selectionRect.left +
					( selectionRect.width - panelRect.width ) / 2;

				// Hide when selection/toolbar would leave the scrollable content area.
				if (
					top < headerBottom ||
					selectionRect.top < headerBottom ||
					panelBottom > footerTop ||
					selectionRect.bottom > footerTop
				) {
					setFloatingUiVisible( panel, false );
					return;
				}

				setFloatingUiVisible( panel, true );
				panel.style.setProperty( 'margin', '0', 'important' );
				panel.style.setProperty( 'position', 'fixed', 'important' );
				panel.style.setProperty( 'top', `${ top }px`, 'important' );
				panel.style.setProperty(
					'left',
					`${ Math.max( 8, left ) }px`,
					'important'
				);

				applyBubbleToolbarHelpers( panel );
			};

			const getSelectionRectSnapshot = () => {
				try {
					if ( ! ed.selection.isCollapsed() ) {
						lastSelectionRange = ed.selection
							.getRng()
							.cloneRange();
					}
				} catch ( error ) {
					// Selection may be unavailable while the editor tears down.
				}

				if ( ! lastSelectionRange ) {
					return null;
				}

				const rect = lastSelectionRange.getBoundingClientRect();
				if ( ! rect || ! rect.height ) {
					return null;
				}

				return {
					top: rect.top,
					left: rect.left,
					width: rect.width,
					height: rect.height,
					bottom: rect.bottom,
					right: rect.right,
				};
			};

			const pinQuicklinkFormBelow = selectionRect => {
				const panel = getVisibleFloatPanel();
				const form = panel?.querySelector( '.mce-form' );
				if (
					! form ||
					! selectionRect ||
					getComputedStyle( form ).display === 'none'
				) {
					return;
				}

				const formWidth = 283;
				const formGap = 8;
				const left = Math.max(
					8,
					selectionRect.left +
						( selectionRect.width - formWidth ) / 2
				);
				const top = selectionRect.bottom + formGap;

				form.style.setProperty( 'left', `${ left }px`, 'important' );
				form.style.setProperty( 'top', `${ top }px`, 'important' );
			};

			const ownsActiveBubble = () =>
				!! ed.id && ed.id === activeBubbleEditorId;

			const repositionFloatingUi = () => {
				if ( ! ownsActiveBubble() ) {
					return;
				}

				const selectionRect = getSelectionRectSnapshot();
				pinBubbleAbove( selectionRect );
				pinQuicklinkFormBelow( selectionRect );
				repositionKbdTooltip();
			};

			const positionBubbleAboveSelection = () => {
				if ( ! bubbleToolbar ) {
					return;
				}

				activeBubbleEditorId = ed.id;
				repositionFloatingUi();

				window.cancelAnimationFrame( positionFrame );
				positionTimeouts.forEach( timeoutId =>
					window.clearTimeout( timeoutId )
				);
				positionTimeouts.length = 0;

				positionFrame = window.requestAnimationFrame(
					repositionFloatingUi
				);

				// Inlite repositions again after ExecCommand / button clicks.
				[ 0, 16, 50, 100 ].forEach( delay => {
					positionTimeouts.push(
						window.setTimeout( repositionFloatingUi, delay )
					);
				} );
			};

			const onScrollOrResize = () => {
				if ( ! bubbleToolbar || ! ownsActiveBubble() ) {
					return;
				}

				if ( scrollFrame ) {
					return;
				}

				scrollFrame = window.requestAnimationFrame( () => {
					scrollFrame = 0;
					repositionFloatingUi();
				} );
			};

			const syncPlaceholderAttr = () => {
				const body = ed.getBody();
				if ( ! body || ! placeholder ) {
					return;
				}

				if ( ed.dom.isEmpty( body ) ) {
					body.setAttribute( 'data-mce-placeholder', placeholder );
				} else {
					body.removeAttribute( 'data-mce-placeholder' );
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

			let quicklinkObserver = null;
			let observedPanel = null;
			let isStylingQuicklink = false;

			const observePanel = panel => {
				if ( ! panel || panel === observedPanel ) {
					return;
				}

				quicklinkObserver?.disconnect();
				observedPanel = panel;
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
			};

			const styleQuicklinkForm = () => {
				if ( isStylingQuicklink || ! ownsActiveBubble() ) {
					return;
				}

				const panel = getVisibleFloatPanel();
				const form = panel?.querySelector( '.mce-form' );
				const input = form?.querySelector( '.mce-textbox' );
				observePanel( panel );
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

				input.setAttribute( 'placeholder', LINK_PLACEHOLDER_URL );
				input.setAttribute( 'aria-label', LINK_PLACEHOLDER_URL );

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
				form.style.setProperty( 'border-radius', '10px', 'important' );

				pinQuicklinkFormBelow( selectionRect );

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
					body.style.setProperty(
						'border-radius',
						'10px',
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
				input.style.setProperty( 'border-radius', '10px', 'important' );

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
					const addLabel = __( 'Add', 'tasty-recipes-lite' );
					primaryButton
						.querySelectorAll( '.mce-ico' )
						.forEach( icon => icon.remove() );
					primaryButton.textContent = addLabel;
					primaryButton.setAttribute( 'aria-label', addLabel );
					primaryButton.removeAttribute( 'title' );
					primaryButton.style.setProperty(
						'overflow',
						'visible',
						'important'
					);
					primaryButton.style.setProperty(
						'border-radius',
						'10px',
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

				window.requestAnimationFrame( () => {
					isStylingQuicklink = false;
				} );
			};

			const quicklinkTimeouts = [];

			const scheduleQuicklinkFormStyle = () => {
				window.requestAnimationFrame( styleQuicklinkForm );
				// One fallback pass covers the panel appearing a tick later;
				// the panel observer handles everything after that.
				quicklinkTimeouts.push(
					window.setTimeout( styleQuicklinkForm, 100 )
				);
			};

			const onLinkShortcutKeyDown = event => {
				const key = event.key?.toLowerCase?.() || '';
				const isLinkShortcut =
					( event.metaKey || event.ctrlKey ) && key === 'k';

				if ( ! isLinkShortcut ) {
					return;
				}

				// Inlite opens quicklink on meta+k; claim ownership + pin form.
				activeBubbleEditorId = ed.id;
				positionBubbleAboveSelection();
				scheduleQuicklinkFormStyle();
			};

			const onBubbleMouseDown = event => {
				if (
					! ownsActiveBubble() ||
					! event.target.closest(
						'.mce-tinymce-inline.mce-floatpanel'
					)
				) {
					return;
				}
				hideKbdTooltip();
				positionBubbleAboveSelection();
				scheduleQuicklinkFormStyle();
			};

			let floatPanelObserver = null;

			const onBubbleTooltipMouseOver = event => {
				if ( ! ownsActiveBubble() ) {
					return;
				}

				const btn = event.target.closest(
					'.mce-tinymce-inline.mce-floatpanel .mce-btn[data-tasty-helper-label]'
				);
				if ( ! btn ) {
					return;
				}

				scheduleKbdTooltipFromAnchor( btn );
			};

			const onBubbleTooltipMouseOut = event => {
				if ( ! ownsActiveBubble() ) {
					return;
				}

				const btn = event.target.closest(
					'.mce-tinymce-inline.mce-floatpanel .mce-btn[data-tasty-helper-label]'
				);
				if ( ! btn ) {
					return;
				}

				const next = event.relatedTarget;
				if ( next && btn.contains( next ) ) {
					return;
				}

				hideKbdTooltip();
			};

			if ( bubbleToolbar ) {
				ed.on(
					'mouseup keyup NodeChange ExecCommand',
					positionBubbleAboveSelection
				);
				ed.on(
					'keyup ExecCommand NodeChange',
					scheduleQuicklinkFormStyle
				);
				ed.on( 'keydown', onLinkShortcutKeyDown );

				document.addEventListener( 'mousedown', onBubbleMouseDown, true );
				document.addEventListener(
					'mouseover',
					onBubbleTooltipMouseOver,
					true
				);
				document.addEventListener(
					'mouseout',
					onBubbleTooltipMouseOut,
					true
				);
				window.addEventListener( 'scroll', onScrollOrResize, true );
				window.addEventListener( 'resize', onScrollOrResize );

				floatPanelObserver = new window.MutationObserver(
					mutations => {
						if ( isStylingQuicklink || ! ownsActiveBubble() ) {
							return;
						}

						const panelAdded = mutations.some( mutation =>
							Array.from( mutation.addedNodes ).some(
								node =>
									1 === node.nodeType &&
									node.classList?.contains(
										'mce-tinymce-inline'
									)
							)
						);

						if ( panelAdded ) {
							scheduleQuicklinkFormStyle();
						}
					}
				);
				floatPanelObserver.observe( document.body, {
					childList: true,
				} );

				ed.on( 'remove', () => {
					if ( activeBubbleEditorId === ed.id ) {
						activeBubbleEditorId = null;
					}
					hideKbdTooltip();
					document.removeEventListener(
						'mousedown',
						onBubbleMouseDown,
						true
					);
					document.removeEventListener(
						'mouseover',
						onBubbleTooltipMouseOver,
						true
					);
					document.removeEventListener(
						'mouseout',
						onBubbleTooltipMouseOut,
						true
					);
					window.removeEventListener(
						'scroll',
						onScrollOrResize,
						true
					);
					window.removeEventListener( 'resize', onScrollOrResize );
					window.cancelAnimationFrame( positionFrame );
					window.cancelAnimationFrame( scrollFrame );
					[ ...positionTimeouts, ...quicklinkTimeouts ].forEach(
						timeoutId => window.clearTimeout( timeoutId )
					);

					if ( quicklinkObserver ) {
						quicklinkObserver.disconnect();
						quicklinkObserver = null;
						observedPanel = null;
					}
					if ( floatPanelObserver ) {
						floatPanelObserver.disconnect();
						floatPanelObserver = null;
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
					{ /* Seeded so the value stays readable if TinyMCE or its
					inline theme fails to load. */ }
					<div
						id={ id }
						className="tasty-recipes-inline-editor"
						data-placeholder={ placeholder }
						dangerouslySetInnerHTML={ { __html: seedHtml } }
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
					onFocus={ onFocus }
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
		</fieldset>
	);
};
