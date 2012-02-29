
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Fdl.Document
 * The document object
 * <pre><code>
var C=new Fdl.Context({url:'http://my.freedom/'});
  
var d=C.getDocument({id:9});
if (d && d.isAlive()) {
    alert(d.getTitle());
}
 * </code></pre>
 * @namespace Fdl.Document
 * @param {Object} config
 * @cfg {String/Number} id document identificator. Could be a logical identificator (a string) or a system identificator (a number)
 * @cfg {Boolean} latest (Optional)  set to false if you don't want the latest revision but the exact revision given by id
 * @cfg {Number} revision (Optional) retrieve a specific revision of the document. 0 is the first. 
 * @cfg {Object} data (Optional) initialize document object from raw data (internal use)
 */

Fdl.Document = function(config){
    if (config) {
	var data=null;
	if (config.context) {
		this.context=config.context;
	}
	if (! config.data) {
	    this.id = config.id;
	    
	    this.latest = (config.latest == null) ? false : config.latest;
	    this.revision = (this.latest == true) ? null : config.revision;	
	    
	    this._data=null;
	    if (this.id)  data=this.context.retrieveData({app:'DATA',action:'DOCUMENT'},config);
	} else data=config.data;
	if (data) this.affect(data);  else return false;
    }
};


Fdl.Document.prototype = {
    id: null,
    latest: null,
    revision: null,
    context:Fdl,
    followingStates:null,
    requestDate:'',
    _mvalues:new Object(),
    _attributes:null,
    /**
     * Initialize object with raw data
     * @param {Object} data row data
     * @return {Boolean} return true if no errors
     */ 
    affect: function (data) {
	if (data && (typeof data == 'object')) {
	    if (! data.error) {	  
		this._mvalues=new Object();
		this._data=data;
		//	      this._properties=data.properties;
		if (data.properties) {
		    this.id=data.properties.id;
		    if (data.properties.informations) this.context.propertiesInformation=data.properties.informations;
		    //this._values=data.values;
		    if (data.attributes) this.completeAttributes(data.attributes);
		    if (data.followingStates) this.followingStates=data.followingStates;
		    if (data.userTags) this.userTags=data.userTags;
		    if (data.requestDate) this.requestDate=data.requestDate;
		    return true;
		} else {
		    alert('error no properties');
		}
	    } else {
		this.context.setErrorMessage(data.error);
		return false;
	    }
	}
	return false;
    },
    toString: function() {
	return 'Fdl.Document';
    },
    /**
     * Return the title of the document
     * @return {String} the title
     */ 
    getTitle: function(otherid){
	if (! this._data) return null;
	if (otherid) {
	    
	    return "search "+otherid;
	} else {
	    return this._data.properties.title;
	}
    },	
    /**
     * Get url to the file icon of the document
     * @param {Number} width the width of icon in pixel
     * @return {String} return the url
     */ 
    getIcon: function(config) {
	if (! this._data) return null;
	var width=false;
	var src=this.context.url+this._data.properties.icon;
	if (config && config.width)  {
	    width=config.width;
	    var u=this.context.url;
	    var ps=u.lastIndexOf('/');
	    if (ps) {
		u=u.substring(0,ps+1);
		src=u+this._data.properties.icon;
	    }
	    src=u+'resizeimg.php?size='+width+'&img='+escape(src);
	}
	return src;
    },	
    /**
     * Get state of document 
     * @return {String} return the state key
     */ 
    getState: function(){
	return this._data.properties.state;
    }, 
    /**
     * Get label state of document 
     * @return {String} return the state key
     */ 
    getLocalisedState: function(){
	return this._data.properties.labelstate;
    },
    /**
     * Get associated color of the current state
     * @return {String} return RGB color (#RRGGBB)
     */ 
    getColorState: function(){
	return this._data.properties.colorstate;
    },
    /**
     * Get current activity of the document
     * @return {String} return description of activity
     */
    getActivityState: function(){
	return this._data.properties.activitystate;
    },
    /**
     * Get value of a attribute
     * @param {String} id the attribute identificator
     * @return {Any} return value of document
     */
    getValue: function(id, def) {
    	var v=this._data.values[id];
    	return ((v==null)?def:v);
    }, 
   
    /**
     * return all values of the array
     * @param {String} id the attribute identificator
     * @return {Object} array Rows composed of columns (attributes) values
     */
    getArrayValues: function(id) {
    	var oa=this.getAttribute(id);
    	var rv=[];
    	if (oa) {
    		var oas=oa.getElements();
    		var vc;

    		if (oas.length > 0 && this.getValue(oas[0].id).length > 0) {
    			// first find max rows
    			var i=0;
    			for ( i=0;i<this.getValue(oas[0].id).length;i++) {
    				rv[i]=new Object();
    			}
    			for ( i=0; i< oas.length; i++) {
    				vc=this.getValue(oas[i].id);
    				for (var ic=0;ic<vc.length;ic++) {	    
    					rv[ic][oas[i].id]=vc[ic];
    				}
    			}
    		}
    	}
    	return rv;

    }, 
    /**
     * Get formated value of a attribute
     * For relation attributes return document title, for enumerate return label
     * @param {String} id the attribute identificator
     * @return {Any} return value of document
     */
    getDisplayValue: function(id,config) {
    	var oa=this.getAttribute(id);
    	var i=0,vs=null,tv=[];
        if (oa) {

            if (! this._data.values[id]) return this._data.values[id];
            if (oa.toString() == 'Fdl.RelationAttribute') return Fdl.encodeHtmlTags(this.getValue(id+'_title',this._data.values[id]));
            if (oa.toString() == 'Fdl.ThesaurusAttribute') return Fdl.encodeHtmlTags(this.getValue(id+'_title',this._data.values[id]));
            if (oa.toString() == 'Fdl.EnumAttribute') {
                if (oa.inArray() || oa.isMultiple()) {
                    tv=[];
                    vs=this._data.values[id];
                    if (vs) {
                        for (i=0;i<vs.length;i++) {
                            tv.push(oa.getEnumLabel({key:vs[i]}));
                        }
                    }
                    return tv;
                } else {
                    return oa.getEnumLabel({key:this._data.values[id]});
                }
            }
            if (oa.toString() == 'Fdl.FileAttribute') {
                if (oa.inArray()) {
                    tv=[];
                    vs=this._data.values[id];
                    if (vs) {
                        for (i=0;i<vs.length;i++) {
                            if (config && config.url) {
                                if (config.dav) {
                                    tv.push(oa.getDavUrl(vs[i],this.id));
                                } else {
                                    config.index=i;
                                    tv.push(oa.getUrl(vs,this.id,config));
                                }
                            } else tv.push(Fdl.encodeHtmlTags(oa.getFileName(vs[i])));
                        }
                    }
                    return tv;
                } else {
                    if (config && config.url) {
                        if (config.dav) return oa.getDavUrl(this._data.values[id],this.id);
                        else return oa.getUrl(this._data.values[id],this.id,config);
                    } else return Fdl.encodeHtmlTags(oa.getFileName(this._data.values[id]));
                }
            }
            if (oa.toString() == 'Fdl.DateAttribute') {
                var fmt=this.context.getUser().getLocaleFormat();
                var dateFmt='';
                if (oa.type=='date') {
                    dateFmt=fmt.dateFormat;
                } else if (oa.type=='timestamp') {
                    dateFmt=fmt.dateTimeFormat;
                }  else if (oa.type=='time') {
                    dateFmt=fmt.timeFormat;
                }
                if (dateFmt) {
                    if (oa.inArray()) {
                        vs=this._data.values[id];
                        tv=[];
                        for (i=0;i<vs.length;i++) {
                            tv.push(Fdl.formatDate(vs[i],dateFmt));
                        }
                        return tv;
                    } else {
                        return Fdl.formatDate(this._data.values[id],dateFmt);
                    }
                }

            }
        }
        return Fdl.encodeHtmlTags(this._data.values[id]);
    },
    
    /**
     * set value to an attribute
     * the document is not updated in database server until it will saved
     * @param {string } id the attribute identificator 
     * @param {String} value the new value to set
     * @return {boolean} true if set succeed
     */
    setValue: function(id,value) {
    	var oa=this.getAttribute(id);
    	if (! oa)  {
    		this.context.setErrorMessage('setValue: attribute '+id+' not exist');
    		return null;
    	}
    	if (this.getProperty('locked')==-1) {
    		this.context.setErrorMessage(this.context._("setValue: document is fixed"));
    		return null;
    	}
    	if (value != this._data.values[oa.id]) {      	
    		this._data.values[oa.id]=value;
    		this._mvalues[oa.id]=value;
    	}
    	return true;
    },
    /**
     * Modify the logical identificator of a document
     * the document is not updated in database server until it will saved
     * @param {string} name the new identificator
     * @return {boolean} true if succeed
     */
    setLogicalIdentificator: function(name) {
    	this._data.properties['name']=name;
    	this._mvalues['name']=name;
    	return true;
    },
    /**
     * Verify if an attribute value has changed by a setValue
     * not verify from database
     * @return {boolean} true if changed
     */
    hasChanged: function() {
    	if (typeof this._mvalues === 'object') {
    		for (i in this._mvalues) {
    			v = this._mvalues[i];
    			if (v !== undefined && typeof v !== 'function') {
    				return true;
    			}
    		}
    	}
	return false;
    },
    /**
     * Return value of a property of the document
     * @param {String} id property identificator can be one of
     * <ul><li>id</li><li>owner</li><li>title</li><li>revision</li><li>version</li><li>initid</li><li>fromid</li><li>doctype</li><li>locked</li><li>allocated</li><li>icon</li><li>lmodify</li><li>profid</li><li>usefor</li><li>cdate</li><li>adate</li><li>revdate</li><li>comment</li><li>classname</li><li>state</li><li>wid</li><li>postitid</li><li>forumid</li><li>cvid</li><li>name</li><li>dprofid</li><li>atags</li><li>prelid</li><li>confidential</li><li>ldapdn</li></ul>
     * @return {String} return the value
     */
    getProperty: function(id) {
    	if (! this._data) return null;
    	return this._data.properties[id];
    },
    /**
     * Return all properties of the document
     * @return {Object} return all properties {id:9, initid:9, locked:0,...}
     */
    getProperties: function(id) {
    	if (! this._data) return null;
    	return this._data.properties;
    },

    /**
     * return all attrtibutes values 
     * @return {Object} indexed array [{key:value},{key:value}....]
     */
    getValues: function() {
    	if (! this._data) return null;
    	return this._data.values;
    },
    /**
     * get attribute definition
     * @param {string} id the attribute identificator
     * @return {Fdl.Attribute}
     */
    getAttribute: function(id) {
    	if (! this._attributes) this.getFamilyAttributes();
    	if (! this._attributes) return null;
    	if (typeof this._attributes == 'object' && this._attributes[id]) return this._attributes[id];
    	return null;
    },
    /**
     * Return all attributes definition of the document
     * @return {Array} return all attribute Fdl.Attribut definition
     */
    getAttributes: function() {
    	if (! this._attributes) {
    		return this.getFamilyAttributes();
    	}
	return this._attributes;
    },	   
    /**
     * Return all attributes definition of the document
     * @return {Array} return all attribute Fdl.Attribut definition
     */
    getFamilyAttributes: function() {
    	if (! this._attributes) {
    		// retrieve attributes from family
    		var f=this.context.getDocument({id:(this.getProperty('doctype')=='C'?this.getProperty('id'):this.getProperty('fromid')),useCache:true,onlyValues:false,propertiesInformation:(this.context.propertiesInformation==null)});
    		if (f && f._attributes) this._attributes=f._attributes;
    		else if (f && (! f._attributes)) {
    			f=this.context.getDocument({id:(this.getProperty('doctype')=='C'?this.getProperty('id'):this.getProperty('fromid')),useCache:false,onlyValues:false,propertiesInformation:(this.context.propertiesInformation==null)});
    			if (f && f._attributes) this._attributes=f._attributes;
    		} else {
    			// family not found
    			this._attributes=[];
    		}
    	}
	  return this._attributes;
    },	
    /**
     * Return all attributes of the document which can be sorted
     * @return {Array} return all attribute Fdl.Attribut definition
     */
    getSortableAttributes: function() {
	var s=[];
	if (! this._attributes) this.getFamilyAttributes();
	if (this._attributes) {
	    for (var i in this._attributes) {
		if (this._attributes[i].isSortable())
		    s.push(this._attributes[i]);
	    }
	}
	return s;
    },	
    /**
     * Return all attributes of the document which can be filtered or searchable
     * @return {Array} return all attribute Fdl.Attribut definition
     */
    getFilterableAttributes: function() {
	var s=[];

	if (! this._attributes) this.getFamilyAttributes();
	if (this._attributes) {
	    for (var i in this._attributes) {
		if (this._attributes[i].isLeaf() &&
		    (this._attributes[i].type != 'htmltext') &&
		    (this._attributes[i].type != 'color') )
		    s.push(this._attributes[i]);
	    }
	}
	return s;
    },	
    /**
     * verify if document exist and it is not in the trash
     * @return {Boolean} return true if exists
     */
    isAlive: function() {
    	return this._data && this._data.properties && (this._data.properties.id > 0) && (this._data.properties.doctype != 'Z');
    },
    /**
     * verify if current user can edit document
     * @return {Boolean} return true if can
     */
    canEdit: function() {
    	return (this.getProperty('readonly')==false);
    },
    /**
     * verify if document is controlled by a workflow
     * @return {Boolean} return true if is controlled by a workflow
     */
    hasWorkflow: function() {
    	return (this.getProperty('wid') > 0);
    },
    /**
     * verify if document is in the trash
     * @return {Boolean} return true if in the trash
     */
    isDeleted: function() {
    	return (this.getProperty('doctype') == 'Z');
    },
    /**
     * verify if document is a fixed revision. It cannot be modified
     * @return {Boolean} return true if fixed
     */
    isFixed: function() {
    	return (this.getProperty('locked') == -1);
    },
    /**
     * verify if document is a collection (see {@link Fdl.Collection method isFolder(), isSearch()} )
     * @return {Boolean} return true if it is a collection (folder or search)
     */
    isCollection: function() {
    	var dt=this.getProperty('defdoctype');
    	return ((dt =='S')||(dt=='D'));
    }
    

};




