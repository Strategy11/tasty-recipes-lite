import classNames from 'classnames';

const PROTOCOL_PATTERN = /^https?:\/\//i;
const PROTOCOL_ONLY_PATTERN = /^https?:\/\/$/i;

const isRawValue = value => /^\s*\[/.test( value );

export const stripUrlProtocol = value => {
	const match = String( value || '' ).match( /^(https?:\/\/)(.*)$/i );

	if ( ! match || ! match[ 2 ] ) {
		return String( value || '' );
	}

	return match[ 2 ];
};

export const withUrlProtocol = value => {
	const trimmed = String( value || '' ).trim();

	if ( ! trimmed ) {
		return '';
	}

	if ( isRawValue( trimmed ) || PROTOCOL_ONLY_PATTERN.test( trimmed ) ) {
		return trimmed;
	}

	if ( PROTOCOL_PATTERN.test( trimmed ) ) {
		return trimmed.replace( /^http:\/\//i, 'https://' );
	}

	return `https://${ trimmed }`;
};

export const toSubmitUrl = value => {
	const trimmed = String( value || '' ).trim();

	if ( ! trimmed || PROTOCOL_ONLY_PATTERN.test( trimmed ) ) {
		return '';
	}

	return trimmed;
};

export const UrlField = ( {
	id,
	name,
	value = '',
	placeholder = '',
	onChange = () => {},
} ) => {
	const usePrefix = ! isRawValue( value );
	const displayValue = usePrefix ? stripUrlProtocol( value ) : value;

	return (
		<div
			className={ classNames( 'tasty-recipes-url-field', {
				'is-raw': ! usePrefix,
			} ) }
		>
			{ usePrefix && (
				<label
					className="tasty-recipes-url-field__prefix"
					htmlFor={ id }
					aria-hidden="true"
				>
					https://
				</label>
			) }
			<input
				id={ id }
				type="text"
				inputMode="url"
				autoComplete="off"
				spellCheck={ false }
				name={ usePrefix ? undefined : name }
				className={
					usePrefix
						? 'tasty-recipes-url-field__input'
						: 'inline-field'
				}
				placeholder={ placeholder }
				value={ displayValue }
				onChange={ event =>
					onChange( withUrlProtocol( event.target.value ) )
				}
			/>
			{ usePrefix && (
				<input type="hidden" name={ name } value={ toSubmitUrl( value ) } />
			) }
		</div>
	);
};
