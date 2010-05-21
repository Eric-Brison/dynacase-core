
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Fdl.Action
 * @param {Object} config
 * @cfg {Fdl.context} context the current context connection
 * @cfg {String} name the action name
 * @constructor
 */

Fdl.Action = function(config) {
	if (config) {
		if (config.application) this.application=config.application;
		if ((! this.context) && this.application.context) this.context=this.application.context;
	    if (config.data)  {
	    	this.completeData(config.data);
	    }
	    
	}

};

Fdl.Action.prototype = {
	id : null,
	/** action name @type {String} */
	name : null,
	/** label @type {String} */
	label : null,
	
	_data:null,
	/** context @type {Fdl.Context} */
	context : null,
	
	toString : function() {
		return 'Fdl.Action';
	}
};

Fdl.Action.prototype.completeData = function(data) {
	if (data) {
		if (! data.error) {
			this._data=data;
			this.id=this._data.id;
			this.name=this._data.name;
			this.label=this._data.label;
		} else {
			this.context.setErrorMessage(data.error);
		}
	}
};