Fdl.Document.prototype.completeAttributes = function(attrs) {
	if (attrs) {
		this._attributes=new Object();
		for (var name in attrs) {
			switch (attrs[name].type) {
			case 'text':
			case 'longtext':
			case 'htmltext':
				this._attributes[attrs[name].id]=new Fdl.TextAttribute(attrs[name]);
				break;
			case 'int':
			case 'double':
			case 'float':
			case 'money':
				this._attributes[attrs[name].id]=new Fdl.NumericAttribute(attrs[name]);
				break;
			case 'date':
			case 'time':
			case 'timestamp':
				this._attributes[attrs[name].id]=new Fdl.DateAttribute(attrs[name]);
				break;
			case 'docid':
				this._attributes[attrs[name].id]=new Fdl.RelationAttribute(attrs[name]);
				break;
			case 'color':
				this._attributes[attrs[name].id]=new Fdl.ColorAttribute(attrs[name]);
				break;
			case 'enum':
				this._attributes[attrs[name].id]=new Fdl.EnumAttribute(attrs[name]);
				break;
			case 'thesaurus':
				this._attributes[attrs[name].id]=new Fdl.ThesaurusAttribute(attrs[name]);
				break;
			case 'file':
			case 'image':
				this._attributes[attrs[name].id]=new Fdl.FileAttribute(attrs[name]);
				break;
			case 'tab':
				this._attributes[attrs[name].id]=new Fdl.TabAttribute(attrs[name]);
				break;
			case 'frame':
				this._attributes[attrs[name].id]=new Fdl.FrameAttribute(attrs[name]);
				break;
			case 'array':
				this._attributes[attrs[name].id]=new Fdl.ArrayAttribute(attrs[name]);
				break;
			case 'menu':
			case 'action':
				this._attributes[attrs[name].id]=new Fdl.MenuAttribute(attrs[name]);
				break;
			default:
				this._attributes[attrs[name].id]=new Fdl.Attribute(attrs[name]);
			}
			this._attributes[attrs[name].id]._family=this;
		}

	}
};
/**
 * convert document object to json string
 * @return {String} return document to json
 */
