
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Fdl
 * @singleton
 */
if (!("console" in window)) {
	window.console = {
		'log' : function(s) {
		}
	};
}; // to debug

var Fdl = {
	version : "0.1",
	_isAuthenticate : null,
	_isConnected : null,
	_serverTime : null
};
Fdl._print_r = function(obj, level, maxlevel) {
	if (!obj)
		return;

	if (!level)
		level = 0;
	if (!maxlevel)
		maxlevel = 1;
	if (typeof obj != 'object')
		return obj.toString();
	if (level > maxlevel)
		return;
	var slev = '';
	if (!level)
		level = 0;
	for ( var i = 0; i < level; i++)
		slev += '......';
	var names = obj.toString() + "\n";
	if ('splice' in obj && 'join' in obj)
		names = 'Array';
	else if (typeof obj == 'object')
		names = obj.toString();
	else
		names = typeof obj;
	names += "\n";
	for ( var name in obj) {
		try {
			if (typeof obj[name] == 'function')
				names += '';// slev+name +" (function)\n";
			else if (typeof obj[name] == 'object')
				names += slev + name + " -"
						+ Fdl._print_r(obj[name], level + 1, maxlevel) + "\n";
			else
				names += slev + name + " - [" + obj[name] + "]\n";
		} catch (ex) {
			names += name + " - [" + "unreadable" + "]\n";
		}
	}
	return names;
};

Fdl.print_r = function(obj, maxlevel) {
	if (!maxlevel)
		maxlevel = 1;
	var names = Fdl._print_r(obj, 0, maxlevel);
	alert(names);
};
/**
 * to record url to access to freedom server
 * 
 * @hide
 * @deprecated
 */
Fdl.connect = function(config) {
	if (config) {
		if (!this.context)
			this.context = new Object();
		if (config.url) {
			this.context.url = config.url;
			if (this.context.url.substr(-4, 4) == '.php')
				this.context.url += '?';
			else if (this.context.url.substr(-1, 1) != '/')
				this.context.url += '/';
			this.url = this.context.url;
		}
	}
};
Fdl.getCookie = function(c_name) {
	if (document.cookie.length > 0) {
		c_start = document.cookie.indexOf(c_name + "=");
		if (c_start != -1) {
			c_start = c_start + c_name.length + 1;
			c_end = document.cookie.indexOf(";", c_start);
			if (c_end == -1)
				c_end = document.cookie.length;
			return unescape(document.cookie.substring(c_start, c_end));
		}
	}
	return "";
};
/**
 * @deprecated
 */
Fdl.isAuthenticated = function(config) {
	if (config && config.reset)
		this._isAuthenticate = null;
	if (this._isAuthenticate === null) {
		var userdata = Fdl.retrieveData( {
			app : 'DATA',
			action : 'USER'
		});
		if (userdata) {
			if (userdata.error) {
				Fdl.setErrorMessage(userdata.error);
				this._isAuthenticate = false;
			} else {
				if (!this.user)
					this.user = new Fdl.User( {
						data : userdata
					});
				this._isAuthenticate = true;
			}
		}
	}
	return this._isAuthenticate;
};

/**
 * @deprecated
 */
Fdl.setAuthentification = function(config) {
	if (config) {
		if (!config.login) {
			Fdl.setErrorMessage('login not defined');
			return null;
		}
		if (!config.password) {
			Fdl.setErrorMessage('password not defined');
			return null;
		}
		var userdata = Fdl.retrieveData( {
			app : 'DATA',
			action : 'USER',
			method : 'authent'
		}, config, true);
		if (userdata.error) {
			this._isAuthenticate = false;
			Fdl.setErrorMessage(userdata.error);
			return null;
		} else {
			this._isAuthenticate = true;
			this.user = new Fdl.User( {
				data : userdata
			});
			return this.user;
		}
	}
};
/**
 * @deprecated
 */
Fdl.setErrorMessage = function(msg) {
	if (msg)
		this.lastErrorMessage = msg;
};
/**
 * @deprecated
 */
