import { useState } from '@wordpress/element';
import { applyBulkText, serializeBulkText, serializeRows } from '../lib/utils';

export const useRowListBulkEdit = ( {
	rows,
	mode,
	serializedValue,
	commitRows,
	pushHistory,
} ) => {
	const [ isBulkEditing, setIsBulkEditing ] = useState( false );
	const [ bulkText, setBulkText ] = useState( '' );

	const formValue = isBulkEditing
		? serializeRows( applyBulkText( bulkText ), mode )
		: serializedValue;

	const handleOpenBulkEdit = () => {
		setBulkText( serializeBulkText( rows ) );
		setIsBulkEditing( true );
	};

	const handleApplyBulkEdit = () => {
		const nextRows = applyBulkText( bulkText );
		if ( serializeRows( nextRows, mode ) !== serializedValue ) {
			pushHistory();
			commitRows( nextRows );
		}
		setIsBulkEditing( false );
	};

	const handleCancelBulkEdit = () => {
		setIsBulkEditing( false );
		setBulkText( '' );
	};

	return {
		isBulkEditing,
		bulkText,
		setBulkText,
		formValue,
		handleOpenBulkEdit,
		handleApplyBulkEdit,
		handleCancelBulkEdit,
	};
};
