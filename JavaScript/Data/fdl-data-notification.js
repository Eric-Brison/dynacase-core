
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Fdl.Notifier 
 * this class can be use to notify javascript object of freedom activity
 * <pre><code>
var C=new Fdl.Context({url:'http://my.freedom/'});
var d=C.getDocument({id:9});
d.fireEvent=function (code,args) {
      alert("fireevent"+code+args);
}

var n=C.getNotifier();
n.loop(); 
var x=n.subscribe(d);
 // or var x=n.subscribe({object:d,callback:'fireEvent'});
 * </core></pre>
 * @namespace Fdl.Notifier
 * @param {Object} config
 * @cfg {Fdl.Context} context the connection {@link Fdl.Context context}
 */
Fdl.Notifier = function (config) {
    if (config) {
	this.context=config.context;
    }
};
Fdl.Notifier.prototype = {
    lastRetrieveTime:null,
    nextDelay:30,
   /**
     * Connection context
     * @type Fdl.Context
     * @property
     */
    context:null,
   /**
     * Array of object's subscriptions
     * @type Array
     * @property
     */
    subscribeItems:[]
    
};
Fdl.Notifier.prototype.toString= function() {
      return 'Fdl.Notifier';
};
/**
 * subscribe an object to be prevent when notification
 * @param {object} config
 * <ul><li><b>object : </b> the javascript object </li>
 * <li><b>callback</b> the method to call when detect new notification (fireEvent by default)</li>
 * </ul>
 * The object to prevent when notification is detected. By default call the fireEvent method of object with two arguments : the event name end the event argument
 * <pre><code>
var C=new Fdl.Context({url:'http://my.freedom/'});
var d=C.getDocument({id:9});
d.fireEvent=function (code,args) {
      alert("fireevent"+code+args);
}

var n=C.getNotifier();
var x=n.subscribe(d);
 // or var x=n.subscribe({object:d,callback:'fireEvent'});
 * </core></pre>
 * @return {Boolean}
 */
Fdl.Notifier.prototype.subscribe = function(obj) {
    var sobj=obj;
    var scall='fireEvent';
       
    if (obj && obj.object) {
	sobj=obj.object;
	if (! obj.callback) {
	    if (obj.object.fireEvent) obj.callback='fireEvent';
	    else return false;
	} else {
	    scall=obj.callback;
	}
	for (var i=0;i<this.subscribeItems.length;i++) {
	    if (this.subscribeItems[i].object==obj.object) return false;
	}	
	this.subscribeItems.push({object:sobj,callback:scall});
	return true;
    }
    return null;
};
/**
 * unsubscribe an object to be prevent when notification
 * @param {object} sobj object to unsubscribe
 * <pre><code>
var C=new Fdl.Context({url:'http://my.freedom/'});
var d=C.getDocument({id:9});
var n=C.getNotifier();
var x=n.unsubscribe(d);
 * </core></pre>
 * @return {Boolean}
 */
Fdl.Notifier.prototype.unsubscribe = function(sobj) {
    if (sobj) {
	for (var i=0;i<this.subscribeItems.length;i++) {
	    if (this.subscribeItems[i].object==sobj) {
		if (this.subscribeItems.length==1) {
		    this.subscribeItems=[];
		} else {
		    if (i < (this.subscribeItems.length-1)) this.subscribeItems[i]=this.subscribeItems[this.subscribeItems.length-1];
		    this.subscribeItems.pop();
		}
		return true;
	    }
	}
    }
    return false;
};


/**
 * retrieve new {@link Fdl.Notifier.Notification notifications} since last retrievement
 * 
 * @return {Array} the Fdl.Notifier.Notification array 
 */
Fdl.Notifier.prototype.retreive = function(config) {
    if (this.context) {
	var r=this.context.retrieveData({date:this.lastRetrieveTime},null,false,'notifier.php');
	if (r.error) {
	    this.context.setErrorMessage(r.error);
	    return null;
	} else {
	    if (r.date) this.lastRetrieveTime=r.date;
	    if (r.delay) this.nextDelay=r.delay;
	    if (r.notifications) return r.notifications;
	}
    }
    return [];
};

/**
 * launch notify polling loop
 * the delay between two polls is done by the server
 * <pre><code>
var C=new Fdl.Context({url:'http://my.freedom/'});
var n=C.getNotifier();
n.loop();
 * </core></pre>
 * @return {Boolean} true if loop is launched
 */
Fdl.Notifier.prototype.loop = function(config) {
	if (this.context) {
		if ((! this.activated) || (config && config.auto)) {
			var r=this.retreive();
			if (r && r.length) {
				for (var i=0;i<r.length;i++) {
					for (var j=0;j<this.subscribeItems.length;j++) {
						try {
							this.subscribeItems[j].object[this.subscribeItems[j].callback](r[i].code,r[i]);
						} catch(exception) {
							//alert(exception);
						}
					}
				}
			}
			var me=this;
			this.activated=true;
			window.setTimeout(function () {me.loop({auto:true});},this.nextDelay*1000);
			return true;
		}
	}
	return false;
};


/**
 * @class Fdl.Notifier.Notification
 * @namespace Fdl.Notifier
 * @param {Object} config
 */
Fdl.Notifier.Notification = function (config) {
    
};
Fdl.Notifier.Notification.prototype = {
   /**
     * Event code
     * the system codes are  
     * <ul>
<li><b>create : </b> When a new document is created</li>
<li><b>changed : </b> when document is modified</li>
<li><b>unlock : </b> when document is unlocked</li>
<li><b>lock : </b>when document is locked</li>
<li><b>delete : </b>when document is deleted</li>
<li><b>revive : </b>when document is undeleted</li>
<li><b>clearcontent : </b>when the containt of folder is cleared</li>
<li><b>addcontent : </b>when document is inserted in a folder</li>
<li><b>delcontent : </b>when document is unlinked from a folder</li>
<li><b>allocate : </b>when new allocation is set</li>
<li><b>unallocate : </b>when allocation is removed</li>
<li><b>attachtimer : </b>when a new timer is attached</li>
<li><b>unattachtimer : </b>when a timer is unattached</li>
<li><b>revision : </b>when document is revised</li>
     *</ul>
     * @type String
     * @property
     */
    code:null,
   /**
     * Argument of the notification
     * @type Object
     * @property
     */
    arg:null,
   /**
     * Date of the notification
     * @type Date
     * @property
     */
    date:null,
   /**
     * Identificator of notified document 
     * @type Number
     * @property
     */
    id:null,
    /**
     * Initial identificator of notified document 
     * @type Number
     * @property
     */
    initid:null,
    /**
     * Title of notified document 
     * @type Date
     * @property
     */
    title:null,
   /**
     * Level of notification
     * @type Number
     * @property
     */
    level:null,
   /**
     * User identificator which produce notification
     * @type Number
     * @property
     */
    uid:null,
   /**
     * User name which produce notification
     * @type String
     * @property
     */
    uname:null    
};