Fdl.Document.prototype.toJSON = function(key) {
    return JSON.parse(JSON.stringify(this._data));
    
};

Fdl.Document.prototype.send = function(config){
	
	var result = this.context.retrieveData({
		app: 'DATA',
		action: 'DOCUMENT',
		id: this.id,
		method: 'send'
	},config);
	
	if(result){
		return true;
	} else {
		this.context.setErrorMessage('send : no result');
		return false ;
	}
	
	
};

/**
 * save document to server
 * the document must be modified by setValue before
 * @return {Boolean} true if saved is done. If false error can be retrieve with getLastErrorMessage()
 */
Fdl.Document.prototype.save = function(config) {
	if (this.getProperty('locked')==-1) {
		this.context.setErrorMessage(this.context._("save : document is fixed"));
		return false;
	}
    if (config && config.form) {
	return this.savefromform(config);
    } else {
	var autounlock=false;
	if (config && config.autounlock) autounlock=true;
	if (!this.hasChanged()) return true; // nothing to save
	var newdata=this.context.retrieveData({app:'DATA',action:'DOCUMENT',
				      method:'save',
				      temporary:this.getProperty('doctype')=='T',
				      id:this.id,autounlock:autounlock},this._mvalues);
	
	if (newdata) {
	    if (! newdata.error) {
		this.affect(newdata);
		return true;
	    } else {	
		this.context.setErrorMessage(newdata.error);
	    }
	} else {      
	    this.context.setErrorMessage('save : no data');
	}
	return false;    
    }
};