Fdl.getLastErrorMessage = function() {
	return this.lastErrorMessage;
};
/**
 * @deprecated
 */
Fdl.retrieveData = function(urldata, parameters, anonymousmode) {
	var bsend = '';
	var ANAKEENBOUNDARY = '--------Anakeen www.anakeen.com 2009';
	var xreq;
	/*
	 * if ((!anonymousmode) && ! Fdl.isAuthenticated()) { alert('not
	 * authenticate'); return null; }
	 */

	if (window.XMLHttpRequest) {
		var xreq = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		// branch for IE/Windows ActiveX version
		var xreq = new ActiveXObject("Microsoft.XMLHTTP");
	}
	var sync = true;

	if (xreq) {
		var url = this.context.url;
		if (!url)
			url = '/';
		if (anonymousmode)
			url += 'guest.php';
		else
			url += 'data.php';
		// url+='?';
		xreq.open("POST", url, (!sync));
		var pvars = true;
		if (!pvars) {
			xreq.setRequestHeader("Content-type",
					"application/x-www-form-urlencoded");
			// xreq.setRequestHeader("Content-Length", "0");
		} else {

			var params = '';
			var ispost = false;
			xreq
					.setRequestHeader("Content-Type",
							"multipart/form-data; boundary=\""
									+ ANAKEENBOUNDARY + "\"");
			for ( var name in urldata) {
				bsend += "\r\n--" + ANAKEENBOUNDARY + "\r\n";
				bsend += "Content-Disposition: form-data; name=\"" + name
						+ "\"\r\n\r\n";
				bsend += urldata[name];
			}
			if (parameters) {
				for ( var name in parameters) {
					bsend += "\r\n--" + ANAKEENBOUNDARY + "\r\n";
					bsend += "Content-Disposition: form-data; name=\"" + name
							+ "\"\r\n\r\n";
					bsend += parameters[name];
				}
			}
		}
		try {
			if (bsend.length == 0)
				xreq.send('');
			else
				xreq.send(bsend);
		} catch (e) {
			Fdl.setErrorMessage('HTTP status: unable to send request');
		}
		if (xreq.status == 200) {
			var r = false;
			try {
				r = eval('(' + xreq.responseText + ')');
				if (r.error)
					Fdl.setErrorMessage(r.error);
			} catch (ex) {
				alert('error on serveur data');
				alert(xreq.responseText);
			}
			return r;
		} else {
			if (xreq)
				Fdl.setErrorMessage('HTTP status:' + xreq.status);
		}
	}
	return false;
};
/**
 * @deprecated
 */
