var __OURL__ = false;

function _autoclose(event) {
	var wopener=window.opener;
	if (! wopener) wopener=window._opener;
	
	if ((!wopener) || (wopener.closed))	self.close();

	// if (console) console.log('test autoclose',wopener);
	var a;
	try {
		a = wopener.location;
		a = wopener.location.href;
	} catch (exception) {
		self.close();
		return;
	}
	if ((!wopener.location) || (!wopener.location.href)
			|| (wopener.location.href != __OURL__)) {
		self.close();
	}
}

if (wopener) {
	if (win.opener)	__OURL__ = window.opener.location.href;
	else if (win._opener)	__OURL__ = window._opener.location.href;
	setInterval('_autoclose()', 2000);
}
