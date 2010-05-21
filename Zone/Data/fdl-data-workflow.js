
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Fdl.Workflow * 
 * @namespace Fdl.Document
 * @extends Fdl.Document
 * @param {Object}   config
 */
Fdl.Workflow = function (config) {
    if (config.needWorkflow !== false) config.needWorkflow=true; // to retrieve all information about definition of workflow
    Fdl.Document.call(this,config);
    if (this.getProperty('doctype') != 'W') {
	// it is not a workflow
	this._data=null;
	this._attributes=null;
	this.id=null;
	Fdl.setErrorMessage('it is not a workflow document');
    }
};
Fdl.Workflow.prototype = new Fdl.Document();
Fdl.Workflow.prototype.toString= function() {
      return 'Fdl.Workflow';
};
/**
 * 
 * @return {Array} Array of states.
 */
Fdl.Workflow.prototype.getStates = function() {
    if ((! this._data) || (! this._data.workflow)) return null;
    return this._data.workflow.states;   	
};



/**
 * 
 * @return {Array} Array of transitions.
 */
Fdl.Workflow.prototype.getTransitions = function() {
    if ((! this._data) || (! this._data.workflow)) return null;
    return this._data.workflow.transitions;   	
   	
};

/**
 * 
 * @return {Array} Array of transition types.
 */
Fdl.Workflow.prototype.getTransitionTypes = function() {   
    if ((! this._data) || (! this._data.workflow)) return null;
    return this._data.workflow.transitionTypes;   	
   	
};
/**
 * @param string start
 * @param string finish
 * @param string transitionType
 * @return {Boolean} True if successful.
 */
Fdl.Workflow.prototype.addTransition = function(config) {
    if ((! this._data) || (! this._data.workflow)) return null;
    
    var states=this.getStates();
    var types=this.getTransitionTypes();

    if (! states[config.start]) {
	Fdl.setErrorMessage('addTransition: start state not exists :'+config.start);
	return null;
    }
    if (! states[config.finish]) {
	Fdl.setErrorMessage('addTransition: finish state not exists :'+config.finish);
	return null;
    }
    if (! types[config.transitionType]) {
	Fdl.setErrorMessage('addTransition: transition type  not exists :'+config.transitionType);
	return null;
    }
    config.method='addTransition';
    return this.callMethod(config);    
};
/**
 * @param string start
 * @param string finish
 * @return {Boolean} True if successful.
 */
Fdl.Workflow.prototype.removeTransition = function(config) {
    if ((! this._data) || (! this._data.workflow)) return null;
    
    var states=this.getStates();
    var types=this.getTransitionTypes();

    if (! states[config.start]) {
	Fdl.setErrorMessage('addTransition: start state not exists :'+config.start);
	return null;
    }
    if (! states[config.finish]) {
	Fdl.setErrorMessage('addTransition: finish state not exists :'+config.finish);
	return null;
    }
    
    config.method='removeTransition';
    return this.callMethod(config);    
};
/**
 * @param string key
 * @param string activity 
 * @param string label
 * @return {Boolean} True if successful.
 */
Fdl.Workflow.prototype.addState = function(config) {
    if ((! this._data) || (! this._data.workflow)) return null;
    
    var states=this.getStates();

    if (! config) {
	Fdl.setErrorMessage('addState:  missing key');
	return null;
    }
    if (! config.key) {
	Fdl.setErrorMessage('addState:  missing key');
	return null;
    }
    var myRegxp = /^([a-z0-9_-]+)$/;
    if(myRegxp.test(config.key)==false) {
	Fdl.setErrorMessage('addState: syntax error in key , must be only alphanumeric:'+config.key);
	return null;
    }

    if (states[config.key]) {
	Fdl.setErrorMessage('addState:  state already exist :'+config.key);
	return null;
    }
    
    config.method='addState';
    return this.callMethod(config);    
};
/**
 * @param string key
 * @param string activity 
 * @param string label
 * @return {Boolean} True if successful.
 */
Fdl.Workflow.prototype.modifyState = function(config) {
    if ((! this._data) || (! this._data.workflow)) return null;
    
    var states=this.getStates();

    if (! config) {
	Fdl.setErrorMessage('modifystate:  missing key');
	return null;
    }
    if (! config.key) {
	Fdl.setErrorMessage('modifystate:  missing key');
	return null;
    }
   
    if (! states[config.key]) {
	Fdl.setErrorMessage('modifystate:  state not exist :'+config.key);
	return null;
    }
    
    config.method='modifystate';
    return this.callMethod(config);    
};

/**
 * @param string key
 * @return {Boolean} True if successful.
 */
Fdl.Workflow.prototype.removeState = function(config) {
    if ((! this._data) || (! this._data.workflow)) return null;
    
    var states=this.getStates();

    if (! config) {
	Fdl.setErrorMessage('removestate:  missing key');
	return null;
    }
    if (! config.key) {
	Fdl.setErrorMessage('removestate:  missing key');
	return null;
    }
   
    if (! states[config.key]) {
	Fdl.setErrorMessage('removestate:  state not exist :'+config.key);
	return null;
    }
    
    config.method='removestate';
    return this.callMethod(config);    
};

/**
 * @param string key
 * @param string label
 * @param string preMethod
 * @param string postMethod
 * @param array ask
 * @param bool noComment
 * @return {Boolean} True if successful.
 */
Fdl.Workflow.prototype.addTransitionType = function(config) {
    if ((! this._data) || (! this._data.workflow)) return null;
    
    
    var types=this.getTransitionTypes();
    if (! config) {
	Fdl.setErrorMessage('addTransitiontype:  missing key');
	return null;
    }
    if (! config.key) {
	Fdl.setErrorMessage('addTransitiontype:  missing key');
	return null;
    }
    if (types[config.key]) {
	Fdl.setErrorMessage('addTransitiontype: type already exists :'+config.key);
	return null;
    }
    
    config.method='addTransitionType';
    return this.callMethod(config);    
};
/**
 * @param string key
 * @param string label
 * @param string preMethod
 * @param string postMethod
 * @param array ask
 * @param bool noComment
 * @return {Boolean} True if successful.
 */
Fdl.Workflow.prototype.modifyTransitionType = function(config) {
    if ((! this._data) || (! this._data.workflow)) return null;
        
    var types=this.getTransitionTypes();
    if (! config) {
	Fdl.setErrorMessage('modifyTransitiontype:  missing key');
	return null;
    }
    if (! config.key) {
	Fdl.setErrorMessage('modifyTransitiontype:  missing key');
	return null;
    }
    if (! types[config.key]) {
	Fdl.setErrorMessage('modifyTransitiontype: type not exists :'+config.key);
	return null;
    }
    
    config.method='modifyTransitionType';
    return this.callMethod(config);    
};
/**
 * @return {Boolean} True if successful.
 * @param string key
 */
Fdl.Workflow.prototype.removeTransitionType = function(config) {
    if ((! this._data) || (! this._data.workflow)) return null;
    
    var types=this.getTransitionTypes();
    if (! config) {
	Fdl.setErrorMessage('removeTransitiontype:  missing key');
	return null;
    }
    if (! config.key) {
	Fdl.setErrorMessage('removeTransitiontype:  missing key');
	return null;
    }
    if (! types[config.key]) {
	Fdl.setErrorMessage('removeTransitiontype: type not exists :'+config.key);
	return null;
    }
    
    
    config.method='removeTransitionType';
    return this.callMethod(config);    
};