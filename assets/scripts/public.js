import * as FormStuff from './form';

(() => {
	const onLoad = () => {
		FormStuff.onLoad();
	};

	// Only initialize if the document has been loaded
	if (document.readyState !== 'loading')
		onLoad();
	else
		document.addEventListener('DOMContentLoaded', onLoad);
})();
