(function(){
	const controlsTemplate = document.querySelector( '#tmpl-tasty-recipes-print-controls' );
	const iframe = document.createElement('iframe');
	iframe.frameBorder = 0;
	iframe.id = 'tasty-recipes-print-controls';
	iframe.height = 51;
	iframe.title = controlsTemplate.dataset.frameTitle || '';
	document.body.prepend(iframe);

	const resizeIframe = () => {
		iframe.height = iframe.contentWindow.document.querySelector('#tr-control-form').getBoundingClientRect().height;
	};

	iframe.onload = () => {
		resizeIframe();
	};

	iframe.contentWindow.document.open();
	iframe.contentWindow.document.write( controlsTemplate.innerHTML );
	iframe.contentWindow.document.close();
	iframe.contentWindow.document.documentElement.lang = document.documentElement.lang;

	/* Keyboard users keep focus on the control they changed. Mouse users keep the Safari workaround below. */
	let isUsingKeyboard = false;
	iframe.contentWindow.document.addEventListener( 'keydown', event => {
		isUsingKeyboard = true;
		if ( ( event.ctrlKey || event.metaKey ) && 'p' === event.key.toLowerCase() ) {
			/* Print the recipe, not the iframe. */
			event.preventDefault();
			window.focus();
			window.print();
		}
	} );
	iframe.contentWindow.document.addEventListener( 'pointerdown', () => {
		isUsingKeyboard = false;
	} );

	const maybeFocusParentWindow = () => {
		if ( isUsingKeyboard ) {
			return;
		}
		/* Prevent Safari from printing the iframe. */
		window.focus();
	};

	const controlForm = iframe.contentWindow.document.querySelector('#tr-control-form');
	controlForm.querySelector('#tasty-recipes-print').addEventListener('click', () => {
		/* Prevent Safari from printing the iframe. */
		window.focus();
		window.print();
	});
	controlForm.querySelector('#tasty-recipes-options').addEventListener('click', (event) => {
		const el = event.target;
		if (el.classList.contains('active')) {
			el.classList.remove('active');
			controlForm.classList.remove('tasty-recipes-show-controls');
		} else {
			el.classList.add('active');
			controlForm.classList.add('tasty-recipes-show-controls');
		}
		resizeIframe();
		maybeFocusParentWindow();
	});

	controlForm.querySelectorAll('input').forEach((input) => {
		input.addEventListener('change', () => {
			applyPrintBodyClasses();
			maybeFocusParentWindow();
		});
	});

	const applyPrintBodyClasses = () => {
		controlForm.querySelectorAll('.tasty-recipes-print-display').forEach((input) => {
			const displayClass = input.id;
			const noDisplayClass = input.id.replace('-display-', '-hide-');
			if (input.checked) {
				document.body.classList.add(displayClass);
				document.body.classList.remove(noDisplayClass);
			} else {
				document.body.classList.remove(displayClass);
				document.body.classList.add(noDisplayClass);
			}
		});
		controlForm.querySelectorAll('.tasty-recipes-print-text-size').forEach((input) => {
			if (input.checked) {
				document.body.classList.add(input.id);
			} else {
				document.body.classList.remove(input.id);
			}
		});
	};
	applyPrintBodyClasses();
}());