/**
 * save document to server from a HTML form useful for file upload
 * inputs of document mult named with attribute ids
 * <pre>
&lt;form id="myform"
       method="POST" ENCTYPE="multipart/form-data" &gt;    
&lt;label for="i_ba_dec"&gt;ba _desc&lt;/label&gt;&lt;input id="i_ba_desc" type="text" name="ba_desc"/&gt;
&lt;label for="i_fi_ofile"&gt;File&lt;/label&gt;&lt;input id="i_fi_ofile" type="file" name="fi_ofile[1]"/&gt;
&lt;label for="i_fi_subject"&gt;Subject&lt;/label&gt;&lt;input id="i_fi_subject" type="text" name="fi_subject"/&gt;
&lt;/form&gt;
 * </pre>
 * <pre><code>
 var doc=context.getDocument({id:6790});
 if (! doc.save({form:document.getElementById('myform'),callback:mycallback })) {    
    var t='ERROR:'+Fdl.getLastErrorMessage();
    alert(t);
  }
  
function mycallback(doc) {
  // I am after saved
  alert(doc.getValue('ba_desc'));
}

 * </code><pre>
 * @return {Boolean} true if saved is done. If false error can be retrieve with getLastErrorMessage()
 */
Fdl.Document.prototype.savefromform = function(config) {
	if (config && config.form!==null) {
		if (config.form.nodeName != 'FORM' && config.form.nodeName != 'html:form') {	  
			this.context.setErrorMessage('not a form object');
			return false;
		}
		var f=config.form;
		var oriaction=f.action;
		var oritarget=f.target;
		var t=null;
		if (oritarget) t=document.getElementById(f.target);

		if (t && t.contentDocument.body.firstChild) {
			t.contentDocument.body.innerHTML='';	  
		}
		var callid=Fdl._waitSave(this, config);
		f.action=this.context.url+'?app=DATA&action=DOCUMENT&method=saveform&id='+this.id+'&callid='+callid;
		if (config.autounlock) f.action += '&autounlock=true';

		f.target=Fdl.getHiddenTarget();
		f.submit();
		if (t && t.contentDocument.body.firstChild) {
			try {
				var v=eval('('+t.contentDocument.body.innerHTML+')');	  
				//	  Fdl.print_r(v);
			} catch (ex) {
			}
		}
		//	if (t.contentDocument.body.firstChild) alert(t.contentDocument.body.firstChild.innerHTML);

		//	console.log(document.getElementById(f.target));
		f.action=oriaction;
		f.target=oritarget;
		return true;
	}
	return false;
};




