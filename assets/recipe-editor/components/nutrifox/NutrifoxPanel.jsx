import { __, _n, sprintf } from '@wordpress/i18n';
import classNames from 'classnames';
import { NutrifoxLabelPreview } from './NutrifoxLabelPreview';
import { CheckIcon, ExternalLinkIcon, LabelPlaceholderIcon } from './icons';

const PanelNotice = ( {
	type = 'info',
	message,
	url,
	linkLabel = __( 'Open Nutrifox', 'tasty-recipes-lite' ),
} ) => (
	<div
		className={ classNames(
			'tasty-recipes-nutrifox-panel__notice',
			`is-${ type }`
		) }
		role={ 'error' === type ? 'alert' : 'status' }
	>
		{ message }
		{ url && (
			<>
				{ ' ' }
				<a href={ url } target="_blank" rel="noopener">
					{ linkLabel }
				</a>
			</>
		) }
	</div>
);

const ConnectActions = ( { isConnecting, trialUrl, onConnect } ) => (
	<div className="tasty-recipes-nutrifox-panel__actions">
		{ trialUrl ? (
			<a
				className="tasty-secondary-button"
				href={ trialUrl }
				target="_blank"
				rel="noopener"
			>
				{ __( 'Try free 14 day trial', 'tasty-recipes-lite' ) }
			</a>
		) : (
			<button
				type="button"
				className="tasty-secondary-button"
				disabled={ isConnecting }
				onClick={ () => onConnect( 'trial' ) }
			>
				{ __( 'Try free 14 day trial', 'tasty-recipes-lite' ) }
			</button>
		) }
		<button
			type="button"
			className="tasty-button"
			disabled={ isConnecting }
			onClick={ () => onConnect( 'connect' ) }
		>
			{ __( 'Connect Account', 'tasty-recipes-lite' ) }
		</button>
	</div>
);

const ConnectedBadge = ( { email } ) => (
	<span
		className="tasty-recipes-nutrifox-panel__badge"
		title={
			email
				? /* translators: %s: Nutrifox account email. */
				  sprintf( __( 'Connected as %s', 'tasty-recipes-lite' ), email )
				: undefined
		}
	>
		{ CheckIcon }
		{ __( 'Connected', 'tasty-recipes-lite' ) }
	</span>
);

const LabelHeadActions = ( { editUrl, onUnlink } ) => (
	<div className="tasty-recipes-nutrifox-panel__label-actions">
		<button
			type="button"
			className="tasty-recipes-nutrifox-panel__text-button"
			onClick={ onUnlink }
		>
			{ __( 'Unlink Label', 'tasty-recipes-lite' ) }
		</button>
		<span
			className="tasty-recipes-nutrifox-panel__label-actions-separator"
			aria-hidden="true"
		/>
		<a
			className="tasty-recipes-nutrifox-panel__text-button"
			href={ editUrl }
			target="_blank"
			rel="noopener noreferrer"
		>
			{ __( 'Edit in Nutrifox', 'tasty-recipes-lite' ) }
			{ ExternalLinkIcon }
		</a>
	</div>
);

const LabelIdField = ( {
	draftId,
	isAdding,
	onDraftChange,
	onAdd,
	onGoBack,
} ) => (
	<div className="tasty-recipes-nutrifox-panel__link">
		<label htmlFor="tasty-recipes-nutrifox-label-id">
			{ __( 'Nutrifox Recipe ID', 'tasty-recipes-lite' ) }
		</label>
		<div className="tasty-recipes-nutrifox-panel__link-row">
			<input
				id="tasty-recipes-nutrifox-label-id"
				type="text"
				className="inline-field"
				placeholder={ __(
					'Paste recipe id from Nutrifox',
					'tasty-recipes-lite'
				) }
				value={ draftId }
				onChange={ event => onDraftChange( event.target.value ) }
				onKeyDown={ event => {
					if ( 'Enter' === event.key ) {
						event.preventDefault();
						if ( ! isAdding ) {
							onAdd();
						}
					}
				} }
			/>
			<button
				type="button"
				className="tasty-secondary-button"
				onClick={ onAdd }
				disabled={ isAdding }
			>
				{ isAdding
					? __( 'Checking…', 'tasty-recipes-lite' )
					: __( 'Add Label', 'tasty-recipes-lite' ) }
			</button>
		</div>
		<button
			type="button"
			className="tasty-recipes-nutrifox-panel__text-button tasty-recipes-nutrifox-panel__go-back"
			onClick={ onGoBack }
		>
			{ __( 'Go back', 'tasty-recipes-lite' ) }
		</button>
	</div>
);

