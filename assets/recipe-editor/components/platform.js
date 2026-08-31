const isApplePlatform = () => {
	const platform = navigator.platform || '';
	const userAgent = navigator.userAgent || '';

	return (
		/Mac|iPhone|iPod|iPad/.test( platform ) || /Mac OS X/.test( userAgent )
	);
};

export const getOptionKeySymbol = () =>
	isApplePlatform() ? '⌥' : 'Alt';

export const getModifierKeySymbol = () =>
	isApplePlatform() ? '⌘' : 'Ctrl';
