
/*!
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */
/**
 * @class Fdl.Context The connection context object to access to freedom
 *        documents
 * 
 * <pre><code>
  var C=new Fdl.Context({url:'http://my.freedom/'});
  if (! C.isConnected()) {   
    alert('error connect:'+C.getLastErrorMessage());
    return;
  }
  
  if (! C.isAuthenticated()) {
    var u=C.setAuthentification({login:'admin',password:'anakeen'});
    if (!u)  alert('error authent:'+C.getLastErrorMessage());    
  }
  &lt;core&gt;
 * </pre>
 * @param {Object} config
 * @cfg {String} url the url to reach freedom server 
 * @constructor
 */
Fdl.Context = function(config) {
	if (! config) config={};
	if (config) {
		if (! config.url) this.url = window.location.protocol+'//'+window.location.host+window.location.pathname;
		else this.url = config.url;
		if (this.url.substr(-4, 4) == '.php')
			this.url += '?';
		else if (this.url.substr(-1, 1) != '/')
			this.url += '/';
	}
};
Fdl.Context.prototype = {
		url : '',
		_isConnected : null,
		_isAuthenticated : null,
		_serverTime : null,
		_documents : new Object(), // cache for latest revision document
		_fixDocuments : new Object(),// cache for fix revision document
		notifier : null,
		lastErrorMessage : '',
		lastErrorCode : '',
		/** debug mode @type {boolean} */
		debug : false,
		propertiesInformation:null,
		catalog:null,
		/** translation catalog is autoloaded, set to false if you don't wan't autoload @type {Boolean}*/
		autoLoadCatalog:true, 
		/** default locale 'fr' (french) or 'en' (english) @type {String}*/
		locale:null, 
		/** mapping family's document to special js class @type {Object}*/
		familyMap:null,
		getPropertiesInformation: function() {
	if (! this.propertiesInformation) {
			// not initialised yet i retreive the folder family
			this.getDocument({id:2,useCache:false,onlyValues:false,propertiesInformation:true});			
	}
	return this.propertiesInformation;
}

};



Fdl.Context.prototype.toString = function() {
	return 'Fdl.Context';
};

/**
 * load catalog of translation
 * @param {String} (optional) define an other locale
 * @return {Boolean} true if loaded is done
 */
Fdl.Context.prototype.loadCatalog = function(locale) { 
	if (! locale) locale=this.locale;
	if (!locale) {
		var u=this.getUser();
		if (u.locale) {
			this.locale=u.locale.substr(0,u.locale.indexOf('_'));
			locale=this.locale;
		} else locale='fr';
	}
	var url='locale/'+locale+'/js/catalog.js';
	console.log("load catalog"+url);
	var c=this.retrieveData('', '', true, url);
	if (c) {
		this.catalog=c;
		return true;
	} else this.catalog=false;
	return false;
};
/**
 * get all sortable properties
 * @return {array} of property identificators
 */
Fdl.Context.prototype.getSortableProperties = function() { 
	var f=[];
		var pi=this.getPropertiesInformation();
		for (var i in pi) {
			if (pi[i].sortable) f.push(i);
		}
	
	return f;
};
/**
 * get information about property
 * @param {String} id the property id
 * @return {Object} example : {"type":"integer","displayable":true,"sortable":true,"filterable":true,"label":"identificateur"},
 */
Fdl.Context.prototype.getPropertyInformation = function(id) { 
	var pis=this.getPropertiesInformation();
	
	if (pis) {
		var pi=pis[id];
		if (pi) return pi;
	} 
	return null;
};
/**
  * get all displayable properties
  * @return {array} of property identificators
  */
Fdl.Context.prototype.getDisplayableProperties = function() { 
	var f=[];
	
		var pi=this.getPropertiesInformation();
		for (var i in pi) {
			if (pi[i].displayable) f.push(i);
		}
	
	return f;
};

/**
 * get all filterable properties
 * @return {array} of property identificators
 */
Fdl.Context.prototype.getFilterableProperties = function() { 
	var f=[];
	
		var pi=this.getPropertiesInformation();
		for (var i in pi) {
			if (pi[i].filterable) f.push(i);
		}
	
	return f;
};