const EmptyLabel = ( { isBuilding, canBuild, onBuildLabel, onLinkLabel } ) => (
	<div className="tasty-recipes-nutrifox-panel__empty">
		<span className="tasty-recipes-nutrifox-panel__empty-icon">
			{ LabelPlaceholderIcon }
		</span>
		<div className="tasty-recipes-nutrifox-panel__empty-content">
			<p>
				<strong>{ __( 'No label yet.', 'tasty-recipes-lite' ) }</strong>{ ' ' }
				{ __(
					'Build it in Nutrifox and it appears here.',
					'tasty-recipes-lite'
				) }
			</p>
			<div className="tasty-recipes-nutrifox-panel__empty-actions">
				<button
					type="button"
					className="tasty-button"
					disabled={ isBuilding || ! canBuild }
					title={
						canBuild
							? undefined
							: __(
									'Add a recipe title and ingredients first.',
									'tasty-recipes-lite'
							  )
					}
					onClick={ onBuildLabel }
				>
					{ isBuilding
						? __( 'Building…', 'tasty-recipes-lite' )
						: __( 'Build from this Recipe', 'tasty-recipes-lite' ) }
				</button>
				<button
					type="button"
					className="tasty-recipes-nutrifox-panel__text-button"
					onClick={ onLinkLabel }
				>
					{ __( 'Link existing label', 'tasty-recipes-lite' ) }
				</button>
			</div>
		</div>
	</div>
);

const PendingNotice = ( { pending } ) => {
	const count = pending.items.length;

	return (
		<PanelNotice
			type="warning"
			message={ sprintf(
				/* translators: %d: number of ingredients Nutrifox could not match. */
				_n(
					'%d ingredient needs review in Nutrifox.',
					'%d ingredients need review in Nutrifox.',
					count,
					'tasty-recipes-lite'
				),
				count
			) }
			url={ pending.editUrl }
			linkLabel={ __( 'Review ingredients', 'tasty-recipes-lite' ) }
		/>
	);
};

export const NutrifoxPanel = ( {
	connection,
	canConnect,
	isConnecting,
	notice,
	onConnect,
	trialUrl,
	nutrifoxUrl,
	logoUrl,
	label,
} ) => {
	const isConnected = 'connected' === connection.status;
	const { labelId, isLinking } = label;
	const hasPending = labelId > 0 && label.pending.items.length > 0;

	const renderHeadActions = () => {
		if ( labelId && ! isLinking ) {
			return (
				<LabelHeadActions
					editUrl={ `${ nutrifoxUrl }/recipes/${ labelId }/edit` }
					onUnlink={ label.onUnlink }
				/>
			);
		}

		if ( ! isConnected && canConnect && ! isLinking ) {
			return (
				<ConnectActions
					isConnecting={ isConnecting }
					trialUrl={ trialUrl }
					onConnect={ onConnect }
				/>
			);
		}

		return null;
	};

	const renderBody = () => {
		if ( isLinking ) {
			return (
				<div className="tasty-recipes-nutrifox-panel__body">
					<LabelIdField
						draftId={ label.draftId }
						isAdding={ label.isAdding }
						onDraftChange={ label.setDraftId }
						onAdd={ label.onAddLabel }
						onGoBack={ label.onGoBack }
					/>
				</div>
			);
		}

		if ( labelId ) {
			return (
				<div className="tasty-recipes-nutrifox-panel__body is-preview">
					<NutrifoxLabelPreview
						labelId={ labelId }
						nutrifoxUrl={ nutrifoxUrl }
					/>
				</div>
			);
		}

		if ( isConnected ) {
			return (
				<div className="tasty-recipes-nutrifox-panel__body">
					<EmptyLabel
						isBuilding={ label.isBuilding }
						canBuild={ label.canBuild }
						onBuildLabel={ label.onBuildLabel }
						onLinkLabel={ label.onLinkLabel }
					/>
				</div>
			);
		}

		return null;
	};

	return (
		<div
			className={ classNames( 'tasty-recipes-nutrifox-panel', {
				'is-connected': isConnected,
			} ) }
		>
			<input type="hidden" name="nutrifox_id" value={ labelId || '' } />
			<input
				type="hidden"
				name="nutrifox_sync"
				value={ label.isSynced ? '1' : '' }
			/>
			<div className="tasty-recipes-nutrifox-panel__head">
				<div className="tasty-recipes-nutrifox-panel__brand">
					<img
						alt={ __( 'Nutrifox', 'tasty-recipes-lite' ) }
						src={ logoUrl }
						width="96"
						height="22"
					/>
					{ isConnected && (
						<ConnectedBadge email={ connection.email } />
					) }
				</div>
				{ renderHeadActions() }
			</div>
			{ notice && <PanelNotice { ...notice } /> }
			{ label.notice && <PanelNotice { ...label.notice } /> }
			{ hasPending && <PendingNotice pending={ label.pending } /> }
			{ renderBody() }
		</div>
	);
};
