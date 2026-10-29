import { __, sprintf } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import classNames from 'classnames';
import { FormField, SelectInput, SettingsGroup } from '@library';
import { trackUsage } from '../../recipe-editor/track';

const API = '/tasty-recipes/v1/ai';

const errorMessage = ( error ) =>
	error?.message ||
	__( 'Something went wrong. Please try again.', 'tasty-recipes-lite' );

// None of these inputs has a `name`: the Settings tab is a classic
// options.php form, and the connection (key and model) is saved over REST as
// soon as the user acts, never with Save Changes.
export const AiConnection = ( { initialStatus } ) => {
	const [ status, setStatus ] = useState( initialStatus );
	const [ provider, setProvider ] = useState(
		initialStatus.provider || initialStatus.providers[ 0 ]?.id || ''
	);
	const [ apiKey, setApiKey ] = useState( '' );
	const [ models, setModels ] = useState( [] );
	const [ busy, setBusy ] = useState( '' );
	const [ error, setError ] = useState( '' );
	const [ notice, setNotice ] = useState( '' );
	const [ isEditingModel, setIsEditingModel ] = useState( false );

	const providerInfo =
		status.providers.find( ( item ) => item.id === provider ) || {};
	const connectedProvider = status.providers.find(
		( item ) => item.id === status.provider
	);
	const usesConstant = Boolean( providerInfo.hasConstantKey );

	// Move focus into the dropdown when it opens, so clicking away or pressing
	// Escape (handled on its wrapper) returns to the text view.
	useEffect( () => {
		if ( ! isEditingModel ) {
			return;
		}

		// Wait for the click that opened it to finish; the dropdown is also
		// disabled (and can't take focus) until the model list has loaded.
		const timer = setTimeout( () =>
			document.querySelector( '#tasty-recipes-ai-model input' )?.focus()
		);

		return () => clearTimeout( timer );
	}, [ isEditingModel, models.length ] );

	useEffect( () => {
		if ( ! status.connected ) {
			return;
		}

		apiFetch( { path: `${ API }/models` } )
			.then( ( response ) => {
				setModels( response.models );
				setStatus( response.status );
			} )
			.catch( ( err ) => setError( errorMessage( err ) ) );
	}, [ status.connected, status.provider ] );

	const run = ( action, request ) => {
		setBusy( action );
		setError( '' );
		setNotice( '' );

		// Sends the action's outcome and provider, never the key.
		const track = ( success, errorCode = '' ) =>
			trackUsage( 'ai_connection_action', {
				action,
				success,
				error_code: errorCode,
				provider,
			} );

		return request()
			.then( () => track( true ) )
			.catch( ( err ) => {
				track( false, err?.code || 'unknown' );
				setError( errorMessage( err ) );
			} )
			.finally( () => setBusy( '' ) );
	};

	const handleConnect = () =>
		run( 'connect', () =>
			apiFetch( {
				path: `${ API }/connection`,
				method: 'POST',
				data: {
					provider,
					api_key: usesConstant ? '' : apiKey.trim(),
				},
			} ).then( ( response ) => {
				setApiKey( '' );
				setModels( response.models );
				setStatus( response.status );
				setIsEditingModel( false );
			} )
		);

	const handleDisconnect = () =>
		run( 'disconnect', () =>
			apiFetch( { path: `${ API }/connection`, method: 'DELETE' } ).then(
				( response ) => {
					setModels( [] );
					setStatus( response );
				}
			)
		);

	const handleTest = () =>
		run( 'test', () =>
			apiFetch( { path: `${ API }/test`, method: 'POST' } ).then(
				( response ) =>
					setNotice(
						sprintf(
							/* translators: %s: response time in seconds, e.g. 1.2 */
							__(
								'Connection works. The model replied in %ss.',
								'tasty-recipes-lite'
							),
							( response.durationMs / 1000 ).toFixed( 1 )
						)
					)
			)
		);

	const modelName = ( id ) => {
		const label = models.find( ( model ) => model.id === id )?.label || id;

		return id === status.recommended
			? sprintf(
				/* translators: %s: model name. */
				__( '%s (Recommended)', 'tasty-recipes-lite' ),
				label
			)
			: label;
	};

	const closeModelEdit = () => {
		if ( status.model && 'model' !== busy ) {
			setIsEditingModel( false );
		}
	};

	// Picking a model saves it right away and returns to the text view.
	const handleModelChange = ( model ) => {
		if ( model === status.model ) {
			closeModelEdit();
			return;
		}

		run( 'model', () =>
			apiFetch( {
				path: `${ API }/model`,
				method: 'POST',
				data: { model },
			} ).then( ( response ) => {
				setStatus( response );
				setIsEditingModel( false );
			} )
		);
	};

	const modelHelper = () => {
		if ( status.modelIsAuto ) {
			return '';
		}

		return status.model
			? __(
				'AI features in Tasty Recipes will use this model.',
				'tasty-recipes-lite'
			)
			: __(
				'Choose the model AI features should use.',
				'tasty-recipes-lite'
			);
	};

	const feedback = (
		<>
			{ error && (
				<p className="tasty-recipes-ai-error" role="alert">
					{ error }
				</p>
			) }
			{ notice && (
				<p className="tasty-recipes-ai-notice" role="status">
					{ notice }
				</p>
			) }
		</>
	);

	if ( status.connected ) {
		return (
			<SettingsGroup label={ __( 'AI Provider', 'tasty-recipes-lite' ) }>
				<FormField
					id="tasty-recipes-ai-provider-status"
					label={ __( 'Provider', 'tasty-recipes-lite' ) }
				>
					<div className="tasty-recipes-ai-connected">
						<span className="tasty-recipes-ai-connected-name">
							{ connectedProvider?.label }
						</span>
						<span className="tasty-recipes-ai-connected-badge">
							{ __( 'Connected', 'tasty-recipes-lite' ) }
						</span>
						<span className="tasty-recipes-ai-connected-key">
							{ 'constant' === status.keySource
								? __(
									'Key from wp-config.php',
									'tasty-recipes-lite'
								)
								: sprintf(
									/* translators: %s: last four characters of the API key. */
									__( 'Key ending in %s', 'tasty-recipes-lite' ),
									status.keyHint
								) }
						</span>
					</div>
				</FormField>
				{ isEditingModel || ! status.model ? (
					<FormField
						id="tasty-recipes-ai-model"
						label={ __( 'Model', 'tasty-recipes-lite' ) }
						variant="top-label"
						helper={ modelHelper() }
					>
						{ /* eslint-disable-next-line jsx-a11y/no-static-element-interactions */ }
						<div
							className="tasty-recipes-ai-model-select"
							onBlur={ ( event ) => {
								if (
									! event.currentTarget.contains( event.relatedTarget )
								) {
									closeModelEdit();
								}
							} }
							onKeyDown={ ( event ) => {
								if ( 'Escape' === event.key ) {
									closeModelEdit();
								}
							} }
						>
							<SelectInput
								id="tasty-recipes-ai-model"
								grouped
								isSearchable
								disabled={ ! models.length || 'model' === busy }
								options={ models.map( ( model ) => ( {
									value: model.id,
									label: modelName( model.id ),
									selected: model.id === status.model,
								} ) ) }
								handleChange={ handleModelChange }
							/>
						</div>
					</FormField>
				) : (
					<FormField
						id="tasty-recipes-ai-model"
						label={ __( 'Model', 'tasty-recipes-lite' ) }
						variant="top-label"
						helper={ modelHelper() }
					>
						<div className="tasty-recipes-ai-model-view">
							<span className="tasty-recipes-ai-model-name">
								{ modelName( status.model ) }
							</span>
							<button
								type="button"
								id="tasty-recipes-ai-model"
								className="tasty-recipes-ai-link-button"
								aria-label={ __(
									'Edit AI model',
									'tasty-recipes-lite'
								) }
								onClick={ () => setIsEditingModel( true ) }
							>
								{ __( 'Edit', 'tasty-recipes-lite' ) }
							</button>
						</div>
					</FormField>
				) }
				<div className="tasty-recipes-ai-actions">
					<button
						type="button"
						className={ classNames( 'tasty-secondary-button', {
							'is-loading': 'test' === busy,
						} ) }
						disabled={ Boolean( busy ) || ! status.model }
						onClick={ handleTest }
					>
						{ 'test' === busy
							? __( 'Testing…', 'tasty-recipes-lite' )
							: __( 'Test Connection', 'tasty-recipes-lite' ) }
					</button>
					<button
						type="button"
						className="tasty-recipes-ai-disconnect"
						disabled={ Boolean( busy ) }
						onClick={ handleDisconnect }
					>
						{ 'disconnect' === busy
							? __( 'Disconnecting…', 'tasty-recipes-lite' )
							: __( 'Disconnect', 'tasty-recipes-lite' ) }
					</button>
				</div>
				{ feedback }
			</SettingsGroup>
		);
	}

	const canConnect =
		provider &&
		( usesConstant || ( status.canEncrypt && apiKey.trim().length >= 8 ) );

	return (
		<SettingsGroup label={ __( 'AI Provider', 'tasty-recipes-lite' ) }>
			{ status.needsReconnect && (
				<p className="tasty-recipes-ai-error" role="alert">
					{ __(
						'The saved API key can no longer be read, usually because the site’s security keys changed. Enter it again to reconnect.',
						'tasty-recipes-lite'
					) }
				</p>
			) }
			<SelectInput
				id="tasty-recipes-ai-provider"
				label={ __( 'Provider', 'tasty-recipes-lite' ) }
				variant="top-label"
				options={ status.providers.map( ( item ) => ( {
					value: item.id,
					label: item.label,
					selected: item.id === provider,
				} ) ) }
				handleChange={ ( value ) => {
					setProvider( value );
					setError( '' );
				} }
			/>
			<FormField
				id="tasty-recipes-ai-api-key"
				label={ __( 'API Key', 'tasty-recipes-lite' ) }
				variant="top-label"
				helper={
					usesConstant
						? __(
							'This provider’s key is set in wp-config.php, so it isn’t stored in the database.',
							'tasty-recipes-lite'
						)
						: ''
				}
			>
				<div className="tasty-recipes-ai-key-row">
					{ ! usesConstant && (
						<input
							id="tasty-recipes-ai-api-key"
							type="password"
							value={ apiKey }
							autoComplete="off"
							spellCheck={ false }
							placeholder={ __(
								'Paste your API key',
								'tasty-recipes-lite'
							) }
							aria-invalid={ Boolean( error ) }
							disabled={ ! status.canEncrypt }
							onChange={ ( event ) => {
								setApiKey( event.target.value );
								setError( '' );
							} }
							onKeyDown={ ( event ) => {
								// Enter would submit the whole settings form.
								if ( 'Enter' === event.key ) {
									event.preventDefault();
									if ( canConnect && ! busy ) {
										handleConnect();
									}
								}
							} }
						/>
					) }
					<button
						type="button"
						className={ classNames( 'tasty-button', 'tasty-button-pink', {
							'is-loading': 'connect' === busy,
						} ) }
						disabled={ ! canConnect || Boolean( busy ) }
						onClick={ handleConnect }
					>
						{ 'connect' === busy
							? __( 'Connecting…', 'tasty-recipes-lite' )
							: __( 'Connect', 'tasty-recipes-lite' ) }
					</button>
				</div>
				{ ! usesConstant && providerInfo.credentialsUrl && (
					<a
						className="tasty-recipes-ai-key-link"
						href={ providerInfo.credentialsUrl }
						target="_blank"
						rel="noreferrer noopener"
					>
						{ sprintf(
							/* translators: %s: AI provider name, e.g. OpenAI. */
							__( 'Get your %s API key', 'tasty-recipes-lite' ),
							providerInfo.label
						) }
					</a>
				) }
				{ ! status.canEncrypt && ! usesConstant && (
					<p className="tasty-recipes-ai-error" role="alert">
						{ __(
							'This server can’t encrypt API keys. Ask your host to enable the PHP sodium extension, or define the key in wp-config.php.',
							'tasty-recipes-lite'
						) }
					</p>
				) }
			</FormField>
			{ feedback }
		</SettingsGroup>
	);
};
