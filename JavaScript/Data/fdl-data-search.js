
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Fdl.SearchDocument
  * <pre><code>
  var C=new Fdl.Context({url:'http://my.freedom/'});    

  var s=C.getSearchDocument();
  if (s) {
    var f=new Fdl.DocumentFilter({criteria:[{operator:'~*',
					     left:'title',
				             right:'foo'}]});
    var dl=s.search({filter:f});	
    var p=dl.getDocuments();
    var doc;
    var ht+='&lt;table&gt;';
    for (var i in p) {
            doc=p[i];
            ht+='&lt;tr&gt;&lt;td&gt;'+i+'&lt;/td&gt;&lt;td&gt;'+doc.getProperty('id')
              +'&lt;/td&gt;&lt;td style="width:200px;overflow:hidden"&gt;'+doc.getTitle()+'&lt;/td&gt;&lt;td&gt;&lt;img src="'
              +doc.getIcon({'width':20})+'"&gt;&lt;/td&gt;&lt;td&gt;'+doc.getProperty('mdate')
              +'&lt;/td&gt;&lt;/tr&gt;';
     }
     ht+='&lt;/table&gt;';     
   }
 * </code></pre>
 * @extends Fdl.Document
 * @param {Object} config
 
 */
Fdl.SearchDocument = function (config) {
  Fdl.Document.call(this,config);
  if (config && config.filter) this.filter=config.filter;
};
Fdl.SearchDocument.prototype = new Fdl.Document();
Fdl.SearchDocument.prototype.toString= function() {
      return 'Fdl.SearchDocument';
};

Fdl.SearchDocument.prototype._count=-1;
/** offset where begin search , 0 is the first @type {Number}*/
Fdl.SearchDocument.prototype.start=0;
/** number of max documents returned @type {Number}*/
Fdl.SearchDocument.prototype.slice=50;
/** search mode must be 'word' or 'regexp' (default is word)@type {String}*/
Fdl.SearchDocument.prototype.mode='word';
/** the filtering criteria @type {Fdl.DocumentFilter}*/
Fdl.SearchDocument.prototype.filter=null;
/** the order by property @type {String}*/
Fdl.SearchDocument.prototype.orderBy=null;

/**
 * send a request to search documents 
 * @param {Object} config
 * <ul><li><b>start : </b>{Number} (optional) offset where begin search , 0 is the first</li>
 * <li><b>slice : </b> {Number}(optional) number of documents returned (default is 50)</li>
 * <li><b>mode : </b> {String}(optional) search mode must be 'word' or 'regexp' (default is word)</li>
 * <li><b>family : </b> {String}(optional) filter of document of the this family and descendants</li>
 * <li><b>filter : </b> {String/Fdl.DocumentFilter}(optional) sql filter such as : "us_mail ~* 'zoo.org'" </li>
 * <li><b>key : </b> {String}(optional) main keyword filter  </li>
 * <li><b>orderBy : </b> {String}(optional) the order by property  if withHighlight order is by default a pertinence degree else it is title by default</li>
 * <li><b>searchProperty : </b> {String}(optional) main property or attribute identicator where apply the key. The operator is ~* (insensitive case include) by default equal to any values ("svalues" property) </li>
 * <li><b>withHighlight : </b> {Boolean}(optional) to return highlight text in concordance with the main keyword</li>
 * </ul>
 * @return {Fdl.DocumentList} array of Fdl.Document
 */
Fdl.SearchDocument.prototype.search = function(config) {  
	if (config) {
		if (typeof (config.start) === "undefined") config.start=this.start;
		if (typeof (config.slice) === "undefined") config.slice=this.slice;
		if (typeof (config.mode) === "undefined") config.mode=this.mode;
	}
	var data=null;
	if (config && config.data) {
		data=config.data;
	} else {
		if (config && config.filter) config.filter=JSON.stringify(config.filter);
		else if (this.filter) {
			if (! config) config={};
			config.filter=JSON.stringify(this.filter);
		}
		var data=this.context.retrieveData({app:'DATA',action:'DOCUMENT',method:'search'},config);
	}
	if (data) {
		this._info=data.info;
		if (! data.error) {
			data.context=this.context;
			return new Fdl.DocumentList(data);
		} else {
			this.context.setErrorMessage(data.error);
		}
	} else return false;
};



/**
 * search family document
 * @param config {object}
 * <ul><li><b>key</b>the title filter</li>
 * </ul>
 * @return {Fdl.DocumentList}  of Fdl.Family
 */
Fdl.SearchDocument.prototype.getFamilies = function(config) {
    if (! config) config=new Object();
    config.famid=-1;
    config.mode='regexp';
    config.searchProperty='title';
    config.slice="ALL";
    return this.search(config);
};   
/**
 * search sub family document
 * @param config {object}
 * <ul><li><b>famid</b>(Number) the family id</li>
 *     <li><b>controlCreation</b>(Boolean) control if user can create document of this family (default is false)</li>
 * </ul>
 * @return {Array} of Fdl.Family
 */
Fdl.SearchDocument.prototype.getSubFamilies = function(config) {
    if ((! config) || (!config.famid)) {
    	this.context.setErrorMessage('no family identificator set');
    	return null;
    }
    var data=this.context.retrieveData({app:'DATA',action:'DOCUMENT',method:'getsubfamilies'},config);
    
    if (data) {
		this._info=data.info;
		if (! data.error) {
			data.context=this.context;
			return new Fdl.DocumentList(data);
		} else {
			this.context.setErrorMessage(data.error);
		}
	} 
   return null;
};   
