
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

/**
 * @class Fdl.DocumentFilter describe the object used to define a document
 *        filter this filter can be use in Fdl.Collection::getContent() method
 *        and in Fdl.SearchDocument object property
 *        <p>
 *        I want all folders document :
 *        </p>
 * 
 * <pre><code>
  var f = new Fdl.DocumentFilter( {
  	family : 'DIR'
  });
  var s = C.getSearchDocument( {
  	filter : f
  });
  var result = s.search();
 * </code></pre>
 * 
 * 
 * <p>
 * I want all folders document included in folder 9:
 * </p>
 * 
 * <pre><code>
  var f = new Fdl.DocumentFilter( {
  	family : 'DIR'
  });
  var nine = C.getDocument( {
  	id : 9
  });
  var result = nine.getContent( {
  	filter : f
  });
 * </code></pre>
 * 
 * <p>
 * I want all folders document where description include 'root':
 * </p>
 * 
 * <pre><code>
  var f = new Fdl.DocumentFilter( {
  	family : 'DIR',
  	criteria : [ {
  		operator : '&tilde;',
  		left : 'ba_desc',
  		right : 'root'
  	} ]
  });
  var s = C.getSearchDocument( {
  	filter : f
  });
  var result = s.search();
 * </code></pre>
 * 
 * <p>
 * I want all persons where locked by me or not locked :
 * </p>
 * 
 * <pre><code>
  var f = new Fdl.DocumentFilter( {
  	family : 'DIR',
  	criteria : [ {
  		or : [ {
  			operator : '=',
  			left : 'locked',
  			right : 0
  		}, {
  			operator : '=',
  			left : 'locked',
  			right : '::getSystemUserId()'
  		} ]
  	}, ]
  });
  var s = C.getSearchDocument( {
  	filter : f
  });
  var result = s.search();
 * </code></pre>
 * 
 * <p>
 * I want all folders with private profil :
 * </p>
 * 
 * <pre><code>
  var f = new Fdl.DocumentFilter( {
  	family : 'DIR strict',
  	criteria : [ {
  		or : [ {
  			operator : '=',
  			left : 'id',
  			right : ':@profid'
  		} ]
  	}, ]
  });
  var s = C.getSearchDocument( {
  	filter : f
  });
  var result = s.search();
 * </code></pre>
 * 
 * @namespace Fdl.Document
 * @cfg {String} mainSelector indicate filter scheme none or all
 * @cfg {Fdl.Collection} collection the collection reference
 * @cfg {Fdl.Filter} filter of the collection
 * @cfg {Fdl.Document} filterItems array of documents
 */
