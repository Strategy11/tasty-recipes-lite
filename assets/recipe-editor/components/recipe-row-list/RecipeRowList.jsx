import { useMemo, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { BulkEditPanel, useRowListBulkEdit } from './bulk';
import {
	useRowListActions,
	useRowListDrag,
	useRowListFlip,
	useRowListHistory,
} from './hooks';
import { clearIsNew, parseRowsFromHtml, serializeRows } from './lib/utils';
import { RecipeRow, RowListFooter } from './row';
import { SelectionToolbar } from './toolbar';

const RecipeRowList = ( {
	name,
	label,
	placeholder = '',
	mode = 'ingredients',
	initialValue = '',
	onChange = () => {},
	addItemLabel: addItemLabelProp,
} ) => {
	const [ rows, setRows ] = useState( () =>
		parseRowsFromHtml( initialValue )
	);
	const [ hasTextSelection, setHasTextSelection ] = useState( false );
	const listRef = useRef( null );
	const rowsRef = useRef( rows );
	const pendingFlipRef = useRef( null );
	const pendingScrollRowIdRef = useRef( null );
	const groupPlaceholder = __( 'For the …', 'tasty-recipes-lite' );
	const isInstructions = 'instructions' === mode;
	const addItemLabel =
		addItemLabelProp ||
		( isInstructions
			? __( 'Add Step', 'tasty-recipes-lite' )
			: __( 'Add Ingredient', 'tasty-recipes-lite' ) );

	rowsRef.current = rows;

	const serializedValue = useMemo(
		() => serializeRows( rows, mode ),
		[ rows, mode ]
	);

	const commitRows = nextRows => {
		setRows( nextRows );
		onChange( serializeRows( nextRows.map( clearIsNew ), mode ) );
	};

	const { lastChangeRef, pushHistory } = useRowListHistory( {
		rowsRef,
		listRef,
		pendingFlipRef,
		commitRows,
	} );
	const {
		isBulkEditing,
		bulkText,
		setBulkText,
		formValue,
		handleOpenBulkEdit,
		handleApplyBulkEdit,
		handleCancelBulkEdit,
	} = useRowListBulkEdit( {
		rows,
		mode,
		serializedValue,
		commitRows,
		pushHistory,
	} );
	const {
		handleValueChange,
		handleAddItem,
		handleAddSectionTitle,
		handleSplitRow,
		handlePaste,
		handleMoveRow,
		handleConvertRow,
		handleAddImage,
		handleEditImage,
		handleRemoveImage,
		handleDeleteRow,
	} = useRowListActions( {
		rows,
		rowsRef,
		isInstructions,
		lastChangeRef,
		pendingScrollRowIdRef,
		pendingFlipRef,
		listRef,
		commitRows,
		pushHistory,
	} );
	const { draggingId, handleGripPointerDown } = useRowListDrag( {
		listRef,
		rowsRef,
		commitRows,
		pushHistory,
	} );

	useRowListFlip( rows, listRef, pendingFlipRef, pendingScrollRowIdRef );

	const itemCount = rows.filter( entry => 'item' === entry.type ).length;

	return (
		<>
			<fieldset className="tasty-recipes-row-list-field">
				<label htmlFor={ isBulkEditing ? `${ name }-bulk` : undefined }>
					{ label }
				</label>
				<input type="hidden" name={ name } value={ formValue } />
				{ isBulkEditing ? (
					<BulkEditPanel
						name={ name }
						field={ mode }
						showImage={ isInstructions }
						bulkText={ bulkText }
						setBulkText={ setBulkText }
						onApply={ handleApplyBulkEdit }
						onCancel={ handleCancelBulkEdit }
					/>
				) : (
					<>
						<div
							className={
								hasTextSelection
									? 'tasty-recipes-row-list tasty-recipes-row-list--has-selection'
									: 'tasty-recipes-row-list'
							}
							ref={ listRef }
							data-mode={ mode }
						>
							{ rows.map( ( row, index ) => (
								<RecipeRow
									key={ row.id }
									row={ row }
									mode={ mode }
									placeholder={ placeholder }
									groupPlaceholder={ groupPlaceholder }
									canMoveUp={ index > 0 }
									canMoveDown={ index < rows.length - 1 }
									canDelete={
										'group' === row.type || itemCount > 1
									}
									isDragging={ draggingId === row.id }
									onValueChange={ handleValueChange }
									onMoveUp={ () =>
										handleMoveRow( row.id, -1 )
									}
									onMoveDown={ () =>
										handleMoveRow( row.id, 1 )
									}
									onConvert={ () =>
										handleConvertRow( row.id )
									}
									onSplit={ handleSplitRow }
									onAddImage={
										isInstructions ? handleAddImage : null
									}
									onEditImage={ handleEditImage }
									onRemoveImage={ handleRemoveImage }
									onDelete={ () =>
										handleDeleteRow( row.id )
									}
									onPaste={ handlePaste }
									onGripPointerDown={ handleGripPointerDown }
								/>
							) ) }
						</div>
						<RowListFooter
							addItemLabel={ addItemLabel }
							onAddItem={ handleAddItem }
							onAddSectionTitle={ handleAddSectionTitle }
							onOpenBulkEdit={ handleOpenBulkEdit }
						/>
					</>
				) }
			</fieldset>
			{ ! isBulkEditing && (
				<SelectionToolbar
					containerRef={ listRef }
					field={ mode }
					onHasSelectionChange={ setHasTextSelection }
				/>
			) }
		</>
	);
};

export const IngredientsList = props => (
	<RecipeRowList mode="ingredients" { ...props } />
);

export const InstructionsList = props => (
	<RecipeRowList mode="instructions" { ...props } />
);