/**
 * To reconnect to another freedom server context
 * 
 * @param {object}
 *            config
 *            <p>
 *            <ul>
 *            <li><b>url:</b> String the url to reach freedom server</li>
 *            </ul>
 *            </p>
 * @return {Void}
 */
Fdl.Context.prototype.connect = function(config) {
	if (config) {
		if (config.url) {
			this.url = config.url;
			if (this.url.substr(-4, 4) == '.php')
				this.url += '?';
			else if (this.url.substr(-1, 1) != '/')
				this.url += '/';
		}
	}
};
/**
 * Try to ping server if connection is detected one time return always true
 * 
 * @param {object}
 *            config
 *            <p>
 *            <ul>
 *            <li><b>reset:</b> Boolean (Optional) set to true to force a new ping</li>
 *            <li><b>timeout:</b> Number (Optional) millisecond to wait connection/ need to have callback</li>
  *           <li><b>onConnect:</b> Function (Optional) callback call when connection is ok</li>
  *           <li><b>onFail:</b> Function (Optional) callback call when connection has failed</li>

 *            </ul>
 *            </p>
 * @return {Boolean} true if connected
 */
Fdl.Context.prototype.isConnected = function(config) {
	if (typeof config == 'object' && config.reset) this._isConnected = null;
	if (this._isConnected === null && this.url) {
	    var lconfig={};
	    var me=this;
	    if (config && config.onConnect && config.onFail) {
	        lconfig.onComplete=function () {
	            me._isConnected=true;
	            if (me._connectTimeId) {
	                clearTimeout(me._connectTimeId);
	                me._connectTimeId=0;
	            }
	            config.onConnect();
	        };
            lconfig.onError=function (x) {
                if (me._isConnected === null) {
                    me._isConnected=false;
                    if (me._connectTimeId) {
                        clearTimeout(me._connectTimeId);
                        me._connectTimeId=0;
                    }
                    config.onFail();
                }
            };
            
            if (config.timeout > 0) {
                me._connectTimeId=setTimeout(function () {
                    if (me._isConnected === null) {
                        me._isConnected=false;
                        me._connectTimeId=0;
                        config.onFail();
                    }
                },config.timeout);
            }
	    }
		var data = this.retrieveData( {
			app : 'DATA',
			action : 'USER',
			method : 'ping'
		}, lconfig, true);
		this._serverTime = null;
		if (data) {
			if (data.error) {
				this.setErrorMessage(data.error);
				this._isConnected = false;
			} else {
				this._isConnected = true;
				this._serverTime = data.time;
			}
		}
	}
	return this._isConnected;
};
/**
 * Verify is user is already authenticated
 * 
 * @param {object}
 *            config
 *            <p>
 *            <ul>
 *            <li><b>reset :</b> Boolean set to true to force new detection</li>
 *            </ul>
 *            </p>
 * @return {Boolean} true if authentication succeded
 */
Fdl.Context.prototype.isAuthenticated = function(config) {
	if (config && config.reset) this._isAuthenticated = null;
	if (this._isAuthenticated === null) {
		var userdata = this.retrieveData( {
			app : 'DATA',
			action : 'USER'
		});
		if (userdata) {
			if (userdata.error) {
				this.setErrorMessage(userdata.error);
				this._isAuthenticated = false;
			} else {
				if (!this.user)
					this.user = new Fdl.User( {
						data : userdata,
						context : this
					});
				if (this.autoLoadCatalog) this.loadCatalog();
				this._isAuthenticated = true;
			}
		}
	}
	return this._isAuthenticated;
};

/**
 * Authenticate to server
 * 
 * @param {object}
 *            config
 *            <p>
 *            <ul>
 *            <li><b>login :</b> String the login</li>
 *            <li><b>password :</b> String the password (clear password)</li>
 *            </ul>
 *            </p>
 * @return {Fdl.User} the current user authentication succeded, null if no
 *         succeed.
 */
Fdl.Context.prototype.setAuthentification = function(config) {
	if (config) {
		if (!config.login) {
			this.setErrorMessage(this._("data::login not defined"));
			return null;
		}
		if (!config.password) {
			this.setErrorMessage(this._("data::password not defined"));
			return null;
		}
		var userdata = this.retrieveData( {
			app : 'DATA',
			action : 'USER',
			method : 'authent'
		}, config, true);
		if (userdata.error) {
			this._isAuthenticated = false;
			this.setErrorMessage(userdata.error);
			return null;
		} else {
			this._isAuthenticated = true;
			this.user = new Fdl.User( {
				data : userdata,
				context : this
			});
			if (this.user.locale) this.locale=this.user.locale.substr(0,this.user.locale.indexOf('_'));
			return this.user;
		}
	}
};
/**
 * set error message
 * @param {String} msg the text message 
 * @param {String} code the code message
 * @return boolean
 */
