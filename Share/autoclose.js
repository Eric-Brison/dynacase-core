var __OURL__ = false;

function _autoclose(event) {
	if ((!window.opener) || (window.opener.closed))	self.close();

	// if (console) console.log('test autoclose',window.opener);
	var a;
	try {
		a = window.opener.location;
		a = window.opener.location.href;
	} catch (exception) {
		self.close();
		return;
	}
	if ((!window.opener.location) || (!window.opener.location.href)
			|| (window.opener.location.href != __OURL__)) {
		self.close();
	}
}

if (window.opener) {
	__OURL__ = window.opener.location.href;
	setInterval('_autoclose()', 2000);
}
