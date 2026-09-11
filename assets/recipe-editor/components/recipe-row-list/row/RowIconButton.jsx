import classNames from 'classnames';
import { EMPTY_SHORTCUT, KbdTooltip } from '../../kbd-tooltip';

export const RowIconButton = ( {
	label,
	shortcut = EMPTY_SHORTCUT,
	disabled = false,
	danger = false,
	onClick = () => {},
	children,
} ) => (
	<KbdTooltip label={ label } shortcut={ shortcut } disabled={ disabled }>
		<button
			type="button"
			className={ classNames( 'tasty-recipes-row-icon', {
				'tasty-recipes-row-icon--danger': danger,
			} ) }
			aria-label={ label }
			disabled={ disabled }
			onClick={ onClick }
		>
			{ children }
		</button>
	</KbdTooltip>
);