/**
 * reload document from server
 * a reload cannot be done if current changed are not saved
 * @return {Boolean} true if saved is done. If false error can be retrieve with getLastErrorMessage()
 */
Fdl.Document.prototype.reload = function(config) {
	if (this.hasChanged()) {
		this.context.setErrorMessage('reload : cannot reload because data are locally modified');
	} else {
		if (! config) config=new Object();
		config.method='reload';
		return this.callMethod(config);	
	}
	return false;    
}; 

/**
 * set new state to document
 * @param {Object} config
 * <ul><li><b>state : </b> the new state</li>
 * </ul>
 * @return {Boolean} true if saved is done. If false error can be retrieve with getLastErrorMessage()
 */
Fdl.Document.prototype.changeState = function(config) {
	if (! config) config=new Object();
	config.method='changestate';
	return this.callMethod(config);
}; 

/**
 * add user tag to document
 * @param {Object} config
 * <ul><li><b>tag : </b> the key tag</li>
 * <li><b>comment : </b> the comment</li>
 * </ul>
 * @return {Boolean} true if saved is done. If false error can be retrieve with getLastErrorMessage()
 */
Fdl.Document.prototype.addUserTag = function(config) {
	if (! config) config=new Object();
	config.method='addusertag';
	if (! config.tag){
		this.context.setErrorMessage(this.context._("data::no tag specified"));
		return null;
	}  
	return this.callMethod(config);
}; 
/**
 * delete user tag to document
 * @param {Object} config
 * <ul><li><b>tag : </b> the key tag</li>
 * </ul>
 * @return {Boolean} true if deletion is done. If false error can be retrieve with getLastErrorMessage()
 */
