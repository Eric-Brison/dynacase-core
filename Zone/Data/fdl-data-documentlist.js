
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Fdl.DocumentList describe the object returned by getContent or Search
 * 
 * 
 * 
 * @namespace Fdl.Document
 * @cfg {String} mainSelector indicate selection scheme none or all
 * @cfg {Fdl.Collection} collection the collection reference
 * @cfg {Fdl.DocumentFilter} filter of the collection
 * @cfg {Fdl.Context} connection context
 * @cfg {Fdl.Document} selectionItems array of documents
 */
Fdl.DocumentList = function(config) {
	this.content = [];
	if (config) {
		for ( var i in config)
			this[i] = config[i];
	}
	if (this.content) this.length=this.content.length;
};
Fdl.DocumentList.prototype = {
	/**
	 * total number of matches document
	 * 
	 * @type Numeric none or all
	 */
	totalCount : 0,
	/**
	 * The array of raw document
	 * 
	 * @type Array
	 */
	content : null,
	/**
	 * Additionnal informations about collection
	 * 
	 * @type Object
	 */
	info : null,

	/**
	 * count of document list
	 * 
	 * @type Numeric
	 */
	length : 0,
	/**
	 * Connexion context
	 * 
	 * @type Fdl.Context
	 */
	context : null,
	/**
	 * return all document objects return by Fdl.Collection::getContent() or
	 * Fdl.SearchDocument::search()
	 * 
	 * @return {Array} document (FDl.Document) array
	 */
	getDocuments : function() {
		var out = [];
		for ( var i = 0; i < this.content.length; i++) {
			if (typeof this.content[i] == 'object') {
				out.push(this.context.getDocument( {
					data : this.content[i]
				}));
			} else
				alert('FdlDocuments: error in returned');
		}
		return out;
	},
	/**
	 * return document at index position
	 * Fdl.SearchDocument::search()
	 * 
	 * @return {Fdl.Document} document 
	 */
	getDocument : function(index) {
		
			if (typeof this.content[index] == 'object') {
				return (this.context.getDocument( {
					data : this.content[index]
				}));
			}
		
		return null;
	},
	/**
	 * return total count of folder content or search result
	 * 
	 * @return {Numeric} number of documents found
	 */
	count : function() {
		return this.totalCount;
	},
	
	toString : function() {
		return 'Fdl.DocumentList';
	}

};
