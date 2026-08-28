const stacks = new Set();
let lastActive = null;

export const registerRowListUndo = api => {
	stacks.add( api );

	return () => {
		stacks.delete( api );
		if ( lastActive === api ) {
			lastActive = null;
		}
	};
};

export const markRowListUndoActive = api => {
	lastActive = api;
};

export const undoRowListHistory = isRedo => {
	const candidates = [ lastActive, ...stacks ].filter(
		( api, index, list ) => api && index === list.indexOf( api )
	);

	const api = candidates.find( candidate =>
		isRedo
			? candidate.canRedo()
			: candidate.canUndo() && candidate.shouldHandleUndo()
	);

	if ( ! api ) {
		return false;
	}

	isRedo ? api.redo() : api.undo();
	lastActive = api;
	return true;
};
