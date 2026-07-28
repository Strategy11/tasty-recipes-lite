import { cloneElement, useEffect, useRef } from '@wordpress/element';
import { EMPTY_SHORTCUT } from './constants';
import {
	hideKbdTooltip,
	scheduleShowKbdTooltip,
} from './controller';

export const KbdTooltip = ( {
	label,
	shortcut = EMPTY_SHORTCUT,
	disabled = false,
	children,
} ) => {
	const buttonRef = useRef( null );

	useEffect( () => {
		return () => hideKbdTooltip();
	}, [] );

	const showTooltip = () => {
		if ( disabled || ! buttonRef.current ) {
			return;
		}

		scheduleShowKbdTooltip( {
			label,
			shortcut,
			anchor: buttonRef.current,
		} );
	};

	const trigger = cloneElement( children, {
		ref: node => {
			buttonRef.current = node;
			const { ref } = children;
			if ( 'function' === typeof ref ) {
				ref( node );
			} else if ( ref ) {
				ref.current = node;
			}
		},
		onMouseEnter: event => {
			children.props.onMouseEnter?.( event );
			showTooltip();
		},
		onMouseLeave: event => {
			children.props.onMouseLeave?.( event );
			hideKbdTooltip();
		},
		onFocus: event => {
			children.props.onFocus?.( event );
			showTooltip();
		},
		onBlur: event => {
			children.props.onBlur?.( event );
			hideKbdTooltip();
		},
		onPointerDown: event => {
			children.props.onPointerDown?.( event );
			hideKbdTooltip();
		},
	} );

	return trigger;
};