Fdl.isConnected = function(config) {
	if (config && config.reset)
		this._isConnected = null;
	if (this._isConnected === null && this.context && this.context.url) {
		var data = Fdl.retrieveData( {
			app : 'DATA',
			action : 'USER',
			method : 'ping'
		}, config, true);
		this._serverTime = null;
		if (data) {
			if (data.error) {
				Fdl.setErrorMessage(data.error);
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
 * @deprecated
 */
Fdl.getUser = function(config) {
	if (config) {
		if (config.reset)
			this.user = null;
	}
	if (!this.user) {
		this.user = new Fdl.User();
	}
	return this.user;
};

// construct an iframe target to send form in background
Fdl.getHiddenTarget = function(config) {
	if (!this._hiddenTarget) {
		this._hiddenTarget = 'fdlhiddeniframe';
		if (!config) {
			if (! document.getElementById(this._hiddenTarget)) {
			var o = document.createElement("div");
			o.innerHTML = '<iframe style="display:none;width:100%" id="'
					+ this._hiddenTarget + '" name="' + this._hiddenTarget
					+ '"></iframe>';
			document.body.appendChild(o);
			}
		}
	}
	return this._hiddenTarget;
};
Fdl._completeSave = function(callid, data) {
	var s = Fdl._getWaitSave(parseInt(callid));
	s.data = data;
	if (s.document) {
		s.document.affect(data);
		s.config.callback.call(null, s.document);
	}
	Fdl._clearWaitSave(parseInt(callid));
};

Fdl._memoWaitSave = [];
Fdl._waitSave = function(me, config) {
	Fdl._memoWaitSave.push( {
		document : me,
		config : config
	});
	return Fdl._memoWaitSave.length - 1;
};
Fdl._getWaitSave = function(callid) {
	return Fdl._memoWaitSave[callid];
};
Fdl._clearWaitSave = function(callid) {
	if (Fdl._memoWaitSave[callid])
		Fdl._memoWaitSave[callid] = null;
};
Fdl.resizeImage = function(icon, width) {
	var u = Fdl.context.url;
	var src = Fdl.context.url + icon;
	var ps = u.lastIndexOf('/');
	if (ps) {
		u = u.substring(0, ps + 1);
		src = u + icon;
	}
	src = u + 'resizeimg.php?size=' + width + '&img=' + escape(src);
	return src;
};

Fdl.getTime = function() {
	var d = new Date();
	var t = d.getTime();
	var i = '--';
	if (this._dtime)
		i = ((t - this._dtime) / 1000).toFixed(3);
	this._dtime = t;
	return i;
};
/**
 * JSON to XML
 * 
 * @param {Object}
 *            JSON
 * @return string the xml string
 */
Fdl.json2xml = function(json, node, childs) {
	var root = false;
	if (!node) {
		node = document.createElement('root');
		root = true;
		childs = [];
	}
	if (json === null) {
		node.appendChild(document.createTextNode(''));
	} else if (typeof json != 'object') {
		node.appendChild(document.createTextNode(json));
	} else {
		var found = false;
		if ((typeof json == 'object')) {
			for ( var i = 0; i < childs.length; i++) {
				if (childs[i] === json) {
					childs[i]['_xmlrecursive'] = true;
					if (childs[i]['_xmlrecursive'] == json['_xmlrecursive']) {
						found = true; // detect really same object
					}
					delete childs[i]['_xmlrecursive'];
				}
			}
			if (!found) {
				childs.push(json);
			}
		}
		if (found) {
			node.appendChild(document.createTextNode('--recursive--'));
		} else {
			for ( var x in json) {
				// ignore inherited properties
				if (json.hasOwnProperty(x)) {
					if ((x == '#text')) { // text
						node.appendChild(document.createTextNode(json[x]));
					} else if (x == '@attributes') { // attributes
						for ( var y in json[x]) {
							if (json[x].hasOwnProperty(y)) {
								node.setAttribute(y, json[x][y]);
							}
						}
					} else if (x == '#comment') { // comment
						// ignore

					} else { // elements
						if ((json[x] instanceof Array) ) { // handle arrays
							for ( var i = 0; i < json[x].length; i++) {
								node
										.appendChild(Fdl
												.json2xml(
														json[x][i],
														document
																.createElement((x == 'link') ? '_xmllink': x),
														childs));
							}
						} else {
							if (x) {
								node
										.appendChild(Fdl
												.json2xml(
														json[x],
														document.createElement((x == 'link') ? '_xmllink': x),
														childs));
							}
						}
					}
				}
			}
		}
	}

	if (root == true) {
		return node.innerHTML.replace(/_xmllink/g, 'link');
	} else {
		return node;
	}

};

/**
 * Convert TEXT to XML DOM document Object
 * 
 * @param {string}
 *            strXML the xml string
 * @return {Object} XML DOM Document
 */
Fdl.text2xml = function(strXML) {
	var xmlDoc = null;
	try {
		xmlDoc = (document.all) ? new ActiveXObject("Microsoft.XMLDOM")
				: new DOMParser();
		xmlDoc.async = false;
	} catch (e) {
		throw new Error("XML Parser could not be instantiated");
	}
	var out;
	try {
		if (document.all) {
			out = (xmlDoc.loadXML(strXML)) ? xmlDoc : false;
		} else {
			out = xmlDoc.parseFromString(strXML, "text/xml");
		}
	} catch (e) {
		throw new Error("Error parsing XML string");
	}
	return out;
};
/**
 * Convert XML to JSON Object
 * 
 * @param {Object}
 *            XML DOM Document
 */
Fdl.xml2json = function(xml) {
	var obj = {};
	if (!xml)
		return false;
	if (xml.nodeType == 1) { // element
		// do attributes
		if (xml.attributes.length > 0) {
			obj['@attributes'] = {};
			for ( var j = 0; j < xml.attributes.length; j++) {
				obj['@attributes'][xml.attributes[j].nodeName] = xml.attributes[j].nodeValue;
			}
		}

	} else if (xml.nodeType == 3) { // text
		obj = xml.nodeValue;
	}

	// do children
	if (xml.hasChildNodes()) {
		if ((!obj['@attributes']) && (xml.childNodes.length == 1)
				&& (xml.childNodes[0].nodeName == '#text')) {
			obj = xml.childNodes[0].nodeValue;
		} else {
			for ( var i = 0; i < xml.childNodes.length; i++) {
				if (typeof (obj[xml.childNodes[i].nodeName]) == 'undefined') {
					obj[xml.childNodes[i].nodeName] = Fdl
							.xml2json(xml.childNodes[i]);
				} else {
					if (typeof (obj[xml.childNodes[i].nodeName].length) == 'undefined') {
						var old = obj[xml.childNodes[i].nodeName];
						obj[xml.childNodes[i].nodeName] = [];
						obj[xml.childNodes[i].nodeName].push(old);
					}
					obj[xml.childNodes[i].nodeName].push(Fdl
							.xml2json(xml.childNodes[i]));
				}
			}
		}
	}

	return obj;
};
/**
 * clone an object
 * the object must not be recursive
 * @return {Object} the cloned object
 */
Fdl.cloneObject= function(srcInstance) {
	if (typeof(srcInstance) != 'object' || srcInstance == null) {
		return srcInstance;
	}
	/*On appel le constructeur de l'instance source pour crée une nouvelle instance de la même classe*/
	var newInstance = srcInstance.constructor();
	/*On parcourt les propriétés de l'objet et on les recopies dans la nouvelle instance*/
	for(var i in srcInstance) {
		newInstance[i] = Fdl.cloneObject(srcInstance[i]);
	}
	/*On retourne la nouvelle instance*/
	return newInstance;
};

/**
 * transform &,< and > characters into their html equivalents
 * @return {string} the formatted string
 */
Fdl.encodeHtmlTags= function(v) {
    if (v && (typeof v == 'string')) {
        v = v.replace(/\&/g,'&amp;');
        v = v.replace(/\</g,'&lt;');
        v = v.replace(/\>/g,'&gt;');
    } else if (v && (typeof v == 'object')) {
        for (var i=0;i<v.length;i++) {
        	 if (v[i] && (typeof v[i] == 'string')) {
            v[i]=v[i].replace(/\&/g,'&amp;');
            v[i]= v[i].replace(/\</g,'&lt;');
            v[i]= v[i].replace(/\>/g,'&gt;');
        	 }
        }
    }
    return v;
};
	
// ---------------------
// Not prototype
// @deprecated
Fdl.createDocument = function(config) {
	if (config && config.familyId) {
		data = this.context.retrieveData( {
			app : 'DATA',
			action : 'DOCUMENT',
			method : 'create',
			id : config.familyId
		});
		if (data) {
			if (!data.error) {
				var nd;
				if (data.properties.defdoctype == 'D')
					nd = new Fdl.Collection( {
						context : this.context
					});
				else
					nd = new Fdl.Document( {
						context : this.context
					});
				nd.affect(data);
				nd._mvalues.familyid = nd.getProperty('fromid');
				return nd;
			} else {
				this.context.setErrorMessage(data.error);
			}
		}
		return false;
	}
};