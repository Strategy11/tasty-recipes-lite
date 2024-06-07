/**
 * WordPress dependencies
 */
const { dispatch } = wp.data;

/**
 * Fetches existing notice data and generates the corresponding notice.
 */
export default function generateNotices() {
	const { createNotice, removeNotice } = dispatch("core/notices");
	window.tastyRecipesBlockEditor.editorNotices.forEach( (notice) => {
		if ( notice.actions.length > 0 ) {
			notice.actions.map( (action) => {
				const actionUrl = action.url;
				if (action.name) {
					action.url = '';
					switch (action.name) {
						case 'dismiss_converter':
							action.onClick = (e) => {
								e.preventDefault();
								/* Send the request to action_url */
								window.fetch(actionUrl);
								removeNotice(notice.id);
							};
							break;
						case 'learn_more':
							action.onClick = () => {
								window.open(actionUrl, '_blank');
							};
							break;
						default:
							action.onClick = async (e) => {
								e.preventDefault();
								await window.fetch(actionUrl);
								location.reload();
							};
					}
				}
				return action;
			} );
		}
		createNotice(notice.type, notice.content, {
			id: notice.id || null,
			isDismissible: notice.dismissible || false,
			actions: notice.actions || [],
			onDismiss: () => {
				if (notice.dismiss_action) {
					window.fetch(notice.dismiss_action);
				}
			}
		});
	});

}