Fdl.Context.prototype.setErrorMessage = function(msg, code) {
	if (msg) {
		this.lastErrorMessage = msg;
	    if (code) this.lastErrorCode = code;
	}
};
/**
 * return last error message
 * @return {String}
 */
Fdl.Context.prototype.getLastErrorMessage = function() {
	return this.lastErrorMessage;
};
/**
 * return translate message
 * @param {String} s the text to translate 
 * @return {String}
 */
Fdl.Context.prototype.getText = function(s) {
	return this._(s);
};
/**
 * return translate message
 * @param {String} s the text to translate 
 * @return {String}
 */
Fdl.Context.prototype._ = function(s) {
	if ((this.catalog===null) && (this.autoLoadCatalog)) this.loadCatalog();
	if (this.catalog && s && this.catalog[s]) return this.catalog[s];
	return s;
};

/**
 * Return user currently connected if already authenticate return always the
 * same object
 * 
 * @param {object}
 *            config
 *            <p>
 *            <ul>
 *            <li><b>reset:</b> Boolean (Optional) set to true to force a new
 *            request to update user information
 *            </ul>
 *            </p>
 * @return {Fdl.User} the user object
 */
Fdl.Context.prototype.getUser = function(config) {
	if (config) {
		if (config.reset)
			this.user = null;
	}
	if (!this.user) {
		this.user = new Fdl.User( {
			context : this
		});			
		if (this.user.locale) this.locale=this.user.locale.substr(0,this.user.locale.indexOf('_'));

	}
	return this.user;
};
Fdl.Context.prototype.resizeImage = function(icon, width) {
	var u = this.url;
	var src = this.url + icon;
	var ps = u.lastIndexOf('/');
	if (ps) {
		u = u.substring(0, ps + 1);
		src = u + icon;
	}
	src = u + 'resizeimg.php?size=' + width + '&img=' + escape(src);
	return src;
};
/**
 * Send a request to the server
 * @param Object urldata object list of key:value : {a:2,app:MYTEST,action:MYACTION}
 * @param Object parameters other parameters to complte urldata
 * @param Boolean anonymousmode 
 * @param String otherroot to call another file in same domain (it is forbidden to call another server domain) 
 * @return Boolean true if ok
 */