Fdl.Document.prototype.deleteUserTag = function(config) {
	if (! config) config=new Object();
	config.method='deleteusertag';
	if (! config.tag){
		this.context.setErrorMessage(this.context._("data::no tag specified"));
		return null;
	}  
	return this.callMethod(config);
}; 
/**
 * get user tags from document
 * @param {Object} config
 * <ul><li><b>reset : </b> (boolean) force ask to the server if already set</li>
 * </ul>
 * @return {Object} return key,value list of tags . If null error can be retrieve with getLastErrorMessage()
 */
Fdl.Document.prototype.getUserTags = function(config) {
	if (! config) config=new Object();
	config.method='getusertags';
	if (this.userTags && (!config.reset)) return this.userTags;

	var r=this.callMethod(config);
	if (! r.error) {
		this.userTags=r.userTags;
		return r.userTags;
	}
	return null;
}; 
/**
 * affect a user to the document
 * @param {Object} config
 * <ul><li><b> userSystemId: </b>  (integer) the user identificator</li>
 * <li><b>comment : </b> (text) describe why it is affected</li>
 * <li><b>lock : </b> (boolean) auto lock for the user</li>
 * <li><b>revision : </b> (boolean) auto revision for the user</li>
 * </ul>
 * @return {Boolean} true if allocation is done. If false error can be retrieve with getLastErrorMessage()
 */
Fdl.Document.prototype.allocate = function(config) {
	if (! config) config=new Object();
	config.method='allocate';
	return this.callMethod(config);
}; 
/**
 * unaffect user to the document. The document has not allocated user after
 * @param {Object} config
 * <li><b>comment : </b> (text) describe why it is unaffected</li>
 * <li><b>revision : </b> (boolean) auto revision for the user</li>
 * </ul>
 * @return {Boolean} true if desallocation is done. If false error can be retrieve with getLastErrorMessage()
 */
Fdl.Document.prototype.unallocate = function(config) {
	if (! config) config=new Object();
	config.method='unallocate';
	return this.callMethod(config);
}; 

/**
 * set document to the trash
 * a remove cannot be done if current changed are not saved
 * @return {Boolean} true if removed is done. If false error can be retrieve with getLastErrorMessage()
 */
Fdl.Document.prototype.remove = function(config) {
	if (this.hasChanged()) {
		this.context.setErrorMessage('remove : cannot remove because data are locally modified');
	} else {
		if (! config) config=new Object();
		config.method='delete';
		return this.callMethod(config);
	}
	return false;    
}; 

/**
 * go out document from the trash
 * a restore cannot be done if current changed are not saved
 * @return {Boolean} true if removed is done. If false error can be retrieve with getLastErrorMessage()
 */
Fdl.Document.prototype.restore = function(config) {
	if (this.hasChanged()) {
		this.context.setErrorMessage('restore : cannot restore because data are locally modified');
	} else {
		if (! config) config=new Object();
		config.method='restore';
		return this.callMethod(config);	
	}
	return false;    
};

/**
 * lock document
 * @param bool auto if true a temporary lock
 */
Fdl.Document.prototype.lock = function(config) {
	if (! config) config=new Object();
	config.method='lock';
	return this.callMethod(config);
};
/**
   * unlock document
   * @param bool auto if true delete temporary lock
   */
Fdl.Document.prototype.unlock = function(config) {
    if (! config) config=new Object();
    config.method='unlock';
    return this.callMethod(config);
};


Fdl.Document.prototype.hasWaitingFiles = function() {   
    var data=this.context.retrieveData({app:'DATA',action:'DOCUMENT',
			       method:'haswaitingfiles',
			       id:this.id});
    if (data) {
	if (! data.error) {	  
	    return data.haswaitingfiles;
	} else {	
	    this.context.setErrorMessage(data.error);
	}
    } else {      
	this.context.setErrorMessage('hasWaitingFiles : no data');
    }
    
    return false;    
};

