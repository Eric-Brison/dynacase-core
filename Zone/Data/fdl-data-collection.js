
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Fdl.Collection ; The collection document object
 * 
 * <pre><code>
  var C = new Fdl.Context( {
  	url : 'http://my.freedom/'
  });
  var d = C.getDocument( {
  	id : 9
  });
  if (d &amp;&amp; d.isAlive()) {
  	if (d.isCollection()) {
  		var dl = d.getContent(); // get Document List
  		var p = dl.getDocuments(); // get array of Documents from document List
  		var ht = '&lt;table&gt;';
  		for ( var i in p) {
  			doc = p[i];
  			ht += '&lt;tr&gt;&lt;td&gt;' + i + '&lt;/td&gt;&lt;td&gt;' + doc.getProperty('id')
  					+ '&lt;/td&gt;&lt;td style=&quot;width:200px;overflow:hidden&quot;&gt;'
  					+ doc.getTitle() + '&lt;/td&gt;&lt;td&gt;&lt;img src=&quot;' + doc.getIcon( {
  						'width' : 20
  					}) + '&quot;&gt;&lt;/td&gt;&lt;td&gt;' + doc.getProperty('mdate')
  					+ '&lt;/td&gt;&lt;/tr&gt;';
  		}
  		ht += '&lt;/table&gt;';
  	}
  }
 * </code></pre>
 * 
 * @namespace Fdl.Document
 * @extends Fdl.Document
 * @param {Object}   config
 */

Fdl.Collection = function (config) {
  Fdl.Document.call(this,config);
};
Fdl.Collection.prototype = new Fdl.Document();
Fdl.Collection.prototype.toString= function() {
      return 'Fdl.Collection';
};

/**
 * return array of documents included in folder or result of a search
 * 
 * @param {Object}
 *            config
 *            <ul>
 *            <li><b>start : </b>{Number} (optional) offset where begin search ,
 *            0 is the first</li>
 *            <li><b>slice : </b> {Number}(optional) number of documents
 *            returned (default is 50)</li>
 *            <li><b>onlyValues : </b> {Boolean}(optional) does not return
 *            attribute definition - more quick. It true return also attribute
 *            definition (default is true)</li>
 *            <li><b>completeProperties : </b> {Boolean}(optional) does not
 *            return property locker and lastmodifiername and followg states
 *            informations (mode quick response). Set to true if you need theses
 *            informations (default is false)</li>
 *            <li><b>orderBy : </b> {string}(optional) ordered by title by
 *            default, can use any property .can use attribute when mono family
 *            search. To choose the direction use asc or desc after the property :
 *            "title desc" for example
 *            <li><b>filter : </b> {Fdl.Filter}(optional) a filter
 * 
 * <pre>
    {sql:&quot;us_fname = 'roy'&quot;,
     family : &quot;USER strict&quot;,
     criteria : [{ operator  : '&tilde;',
                   right : 'us_login',
                   left : 'carter'},
                 { operator : '&gt;&lt;',
                   right : 'us_date',
                    min : '2009-10-01',
                   max : '::getDate(3)'},
                 { operator : '&gt;',
                   right : 'us_date',
                   left : '@us_otherdate'},
                 { operator  : '=',
                   right : 'owner',
                   left : '::getSystemUserId()'},
                 {or : [{ locked : '!=',
                          right : 'locked',
                          left : 0},
                       { locked : '!=',
                         right : 'locked',
                         left : '::getSystemUserId()'}]},
                   { operator : '=&tilde;',
                     right : 'allocated',
                     left : 'roy'}]
     ]}
 * </pre>
 * 
 * </li>
 * <li><b>start : </b> {Numeric}(optional) the offset range (default is 0 : from first matches)  </li>
 * <li><b>slice : </b> {Numeric}(optional) the limit of result returned (default is 100), set to "ALL" for unlimited  </li>
 * <li><b>verifyhaschild : </b> {Boolean}(optional) (default is false) </li>
 * <li><b>searchProperty : </b> {String}(optional) main property or attribute
 * identicator where apply the key. The oprator is ~* (insensitive case include)
 * by default equal to any values ("svalues" property) </li>
 * <li><b>withHighlight : </b> {Boolean}(optional) to return highlight text in
 * concordance with the main keyword</li>
 * <li><b>key : </b> {String}(optional) main keyword filter  </li>
 * <li><b>mode : </b> {String}(optional) search mode fir main keyword must be 'word' or 'regexp' (default is word)</li>
 * <li><b>searchProperty : </b> {String}(optional) main property or attribute identicator where apply the key. The operator is ~* (insensitive case include) by default equal to any values ("svalues" property) </li>
 * </ul>
 * @return {Fdl.DocumentList} list of Fdl.Document
 */
