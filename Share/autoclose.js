var __OURL__ = false;

function _autoclose(event) {
	var wopener=window.opener;
	
	if ((!wopener) || (wopener.closed))	{
		self.close();
		return;
	}

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


	if (window.opener)	__OURL__ = window.opener.location.href;
	else if (window._opener)	__OURL__ = window._opener.location.href;
	setInterval('_autoclose()', 2000);

