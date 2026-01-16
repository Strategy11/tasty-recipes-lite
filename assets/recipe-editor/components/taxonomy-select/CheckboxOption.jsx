import { components } from 'react-select';
import { DeleteConfirmation } from './DeleteConfirmation';
import { EditInlineOption } from './EditInlineOption';

export const CheckboxOption = ( props ) => {
	const { isSelected, label, data, selectProps } = props;
	const {
		deleteCandidate,
		onDeleteClick,
		onCancelDelete,
		onConfirmDelete,
		onEditClick,
		editCandidate,
		editValue,
		onEditInputChange,
		onCancelEdit,
		onConfirmEdit,
	} = selectProps;
	const isDefault = data?.isDefault ?? true;
	const isDeleteCandidate = deleteCandidate?.id
		? deleteCandidate.id === data.id
		: deleteCandidate?.value === data.value;
	const isEditCandidate = editCandidate?.id
		? editCandidate.id === data.id
		: editCandidate?.value === data.value;

	if ( isEditCandidate ) {
		return (
			<EditInlineOption
				optionProps={ props }
				data={ data }
				editValue={ editValue }
				onEditInputChange={ onEditInputChange }
				onCancelEdit={ onCancelEdit }
				onConfirmEdit={ onConfirmEdit }
			/>
		);
	}

	if ( isDeleteCandidate ) {
		return (
			<DeleteConfirmation
				optionProps={ props }
				label={ label }
				data={ data }
				onCancelDelete={ onCancelDelete }
				onConfirmDelete={ onConfirmDelete }
			/>
		);
	}

	return (
		<components.Option { ...props }>
			<span
				className={ `tasty-taxonomy-checkbox ${
					isSelected ? 'tasty-taxonomy-checkbox--checked' : ''
				}` }
			>
				{ isSelected && (
					<svg
						viewBox="0 0 24 24"
						width="14"
						height="14"
						fill="currentColor"
					>
						<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
					</svg>
				) }
			</span>
			<span className="tasty-taxonomy-option-label">{ label }</span>
			{ ! isDefault && (
				<>
					<span
						className="tasty-taxonomy-option-edit"
						onClick={ ( event ) => {
							event.stopPropagation();
							onEditClick( data );
						} }
						role="button"
						tabIndex={ 0 }
						onKeyDown={ ( event ) => {
							if ( event.key === 'Enter' || event.key === ' ' ) {
								event.stopPropagation();
								onEditClick( data );
							}
						} }
						onMouseDown={ ( event ) => {
							event.stopPropagation();
							event.preventDefault();
						} }
					>
						<svg
							xmlns="http://www.w3.org/2000/svg"
							width="20"
							height="20"
							fill="none"
						>
							<path
								stroke="#ED4996"
								strokeLinecap="round"
								strokeLinejoin="round"
								strokeWidth="1.5"
								d="m3.958 16.042 3.542-.834 7.744-7.744a.833.833 0 0 0 0-1.178l-1.53-1.53a.833.833 0 0 0-1.178 0L4.792 12.5l-.834 3.542ZM16.042 16.042h-4.584"
							/>
						</svg>
					</span>
					<span
						className="tasty-taxonomy-option-delete"
						onClick={ ( event ) => {
							event.stopPropagation();
							onDeleteClick( data );
						} }
						role="button"
						tabIndex={ 0 }
						onKeyDown={ ( event ) => {
							if ( event.key === 'Enter' || event.key === ' ' ) {
								event.stopPropagation();
								onDeleteClick( data );
							}
						} }
						onMouseDown={ ( event ) => {
							event.stopPropagation();
							event.preventDefault();
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
								stroke="#ed4996"
								strokeLinecap="round"
								strokeLinejoin="round"
								strokeWidth="1.25"
								d="m4.792 6.458.7 8.061a1.667 1.667 0 0 0 1.661 1.523h4.86c.865 0 1.586-.661 1.661-1.523l.701-8.06zM8.125 8.958v4.584M11.042 8.958v4.584M7.292 6.458v-.833c0-.92.746-1.667 1.666-1.667h1.25c.92 0 1.667.746 1.667 1.667v.833M3.958 6.458h11.25"
							/>
						</svg>
					</span>
				</>
			) }
		</components.Option>
	);
};
