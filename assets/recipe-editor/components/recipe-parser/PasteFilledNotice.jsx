import { __, _n, sprintf } from '@wordpress/i18n';
import { CloseIcon } from '../icons';

export const PasteFilledNotice = ( { count, onUndo, onDismiss } ) => (
	<div className="tasty-recipes-paste-notice" role="status">
		<span className="tasty-recipes-paste-notice__message">
			{ count > 0
				? sprintf(
					/* translators: %d: number of recipe fields filled. */
					_n(
						'Filled %d field from your pasted recipe.',
						'Filled %d fields from your pasted recipe.',
						count,
						'tasty-recipes-lite'
					),
					count
				)
				: __(
					'Every matching field already had content, so nothing changed.',
					'tasty-recipes-lite'
				) }
		</span>
		{ count > 0 && (
			<button
				type="button"
				className="tasty-recipes-paste-notice__undo"
				onClick={ onUndo }
			>
				{ __( 'Undo', 'tasty-recipes-lite' ) }
			</button>
		) }
		<button
			type="button"
			className="tasty-recipes-paste-notice__close"
			onClick={ onDismiss }
			aria-label={ __( 'Dismiss', 'tasty-recipes-lite' ) }
		>
			{ CloseIcon }
		</button>
	</div>
);