Fdl.Context.prototype.retrieveData = function(urldata, parameters,
		anonymousmode, otherroot) {
	var bsend = '';
	var ANAKEENBOUNDARY = '--------Anakeen www.anakeen.com 2009';
	var xreq=null;
	if (typeof window != 'undefined') {
		if (window.XMLHttpRequest) {
			xreq = new XMLHttpRequest();
		} else if (window.ActiveXObject) {
			// branch for IE/Windows ActiveX version
			xreq = new ActiveXObject("Microsoft.XMLHTTP");
		}
	} else {
		xreq = Components.classes["@mozilla.org/xmlextras/xmlhttprequest;1"].createInstance(Components.interfaces.nsIXMLHttpRequest);
	}
	var sync = true;

	if (xreq) {
	    if (parameters && parameters.onComplete) {
	        sync=false;
	        xreq.onreadystatechange=function () {
	            if (xreq.readyState == 4) {
	                if (xreq.status == 200) {
	                    parameters.onComplete(xreq);
	                } else {
	                    if (parameters.onError) {
	                        parameters.onError(xreq);
	                    }
	                }
	            }
	           
	        }
	    }
		var url = this.url;
		if (!url)
			url = '/';
		if (otherroot)
			url += otherroot;
		else if (anonymousmode)
			url += 'guest.php';
		else
			url += 'data.php';
		// url+='?';
		var method = "POST";
		if( (!urldata) && (!parameters) && otherroot ) {
			method = "GET";
		}
		xreq.open(method, url, (!sync));
		if (method) {
			var name=null;
			xreq.setRequestHeader("Content-Type",
							"multipart/form-data; boundary=\""
									+ ANAKEENBOUNDARY + "\"");
			for (name in urldata) {
				bsend += "\r\n--" + ANAKEENBOUNDARY + "\r\n";
				bsend += "Content-Disposition: form-data; name=\"" + name
						+ "\"\r\n\r\n";
				bsend += urldata[name];
			}
			if (parameters) {
				for (name in parameters) {
					if (name != 'context') {
						bsend += "\r\n--" + ANAKEENBOUNDARY + "\r\n";
						bsend += "Content-Disposition: form-data; name=\"" + name
						+ "\"\r\n\r\n";
						if (typeof parameters[name]=='object') {
							try {

								bsend += JSON.stringify(parameters[name]);
							} catch (e){
								bsend += parameters[name];
							}
						} else bsend += parameters[name];
					}
				}
			}
		}
		try {
		    if (bsend.length == 0)
		        xreq.send('');
		    else
		        xreq.send(bsend);
		} catch (e) {
		    this.setErrorMessage('HTTP status: unable to send request');
		}
		if (sync) {
		    if (xreq.status == 200) {
		        var r = false;
		        try {
		            var db1=new Date().getTime();
		            if (parameters && parameters.plainfile) {
		                r =  xreq.responseText;
		            } else {
		                r = eval('(' + xreq.responseText + ')');
		                if (this.debug) r["evalDebugTime"]=(new Date().getTime())-db1;
		                if (r.error) this.setErrorMessage(r.error);
		                if (r.log) {
		                    console.log('datalog:',r.log);
		                    delete r.log;
		                }
		                if (r.spentTime)
		                    console.log( {
		                        time : r.spentTime
		                    });
		                delete r.spentTime;
		            }
		        } catch (ex) {
		            alert('error on serveur data:'+xreq.responseText);
		        }
		        return r;
		    } else {
		        if (xreq)
		            this.setErrorMessage('HTTP status:' + xreq.status);
		    }
		}
	}
	return false;
};

/**
 * Send a request to the server
 * @param Object filepath 
 * @param Object parameters other parameters to complte urldata
 * @param Boolean anonymousmode 
 * @param String otherroot to call another file in same domain (it is forbidden to call another server domain) 
 * @return Boolean true if ok
 */
Fdl.Context.prototype.retrieveFile = function(filepath, parameters) {
	return this.retrieveData(parameters,{plainfile:true},false,filepath);
};
/**
 * Send a form to the server
 * @param Object urldata object list of key:value : {a:2,app:MYTEST,action:MYACTION}
 * @param String target target where send result (can be _hidden to not see the result or if result is a file which will be downloading in your desktop)
 * @param String otherroot to call another file in same domain (it is forbidden to call another server domain) 
 * @return Boolean true if ok
 */
Fdl.Context.prototype.sendForm = function(urldata, target, otherroot) {
	var url = this.url;
	if (!url) url = '/';
	if (otherroot) url += otherroot;
	else url += 'data.php';
	var form=document.getElementById('fdlsendform');
	if (form) document.body.removeChild(form);
	form = document.createElement('form');
	form.setAttribute('action',url);
	form.setAttribute('enctype','multipart/form-data');
	form.setAttribute('id','fdlsendform');
	if (target=='_hidden' ) form.setAttribute('target',Fdl.getHiddenTarget());
	else form.setAttribute('target',target);
	form.setAttribute('method', 'POST');
	form.style.display='none';
	document.body.appendChild(form);
	for ( var name in urldata) {
		var newElement = document.createElement("input");
		newElement.setAttribute('type','hidden');
		newElement.setAttribute('name',name);
		form.appendChild(newElement);
		if (typeof urldata[name]=='object') {
			try {
				newElement.value= JSON.stringify(urldata[name]);
			} catch (e){
				newElement.value = urldata[name];
			} 
		}else newElement.value = urldata[name];
	}
	form.submit();
	return true;
};

/**
 * get a document object
 * 
 * @param {object} config
 *     <p><ul>
 *          <li><b>familyName</b> family name to map</li>
 *          <li><b>className</b> class name to map</li>
 *     </ul></p>
 *     
 * @return {Boolean}
 */
