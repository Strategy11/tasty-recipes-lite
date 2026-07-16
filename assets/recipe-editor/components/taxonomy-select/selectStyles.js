export const selectStyles = {
	control: ( base, state ) => ( {
		...base,
		minHeight: '40px',
		alignItems: 'center',
		backgroundColor: '#fff',
		borderColor: state.isFocused ? '#ED4996' : '#CACFD8',
		borderRadius: '10px',
		boxShadow: state.isFocused
			? '0 0 0 1px #ED4996'
			: '0px 1px 2px 0px rgba(10, 13, 20, 0.03)',
		'&:hover': {
			borderColor: state.isFocused ? '#ED4996' : '#a0a5ae',
		},
		overflow: 'hidden',
	} ),
	valueContainer: ( base ) => ( {
		...base,
		padding: '4px 8px',
		gap: '4px',
		flexWrap: 'wrap',
		alignItems: 'center',
		overflow: 'hidden',
	} ),
	multiValue: ( base ) => ( {
		...base,
		backgroundColor: '#F5F7FA',
		borderRadius: '6px',
	} ),
	multiValueLabel: ( base ) => ( {
		...base,
		color: '#0E121B',
		fontSize: '13px',
		padding: '2px 4px 2px 8px',
	} ),
	multiValueRemove: ( base ) => ( {
		...base,
		color: '#525866',
		borderRadius: '0 6px 6px 0',
		'&:hover': {
			backgroundColor: '#E5E7EB',
			color: '#0E121B',
		},
	} ),
	menu: ( base ) => ( {
		...base,
		borderRadius: '10px',
		border: '1px solid #CACFD8',
		boxShadow: '0 4px 12px rgba(0, 0, 0, 0.1)',
		overflow: 'hidden',
		zIndex: 1000,
	} ),
	menuList: ( base ) => ( {
		...base,
		padding: '4px 0',
	} ),
	option: ( base, state ) => ( {
		...base,
		display: 'flex',
		alignItems: 'center',
		gap: '10px',
		padding: '8px 12px',
		backgroundColor: state.isFocused ? '#F5F7FA' : 'transparent',
		color: '#0E121B',
		cursor: 'pointer',
		'&:active': {
			backgroundColor: '#F5F7FA',
		},
	} ),
	input: ( base ) => ( {
		...base,
		margin: 0,
		padding: 0,
		overflow: 'hidden',
	} ),
	indicatorSeparator: () => ( {
		display: 'none',
	} ),
	dropdownIndicator: ( base ) => ( {
		...base,
		color: '#6B7280',
		'&:hover': {
			color: '#6B7280',
		},
	} ),
};