Fdl.Collection.prototype.getContent = function(config) {
	if (! this.getProperty('initid')) {
		this.context.setErrorMessage('getContent: no identificator set');
		return null;
	}
	var data=null;
	if (config && config.data) {
		data=config.data;
	} else {
		if (config && config.filter) config.filter=JSON.stringify(config.filter);
		data=this.context.retrieveData({app:'DATA',action:'DOCUMENT',method:'getcontent',id:this.getProperty('initid')},config);
	}
	if (data) {
		if (! data.error) {
			data.context=this.context;
			return new Fdl.DocumentList(data);
		} else {
			this.context.setErrorMessage(data.error);
		}
	} else return null;
};


/**
 * return sub collections included in the collection return folder and searches
 * 
 * @return {Fdl.DocumentList} of Fdl.Document
 */
Fdl.Collection.prototype.getStoredContent = function() {
	if (this._data.storedContent) {
		return this.getContent({data:this._data.storedContent});
	}
  return null;
};
/**
 * return sub collections included in the collection return folder and searches
 * 
 * @return {Fdl.DocumentList} of Fdl.Document
 */
Fdl.Collection.prototype.getSubCollections = function() {
  return this.getContent({verifyhaschild:true,filter:"doctype = 'D' or doctype = 'S'"});
};
/**
 * return sub folders included in collection not return seareches
 * 
 * @return {Fdl.DocumentList} of Fdl.Document
 */
Fdl.Collection.prototype.getSubFolders = function() {
  return this.getContent({verifyhaschild:true,filter:"doctype = 'D'"});
};
/**
 * insert document into a folder
 * 
 * @param {Object}
 *            config
 *            <ul>
 *            <li><b>id : </b>{Number} the document identificator to add</li>
 *            </ul>
 * @return {Boolean} true if document is inserted
 */
Fdl.Collection.prototype.insertDocument = function(config) {
	if (config && config.id) {	
		var data=null;
		if (config.norequest) {
			data=this._data;
		} else {
			data=this.context.retrieveData({app:'DATA',action:'DOCUMENT',method:'insertdocument',
				id:this.getProperty('initid'),
				idtoadd:config.id});
		}
		if (! data.error) {
			return true;
		} else {
			this.context.setErrorMessage(data.error);
		}
	}  
	return false;
};
/**
 * unlink document from a folder (the document is not deleted)
 * 
 * @param {Object}
 *            config
 *            <ul>
 *            <li><b>id : </b>{Number} the document identificator to remove
 *            from folder</li>
 *            </ul>
 * @return {Boolean} true if document is unlinked
 */
Fdl.Collection.prototype.unlinkDocument = function(config) {
	if (config && config.id) {
		var data=null;
		if (config.norequest) {
			data=this._data;
		} else {
			data=this.context.retrieveData({app:'DATA',action:'DOCUMENT',method:'unlinkdocument',
				id:this.getProperty('initid'),
				idtounlink:config.id});
		}
		if (! data.error) {
			return true;
		} else {
			this.context.setErrorMessage(data.error);
		}
	}  
	return false;
};

/**
 * insert documents to a folder
 * 
 * <pre><code>
  // insert all documents of folder 9 to basket 1012 except three documents
  var basket = C.getDocument( {
  	id : 1012
  });
  var nine = C.getDocument( {
  	id : 9
  });
  if (basket &amp;&amp; nine) {
  	var s = new Fdl.DocumentSelection();
  	s.setAllCollection( {
  		collection : nine
  	});
  	s.insertToList( {
  		id : 52283
  	});
  	s.insertToList( {
  		id : 47219
  	});
  	s.insertToList( {
  		document : C.getDocument( {
  			id : 47882,
  			useCache : true
  		})
  	});
  	var r = basket.insertDocuments( {
  		selection : s
  	});
  }
 * </code></pre>
 * 
 * @param {Object}
 *            config
 *            <ul>
 *            <li><b>selection : </b>{Fdl.DocumentSelection} the references to
 *            documents to insert</li>
 *            </ul>
 * @return {Object} insertedCount:the number of document inserted,
 *         notInsertedCount : the number of document not inserted , inserted :
 *         array of message (indexed ny id), notInserted : array of message
 *         (indexed by id)
 */
Fdl.Collection.prototype.insertDocuments = function(config) {
	if (config && config.selection) {
		var data=null;
		if (config.norequest) {
			data=this._data;
		} else {
			console.log('insert',config);
			if (config.selection) config.selection=JSON.stringify(config.selection);
			data=this.context.retrieveData({app:'DATA',action:'DOCUMENT',method:'insertdocuments',
				id:this.getProperty('initid')},config);
		}
		if (! data.error) {
			return data;
		} else {
			this.context.setErrorMessage(data.error);
		}
	}  
	return false;
};