Fdl.Context.prototype.addFamilyMap = function(config) {
	if (config.familyName && config.className) {
		if (this.familyMap == null) this.familyMap={};
		this.familyMap[config.familyName]=config.className;
	}
	return true;
};
Fdl.Context.prototype.stringToFunction = function(str) {
	  var fn=eval(str);

	  if (typeof fn !== "function") {
	    throw new Error("function not found");
	  }

	  return  fn;
	};

/**
 * get a document object
 * 
 * @param {object}
 *            config
 *            <p>
 *            <ul>
 *            <li><b>id</b> String/Number the document identifier to
 *            retrieve</li>
 *            <li><b>latest</b> Boolean (Optional) the latest revision
 *            (default is true)
 *            <li><b>needWorkflow</b> Boolean (Optional) set to true for
 *            workflow document to retrieve more informations about workflow
 *            (default is false)</li>
 *            <li><b>useCache</b> Boolean (Optional) set to true if you don't
 *            want a explicit new request to the server. In this case you reuse
 *            the latest document retrieved (default is set to false)</li>
 *            <li><b>noCache</b> Boolean (Optional) set to true if you don't
 *           want set the document in cache</li>
 *            <li><b>getUserTags</b> Boolean (Optional) set to true if you want user tags also</li>
 *            <li><b>contentStore</b> Boolean (Optional) set to true if you want also retriev content of o a collection. This is possible only for collection. After get you can retrieve content with method getStoredContent of Fdl.collection</li>
 *            <li><b>contentConfig : </b> {Object}(optional)  Option for content (see Fdl.Collection.getContent() </li>
 *            </ul>
 *            <pre><code>
 		var d = C.getDocument( {
			id : 9,
			contentStore : true,
			contentConfig : {
				slice : 25,
				orderBy : 'title desc'
			}
		});
		if (d && d.isAlive()) {
			var dl = d.getStoredContent(); // document list object			
			var p = dl.getDocuments();  // array of Fdl.Documents   
 *            </code></pre>
 *            </li>
 *            </ul>
 *            </p>
 *            <code><pre>
 * var C = new Fdl.Context( {
 * 	url : 'http://my.freedom/'
 * });
 * var d = C.getDocument( {
 * 	id : 9
 * });
 * </pre><core>
 * @return {Fdl.Document} One of these classes Fdl.Document, Fdl.Collection, Fdl.Workflow, Fdl.Family return null if document not exist or cannot be readed
 */
Fdl.Context.prototype.getDocument = function(config) {
	if (config)
		config.context = this;
	else
		config = {
			context : this
		};
	var docid = config.id;

	var latest=true;
	if (typeof config == 'object' && config.latest === false) latest=false;
	
	if (docid && (typeof docid == 'object') && (docid.length==1)) {
		docid=docid[0];
		config.id=docid;
	}
	if (typeof docid == 'object') {
		this.setErrorMessage(this._("data:document id must not be an object"));
		return null;
	}
	if ((!docid) && config.data && config.data.properties) {
		docid = config.data.properties.id;
	}
	if (config.data && config.data.properties && config.data.properties.id && docid) {
	    if (latest) {
	        // verify revdate
	        if (this._documents[docid] && this._documents[docid]._data && 
	                (config.data.requestDate <= this._documents[docid]._data.requestDate)) {
	            // use cache if data is oldest cache
	           // console.log("use latest cache", docid, this._documents[docid].getTitle());
	            return this._documents[docid];
	        }
	    } else {
	        if (this._fixDocuments[docid] && this._fixDocuments[docid]._data) {
               // console.log("use fix cache", docid, this._fixDocuments[docid].getTitle());
                return this._fixDocuments[docid];
            }
	    }
	} else if (docid) {
	    // no data
	    if (config.useCache && this._documents[docid]) {
            //console.log("use latest cache", docid, this._documents[docid].getTitle());
	        return this._documents[docid];
	    }
	}
   if (! docid) {
	   this.setErrorMessage(this._("data:document id not set"));
	   return null;
   }
	var wdoc = new Fdl.Document(config);
  
	
	if (! wdoc._data) return null;
	if (this.familyMap != null && this.familyMap[wdoc.getProperty('fromname')]) {
		var sname=wdoc.getProperty('fromname');
		config.data = wdoc._data;
		
		var sclass=this.stringToFunction(this.familyMap[sname]);
		wdoc = new sclass(config);
	}else if ((wdoc.getProperty('defdoctype') == 'D')
			|| (wdoc.getProperty('defdoctype') == 'S')) {
		config.data = wdoc._data;
		wdoc = new Fdl.Collection(config);
	} else if (wdoc.getProperty('doctype') == 'C') {
		config.data = wdoc._data;
		wdoc = new Fdl.Family(config);
	} else if (wdoc.getProperty('doctype') == 'W') {
		config.data = wdoc._data;
		wdoc = new Fdl.Workflow(config);
	}
	if (config && (! config.noCache)) {
		if (latest) {
			this._documents[wdoc.getProperty('initid')] = wdoc;
			if (wdoc.getProperty('id') != wdoc.getProperty('initid')) {
				this._documents[wdoc.getProperty('id')] = wdoc; // alias cache
			}
			if ((docid!=wdoc.getProperty('id')) && (docid!=wdoc.getProperty('initid'))) {
				this._documents[docid] = wdoc; // other alias cache
			}
		} else {	
			this._fixDocuments[wdoc.getProperty('id')] = wdoc;
		}
	}
	return wdoc;
};

