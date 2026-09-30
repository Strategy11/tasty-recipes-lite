import { __, sprintf } from '@wordpress/i18n';
import classNames from 'classnames';
import { NutrifoxLabelPreview } from './NutrifoxLabelPreview';
import { CheckIcon, ExternalLinkIcon, LabelPlaceholderIcon } from './icons';

const PanelNotice = ( { type = 'info', message, url } ) => (
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
					{ __( 'Open Nutrifox', 'tasty-recipes-lite' ) }
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

const LabelIdField = ( { nutrifoxId, onChange, onDone } ) => (
	<div className="tasty-recipes-nutrifox-panel__link">
		<label htmlFor="tasty-recipes-recipe-nutrifox_id">
			{ __( 'Nutrifox label ID', 'tasty-recipes-lite' ) }
		</label>
		<div className="tasty-recipes-nutrifox-panel__link-row">
			<input
				id="tasty-recipes-recipe-nutrifox_id"
				type="text"
				name="nutrifox_id"
				className="inline-field"
				placeholder={ __(
					'Paste your Nutrifox recipe ID',
					'tasty-recipes-lite'
				) }
				value={ nutrifoxId }
				onChange={ event => onChange( event.target.value ) }
			/>
			<button type="button" className="tasty-button" onClick={ onDone }>
				{ __( 'Done', 'tasty-recipes-lite' ) }
			</button>
		</div>
	</div>
);

const EmptyLabel = ( { onBuildLabel, onLinkLabel } ) => (
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
					onClick={ onBuildLabel }
				>
					{ __( 'Build from this Recipe', 'tasty-recipes-lite' ) }
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

const LinkedLabel = ( { labelId, nutrifoxUrl, onLinkLabel } ) => (
	<>
		<NutrifoxLabelPreview labelId={ labelId } nutrifoxUrl={ nutrifoxUrl } />
		<div className="tasty-recipes-nutrifox-panel__label-actions">
			<a
				className="tasty-recipes-nutrifox-panel__text-button"
				href={ `${ nutrifoxUrl }/recipes/${ labelId }/edit` }
				target="_blank"
				rel="noopener noreferrer"
			>
				{ ExternalLinkIcon }
				{ __( 'Edit in Nutrifox', 'tasty-recipes-lite' ) }
			</a>
			<button
				type="button"
				className="tasty-recipes-nutrifox-panel__text-button"
				onClick={ onLinkLabel }
			>
				{ __( 'Change label', 'tasty-recipes-lite' ) }
			</button>
		</div>
	</>
);

export const NutrifoxPanel = ( {
	connection,
	canConnect,
	isConnecting,
	notice,
	onConnect,
	trialUrl,
	nutrifoxUrl,
	logoUrl,
	nutrifoxId,
	onNutrifoxIdChange,
	isLinking,
	onBuildLabel,
	onLinkLabel,
	onLinkDone,
} ) => {
	const isConnected = 'connected' === connection.status;
	const labelId = Number.parseInt( nutrifoxId, 10 ) || 0;
	const showBody = isConnected || isLinking || labelId > 0;

	const renderBody = () => {
		if ( isLinking ) {
			return (
				<LabelIdField
					nutrifoxId={ nutrifoxId }
					onChange={ onNutrifoxIdChange }
					onDone={ onLinkDone }
				/>
			);
		}

		if ( labelId > 0 ) {
			return (
				<LinkedLabel
					labelId={ labelId }
					nutrifoxUrl={ nutrifoxUrl }
					onLinkLabel={ onLinkLabel }
				/>
			);
		}

		return (
			<EmptyLabel
				onBuildLabel={ onBuildLabel }
				onLinkLabel={ onLinkLabel }
			/>
		);
	};

	return (
		<div
			className={ classNames( 'tasty-recipes-nutrifox-panel', {
				'is-connected': isConnected,
			} ) }
		>
			{ ! isLinking && (
				<input type="hidden" name="nutrifox_id" value={ nutrifoxId } />
			) }
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
				{ ! isConnected && canConnect && (
					<ConnectActions
						isConnecting={ isConnecting }
						trialUrl={ trialUrl }
						onConnect={ onConnect }
					/>
				) }
			</div>
			{ notice && <PanelNotice { ...notice } /> }
			{ showBody && (
				<div className="tasty-recipes-nutrifox-panel__body">
					{ renderBody() }
				</div>
			) }
		</div>
	);
};
