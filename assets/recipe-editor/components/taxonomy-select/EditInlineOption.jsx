import { useEffect, useRef } from '@wordpress/element';
import { components } from 'react-select';

export const EditInlineOption = ( {
	optionProps,
	data,
	editValue,
	onEditInputChange,
	onCancelEdit,
	onConfirmEdit,
} ) => {
	const editInputRef = useRef( null );

	useEffect( () => {
		if ( editInputRef.current ) {
			editInputRef.current.focus();
		}
	}, [] );

	return (
		<components.Option
			{ ...optionProps }
			className="tasty-taxonomy-option--editing"
		>
			<div
				className="tasty-taxonomy-edit-container"
				onClick={ ( event ) => {
					if ( event.target.closest( 'input' ) ) {
						return;
					}
					event.stopPropagation();
					event.preventDefault();
				} }
				onMouseDown={ ( event ) => {
					if ( event.target.closest( 'input' ) ) {
						return;
					}
					event.stopPropagation();
					event.preventDefault();
				} }
				role="presentation"
			>
				<div className="tasty-taxonomy-edit-field">
					<input
						ref={ editInputRef }
						type="text"
						className="tasty-taxonomy-edit-input"
						style={ {
							width: '100%',
							outline: 'none',
							boxShadow: 'none',
						} }
						value={ editValue }
						onClick={ ( event ) => {
							event.stopPropagation();
						} }
						onChange={ ( event ) =>
							onEditInputChange( event.target.value )
						}
						onMouseDown={ ( event ) => {
							event.stopPropagation();
						} }
						onFocus={ ( event ) => {
							event.target.style.outline = 'none';
							event.target.style.boxShadow = 'none';
						} }
						onKeyDown={ ( event ) => {
							event.stopPropagation();
							if ( event.key === 'Enter' ) {
								onConfirmEdit( data );
							} else if ( event.key === 'Escape' ) {
								onCancelEdit();
							}
						} }
					/>
				</div>
				<div className="tasty-taxonomy-edit-actions">
					<button
						type="button"
						className="tasty-taxonomy-edit-confirm"
						onClick={ ( event ) => {
							event.stopPropagation();
							onConfirmEdit( data );
						} }
					>
						<svg
							xmlns="http://www.w3.org/2000/svg"
							width="20"
							height="20"
							fill="none"
							viewBox="0 0 20 20"
						>
							<path
								stroke="#22C55E"
								strokeLinecap="round"
								strokeLinejoin="round"
								strokeWidth="2"
								d="M4 10l4 4 8-8"
							/>
						</svg>
					</button>
					<button
						type="button"
						className="tasty-taxonomy-edit-cancel"
						onClick={ ( event ) => {
							event.stopPropagation();
							onCancelEdit();
						} }
					>
						<svg
							xmlns="http://www.w3.org/2000/svg"
							width="20"
							height="20"
							fill="none"
							viewBox="0 0 20 20"
						>
							<path
								stroke="#ED4996"
								strokeLinecap="round"
								strokeLinejoin="round"
								strokeWidth="2"
								d="M5 5l10 10M15 5L5 15"
							/>
						</svg>
					</button>
				</div>
			</div>
		</components.Option>
	);
};