/**
 * get the notifier object after the first call retrun always the same notifier
 * object : one notifier by context
 * 
 * @param {object}
 *            config
 *            <p>
 *            <ul>
 *            <li><b>reset : </b> Boolean (Optional) set to true to return a
 *            new notifier
 *            </ul>
 *            </p>
 * @return {Fdl.Notifier} return the object notifier
 */
Fdl.Context.prototype.getNotifier = function(config) {
	if (config) {
		if (config.reset)
			this.notifier = null;
	}
	if (!this.notifier) {
		if (config)
			config.context = this;
		else
			config = {
				context : this
			};
		this.notifier = new Fdl.Notifier(config);
	}
	return this.notifier;
};
/**
 * get a new search document
 * 
 * @param {object}
 *            config (see
 *            {@link Fdl.SearchDocument configuration of Fdl.SearchDocument } )
 * @return {Fdl.SearchDocument} return the object notifier
 */
Fdl.Context.prototype.getSearchDocument = function(config) {
	if (config)
		config.context = this;
	else
		config = {
			context : this
		};
	return new Fdl.SearchDocument(config);
};

/**
 * get available operators for search criteria by attribute type
 * @return {Object} the operator available by attribute type  
{text:[{operator:'=', label:'equal', operand:['left','right'],labelTpl:'{left} is equal to {right}'},{operator:'~*', label:'include',operand:['left','right'],labelTpl:'{left} is equal to {right}'}],integer:[{operator:'=', label:'equal'},{operator:'>', label:'&gt;'}],...
 */
Fdl.Context.prototype.getSearchCriteria = function() { 
	if (this._opcriteria) return this._opcriteria;
	
	var r= this.retrieveData({app:'DATA',action:'DOCUMENT',
		method:'getsearchcriteria'});
	if (! r.error) {
		this._opcriteria=r.operators;
		return this._opcriteria;
	}
	return null;
};

/**
 * get a groupRequest object
 * 
 * @param {object}
 *            config
 * @return {Fdl.GroupRequest} return the object group request
 */
Fdl.Context.prototype.createGroupRequest = function(config) {
	if (config)
		config.context = this;
	else
		config = {
			context : this
		};
	return new Fdl.GroupRequest(config);
};
/**
 * get home folder of current user
 * 
 * @return {Fdl.Collection} the home folder, null is no home
 */
Fdl.Context.prototype.getHomeFolder = function(config) {
	if (this._homeFolder)
		return this._homeFolder;
	var u = this.getUser();
	if ((u != null) && u.id) {
		var idhome = 'FLDHOME_' + u.id;
		if (! config) config={};
		config.id=idhome;
		var h = this.getDocument(config);
		if (h!=null && h.isAlive()) {
			this._homeFolder = h;
			return h;
		}
	}
	return null;
};
/**
 * get desktop folder of current user
 * 
 * @return {Fdl.Collection} the home folder, null is no desktop folder
 */
