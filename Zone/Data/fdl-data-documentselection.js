
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Fdl.DocumentSelection describe the object use for define a document
 *        selection
 *        <p>
 *        I want to select documents 2034 and 2045 :
 *        </p>
 * 
 * <pre><code>
  var s = new DocumentSelection();
  s.insertToList( {
 	id : 2024
 }); // first way : use only the identificator
  var d = C.getDocument( {
  	id : 2045
  }); // C is the context
  s.insertToList( {
  	document : d
  }); // second way : with the complete document object
 * </code></pre>
 * 
 * <p>
 * I want to select all documents of folder 9 except 2067 and 2098 :
 * </p>
 * 
 * <pre><code>
  var nine=C.getDocument({id:9}); // C is the context
  var s=new DocumentSelection({mainSelector:'all',collection:nine);
  s.insertToList({id:2067});
  s.insertToList({id:2098});
 * </code></pre>
 * 
 * <p>
 * I want to select only subfolders of folder 9 :
 * </p>
 * 
 * <pre><code>
  var nine=C.getDocument({id:9}); // C is the context
  var s=new DocumentSelection();
  s.setAllCollection({collection:nine};)
  s.filter=new Fdl.DocumentFilter({family:'DIR'});
 * </code></pre>
 * 
 * @namespace Fdl.Document
 * @cfg {String} mainSelector indicate selection scheme none or all
 * @cfg {Fdl.Collection} collection the collection reference
 * @cfg {Fdl.DocumentFilter} filter of the collection
 * @cfg {Fdl.Context} connection context
 * @cfg {Fdl.Document} selectionItems array of documents
 */
Fdl.DocumentSelection = function (config) {
    this.selectionItems=[];
    this.mainSelector='none';
    if (config) {
    	for (var i in config) this[i]=config[i];
    }
};
Fdl.DocumentSelection.prototype = {
    /**
	 * indicate selection scheme
	 * 
	 * @type String none or all
	 */
    mainSelector:'none',
    /**
	 * list of document selected
	 * 
	 * @type Array of Number of Fdl.Document
	 */
    selectionItems:[],
    /**
	 * collection identificator use for selection
	 * 
	 * @type Fdl.Collection
	 */
    collectionId:null,
    /**
	 * filter use for selection {@link Fdl.DocumentFilter filter}
	 * 
	 * @type Fdl.DocumentFilter
	 */
    filter:null,
    /**
	 * Connexion context
	 * 
	 * @type Fdl.Context
	 */
    context:null,
    /**
	 * if mainSelector is none, it means add document to selection, if
	 * mainSelector is all, it means unselect this document from collection
	 * containt (if exists of course)
	 * 
	 * @param config
	 *            {Nmmber/Fdl.Document} document identificator or document
	 *            object
	 *            <ul>
	 *            <li><b>id : </b>{Number} document identificator</li>
	 *            <li><b>document : </b>{Fdl.Document} document identificator</li>
	 *            </ul>
	 * @return {Boolean} add document to selection object
	 */
    insertToList:function (config) {
	var id=0;
	if (config) {
		if (config.document && config.document.getProperty) {
			config.id=config.document.getProperty('initid');
			if ((! this.context) && (config.document.context)) this.context=config.document.context;
		}
		if (config.id) {
			for (var i=0;i<this.selectionItems.length;i++) {
				if (this.selectionItems[i]==config.id) return false;
			}	
			this.selectionItems.push(config.id);
			return true;
		}
	}
	return null;
    },
    /**
	 * if mainSelector is none, it means remove document from selection, if
	 * mainSelector is all, it means unselectreadd this document from all
	 * document (if exists of course)
	 * 
	 * @return {Boolean} remove document to selection object
	 */
    removeFromList:function (config) {
    	if (config) {
    		for (var i=0;i<this.selectionItems.length;i++) {
    			if (this.selectionItems[i]==config.id) {
    				if (this.selectionItems.length==1) {
    					this.selectionItems=[];
    				} else {
    					if (i < (this.selectionItems.length-1)) this.selectionItems[i]=this.selectionItems[this.selectionItems.length-1];
    					this.selectionItems.pop();
    				}
    				return true;
    			}
    		}
    	}
    	return false;
    },
    toJSON: function(key) {
    	var o={};
    	for (var i in this) {
    		if ((typeof this[i] != 'function') && (i!='context')) o[i]=this[i];
    	}
        return JSON.parse(JSON.stringify(o));        
    },
/**
 * if mainSelector is none, it means remove all documents from selection, if
 * mainSelector is all, it means select all document from collection containt
 * 
 * @return {Boolean} remove document to selection object
 */
    clearSelection:function (config) {
    	this.selectionItems=[];
    },
    /**
	 * invert the selection by switching mainSelector between 'all' and 'none'
	 * value.
	 * 
	 * @return {void}
	 */
    invertSelection:function (config) {
    	if (this.mainSelector=='none') this.mainSelector='all';
    	else this.mainSelector='none';
    },
    /**
	 * Return the documentList identicators, this is no Fdl.Document but only
	 * document id Numbers.
	 * 
	 * @return {Array} of document identificator
	 */
    getDocumentIdList:function (config) {
    	if (this.mainSelector =='all') {
    		// [TODO] call server to getElementList
    	} else {
			return this.selectionItems;
    	}
    },   
    
    /**
	 * Return the documentList
	 * 
	 * @return {Array} of Fdl.Document
	 */
    getDocumentList:function (config) {
    	if (this.mainSelector =='all') {
    		if (this.collectionId) {
    			var tfilter=[];
    			for (var i=0;i<this.selectionItems.length;i++){
    				tfilter.push({operator:'!=',left:'id',right:this.selectionItems[i]});
    			}
    			var f=new Fdl.DocumentFilter({criteria:tfilter});
    			var c=this.context.getDocument({id:this.collectionId,usecache:true});
    			return c.getContent({filter:f});
    		}
    	} else {
    		var g=this.context.createGroupRequest();
    		for (var i=0;i<this.selectionItems.length;i++){
    			var o={};
    			o['d'+i]=g.getDocument({id:this.selectionItems[i]});
    			g.addRequest(o);
    		}
    		var r=g.submit();
    		if (r) {
    			var rt=[];
    			var d;
    			for (var i=0;i<this.selectionItems.length;i++){
    				d=r.get('d'+i);
    				if (d) rt.push(d);
    			}
    			return rt;
    		}
    	}
    	return null;
    },
    /**
	 * set selection to the content of a collection
	 * 
	 * @param {Object}
	 *            config
	 *            <ul>
	 *            <li><b>collection : </b>{Fdl.Collection} the collection
	 *            object</li>
	 *            </ul>
	 * @return {boolean} true if selection done, false if cannot (use
	 *         context.getLastErrorMessage() to see the reason
	 */
    setAllCollection:function (config) {
    	if (config) {
    		if (config.collection) {
    			if (config.collection.isAlive()) {
    				if (config.collection.isCollection()) {
    					this.collectionId=config.collection.getProperty('initid');
    					this.mainSelector='all';
    					if ((! this.context) && (config.collection.context)) this.context=config.collection.context;
    					return true;
    				} else config.collection.context.setErrorMessage('document is not a collection');
    			} else config.collection.context.setErrorMessage('document is not alive');
    			return false;
    		}
    	} 
    	return null;

    },
    toString: function() {
	return 'Fdl.DocumentSelection';
    },
	
	/**
	 * Return the number of currently selected documents.
	 * @method count
	 */
	count: function() {		
		
		if (this.mainSelector == 'all') {
			var count = this.totalCount; // this.totalCount must be stored in the selection by external calls
			return count - this.selectionItems.length;
		} else {
			return this.selectionItems.length;
		}
		
	}
};