/**
 * move documents to a folder
 * 
 * @param {Object}
 *            config
 *            <ul>
 *            <li><b>selection : </b>{Fdl.DocumentSelection} the references to
 *            documents to insert</li>
 *            <li><b>targetIdentificator : </b>{Number} Folder identificator</li>
 *            </ul>
 * @return {Object} insertedCount:the number of document inserted,
 *         notInsertedCount : the number of document not inserted , inserted :
 *         array of message (indexed ny id), notInserted : array of message
 *         (indexed by id)
 */
Fdl.Collection.prototype.moveDocuments = function(config) {
	if (config && config.selection) {
		var data=null;
		if (config.norequest) {
			data=this._data;
		} else {
			if (config.selection) config.selection=JSON.stringify(config.selection);
			data=this.context.retrieveData({app:'DATA',action:'DOCUMENT',method:'movedocuments',
				id:this.getProperty('initid')},config);
		}
		if (! data.error) {
			return data;
		} else {
			this.context.setErrorMessage(data.error);
		}
	}  
	return false;
};
/**
 * unlink documents from a folder (the document is not deleted)
 * 
 * @param {Object}
 *            config
 *            <ul>
 *            <li><b>selection : </b>{Fdl.DocumentSelection} the references to
 *            documents to unlink</li>
 *            </ul>
 * @return {Object} unlinkedCount:the number of document unlinked,
 *         notUnlinkedCount : the number of document not unlinked , unlinked :
 *         array of message (indexed ny id), notUnlinked : array of message
 *         (indexed by id)
 */
Fdl.Collection.prototype.unlinkDocuments = function(config) {
	if (config && config.selection) {
		var data=null;
		if (config.norequest) {
			data=this._data;
		} else {
			if (config.selection) config.selection=JSON.stringify(config.selection);
			data=this.context.retrieveData({app:'DATA',action:'DOCUMENT',method:'unlinkdocuments',
				id:this.getProperty('initid')},config);
		}
		if (! data.error) {
			return data;
		} else {
			this.context.setErrorMessage(data.error);
		}
	}  
	return false;
};

/**
 * unlink all documents from a folder ( documents are not deleted)
 * 
 * @return {Boolean} true if folder is cleaned
 */
Fdl.Collection.prototype.unlinkAllDocuments = function(config) {
	var data=null;
	if (config && config.norequest) {
		data=this._data;
	} else {
		data=this.context.retrieveData({app:'DATA',action:'DOCUMENT',method:'unlinkalldocuments',
			id:this.getProperty('initid')},config);
	}
	if (! data.error) {
		return true;
	} else {
		this.context.setErrorMessage(data.error);
	}
	return false;
};

/**
 * get restricted families when folder has restrcition
 * 
 * @param {Object}
 *            config (optional)
 *            <ul>
 *            <li><b>reset : </b>if true force another request </li>
 *            </ul>
 * @return array of object {id:<identificator>,title:<family title>}
 */
Fdl.Collection.prototype.getAuthorizedFamilies = function(config) {
	if (this.id) {
		if (this.authorizedFamilies && ((!config) || (config && (!config.reset)))) return this.authorizedFamilies;
		var data=null;
		if (config && config.data) {
			data=config.data;
		} else {
			data=this.context.retrieveData({app:'DATA',action:'DOCUMENT',method:'getAuthorizedFamilies',
				id:this.getProperty('initid')});
		}
		if (! data.error) {
			this.authorizedFamilies=new Object();
			if (data.authorizedFamilies.restriction) {
				this.authorizedFamilies.families=new Object();
				for (var i in data.authorizedFamilies.families) {
					this.authorizedFamilies.families[i]={id:i,
							title:data.authorizedFamilies.families[i]['title']};
				}
			} else {
				this.authorizedFamilies.families=null;
			}
			this.authorizedFamilies.restriction=data.authorizedFamilies.restriction;
			return this.authorizedFamilies;  
		} else {
			this.context.setErrorMessage(data.error);
		}
	}  
	return false;
};

/**
 * verify if collection is restriction on family type
 * 
 * @return {Boolean} return true if has restriction
 */
Fdl.Collection.prototype.hasRestriction = function(config) {
  if (this.id) {
      var authfam=this.getAuthorizedFamilies(config);
      if (authfam) return authfam.restriction;
  }  
  return false;
};
/**
 * verify if collection is a folder
 * 
 * @return {Boolean} return true if it is a folder ( not a search)
 */