Fdl.Document.prototype.getFollowingStates = function() {
    if (! this.id) return null;
    if (this.followingStates) return this.followingStates;
    else {
	var data=this.context.retrieveData({app:'DATA',action:'DOCUMENT',
				   method:'getfollowingstates',
				   id:this.id});
	if (data) {
	    if (! data.error) {	  
		this.followingStates=data.followingStates;
		return data.followingStates;
	    } else {	
		this.context.setErrorMessage(data.error);
	    }
	} else {      
	    this.context.setErrorMessage('getFollowingStates : no data');
	}	
    }
    return false;    
};


/**
 * retrieve history items
 * @return {Fdl.DocumentHistory} the history object
 */
Fdl.Document.prototype.getHistory = function() {
    this.history=new Fdl.DocumentHistory({id:this.id,context:this.context});
    if (this.history.items == null) return null;
    return this.history;
};

/**
   * create a new revision
   * @param string comment
   * @param string version
   */
Fdl.Document.prototype.addRevision = function(config) {
    if (! config) config=new Object();
    config.method='addrevision';
    return this.callMethod(config);
};

Fdl.Document.prototype.callMethod = function(config) {
    if (config && config.method) {
	var data=null;
	if (config.norequest) {
	    data=this._data;
	} else {
	    data=this.context.retrieveData({app:'DATA',action:'DOCUMENT',
						method:config.method,
						id:this.id},config);
	}
	if (data) {
	    if (! data.error) {
		if (! config.norequest) {
		    if (data.properties) this.affect(data);
		    else return data;
		}
		return true;
	    } else {	
		this.context.setErrorMessage(data.error);
	    }
	} else {      
	    this.context.setErrorMessage(config.method+' : no data');
	}
    }
    return false;    
};

/**
  * create a new document from another one
  * @param {Object} config
  * <ul><li><b>cloneFiles : </b>(Boolean)(optional) set to true if you want also clone atached files else the document references same files (default is false)</li>
  * <li><b>linkFolder : </b> (Boolean)(optional) set to false iy you don't want the copy will be inserted in the same primary folder than the source (default is true) </li>
  * <li><b>temporary : </b> (Boolean)(optional) the clone is a temporary document</li>
  * <li><b>title : </b> (String)(optional) the new title of document else it is the same as original</li>
  * </ul>
  * @return {Fdl.Document}  if clone suceeded, return null if no success
  */
Fdl.Document.prototype.cloneDocument = function(config) {
	var data=null;
	if (config && config.norequest) {
	    data=this._data;
	} else {
	 data=this.context.retrieveData({app:'DATA',action:'DOCUMENT',
				   method:'clone',
	 			   id:this.id},config);
	}
	if (data) {
	    if (! data.error) {
		var clone=eval('new '+this.toString()+'()');
		clone.context=this.context;
		clone.affect(data);

		return clone;
	    } else {	
		this.context.setErrorMessage(data.error);
	    }
	} else {      
	    this.context.setErrorMessage(config.method+' : no data');
	}
    
    return null;    
};

/**
   * move document from primary folder to another folder
   * @param {Object} config
   * <ul><li><b>folderId : </b> the identificator of folder destination</li>
   * <li><b>fromFolderId : </b> (optional) the source folder, if not defined it is the primary folder of the document</li>
   * </ul>
   * @return {Boolean} true if move suceeded
   */
Fdl.Document.prototype.moveTo = function(config) {
    if (config && config.folderId) {
	if (! config) config=new Object();
	config.method='moveto';
	return this.callMethod(config);
    }
    return false;    
};

/**
 * get all possible views for this document
 * @return {Object} set of available view
 */
Fdl.Document.prototype.getViews = function() {
	if (! this._data) {
		this.context.setErrorMessage('getviews : no data');
		return null;
	} else if (this._data.configuration && this._data.configuration.views) {
		return this._data.configuration.views;
	}        
	return false;    
};
/**
 * get the default consultation view for this document
 * @return {Object} information about view, false if none
 */
Fdl.Document.prototype.getDefaultConsultationView = function() {
	var vs=this.getViews();
	if (vs) {
		for (v in vs) {
			if ((vs[v]['default']=="yes") && (vs[v].kind=="consultation")) return vs[v];
		}
	}
	return false;    
};
/**
 * get all possible edition views for this document
 * @return {Object} information about view, false if none
 */
