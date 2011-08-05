
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Fdl.GroupRequest
 * <pre><code>
  var C=new Fdl.Context({url:http://'my.freedom'});
  var g=C.createGroupRequest();
 
  g.addRequest({d:g.getDocument({id:9});
  g.addRequest({l:g.get('d').callMethod('lock')});
  g.addRequest({c:g.get('d').callMethod('getContent')});
  g.addRequest({a:g.get('d').callMethod('getAuthorizedFamilies')});
  g.addRequest({z:g.foreach('c').callMethod('lock')});
  var r=g.submit();
  // the result is :
  var mydoc=r.get('d');
  if (r.get('l')) alert('the doc '+mydoc.getTitle()+ 'is locked by me');
  else alert(r.getError('l'));
  var content=r.get('c');
  
  for (var ic=0;ic&lt;content.length;ic++) {
  	alert((ic+1)+') '+content[ic].getTitle());
  }
  var iter=r.get('z');
  alert('testing lock of content');
  for (var ic=0;ic&lt;iter.length;ic++) {
  	if (iter[ic].error) {
  	    alert((ic+1)+') lock failed for '+iter[ic].document.getTitle()+ ': &lt;span style="color:red"&gt;'+iter[ic].error+'&lt;/span&gt;');
  	} else {
  	    alert((ic+1)+') lock succeded for '+iter[ic].document.getTitle());
  	}
   }
 * </code></pre>
 * Usage with selection object
 * <pre><code>
    var C=new Fdl.Context({url:http://'my.freedom'});
    var s=new Fdl.DocumentSelection({selectionItems:[52283,47882,47219]});
	var g=C.createGroupRequest();
	g.addRequest({s:g.getSelection(s)});
	g.addRequest({locks:g.foreach('s').callMethod('lock')});
	var r=g.submit();
	var iter=r.get('locks');
    alert('testing lock of selection');
    for (var ic=0;ic&lt;iter.length;ic++) {
  	  if (iter[ic].error) {
  	    alert((ic+1)+') lock failed for '+iter[ic].document.getTitle()+ ': &lt;span style="color:red"&gt;'+iter[ic].error+'&lt;/span&gt;');
  	  } else {
  	    alert((ic+1)+') lock succeded for '+iter[ic].document.getTitle());
  	  }
    }
 * </code></pre>
 * @namespace Fdl.GroupRequest
 * @param {Object} config
 * @cfg {Fdl.Context} context the connection {@link Fdl.Context context}
 */
Fdl.GroupRequest = function (config) {
    if (config) {
	this.context=config.context;	
    }
    this.requestItems=[];
};
Fdl.GroupRequest.prototype = {
   /**
     * Connection context
     * @type Fdl.Context
     * @property
     */
    context:null,
   /**
     * Array of requests
     * @type Array
     * @property
     */
    requestItems:[]
};
Fdl.GroupRequest.prototype.toString= function() {
      return 'Fdl.GroupRequest';
};
/**
 * Add a new request to group
 * @param {object} request
 * 
 *
 * @return {Boolean}
 */
Fdl.GroupRequest.prototype.addRequest = function(request) {
    this.requestItems.push(request);
    return true;
};

/**
 * get information about a specific request
 * @param {String } name the identificator of the request
 * 
 *
 * @return {Object} the request information, null if not found
 */
Fdl.GroupRequest.prototype.getRequest = function(name) {
    for (var i=0;i<this.requestItems.length;i++) {
	if (this.requestItems[i][name]) return this.requestItems[i][name];
    }
    return null;
};

/**
 * to retrieve document from the server
 * @param {object} config {id:<document identificator>}
 * 
 * @return {Fdl.Document}
 */
Fdl.GroupRequest.prototype.getDocument = function(config) {    
    return {method:'',config:config};
};
/**
 * to save selection before call foreach
 * @param {Fdl.DocumentSelection} selection 
 * 
 * @return {Array} identificator objects
 */
Fdl.GroupRequest.prototype.getSelection = function(selection) {
    return {method:'getSelection',config:selection};
};
/**
 * Return request document to use it to call a method
 * @param {String} name Variable name
 * 
 * @return {Fdl.GroupRequestDocument}
 */
Fdl.GroupRequest.prototype.get = function(name) {
    return new Fdl.GroupRequestDocument({gr:this,name:name});
};
/**
 * Return request collection to use it to call a method on each document of the collection
 * @param {String} name Variable name
 * 
 * @return {Fdl.GroupRequestDocument}
 */
Fdl.GroupRequest.prototype.foreach = function(name) {    
    return new Fdl.GroupRequestCollection({gr:this,name:name});
};
/**
 * Send request to the server
 * 
 * 
 * @return {Fdl.GroupRequestResult} one response by request
 */
Fdl.GroupRequest.prototype.submit = function() {    
    if (this.context) {
	var r=this.context.retrieveData({app:'DATA',action:'GROUPREQUEST'},{request:JSON.stringify(this.requestItems)});
	if (r.error) {
	    this.context.setErrorMessage(r.error);
	    return null;
	} else {
	    return new Fdl.GroupRequestResult({gr:this,result:r});
	}
    }
    return null;
};
/**
 * @class Fdl.GroupRequestDocument
 * @namespace Fdl.GroupRequest
 * @param {Object} config
 * @cfg {Fdl.Context} context the connection {@link Fdl.Context context}
 */
Fdl.GroupRequestDocument = function (config) {
    if (config) {
	this.config=config;
    }
};
Fdl.GroupRequestDocument.prototype = {   
    /**
     * Return apply method description for call
     * @param method {String} the method to call
     * @param config {Object} argument for the method call
     */
    callMethod: function (method, config) {
	return {variable:this.config.name,method:method,config:config};
    },
    toString: function () {
	 return 'Fdl.GroupRequestDocument';
    }
    
};


/**
 * @class Fdl.GroupRequestCollection
 * @namespace Fdl.GroupRequest
 * @param {Object} config
 * @cfg {Fdl.Context} context the connection {@link Fdl.Context context}
 */
Fdl.GroupRequestCollection = function (config) {
    if (config) {
	this.config=config;
    }
};
Fdl.GroupRequestCollection.prototype = {   
    /**
     * Return apply method description for call
     * @param method {String} the method to call
     * @param config {Object} argument for the method call
     */
    callMethod: function (method, config) {
	return {iterative:true,variable:this.config.name,method:method,config:config};
    },
    toString: function () {
	 return 'Fdl.GroupRequestCollection';
    }
    
};



/**
 * @class Fdl.GroupRequestResult
 * @namespace Fdl.GroupRequest
 * @cfg {Fdl.GroupRequest} gr group request
 * @cfg {Object} result the server response
 * 
 */
Fdl.GroupRequestResult = function (config) {
	if (config) {
		if (config.gr) this.gr=config.gr;
		if (config.result) this.result=config.result;
	}
};
Fdl.GroupRequestResult.prototype = {   
		/**
		 * the group request caller
		 */ 
		gr:null,
		/**
		 * Return a request result
		 * @param name {String} the identificator of the request
		 */
		get: function (name) {
	if (this.gr) {
		var request=this.gr.getRequest(name);
		if (request) {
			if (this.result[name]) {
				var res1=this.result[name];
				if (res1.error) {
					this.gr.context.setErrorMessage('GroupRequestResult::get : '+res1.error);
					return false;
				} else if (res1.properties && res1.properties.id > 0) {
					var rd=this.gr.context.getDocument({data:res1});			
					try {
						if (rd[request.method]) {
							var rm=rd[request.method]({norequest:true});
							return rm;
						}
					}  catch (ex) {
					}
					return rd;
				} else {
					// not a document
					if (request.method && request.variable) {
						var rd=this.get(request.variable);
						if (rd) {
							try {
								if (request.iterative) {
									var ld;
									var lr=[];
									var ldr;
									var lerr='';
									for (var li=0;li<res1.iterative.length;li++) {
										lerr=res1.iterative[li].error;
										res1.iterative[li].error='';
										ld=this.gr.context.getDocument({data:res1.iterative[li]});
										try {
											if (ld[request.method]) {
												if (lerr) ldr=false;
												else ldr=ld[request.method]({norequest:true,data:res1.iterative[li]});					    
												res1.iterative[li].error=lerr;
												lr.push({error:lerr,document:ld,result:ldr});
											}
										} catch (ex) {
										}
									}
									return lr;
								} else if (rd[request.method]) {
									var rm=rd[request.method]({norequest:true,data:res1});
									return rm;
								}
							}  catch (ex) {
							}
						}
					}
				}
				return res1;		    
			} else {
				this.gr.context.setErrorMessage('GroupRequestResult::get :no result for '+name);
			}
		}

	}	
},
toString: function () {
	return 'Fdl.GroupRequestResult';
},
/**
 * Return error message for a specific request
 * @param name {String} the identificator of the request
 * @return  {String} error message - may be empty if no errors
 */
getError: function (name) {
	if (this.gr) {
		var request=this.gr.getRequest(name);
		if (request) {
			if (this.result[name]) {
				return this.result[name].error;
			}
		}
	}
}
};