Fdl.Collection.prototype.isFolder = function() {
      return (this.getProperty('defdoctype')=="D");
};
/**
 * verify if collection is a search
 * 
 * @return {Boolean} return true if it is a search (not a folder)
 */
Fdl.Collection.prototype.isSearch = function() {
      return (this.getProperty('defdoctype')=="S");
};


/**
 * add a filter to a search
 * @param {Fdl.DocumentFilter} filter the filter to add
 * @return {Boolean} return true if succeed
 */
Fdl.Collection.prototype.addFilter = function(filter) {
	if (! this.isSearch()) return false;
	if (! filter) return false;
	if (filter.family) {
		var st=filter.family.indexOf('strict');
		if (st) {
			st=filter.family.indexOf(' ');
			this.setValue("se_famid",filter.family.substr(0,st));
			this.setValue("se_famonly","yes");
		} else {
			this.setValue("se_famid",filter.family);
		}
	}  
	if (filter.criteria || filter.sql || filter.family) {
		var fs=this.getValue("se_filter");
		var fsType=this.getValue("se_typefilter");
		var newfs=[];
		var newfsType=[];
		if (fs) newfs=newfs.concat(fs);
		if (fsType) newfsType=newfsType.concat(fsType);
		newfs.push(Fdl.json2xml({filter:filter.normalize()}));
		newfsType.push('specified');
		this.setValue("se_filter",newfs);
		this.setValue("se_typefilter",newfsType);
		//this.setValue("se_filter",[Fdl.json2xml(filter)]);
	}
	return true;
};


/**
 * delete all filters from search
 * @return {Boolean} return true if succeed
 */
Fdl.Collection.prototype.resetFilter = function() {
	if (! this.isSearch()) return false;	
		this.setValue("se_filter",'');
	
	return true;
};

/**
 * get filters used for a search
 * @return {Array} of Fdl.DocumentFilter
 */
Fdl.Collection.prototype.getFilters = function(filter) {

	if (! this.isSearch()) return null;
	var xmlfilters=this.getValue("se_filter");
	if (xmlfilters && (xmlfilters.length > 0)) {
		var filters=[];
		var xml;
		var ojs;
		for (var i=0;i<xmlfilters.length;i++) {
			xml=Fdl.text2xml(xmlfilters[i]);
			ojs=Fdl.xml2json(xml);
			if (ojs.filter && ojs.filter.criteria) {
				if (ojs.filter.criteria.operator || ojs.filter.criteria.or || ojs.filter.criteria.and) ojs.filter.criteria=[ojs.filter.criteria];
			}
			if (ojs.filter.criteria) {
			for (var j=0;j<ojs.filter.criteria.length;j++) {
				// if only one criteria means no operators needed
				if ((ojs.filter.criteria[j].or) && (ojs.filter.criteria[j].or.operator)) ojs.filter.criteria[j]=ojs.filter.criteria[j].or;
				if ((ojs.filter.criteria[j].and) && (ojs.filter.criteria[j].and.operator)) ojs.filter.criteria[j]=ojs.filter.criteria[j].and;
			}
			}
			var of=new Fdl.DocumentFilter(ojs.filter);
			filters.push(of);
		}
		return filters;
	} else {
		var se_attrids=this.getValue('se_attrids');
		console.log("old",se_attrids);
	}
};


// ========== NOT PROTOTYPE (DEPRECATED)================

// @deprecated
Fdl.getHomeFolder = function() {
  var u=Fdl.getUser();
  if (u != null && u.id) {
    var idhome='FLDHOME_'+u.id;
    var h=new Fdl.Collection({id:idhome});
    if (h.isAlive()) return h;
  }
  return null;
};
// @deprecated
Fdl.getDesktopFolder = function() {
  if (Fdl._desktopFolder) return Fdl._desktopFolder;
  var u=Fdl.getUser();
  if (u != null && u.id) {
    var idhome='FLDDESKTOP_'+u.id;
    var h=new Fdl.Collection({id:idhome});
    if (h.isAlive()) {
      Fdl._desktopFolder=h;
      return h;
    }
  }
  return null;
};
// @deprecated
Fdl.getOfflineFolder = function() {
  if (Fdl._offlineFolder) return Fdl._offlineFolder;
  var u=Fdl.getUser();
  if (u != null && u.id) {
    var idhome='FLDOFFLINE_'+u.id;
    var h=new Fdl.Collection({id:idhome});
    if (h.isAlive()) {
      Fdl._offlineFolder=h;
      return h;
    }
  }
  return null;
};
