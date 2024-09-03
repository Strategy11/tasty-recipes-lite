window.TastyRecipes = window.TastyRecipes || {};
window.TastyRecipes.cookMode = {
	wakeLockApi: false,
	wakeLock: false,
	cookModeSelector: '.tasty-recipes-cook-mode',
	init() {
		if ("wakeLock" in navigator && "request" in navigator.wakeLock) {
			this.wakeLockApi = navigator.wakeLock;
		}

		const cookModes = document.querySelectorAll(this.cookModeSelector);

		if (cookModes.length > 0) {
			for (const cookMode of cookModes) {
				if (this.wakeLockApi) {
					cookMode.querySelector('input[type="checkbox"]').addEventListener("change", event => {
						this.checkboxChange(event.target);
					}, false);
				} else {
					cookMode.style.display = "none";
				}
			}
		}
	},
	checkboxChange(checkbox) {
		if (checkbox.checked) {
			this.lock();
		} else {
			this.unlock();
		}
	},
	setCheckboxesState(state) {
		const checkboxes = document.querySelectorAll(this.cookModeSelector + ' input[type="checkbox"]');
		for (const checkbox of checkboxes) {
			checkbox.checked = state;
		}
	},
	async lock() {
		try {
			this.wakeLock = await this.wakeLockApi.request("screen");
			this.wakeLock.addEventListener("release", () => {
				this.wakeLock = false;
				this.setCheckboxesState(false);
			});
			this.setCheckboxesState(true);
		} catch (error) {
			this.setCheckboxesState(false);
		}
	},
	unlock() {
		if (this.wakeLock) {
			this.wakeLock.release();
			this.wakeLock = false;
		}
		this.setCheckboxesState(false);
	}
};

(function(callback) {
	if (document.readyState !== "loading") {
		callback();
	} else {
		document.addEventListener("DOMContentLoaded", callback);
	}
})(() => {
	window.TastyRecipes.cookMode.init();
});
