
const RunIf = {

	selector: function(selector, f) {
		if (document.querySelector(selector))
			return f ? f() : true;
		else
			return false;
	},

	isPage: function(pageClass, f) {
		return this.selector(`body.${pageClass}`, f);
	},

};

export default RunIf;
