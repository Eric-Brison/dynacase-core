var Ih = {}; // Interface Help Control

Ih.callOpener=function(callfunc,config) {
	var wopener=window.opener;
	if ((!wopener) || wopener.closed) {
		alert("[TEXT:Cannot do this action. Parent window is closed]");
		return null;
	}
	if (wopener[callfunc]) {
		return wopener[callfunc](config);
	} else {
		alert("[TEXT:Cannot do this action. The function of parent window is not found]"
				+ ' : ' + callfunc);
	}
	return null;

};
/**
 * get value in opener document form
 * @param config
 * @return Any null if call not succeed else the return of opener function
 */
Ih.docGetFormValue= function(config) {
	return this.callOpener('getFormValue',config);
};
/**
 * set value in opener document form
 * @param config
 * @return Boolean null if call not succeed else the return of opener function
 */
Ih.docSetFormValue=function(config) {
	return this.callOpener('setFormValue',config);
};
/**
 * add new row in opener document form
 * @param config
 * @return Boolean null if call not succeed else the return of opener function
 */
Ih.docAddTableRow= function(config) {
	return this.callOpener('addTableRow',config);
};
/**
 * add new row in opener document form
 * @param config
 * @return Boolean null if call not succeed else the return of opener function
 */
Ih.docClearTableRow= function(attributename) {
	return this.callOpener('clearTableRow',attributename);
};
/**
 * add new row in opener document form
 * @param config
 * @return Boolean null if call not succeed else the return of opener function
 */
Ih.getReturnAttribute= function(index) {
	if (this.resultArguments) {
		return this.resultArguments[index];
	}
	return null;
};

