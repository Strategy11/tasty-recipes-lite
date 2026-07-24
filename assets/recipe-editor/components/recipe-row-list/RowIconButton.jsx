import {
	createPortal,
	useEffect,
	useLayoutEffect,
	useRef,
	useState,
} from '@wordpress/element';
import classNames from 'classnames';
import { EMPTY_SHORTCUT, TOOLTIP_SHOW_DELAY_MS } from './constants';

export const RowIconButton = ( {
	label,
	shortcut = EMPTY_SHORTCUT,
	disabled = false,
	danger = false,
	onClick = () => {},
	children,
} ) => {
	const buttonRef = useRef( null );
	const tipRef = useRef( null );
	const showTimerRef = useRef( null );
	const [ isVisible, setIsVisible ] = useState( false );
	const [ position, setPosition ] = useState( { top: 0, left: 0 } );
	const shortcutKey = shortcut.join( '\0' );

	const clearShowTimer = () => {
		if ( showTimerRef.current ) {
			window.clearTimeout( showTimerRef.current );
			showTimerRef.current = null;
		}
	};

	const hideTooltip = () => {
		clearShowTimer();
		setIsVisible( false );
	};

	const showTooltip = () => {
		if ( disabled ) {
			return;
		}

		clearShowTimer();
		showTimerRef.current = window.setTimeout( () => {
			setIsVisible( true );
		}, TOOLTIP_SHOW_DELAY_MS );
	};

	useLayoutEffect( () => {
		if ( ! isVisible || ! buttonRef.current || ! tipRef.current ) {
			return;
		}

		const buttonRect = buttonRef.current.getBoundingClientRect();
		const tipRect = tipRef.current.getBoundingClientRect();
		const nextLeft = Math.max(
			8,
			Math.min(
				buttonRect.left + buttonRect.width / 2 - tipRect.width / 2,
				window.innerWidth - tipRect.width - 8
			)
		);
		const nextTop = Math.max( 8, buttonRect.top - tipRect.height - 6 );

		setPosition( ( prev ) => {
			if ( prev.top === nextTop && prev.left === nextLeft ) {
				return prev;
			}

			return { top: nextTop, left: nextLeft };
		} );
	}, [ isVisible, label, shortcutKey ] );

	useEffect( () => {
		if ( ! isVisible ) {
			return undefined;
		}

		const onScrollOrResize = () => hideTooltip();
		window.addEventListener( 'scroll', onScrollOrResize, true );
		window.addEventListener( 'resize', onScrollOrResize );

		return () => {
			window.removeEventListener( 'scroll', onScrollOrResize, true );
			window.removeEventListener( 'resize', onScrollOrResize );
		};
	}, [ isVisible ] );

	useEffect( () => {
		return () => clearShowTimer();
	}, [] );

	return (
		<>
			<button
				ref={ buttonRef }
				type="button"
				className={ classNames( 'tasty-recipes-row-icon', {
					'tasty-recipes-row-icon--danger': danger,
				} ) }
				aria-label={ label }
				disabled={ disabled }
				onClick={ ( event ) => {
					hideTooltip();
					onClick( event );
				} }
				onMouseEnter={ showTooltip }
				onMouseLeave={ hideTooltip }
				onFocus={ showTooltip }
				onBlur={ hideTooltip }
				onPointerDown={ hideTooltip }
			>
				{ children }
			</button>
			{ isVisible &&
				createPortal(
					<div
						ref={ tipRef }
						className="tasty-recipes-kbd-tooltip is-visible"
						style={ {
							top: `${ position.top }px`,
							left: `${ position.left }px`,
						} }
						role="tooltip"
					>
						<span className="tasty-recipes-kbd-tooltip__label">
							{ label }
						</span>
						{ !! shortcut.length && (
							<span className="tasty-recipes-kbd-tooltip__keys">
								{ shortcut.map( ( key ) => (
									<kbd
										key={ key }
										className="tasty-recipes-kbd-tooltip__key"
									>
										{ key }
									</kbd>
								) ) }
							</span>
						) }
					</div>,
					document.body
				) }
		</>
	);
};
