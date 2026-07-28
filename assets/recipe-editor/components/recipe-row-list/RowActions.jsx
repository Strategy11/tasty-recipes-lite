import { __ } from '@wordpress/i18n';
import {
	AddImageIcon,
	MoveDownIcon,
	MoveUpIcon,
	ParagraphIcon,
	RowTrashIcon,
	SectionTitleIcon,
} from '../icons';
import { getOptionKeySymbol } from './utils';
import { RowIconButton } from './RowIconButton';

export const RowActions = ( {
	type,
	canMoveUp = true,
	canMoveDown = true,
	canDelete = true,
	onMoveUp = () => {},
	onMoveDown = () => {},
	onConvert = () => {},
	onAddImage = null,
	onDelete = () => {},
	mode = 'ingredients',
} ) => {
	const isGroup = 'group' === type;
	const showAddImage =
		'instructions' === mode && ! isGroup && 'function' === typeof onAddImage;
	const optionKey = getOptionKeySymbol();
	const itemNoun =
		'instructions' === mode
			? __( 'Convert to step', 'tasty-recipes-lite' )
			: __( 'Convert to ingredient', 'tasty-recipes-lite' );
	const convertLabel = isGroup
		? itemNoun
		: __( 'Convert to section title', 'tasty-recipes-lite' );

	return (
		<div className="tasty-recipes-row-actions">
			<RowIconButton
				label={ __( 'Move up', 'tasty-recipes-lite' ) }
				shortcut={ [ optionKey, '↑' ] }
				disabled={ ! canMoveUp }
				onClick={ onMoveUp }
			>
				{ MoveUpIcon }
			</RowIconButton>
			<RowIconButton
				label={ __( 'Move down', 'tasty-recipes-lite' ) }
				shortcut={ [ optionKey, '↓' ] }
				disabled={ ! canMoveDown }
				onClick={ onMoveDown }
			>
				{ MoveDownIcon }
			</RowIconButton>
			{ showAddImage && (
				<RowIconButton
					label={ __( 'Add image', 'tasty-recipes-lite' ) }
					onClick={ onAddImage }
				>
					{ AddImageIcon }
				</RowIconButton>
			) }
			<RowIconButton label={ convertLabel } onClick={ onConvert }>
				{ isGroup ? ParagraphIcon : SectionTitleIcon }
			</RowIconButton>
			<span
				className="tasty-recipes-row-actions__sep"
				aria-hidden="true"
			/>
			<RowIconButton
				label={ __( 'Delete', 'tasty-recipes-lite' ) }
				danger
				disabled={ ! canDelete }
				onClick={ onDelete }
			>
				{ RowTrashIcon }
			</RowIconButton>
		</div>
	);
};