Fdl.DocumentFilter = function (config) {
    if (config) {
	for (var i in config) this[i]=config[i];
    }
};
Fdl.DocumentFilter.prototype = {
    /**
	 * family filterTo not include sub family (the default) use 'strict' after
	 * family name : 'USER strict' for example
	 * 
	 * @type String use the logical name of family.
	 */
    family:null,
    /**
	 * all detailed criteria
	 * 
	 * @type Object
	 */
    criteria:null,
    /**
	 * sql where part. Operator must be internal postgresql operators
	 * 
	 * @type String
	 */
    sql:null,
	toString: function(){
		return 'Fdl.DocumentFilter';
	},
	getCriteria: function (clone) {
		if (clone) return Fdl.cloneObject(this.criteria);
		else return this.criteria;
	},
	/**
	 * linearize criteria
	 * @return array like [{condition},{logical operator}, {condition},[logical operator}...]
	 */
	linearize: function(ca,ol) {
		var l=[];
		if (!ol) ol='and';
		var lp=false;
		var rp=false;
		var top=(!c);
		var c;
		if (!ca) c=this.getCriteria(true); // clone array
		else c=Fdl.cloneObject(ca);

		if (!c) return l;
		for (var i=0;i<c.length;i++) { 
			if (c[i].operator) {
				if (i >0) c[i].ol=ol;
				c[i].lp=lp;
				c[i].rp=rp;
				l.push(c[i]);
			} else if (c[i].or || c[i].and || c[i].length) {
				var ln=[];
				if (c[i].or)  ln=this.linearize(c[i].or,'or');
				else if (c[i].and) ln=this.linearize(c[i].and,'and');
				else if (c[i].length) ln=this.linearize(c[i],'and');
				else console.log("ERROR ADD",c[i]);
				
				if (ln.length> 0) {
					if (!ln[0].lp) {
					ln[0].lp=true;
					ln[ln.length-1].rp=true;
					}
					ln[0].ol=ol;
					for (var j=0;j<ln.length;j++) {
							l.push(ln[j]);
					}
				}				
			} 
		}
		
		if (top && l.length >0) l[0].ol=null;
		return l;
	},
	/**
	 * 
	 * @param {array} la array of linearized criteria
	 * @return {object} criteria object
	 */
	unLinearize: function(la,level) {
		if (!level) level=0;
		if (level > 4) {
			console.log('recursion detect');
			return;
		}
		var l=Fdl.cloneObject(la);
		var c=[];
		if (l.length==0) return c;
		if (l.length==1) {
			l[0].ol=null;
			l[0].lp=null;
			l[0].rp=null;
			return [l[0]];
		}
		var ol=l[1].ol;
		var onlyand=true;
		for (var i=1;i<l.length;i++) {
			if (l[i].ol != 'and') onlyand=false;
		}
		if (onlyand) {
			for (var i=0;i<l.length;i++) {
				l[i].ol=null;
				l[i].lp=null; //ignore parenthesis
				l[i].rp=null;
				if (l[i].ul) c.push(l[i].ul);
				else c.push(l[i]);
			}
		} else {
			var onlyor=true;
			for (var i=1;i<l.length;i++) {
				if (l[i].ol != 'or') onlyor=false;
			}
			if (onlyor) {
				var cor={or:[]};
				for (var i=0;i<l.length;i++) {
					l[i].ol=null;
					l[i].lp=null; //ignore parenthesis
					l[i].rp=null;
					if (l[i].ul) cor.or.push(l[i].ul);
					else cor.or.push(l[i]);
				}
				c.push(cor);
			} else {
				// mixing and or
				var pa=0;
				var bp=false;
				for (var i=1;i<l.length;i++) {
					if ((! bp) && (l[i].ol == 'and') && (!l[i].lp) && (!l[i].rp) ) {
						// begin parenthesis
						pa=i-1;
						bp=true;
						l[i].lp=false;
						l[i].rp=false;
						if (i==l.length-1) {
							l[pa].lp=true;							
							l[i].rp=true;	
						}
					} else if ( bp && (!l[i].lp) && (!l[i].rp)&& (l[i].ol == 'and') && (i<(l.length-1))) {
						l[i].lp=false;							
						l[i].rp=false;	
					} else if ( bp && (!l[i].lp) && (!l[i].rp) && (l[i].ol == 'and') && (i==l.length-1)) {
						l[pa].lp=true;							
						l[i].rp=true;	
					} else if ( bp && ((l[i].ol != 'and') || (l[i].lp)|| (l[i].rp))) {
						l[i-1].rp=true;							
						l[pa].lp=true;
						bp=false;
					} 
				}
				//console.log('mix orand',n2s(l));
				// search or
				var cp=0;
				var lu;
				var wu;
				var wi=0;
				var nl=[];
				for (var i=0;i<l.length;i++) {
					//console.log(l[i],cp);
					if ((l[i].lp) ) {
						wi=i;
						
					} 
					if ((l[i].rp)) {
						wu=l.slice(wi,i+1);
						lu=this.unLinearize(wu,level+1);
						//console.log('wu',wu,'lu',lu);
						nl.push({ol:l[wi].ol,ul:lu});
					}
					if ((!l[i].lp) && (!l[i].rp) && (cp==0)) {
						nl.push(l[i]);
					}
					if (l[i].lp) cp++;
					if (l[i].rp) cp--;					
				}
				//console.log('second pass',nl);
				return this.unLinearize(nl,level+1);
			}
		}
		return c;
	},
	/**
	 * explicit and operators 
	 * @return {object} self
	 */
	normalize: function() {
		if (this.criteria) {
			for (var j=0;j<this.criteria.length;j++) {
				if (this.criteria[j].or || this.criteria[j].and) {
					var ic=this.criteria[j].or;
					if (!ic) ic=this.criteria[j].and;
					for (var i=0;i<ic.length;i++) {
						if (ic[i].length > 0) {
							ic[i]={and:ic[i]};
						}
					}
				}
				
			}
		}
		return this;
	}
};


// ========== NOT PROTOTYPE (DEPRECATED)================

// @deprecated
Fdl.getHomeFolder = function() {
  var u=Fdl.getUser();
  if (u && u.id) {
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
  if (u && u.id) {
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
  if (u && u.id) {
    var idhome='FLDOFFLINE_'+u.id;
    var h=new Fdl.Collection({id:idhome});
    if (h.isAlive()) {
      Fdl._offlineFolder=h;
      return h;
    }
  }
  return null;
};