Fdl.Document.prototype.getDefaultEditionView = function() {
	var vs=this.getViews();
	if (vs) {
		for (v in vs) {
			if ((vs[v]['default']=="yes") && (vs[v].kind=="edition")) return vs[v];
		}
	}
	return false;    
};

/**
   * get all attached timers informations
   * <pre>2 -[object Object]
......level - [4]
......delay - [0]
......actions -[object Object]
............state - []
............tmail - [47645
47593]
............method - []

......execdate - [2009-11-19 20:51]
......execdelay - [-5.85903333267]
......timerid - [47232]
......timertitle - [Mon minuteur]
......local -[object Object]
............lstate - [false]
............lmethod - [false]
............tmailtitle - [Expédition Rapport
MAIL_ARTICLE_TO_APPROUV]
............hdelay - []
    </pre>
   * @param bool reset : if true force another request
   * @return {array} of object
   */
Fdl.Document.prototype.getAttachedTimers = function(config) {   
	if (! this.id) return null;
	if (this.attachedTimers && ((!config) || (config && (!config.reset)))) return this.attachedTimers;
	else {
		var data=this.context.retrieveData({app:'DATA',action:'DOCUMENT',
			method:'getAttachedTimers',
			id:this.id});
		if (data) {
			if (! data.error) {	  
				this.attachedTimers=data.attachedTimers;
				return data.attachedTimers;
			} else {	
				this.context.setErrorMessage(data.error);
			}
		} else {      
			this.context.setErrorMessage('getAttachedTimers : no data');
		}	
	}

	return false; 
};

/**
   * control a particular access of a document such as 'view' or 'delete'
   * @param string : the acl
   * @return {Boolean} true is acces granted
   */
Fdl.Document.prototype.control = function(acl) {   
	if (! this.id) return null;
	if (! this._data.security) return null;
	if (! acl) return null;
	if (acl.acl) acl=acl.acl;
	if (this._data.security[acl]) {    
		return this._data.security[acl].control;
	}	
	return null; 
};
/**
   * get all attached timers informations
   * @param bool reset : if true force another request
   * @return array
   */
Fdl.Document.prototype.getProfilAcls = function() {   
	if (! this.id) return null;
	if (this._data.security) return this._data.security;
	return false; 
};
/**
  * get all filterable properties
  * @return {array} of property identificators
  */
Fdl.Document.prototype.getFilterableProperties = function() { 
	f=[];
	if (this.context) {
		return this.context.getFilterableProperties();
	}
	return f;
};

/**
  * get all displayable properties
  * @return {array} of property identificators
  */
Fdl.Document.prototype.getDisplayableProperties = function() { 
	f=[];
	if (this.context) {
		return this.context.getDisplayableProperties();
	}
	return f;
};
/**
  * get all sortable properties
  * @return {array} of property identificators
  */
Fdl.Document.prototype.getSortableProperties = function() { 
	f=[];
	if (this.context) {
		return this.context.getSortableProperties();
	}
	return f;
};
/**
 * get information about property
 * @param {String} id the property id
 * @return {Object} example : {"type":"integer","displayable":true,"sortable":true,"filterable":true,"label":"identificateur"},
 */
Fdl.Document.prototype.getPropertyInformation = function(id) { 
	if (this.context) {
		return this.context.getPropertyInformation(id);
	}
	return null;
};
/**
 * get available operators for search criteria by attribute type
 * @return {Object} the operator available by attribute type  
{text:[{operator:'=', label:'equal', operand:['left','right'],labelTpl:'{left} is equal to {right}'},{operator:'~*', label:'include',operand:['left','right'],labelTpl:'{left} is equal to {right}'}],integer:[{operator:'=', label:'equal'},{operator:'>', label:'&gt;'}],...
 */
Fdl.Document.prototype.getSearchCriteria = function() { 
	if (this.context) return this.context.getSearchCriteria();
	return null;
};

/**
 * Can be use after a search with hightlight option
 * <pre><code>
 * </code></pre>
 * @param {String} id the attribute identificator
 * @return {String} the key is between &gt;b<&lt; HTML tag
 */
Fdl.Document.prototype.getHighlight = function() { // in case of search with highlight
	return this._data.highlight;
};


