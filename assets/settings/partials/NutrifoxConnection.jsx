import { __, sprintf } from '@wordpress/i18n';
import { FormField } from '@library';
import { useNutrifoxConnection } from '../../recipe-editor/components/nutrifox/useNutrifoxConnection';

const nutrifoxConfig = window.tastyRecipesSettings?.nutrifox || {};

const getStatusText = ( { isConnected, isConnecting, connectedFor } ) => {
	if ( isConnecting ) {
		return __( 'Waiting for Nutrifox…', 'tasty-recipes-lite' );
	}

	if ( ! isConnected ) {
		return __( 'Not connected', 'tasty-recipes-lite' );
	}

	return connectedFor
		? /* translators: %s: Human-readable duration, e.g. "2 months". */
		  sprintf( __( 'Connected %s ago', 'tasty-recipes-lite' ), connectedFor )
		: __( 'Connected', 'tasty-recipes-lite' );
};

export const NutrifoxConnection = () => {
	const {
		connection,
		isConnected,
		isConnecting,
		isDisconnecting,
		notice,
		start,
		disconnect,
	} = useNutrifoxConnection( { config: nutrifoxConfig } );

	if ( ! nutrifoxConfig.restBase || ! nutrifoxConfig.canConnect ) {
		return null;
	}

	return (
		<FormField
			id="tasty-recipes-nutrifox-connection"
			label={ __( 'Nutrifox Connection', 'tasty-recipes-lite' ) }
			className="tasty-recipes-nutrifox-connection"
		>
			<div className="tasty-recipes-nutrifox-connection__body">
				<div className="tasty-recipes-nutrifox-connection__row">
					<button
						id="tasty-recipes-nutrifox-connection"
						type="button"
						className="tasty-secondary-button"
						disabled={ isConnecting || isDisconnecting }
						onClick={ () =>
							isConnected ? disconnect() : start( 'connect' )
						}
					>
						{ isConnected
							? __( 'Disconnect', 'tasty-recipes-lite' )
							: __( 'Connect', 'tasty-recipes-lite' ) }
					</button>
					<span
						className="tasty-recipes-nutrifox-connection__status"
						title={ connection.email || undefined }
					>
						{ getStatusText( {
							isConnected,
							isConnecting,
							connectedFor: connection.connectedFor,
						} ) }
					</span>
				</div>
				{ notice && (
					<p
						className={ `tasty-recipes-nutrifox-connection__notice is-${ notice.type }` }
						role={ 'error' === notice.type ? 'alert' : 'status' }
					>
						{ notice.message }
						{ notice.url && (
							<>
								{ ' ' }
								<a
									href={ notice.url }
									target="_blank"
									rel="noopener noreferrer"
								>
									{ __( 'Open Nutrifox', 'tasty-recipes-lite' ) }
								</a>
							</>
						) }
					</p>
				) }
			</div>
		</FormField>
	);
};
