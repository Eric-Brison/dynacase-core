/*!
 * Application Class
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Fdl.Application
 * @param {Object} config
 * @cfg {Fdl.context} context the current context connection
 * @cfg {String} name the application name
 * @constructor
 */

Fdl.Application = function(config) {
	if (config && config.context) {
		var data=null;
		this.context = config.context;

	    if (config.name)  {
	    	data=this.context.retrieveData({app:'DATA',action:'APPLICATION',method:'get'},config);
	    	if (data) this.completeData(data);
	    }
	    
	}

};

Fdl.Application.prototype = {
	/** application id @type {Numeric} */
	id : null,
	/** application name @type {String} */
	name : null,
	/** description @type {String} */
	description : null,
	/** icon url @type {String} */
	icon : null,
	/** version @type {Numeric} */
	version : null,
	/** internal data @type {Object} */
	_data:null,
	/** context @private @type {Fdl.Context} */
	context : null,
	affect : function(data) {
		if (data) {
			if (!data.error) {
				this._data = data;
				if (data)
					this.completeData(data);
				return true;
			} else {
				this.context.setErrorMessage(data.error);
			}
		}
		return false;
	},
	toString : function() {
		return 'Fdl.Application';
	}
};

Fdl.Application.prototype.completeData = function(data) {
	if (data) {
		if (! data.error) {
			this._data=data;
			this.id=this._data.id;
			this.name=this._data.name;
			this.description=this._data.description;
			this.label=this._data.label;
			this.available=this._data.available;
			this.icon=this._data.icon;
			this.displayable=this._data.displayable;
			this.version=this._data.version;
		} else {
			this.context.setErrorMessage(data.error);
		}
	}
};

/**
 * get value from application parameter
 * 
 * @param {object} config
 *            <ul>
 *            <li><b>id : </b>the param identificator</li>
 *            </ul>
 * @return {string}
 */
Fdl.Application.prototype.getParameter = function(config) {
	if (config && config.id) {
		var data = this.context.retrieveData( {
			app : 'DATA',
			action : 'APPLICATION',
			method : 'getParameter',
			id : config.id,
			name:this.name
		});
		if (data) {
			if (!data.error) {
				if (data.value)
					return data.value;
			} else {
				this.context.setErrorMessage(data.error);
			}
		}
	} else
		this.context.setErrorMessage("no parameter id set");

	return null;
};
/**
 * set new value to an application parameter
 * 
 * @param {object} config
 *            <ul>
 *            <li><b>id : </b>the param identificator</li>
 *            <li><b>value : </b>the value</li>
 *            </ul>
 * @return {string} the new value
 */
Fdl.Application.prototype.setParameter = function(config) {
	if (config && config.id) {
		var data = this.context.retrieveData( {
			app : 'DATA',
			action : 'APPLICATION',
			method : 'setParameter',
			name:this.name,
			id : config.id,
			value : config.value
		});
		if (data) {
			if (!data.error) {
				if (data.value)
					return data.value;
			} else {
				Fdl.setErrorMessage(data.error);
			}
		}
	} else
		Fdl.setErrorMessage("no parameter id set");

	return null;
};

/**
 * retrieve all actions executables
 * 
 * @param {object} config
 *            <ul>
 *            <li><b>reset:</b> Boolean (Optional) set to true to force an update from server
 *            </ul>
 * @return {Array} of Fdl.Action
 */
Fdl.Application.prototype.getExecutableActions = function(config) {
	if (config && config.reset) this._actions = null;
	if (this._actions) return this._actions;
	var data = this.context.retrieveData( {
		app : 'DATA',
		action : 'APPLICATION',
		method : 'getExecutableActions',
		name : this.name
	});
	if (data) {
		if (!data.error) {
			if (data.actions)
				this._actions=[];
				for (var i=0;i< data.actions.length;i++) {
					this._actions.push(new Fdl.Action({application:this,data:data.actions[i]}));
				}
				
			return this._actions;
		} else {
			this.context.setErrorMessage(data.error);
		}
	}
	return null;
};


/**
 * verify if user can execute action
 * 
 * @param {object} config
 *            <ul>
 *            <li><b> name : </b>the action name</li>
 *            </ul>
 * @return {Boolean} true if user can, false if not, null if error
 */
Fdl.Application.prototype.canExecuteAction = function(config) {
    if ((! config) || (! config.name)) {
		this.context.setErrorMessage(this.context._("data:action name not set"));
		return null;
    }
    if (! this._actions) {
    	this.getExecutableActions();
    }
    if (! this._actions) {
    	return null;
    }
    for (var i=0;i<this._actions.length;i++) {
    	if (this._actions[i].name==config.name) return true;
    }
	return false;
};
// ---------------------------------------
// add new methods to Fdl object to use application object
/**
 * @deprecated
 * @param {object} config
 *            <ul>
 *            <li><b>id : </b>the param identificator</li>
 *            </ul>
 * @return {string} the parameter's value
 */
Fdl.getParameter = function(config) {
	if (config) {
		if (config.id) {
			if (!this.application)
				this.application = new Fdl.Application();
			return this.application.getParameter(config);
		}
	}
};
/**
 * @deprecated
 * @param {object} config
 *            <ul>
 *            <li><b>id : </b>the param identificator</li>
 *            <li><b>value : </b>the value</li>
 *            </ul>
 * @return {string} the new value
 */
Fdl.setParameter = function(config) {
	if (config) {
		if (config.id) {
			if (!this.application)
				this.application = new Fdl.Application();
			return this.application.setParameter(config);
		}
	}
};
