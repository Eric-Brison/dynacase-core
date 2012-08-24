
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Fdl.DocumentHistory
 * <pre><code>
var C=new Fdl.Context({url:window.location.protocol+'//'+window.location.hostname+'/freedom'});    
var d= C.getDocument({id:document.getElementById('docid').value});
if (d && d.isAlive()) {
    var H=d.getHistory();
    if (H) {
	var HI=H.getItems();
	var ht='&lt;table rules="all"&gt;';
	var hi;
	for (var i=0;i&lt;HI.length;i++) {
	    hi=HI[i];
	    ht+='&lt;tr&gt;&lt;td&gt;'+hi.id+'&lt;/td&gt;&lt;td&gt;'+hi.code+'&lt;/td&gt;&lt;td&gt;'+hi.date+'&lt;/td&gt;&lt;td&gt;'+hi.userName+'&lt;/td&gt;&lt;td&gt;'+hi.comment+'&lt;/td&gt;&lt;/tr&gt;';
        }
	ht+='&lt;/table&gt;';
 * </code></pre>
 * @param {Object} config
 */

Fdl.DocumentHistory = function(config){
    if (config) {
	var data;
	if (config.context) this.context=config.context;
	if (config.id) {
	    this.id = config.id;	     
	    this._data=null;
	    data=this.context.retrieveData({app:'DATA',action:'DOCUMENT',method:'history'},config);
	    if (data && data.error) {	
		this.error=data.error;
		return false;
	    }
	    if (data) {
		this.items=data.items;
		this.revisions=[];
		for (var i=0;i<data.revisions.length;i++) {
		    if (! data.revisions[i].error) {
		       this.revisions.push(this.context.getDocument({latest:false,data:data.revisions[i]})); 	
		    }
		}
	    }
	} 
    }
};

Fdl.DocumentHistory.prototype = {
	id: null,
	error:null,
        /** list of history items @type array */
	items:null,
        toString: function() {
           return 'Fdl.DocumentHistory';
        },
        /**
         * get history items objects
         * an item is compose of following fields
         * @param {Number} id (optional)the position an item, if no specify return all items
         * <ul><li><b>comment : </b> the comment</li>
         * <li><b>code : </b> specific key to describe action like 'CREATE', 'MODIFY'</li>
         * <li><b>date : </b> date of action</li>
         * <li><b>id : </b> document identificator</li>
         * <li><b>initid : </b>initial document identifier (always the same)</li>
         * <li><b>level : </b> message level <ul><li>1 : notice</li><li>2 : info</li><li>4 : message</li><li>8 : warning</li><li>16 : error</li></ul></li>
         * <li><b>userId : </b> the system identifier of the user</li>
         * <li><b>userName : </b>the user name</li>
         * </ul>
         * @return array of history items
         */
	getItems: function(id) {
            if (! id)   return this.items;
	    var it=[];
	    for (var i=0;i<this.items.length;i++) {
	      if (this.items[i].id==id) it.push(this.items[i]);
	    }
	    return it;
         },
        /**
         * get all document revised
         * @return array of Fdl.Document
         */
	getRevisions: function() {
            return this.revisions;
         }
};
