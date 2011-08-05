
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Fdl.User
 * <pre><code>
var C=new Fdl.Context({url:'http://my.freedom/'});
var user;
if (! C.isAuthenticated()) {
  user=C.setAuthentification({login:'admin',password:'anakeen'});
  if (!user)  alert('error authent:'+C.getLastErrorMessage());    
} else {
  user=C.getUser();
}
if (user) {
  alert(user.getDisplayName());
  var duid=u.getUserDocumentIdentificator();
    if (duid) {
      var d=C.getDocument({id:duid});
      if (d && d.isAlive()) {
          var phone=d.getValue("us_phone");
	  alert('Phone:'+phone);
      }
    }
}
 * </core></pre>
 * @param {Object} config
 */

Fdl.User = function(config){
	if (config && config.context) this.context=config.context;

	if (config && config.data) {
		this.affect(config.data); 
	} else {
		var  data=this.context.retrieveData({app:'DATA',action:'USER'});   
		if (data) this.affect(data); else return false;
	}
};


Fdl.User.prototype = {
		/** system user identificator 
        @type Number */
		id:null,
		/** system user login 
        @type String */
		login:null,
		/** first name of user
        @type String */
		firstname:null,
		/** last name of user
        @type String */
		lastname:null,
		/** email of user
        @type String */
		mail:null,
		/** locale (language) of user
        @type String */
		mail:null,
		info:new Object(),
		context:Fdl,
		affect: function (data) {
	if (data) {
		if (! data.error) {	  
			this._data=data;
			if (data.info) this.completeData(data.info);
			return true;
		} else {
			this.context.setErrorMessage(data.error);
		}
	}
	return false;
},
toString: function() {
	return 'Fdl.User';
}
};

Fdl.User.prototype.completeData = function(data) {
	if (data) {
		for (var i in data) this.info[i]=data[i];
		this.id=data.id;
		this.login=data.login;
		this.firstname=data.firstname;
		this.lastname=data.lastname;
		this.locale=data.locale;
	}
};

/** get display name : first and last name
 * @return {String}
 */
Fdl.User.prototype.getDisplayName = function() {
	if (this.info) {
		return this.info.firstname+' '+this.info.lastname;
	}
	return null;
};

/** get all properties of user
 * @return {Object}
 */
Fdl.User.prototype.getInfo = function() {    
	if (this.info) {
		return this.info;
	}
	return null;
};

/** get all format of different locale
 * dateFormat
	"%m/%d/%Y"
	
   dateTimeFormat
	"%m/%d/%Y %H:%M"
	
   label
	"English"
	
   locale
	"en"
	
   timeFormat
	"%H:%M:%S"
 * @return {Object}
 */
Fdl.User.prototype.getLocaleFormat = function() {    
	if (this._data.localeFormat) {
		return this._data.localeFormat;
	}
	return null;
};

/** get document identificator associated with the user
 * @return {Number}
 */
Fdl.User.prototype.getUserDocumentIdentificator = function() {    
	if (this.info) {
		return parseInt(this.info.fid);
	}
	return null;
};