Fdl.Context.prototype.getDesktopFolder = function(config) {
	if (this._desktopFolder)
		return this._desktopFolder;
	var u = this.getUser();
	if ((u!=null) && u.id) {
		var idhome = 'FLDDESKTOP_' + u.id;
		if (! config) config={};
		config.id=idhome;
		var h = this.getDocument(config);
		if (h!=null && h.isAlive()) {
			this._desktopFolder = h;
			return h;
		}
	}
	return null;
};
/**
 * get offline folder of current user
 * 
 * @return {Fdl.Collection} the home folder, null is no offline folder
 */
Fdl.Context.prototype.getOfflineFolder = function(config) {
	if (this._offlineFolder)
		return this._offlineFolder;
	var u = this.getUser();
	if ((u!=null) && u.id) {
		var idhome = 'FLDOFFLINE_' + u.id;
		if (! config) config={};
		config.id=idhome;
		var h = this.getDocument(config);
		if (h!=null && h.isAlive()) {
			this._offlineFolder = h;
			return h;
		}
	}
	return null;
};
/**
 * get basket folder of current user
 * 
 * @return {Fdl.Collection} the home folder, null is no home
 */
Fdl.Context.prototype.getBasketFolder = function(config) {
	if (this._basketFolder)
		return this._basketFolder;
	var u = this.getUser();
	if ((u!=null) && u.id) {
		var idhome = 'FLDHOME_' + u.id;
		if (! config) config={};
		config.id=idhome;
		var h = this.getDocument(config);
		if (h!=null && h.isAlive()) {
			this._basketFolder = h;
			return h;
		} else {
			var f=new Fdl.DocumentFilter({family:'DIR strict',  
				criteria:[{operator:'=',
					left:'owner',
					right:'-'+u.id}]});
			var s=this.context.getSearchDocument({filter:f});
			var dl=s.search();
			if (dl && (dl.count>0)) {
				var p=dl.getDocuments();
				this._basketFolder = p[0];
				return this._basketFolder;
			}
		}
	}
	return null;
};
/**
 * create a new object document (not set in database since it is saved)
 * 
 * @param {Object}
 *            config
 *            <ul>
 *            <li><b>familyId : </b> The family identifier for the new document</li>
 *            <li><b>temporary : </b> (optional) boolean set to true if want only a working document</li>
 *            </ul>
 * @return {Fdl.Document} a new document
 */
Fdl.Context.prototype.createDocument = function(config) {
	if (config && (config.family || config.familyId)) {
		var data = this.retrieveData( {
			app : 'DATA',
			action : 'DOCUMENT',
			method : 'create',
			id : (config.family)?config.family:config.familyId,
			temporary:config.temporary
		});
		if (data) {
			if (!data.error) {
				var nd;
				if ((data.properties.defdoctype == 'D') || (data.properties.defdoctype == 'S'))
					nd = new Fdl.Collection( {
						context : this
					});
				else
					nd = new Fdl.Document( {
						context : this
					});
				nd.affect(data);
				nd._mvalues.family = nd.getProperty('fromid');
				return nd;
			} else {
				this.setErrorMessage(data.error);
			}
		}
		return null;
	}
};


/**
 * get application object
 * 
 * @param {object} config
 * <ul>
 * <li><b>name : </b> (String) the application name</li>
 * </ul>
 * @return {Fdl.Application} return the application if exist else return null
 */
Fdl.Context.prototype.getApplication = function(config) {
	if (config) config.context = this;
	else config = {context : this};
	
	var a= new Fdl.Application(config);
	if (! a.id ) return null;
	return a;
};
/**
 * 
 * @param config .id the param identificator
 * <ul><li><b>id : </b>the param identificator</li>
 * </ul>
 * @return {string} the value of parameter
 */
Fdl.Context.prototype.getParameter = function (config) {
    if (config) {
      if (config.id) {
	if (! this.application) this.application=new Fdl.Application({context:this,name:'CORE'});
	return this.application.getParameter(config);
      }
    }
  };
/**
 * 
 * @param config .id the param identificator
 * <ul><li><b>id : </b>the param identificator</li>
 *     <li><b>value : </b>the value</li>
 * </ul>
 * @return {string} the value of parameter
 */
  Fdl.Context.prototype.setParameter = function (config) {
    if (config) {
      if (config.id) {
	if (! this.application) this.application=new Fdl.Application({context:this,name:'CORE'});
	return this.application.setParameter(config);
      }
    }
  };