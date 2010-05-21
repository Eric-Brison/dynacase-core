
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Fdl.Family
 * @param {Object} config
 */
Fdl.Family = function (config) {
  Fdl.Document.call(this,config);
    if (this.getProperty('doctype') != 'C') {
	// it is not a family
	this._data=null;
	this._attributes=null;
	this.id=null;
	this.context.setErrorMessage('it is not a family document');
    }
};
Fdl.Family.prototype = new Fdl.Document();
Fdl.Family.prototype.toString= function() {
      return 'Fdl.Family';
};
/**
 * add a new attribute in family
 * @param {object} config
 * <ul>
 * <li><b> string attributeId : </b></li>
 * <li><b> string label : </b></li>
 * <li><b> string type : </b></li>
 * <li><b> string visibility : </b></li>
 * <li><b> string parent  : </b></li>
 * <li><b> bool needed : </b></li>
 * <li><b> bool inTitle : </b></li>
 * <li><b> bool inAbstract : </b></li>
 * <li><b> string link : </b></li>
 * <li><b> string elink : </b></li>
 * <li><b> int order : </b></li>
 * <li><b> string phpFile : </b></li>
 * <li><b> string phpFunction : </b></li>
 * <li><b> string constraint : </b></li>
 * <li><b> object options : </b></li>
 * </ul>
 * @return {Boolean} 
 */
Fdl.Family.prototype.addAttribute = function(config) {
    if (! config) {	
	this.context.setErrorMessage('addAttribute: need parameter');
	return false;
    }
    if ((! config.attributeId) || (! config.label) || (! config.type)) {	
	this.context.setErrorMessage('addAttribute: incomplete definition');
	return false;
    }
    config.method='addattribute';
    return this.callMethod(config);	
};

Fdl.Family.prototype.modifyAttribute = function(config) {
    if (! config) {	
	this.context.setErrorMessage('modifyAttribute: need parameter');
	return false;
    }
    if ((! config.attributeId)) {	
	this.context.setErrorMessage('modifyAttribute: incomplete definition');
	return false;
    }
    if (config.options && typeof config.options=='object') config.options=JSON.stringify(config.options);
   
    config.method='modifyattribute';
    return this.callMethod(config);	
};
Fdl.Family.prototype.removeAttribute = function(config) {
    if (! config) {	
	this.context.setErrorMessage('removeAttribute: need parameter');
	return false;
    }
    if ((! config.attributeId)) {	
	this.context.setErrorMessage('removeAttribute: incomplete definition');
	return false;
    }    
    config.method='removeattribute';
    return this.callMethod(config);	
};
/**
 * get value of parameter
 * @param {String} idParameter the parameter id
 * @return {String} the value of paramter
 */
Fdl.Family.prototype.getParameterValue = function(idParameter) {
    if (this._data) {
	var p=this._data.values.param;
	var tp=p.substring(1,p.length-1).split('][');
	var s;
	for (var i=0;i<tp.length;i++) {
	    s=tp[i].split('|');
	    if ((s.length==2) && (s[0]==idParameter)) {
		return s[1];
	    }
	}
    }
    return null;
};