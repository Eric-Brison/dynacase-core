
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

include_js("FDC/Layout/inserthtml.js");
var isNetscape = navigator.appName=="Netscape";
// auxilarry window to select choice
var wichoose= false;

var NB_FCKEDITORS=0;
var FCKEDITORS=new Array();
// current instance
var colorPick ;
//if (window.pickColor)  colorPick = new ColorPicker();
if (window.initDHTMLAPI) initDHTMLAPI();
var THESTATUS=new Array();
var MDOCSCRUCT=new Array();

document.isCancelled=false;
document.isSubmitted=false;
function createColorPicker() {  
  if (window.pickColor)  colorPick = new ColorPicker();
}
function scruteadocs() { 
  var newTa = document.getElementsByTagName('iframe');
  for (var i=0; i < newTa.length; i++){ 
    ofr=newTa[i];
    updateOfr(ofr,true);
  }
}
Array.prototype.getUnique = function () {
  var o = new Object();
  var i, e;
  for (i = 0; e = this[i]; i++) {o[e] = 1;};
  var a = new Array();
  for (e in o) {a.push(e);};
  return a;
};

function scrutemdocs() { 
  var n,iid,tiid;
  var inpid,inptext,inpsel,inptitle;
  var itext,isel,ititle;
  var ti;
  var nid,ntitle,nval;
  
  if (MDOCSCRUCT.length > 0) {
    MDOCSCRUCT=MDOCSCRUCT.getUnique();
    for (var i=0; i < MDOCSCRUCT.length; i++) { 
      addmdocs(MDOCSCRUCT[i]);               
    }
    setTimeout('scrutemdocs()',1000); 
  }
  
}

function addmdocs(n) {    
  var n,iid,tiid;
  var inpid,inptext,inpsel,inptitle;
  var itext,isel,ititle;
  var ti;
  var nid,ntitle,nval;
      
  tiid=[];
  if (n.substr(n.length-1,1) == ']') {
      var postfix=n.substr(n.lastIndexOf('['));
      n=n.substr(0,n.lastIndexOf('['));
    if (isNetscape) tiid=document.getElementsByName('mdocid_work'+n+postfix);
    else  tiid = getInputsByName('mdocid_work'+n);
  } else {		
    tiid.push(document.getElementById('mdocid_work'+n.substr(1)));	
  }            
  for (var j=0; j < tiid.length; j++) { 
    inpid=tiid[j];
    if (inpid) {
      if (inpid.value != '') {
	iid=inpid.id;
	itext=iid.substr(11);
	ititle='ilink_'+itext;
	isel='mdocid_isel_'+itext;
	inptext=document.getElementById(itext);
	inptitle=document.getElementById(ititle);
	inpsel=document.getElementById(isel);	   
	if (inpsel && inptext) {
	  nid=inpid.value;
	  if (! inptitle) ntitle=nid;
	  else ntitle=inptitle.value;
	  // verify if no double
	  var find=false;
	  for (var k=0;k<inpsel.options.length;k++) {
	    if (inpsel.options[k].value==nid) find=true;
	  }
	  if (! find)  addinlist(inpsel,ntitle,nid,false);
	  else alert('[TEXT:item already set]');
	  inpid.value='';
	  inptitle.value='';
	  inptitle.focus();
	  transfertDocIdInputs(inpsel,inptext);

	}
      }
    }
  }
    
}
function addmdocsattrid(attrid,nid,ntitle) { 
  var isel;
  var inpsel,inptext;
  isel='mdocid_isel_'+attrid;

  inptext=document.getElementById(attrid);
  inpsel=document.getElementById(isel);
  if (inpsel && inptext) {
    find=false;
    for (var k=0;k<inpsel.options.length;k++) {
      if (inpsel.options[k].value==nid) find=true;
    }
    if (! find)  addinlist(inpsel,ntitle,nid,false);
    //else alert('[TEXT:item already set]');
    transfertDocIdInputs(inpsel,inptext);
  }  
}
function clearDocIdInputs(attrid,selid,th) {
  var inpsel=document.getElementById(selid);
  
  if (inpsel) {
    var itext=attrid;
    var inptext=document.getElementById(itext);
    if (inptext) {
      for (var k=0;k<inpsel.options.length;k++) {
	if (inpsel.options[k].selected) inpsel.remove(k--);
      }
      if (th) th.disabled=true;
      transfertDocIdInputs(inpsel,inptext);
    }
  }
}

function transfertDocIdInputs(inpsel,inptext) {
  var maxsize=6;
  var nval='';
  for (var k=0;k<inpsel.options.length;k++) {
    nval=nval+inpsel.options[k].value+"\n";
    inpsel.options[k].selected=false;
  }
  if (inpsel.options.length == 0) inpsel.size=1;
  else if (inpsel.options.length < maxsize) inpsel.size=inpsel.options.length;
  else inpsel.size=maxsize;

  inptext.value=nval.substr(0,nval.length - 1);
}
function disableClearDocIdInputs(attrid, inpsel) {
  var iinput=document.getElementById('ix_'+attrid);
  var needdisable=true;
  if (iinput) {
    for (var k=0;k<inpsel.options.length;k++) {
      if (inpsel.options[k].selected) needdisable=false;
    }
    iinput.disabled=needdisable;
  }
}


function updateEnumauto(o,idsel,idval) {
  var nv=o.value;
  var sel=document.getElementById(idsel);
  var ival=document.getElementById(idval);
  if (sel && ival) {
    var find=false;
    for (var k=0;k<sel.options.length;k++) {
      if (sel.options[k].value==nv) find=true;
    }
    if (! find)    addinlist(sel,ival.value,nv,false);
    transfertDocIdInputs(sel,o);
    sel.options[sel.options.length - 1].selected=true;
    ival.value='';
    ival.disabled=false;
    ival.focus();
    disableClearDocIdInputs(o.id,sel);
  }
}

function updateIfr(ifr) { 
  var ofr=document.getElementById(ifr);
  if (ofr) updateOfr(ofr,false);
}

function updateOfr(ofr,onlyclose) {    
  if (ofr.id.substr(0,4)=="ifr_") {
	  
      iid=ofr.id.substr(4);
      viid=document.getElementById(iid);
      if (viid) {
	nurl=ofr.src;
	if ((viid.value==' ') || (viid.value=='')) {
	  nurl='[IMG:1x1.gif]';
	  ofr.style.display='none';
	} else {
	  if (!onlyclose)  nurl='[CORE_STANDURL]&app=FDL&action=IMPCARD&zone=FDL:VIEWTHUMBCARD:T&id='+viid.value;
	}

	if (ofr.src.substr(-10) != nurl.substr(-10) ) {
	  ofr.src=nurl;	      
	  if (/1x1.gif/i.test(nurl)) {
	    ofr.style.display='';  
	    sdisplay='none';
	  } else {
	    sdisplay='';
	  }
	  nnode=ofr.nextSibling;
	  while (nnode && (nnode.nodeType != 1)) nnode = nnode.nextSibling; //case TEXT node
	  nnode.style.display=sdisplay;
	      
	}
      }
    }
  
}

setInterval('scruteadocs()',1000);

// search the row number of an element present in array
function getRowNumber(el, array_class) {
  var stack = new Array();
  var parent = null;
  var elmt = el;
  var found_tarray = false;

  var re = null;
  if( array_class != null ) {
    re = new RegExp("\\b"+array_class+"\\b");
  }

  // Store TR elmts up to TABLE.tarray
  while( elmt != null ) {
    parent = elmt.parentNode;
    if( parent == null ) {
      break;
    }
    if( parent.tagName == 'TABLE' ) {
      if( re == null ) {
	found_tarray = true;
	break;
      }
      if( parent.className.match(re) ) {
	found_tarray = true;
	break;
      } else {
	stack.splice(0, stack.length);
      }
    }
    if( parent.tagName == 'TR' ) {
      stack.push(parent);
    }
    elmt = parent;
  }

  if( ! found_tarray ) {
    return null;
  }

  // Take first TR elmt from stack
  var tr = null;
  var i = stack.length - 1;
  while( i >= 0 ) {
    if( stack[i].tagName == 'TR' ) {
      tr = stack[i];
      break;
    }
    i--;
  }
  if( tr == null ) {
    return null;
  }

  // Compute row indice by counting previous sibling TR elmts
  var nrow = 0;
  if( tr != null ) {
    tr=tr.previousSibling;
    while( tr != null && (tr.nodeType != 1 || tr.tagName == 'TR') ) {
      if( tr.nodeType == 1 ) {
	nrow++;
      }
      tr = tr.previousSibling;
    }
  }

  return nrow;
}

var enuminprogress=false;
function sendEnumChoice(event,docid,  choiceButton ,attrid, sorm,options) {


  var inp  = choiceButton.previousSibling;
  var index='';
  var attrid;
 
  var domindex=''; // needed to set values in arrays
  // search the input button in previous element
 
  var inid;

  if (enuminprogress) return;
  enuminprogress=true;  
  //  inid= choiceButton.id.substr(3);
  inp=document.getElementById(attrid);


  if ((! inp)||(inp==null)) {
    alert('[TEXT:enumerate input not found]'+':'+attrid);
  }

  if (inp.name.substr(inp.name.length-2,2) == '[]') {
    // it is an attribute in array
    attrid=inp.name.substr(1,inp.name.length-3);
    index=getRowNumber(choiceButton);
    domindex = inp.id.substring(attrid.length);    
  } else {
    attrid=inp.name.substr(1,inp.name.length-1);;
  }

  if (! options) options='';

  f =inp.form;
  // modify to initial action
  oldact = f.action;
  oldtar = f.target;
  f.action = '[CORE_STANDURL]&app=FDL&action=ENUM_CHOICE&docid='+docid+'&attrid='+attrid+'&sorm='+sorm+'&index='+index+'&domindex='+domindex+options;

  

  var xy= getAnchorWindowPosition(inp.id);

  if (isNaN(window.screenX)){
    xy.y+=15; // add supposed decoration height
    // add body left width for IE sometimes ...
    if (parent.ffolder)  xy.x += parent.ffolder.document.body.clientWidth;
    
  }

  wichoose = window.open('', 'wchoose', 'scrollbars=yes,resizable=yes,height=30,width=290,left='+xy.x+',top='+xy.y);
  wichoose.focus();

  wichoose.moveTo(xy.x, xy.y+10);
  f.target='wchoose';


  enableall();
  f.submit();
  restoreall();
  disableReadAttribute();
  // reset to initial action
  f.action=oldact;
  f.target=oldtar;

  enuminprogress=false;
}
function sendSpecialChoice(event,inpid,docid ,attrid,index) {
	  var inp  = inp=document.getElementById(inpid);
	  var attrid;
	 
	  var domindex=''; // needed to set values in arrays
	  // search the input button in previous element
	 
	  var inid;

	  if (enuminprogress) return;
	  enuminprogress=true;  


	  if ((! inp)||(inp==null)) {
		  inp=document.getElementById('T'+attrid);
		  if (!inp) {
	    alert('[TEXT:enumerate input not found]'+':'+attrid);
	    return;
		  } else {
			  var inps=inp.getElementsByTagName('input');
			  if (inps.length >0) f=inps[0].form;
			  domindex=0;
		  }
	  } else {

	  if (inp.name.substr(inp.name.length-1,1) == ']') {
	    // it is an attribute in array
	    domindex = inp.id.substring(attrid.length);    
	  }


	  f =inp.form;
	  }
	  // modify to initial action
	  oldact = f.action;
	  oldtar = f.target;
	  f.action = '[CORE_STANDURL]&app=FDL&action=SPECIALHELP&docid='+docid+'&attrid='+attrid+'&index='+index+'&domindex='+domindex;

	  var xy= getAnchorWindowPosition(inp.id);

	  var wname='helpi'+inp.id;
	 // subwindow(30,290, wname, 'about:blank');
	  subwindow(30,290, wname, '?app=CORE&action=BLANK');
	  
	  f.target=wname;


	  enableall();
	  f.submit();
	  restoreall();
	  disableReadAttribute();
	  // reset to initial action
	  f.action=oldact;
	  f.target=oldtar;

	  enuminprogress=false;
	}

/**
 * add a row in form array attribute
 * @param config
 * @return Boolean true if succeed
 */
function addTableRow(config) {
	if (config) {
		var ntr;
		var docid, doctitle,docicon;
		var i, j;
		var texbody=null;
		var tnewid=false;
		for (i in config) {
			if (! texbody) {
				var o=document.getElementById(i.toLowerCase()+'___1x_');
				if (o) {
					var p=o.parentNode;
					while (p && p.tagName != 'TR') p=p.parentNode;
					if (p) {
						tnewid=p.id;
					      var tbodies=p.parentNode.parentNode.getElementsByTagName('tbody');
					      if (tbodies.length==1) texbody=tbodies[0];
					}
				}
			}
		}
		
		//var texbody = document.getElementById('tbodyid_t_applicable_document');
		if (!texbody) {
			alert('array of documents not found');
			return;
		}
		// serach unique attribut
		var uniqattr=false,uniqvalue=false;
		for (var i in config) {
			if (typeof config[i] == 'object') {
				if (config[i].id) {
					if (config[i].unique) {
						uniqattr=i;
						uniqvalue=config[i].id;
					}
				}
			}
		}
		var allexpinputs = texbody.getElementsByTagName('input');
		if (uniqattr) {
		for (j = 0; j < allexpinputs.length; j++) {
			if (_matchName(allexpinputs[j].name,'_'+uniqattr)) {
				if (parseInt(allexpinputs[j].value) > 0) {
					// test unique
					if (allexpinputs[j].value == uniqvalue) return false;
				} else {
					var p=allexpinputs[j].parentNode;
					while (p && p.tagName != 'TR') p=p.parentNode;
					if (p) {
						deltr(p);
						j--;
					}
				}
			}
		}
		}

		ntr = addtr(tnewid, texbody.id);
		 var linp = ntr.getElementsByTagName('input');
		 var lsel = ntr.getElementsByTagName('select');
		 var ltxt = ntr.getElementsByTagName('textarea');
		 // it is just to concat node list (beautifull)
		 linp=_concat(linp,lsel);
		 linp=_concat(linp,ltxt);
		for (var j=0;j<linp.length;j++) {
			for (var i in config) {
				if (_matchName(linp[j].name,'_'+i)) {
					if (typeof config[i] == 'object') {
						if (config[i].id) {
							linp[j].value = config[i].id;
							if (config[i].title) {
							var t=document.getElementById('ilink_'+linp[j].id);
							if (t) t.value=config[i].title;
							}
							if (config[i].url) {
								var t=document.getElementById('img_'+linp[j].id);
								if (t) t.src=config[i].url;
							}
						}
					} else {
						setIValue(linp[j], config[i]);
						sendEvent(linp[j],"change");
					}
				}
			}
		}
	}
	return true;
}

/**
 * set value in document form (cannot be applied in arrays and multivalues)
 * @param config
 * @return Boolean true if succeed
 */
function setFormValue(config) {
	for (var i in config) {
		var inp=document.getElementById(i.toLowerCase());
		if (inp && (inp.name == '_'+i)) {
			if (typeof config[i] == 'object') {
				if (config[i].id) {
					inp.value = config[i].id;	
					if (config[i].title) {
					var t=document.getElementById('ilink_'+inp.id);
					if (t) t.value=config[i].title;
					}
					if (config[i].url) {
						var t=document.getElementById('img_'+inp.id);
						if (t) t.src=config[i].url;
					}
				}
			} else {
				setIValue(inp, config[i]);
				sendEvent(inp,"change");
			}
			return true;
		}
	}
	return false;
}
/**
 * set value in document form 
 * @param attrid an id attribute
 * @return Any the value (null if not found)
 */
function getFormValue(attrid) {
		var inp=document.getElementById(attrid.toLowerCase());
		if (inp && inp.name == '_'+attrid) {
			return getIValue(inp);
		} else {
			 var linp = document.getElementsByTagName('input');
			 var r=[];
			 for (var j=0;j<linp.length;j++) {
					if (_matchName(linp[j].name,'_'+attrid)) {
						if (linp[j].name.substr(-6,5) != '__1x_') {
							r.push(getIValue(linp[j]));
						}
					}
			 }
			 return r;
		}
		
	
	return null;
}
function _concat(a,b) {
	return [].slice.call(a, 0,a.length).concat([].slice.call(b, 0,b.length));
}

function _inarray(e, t) {
	for ( var i = 0; i < t.length; i++) {
		if (t[i] == e) return true;
	}
	return false;
}
function _matchName(s1,s2){
	var s3= s1.substr(0,s1.lastIndexOf("["));
	return (s2==s3);
}

function enableall() {

  with (document.getElementById('fedit')) {
       for (i=0; i< length; i++) {	
           elements[i].oridisabled=elements[i].disabled;
           elements[i].disabled=false;
       }
  }
}
function restoreall() {

  with (document.getElementById('fedit')) {
       for (i=0; i< length; i++) {	
           elements[i].disabled=elements[i].oridisabled;
       }
  }
}

// tranfert value from s to d
function transfertValue(s,d) {
  var sob=document.getElementById(s);
  var dob=document.getElementById(d);
  if (sob && dob) {
    dob.value=sob.value;
  }
}
function resizeInputFields() {
  var w, newsize;
  with (document.getElementById('fedit')) {
       for (i=0; i< length; i++) { 
         if (elements[i].className == 'autoresize') {
	   w=getObjectWidth(elements[i].parentNode);
	   if (w > 45) {
	     newsize = (w - 45) / 9;
	     if (newsize < 10) newsize=10; //min size is 10 characters
	     if (elements[i].type == 'text')
	       elements[i].size=newsize;
	     if (elements[i].type == 'textarea')
	       elements[i].cols=newsize;
	   }
	 }
       }
  }
}

// close auxillary window if open
function closechoose() {

    if (wichoose) wichoose.close();
}

function canmodify(withoutalert) {
    var err='';
    var v;
    for (var i=0; i< attrNid.length; i++) {
      e=document.getElementById(attrNid[i]);
      if (!e) e=document.getElementById('_'+attrNid[i]);
	if (e) {
	  v=getIValue(e);
	  if (v === false) {
	    ta = document.getElementsByName('_'+attrNid[i]+'[]');
	    if (ta.length == 0)	err += ' - '+attrNtitle[i]+'\n';
	    for (var j=0; j< ta.length; j++) {
	      v=getIValue(ta[j]);
	      if ((v === '')||(v === ' ')) err +=  ' - '+attrNtitle[i]+'/'+(j+1)+'\n';
	    }
	  } else {
	    if ((v === '')||(v === ' ')) err +=  ' - '+attrNtitle[i]+'\n';
	  }
        } else {
	  // search in multiple values
	  v=getInputValues(attrNid[i]);
	  
	  if ((v!==false) && ((v === '')||(v === ' '))) err +=  ' - '+attrNtitle[i]+'\n';
	}
    }
    if (err != '') {
      if (! withoutalert) displayWarningMsg('[TEXT:these needed attributes are empty]\n'+err);
	    return false;
    }
    return true;
}

// to define which attributes must be disabled
var tain= new Array();
var taout= new Array();
[BLOCK RATTR]
tain[[jska]]=[jstain];
taout[[jska]]=[jstaout];
[ENDBLOCK RATTR]

function getInputValue(id,index) {
  if (!index) index=0;
  var o=document.getElementById(id);
  if (o) {
    return o.value;
  } else {
	if (index) {
		o=document.getElementById(id+'_'+index)
		if (o)return o.value;
	}
    le = document.getElementsByName('_'+id+'[]');
    //if (isNetscape) le = document.getElementsByName('_'+id+'[]');
    //else le = getInputsByName('_'+id);
    if ((le.length - 1) >= index) {
      return le[index].value;
    }    
  }
  return '';
}

// return values for input multiples 
function getInputValues(n) {
 var v='';
 var ta;
  
 ta = getInputsByName('_'+n);
 if (ta.length==0) ta = document.getElementsByName('_'+n);
 if (ta.length==0) return false;
  for (var j=0; j< ta.length; j++) {
    switch (ta[j].type) {
    case 'radio':
      if (ta[j].checked) v=ta[j].value;
      break;
    case 'checkbox':
      if (ta[j].checked) v=ta[j].value;
      break;
    }

  }
  return v;
}
function getInputLocked() {
  var tlock=new Array();
  if (tain) {
  for (var c=0; c< tain.length; c++) {
    ndis = true;
    for (var i=0; i< tain[c].length; i++) {
      vin = getInputValue(tain[c][i]);
      if ((vin == '') || (vin == ' ')) ndis = false;
    }
    if (ndis) {
      // the attribute can lock others

      tlock=tlock.concat(taout[c]);
    }
  }
  }
 
  return (tlock);
}

function getInputsByName(n,node) {
	if (! node) node=document;
  var ti= node.getElementsByTagName("input");    
  var t = new Array();
  var ni;
  var pos;
	
  for (var i=0; i< ti.length; i++) { 
    pos=ti[i].name.indexOf('[');
    if (pos==-1) ni=ti[i].name;
    else ni=ti[i].name.substr(0,pos);
    if ((ni == n) && (ti[i].name.substr(ti[i].name.length-7,7) != '[__1x_]')) {	
     
      t.push(ti[i]);
    }
  }

  if (t.length == 0) { 
    // try with select
    ti= node.getElementsByTagName("select");
    
    for (var i=0; i< ti.length; i++) {       
      pos=ti[i].name.indexOf('[');
      if (pos==-1) ni=ti[i].name;
      else ni=ti[i].name.substr(0,pos);
      if ((ni == n) && (ti[i].name.substr(ti[i].name.length-7,7) != '[__1x_]')) {		
	t.push(ti[i]);
      }
    }      
  }  

  if (t.length == 0) { 
    // try with select
    ti= node.getElementsByTagName("textarea");
    
    for (var i=0; i< ti.length; i++) {       
      pos=ti[i].name.indexOf('[');
      if (pos==-1) ni=ti[i].name;
      else ni=ti[i].name.substr(0,pos);
      if ((ni == n) && (ti[i].name.substr(ti[i].name.length-7,7) != '[__1x_]')) {		
	t.push(ti[i]);
      }
    }      
  }
  
  return t;
}

function getIValue(i) {
  if (i) {
    if (i.tagName == "TEXTAREA") return i.value;
    if (i.tagName == "INPUT") {
      if ((i.type=='radio')||(i.type=='checkbox')) return i.checked;      
      return i.value;
    }
    if (i.tagName == "SELECT") {
      if (i.selectedIndex >= 0)   return i.options[i.selectedIndex].value;
      else return '';
    } 
  }
  return false;
}
function setIValue(i,v) {
  if (i) {
   
    if (i.tagName == "INPUT") {
      if ((i.type=='radio')||(i.type=='checkbox')) {
	i.checked=v;
	if (v && (i.type=='radio')) changeCheckClasses(i,false);
      }
      else i.value=v;
    }
    else if (i.tagName == "TEXTAREA")  i.value=v;
    else  if (i.tagName == "SELECT") {
      for (var k=0;k<i.options.length;k++) {
	if (i.options[k].value == v) i.selectedIndex=k;
      }
    }
  }
}

function isInputLocked(id) {
  var tlock=new Array();
  for (var c=0; c< tain.length; c++) {
    ndis = true;
    for (var i=0; i< tain[c].length; i++) {
      vin = getInputValue(tain[c][i]);
      if ((vin == '') || (vin == ' ')) ndis = false;
    }
    if (ndis) {
      // the attribute can lock others
      
      for (var i=0; i< taout[c].length; i++) {
	//	alert(tain[c][i] + '/' + id);
	if (taout[c][i] == id) return true;
      }
    }
  }
  return false;;
}


function disableReadAttribute() {
    
  var ndis = true;
  var i;
  var vin;
  var lin,aid;
  var inx,inc,ind,inb; // input button
  if (tain) {
    for (var c=0; c< tain.length; c++) {
      ndis = true;
      for (var i=0; i< tain[c].length; i++) {
	vin = getInputValue(tain[c][i]);

	if ((vin == '') || (vin == ' ')) ndis = false;
      
      }

      for (var i=0; i< taout[c].length; i++) {
	if (document.getElementById(taout[c][i])) {
	    if (document.getElementById(taout[c][i]).type != 'hidden') {
		if (document.getElementById(taout[c][i]).getAttribute('readonly') != '1')  document.getElementById(taout[c][i]).disabled=ndis;
	      inc=document.getElementById('ic_'+taout[c][i]);
	      inx=document.getElementById('ix_'+taout[c][i]);
	      ind=document.getElementById('id_'+taout[c][i]);
	      if (inc) inc.disabled=ndis;
	      if (ind) ind.disabled=ndis;	 
	      if (ndis) {
		// document.getElementById(taout[c][i]).style.backgroundColor='[CORE_BGCOLORALTERN]';
		//if (inc)  inc.style.backgroundColor='[CORE_BGCOLORALTERN]';	      	    
	      } else {
	    
		if (inc) inc.style.backgroundColor='';
		//if (document.getElementById(taout[c][i]).style.backgroundColor == '[CORE_BGCOLORALTERN]')
		document.getElementById(taout[c][i]).style.backgroundColor == '';
	      }
	    } else {
	      // search radio
	    
	      var rx=document.getElementById(taout[c][i]+'0');
	      if (rx && (rx.type=='radio')) {
		var ir=1;
		while (rx) {
		  rx.disabled=ndis;
		  rx=document.getElementById(taout[c][i]+ir);
		  ir++;
		}
	      }
	    }
	  
	} else {
	  // search in arrays
	 
		  lin = getInputsByName('_'+taout[c][i]);
	 
		  var kj;
	  for (var j=0; j< lin.length; j++) {
	    ndis=true;
	    for (var k=0; k< tain[c].length; k++) {
	    	var linname=lin[j].name;
	    	if (linname) kj=linname.substring(linname.indexOf('[')+1,linname.indexOf(']'));
	    	if (!kj) kj=j;
	      vin = getInputValue(tain[c][k],kj);
	      if ((vin == '') || (vin == ' ')) ndis = false;
	    
	    }
	    //	  alert(tain[c].toString()+'['+j+']'+ndis);
	    if (lin[j].type != 'hidden') {
	      aid=lin[j].id;
	      lin[j].disabled=ndis;
	      inc=document.getElementById('ic_'+aid);
	      ind=document.getElementById('ic_'+aid);
	      if (inc) inc.disabled=ndis;
	      if (ind) ind.disabled=ndis;
	      if (lin[j].type=='checkbox') { // for bool checkbox
		if (aid) aid=aid.substring(0,aid.length -1);
		inb=document.getElementById(aid);
		if (inb && (inb.type='checkbox')) inb.disabled=ndis;
	      }
	      
	      //lin[j].style.backgroundColor=(ndis)?'[CORE_BGCOLORALTERN]':'';		
	    } else {
	      aid=lin[j].id;
	      // search radio
	    
	      var rx=document.getElementById(aid+'0');
	      if (rx && (rx.type=='radio')) {
		var ir=1;
		while (rx) {
		  rx.disabled=ndis;
		  rx=document.getElementById(aid+ir);
		  ir++;
		}
	      }
	    }
	  }	
	}
      }
    }
  } 
}

function editOnLoad() {
    createColorPicker();
    resizeInputFields();
    disableReadAttribute();
}

function clearEmptyEnum(ikey, ival) {
  var key=document.getElementById(ikey);
  var val=document.getElementById(ival);

  if (key && val) {
    var vkey=trim(key.value);
    if (vkey=='') {
      if (trim(val.value)!='') val.value='';
    }
  }
}

function clearInputs(tinput, idx,attrid) {
  var iinput;
  var err='';
 
  for (var i=0; i< tinput.length; i++) {
      if (idx) iinput=tinput[i]+'_'+idx;
      else iinput=tinput[i];
   
    if (document.getElementById(iinput)) {
      if (! isInputLocked(iinput)) {	
	document.getElementById(iinput).value=' ';
	//	document.getElementById(iinput).style.backgroundColor='[CORE_BGCOLORHIGH]';
	
      } else {
	err = err + "\n" + iinput;
      }
    } else {
      if (! document.getElementById(iinput+'0'))   alert('[TEXT:Attribute not found]'+' : '+iinput);
    }
  }
  disableReadAttribute();

  if (err != '')  alert('[TEXT:NOT Clear]'+err);
  if (attrid && document.getElementById(attrid)) document.getElementById(attrid).focus();

}
function addEnum(th,cible,docid,attrid,key) {
  if (cible) {
    if (key.style.display=='none') {
      key.style.display='';
      key.focus();
      if (th) {
	th.value='>';
	th.title='[TEXT:Add new entry]';
      }
    } else {
      if (trim(key.value)=='') {
	alert('[TEXT:Item cannot be empty]');
      } else {
	var corestandurl=window.location.pathname+'?sole=Y';      
	var url=corestandurl+'&app=FDL&action=ADDENUMITEM&docid='+docid+'&aid='+attrid+'&key='+key.value;
        enableSynchro();
	requestUrlSend(cible.parentNode,url);
	disableSynchro();
	disableReadAttribute();
      }
    }
  }
}


function unselectInput(id) {
  var sel=document.getElementById(id);
  if (sel) {
    for (var i=0; i< sel.options.length; i++) {
      sel.options[i].selected=false;
    }
  }
  sel.options[sel.options.length-1].selected=true;
}
function autoUnlock(docid) {
  var r;
  var corestandurl=window.location.pathname+'?sole=Y';
  // branch for native XMLHttpRequest object
  if (window.XMLHttpRequest) {
    r = new XMLHttpRequest(); 
  } else if (window.ActiveXObject) {
    // branch for IE/Windows ActiveX version     
    r = new ActiveXObject("Microsoft.XMLHTTP");
  }
  if (r) {     
    r.open("GET", corestandurl+'&app=FDL&action=UNLOCKFILE&auto=Y&autoclose=Y&id='+docid,false);
    //      req.setRequestHeader("Content-length", "1");     
    r.send('');
    if(r.status == 200) { 
      if (r.responseXML) {
	var xmlres=r.responseXML;
	var elts = xmlres.getElementsByTagName("status");
	if (elts.length == 1) {
	  var elt=elts[0];
	  var code=elt.getAttribute("code");
	  var delay=elt.getAttribute("delay");
	  var w=elt.getAttribute("warning");
	  
	  if (w != '') alert(w);
	  if (code != 'OK') {
	    alert('code not OK\n'+req.responseText);
	    return false;
	  }	
	  return true;
	}
      }
      else {
	//alert('no xml\n'+r.responseText);
      } 
    }    
  }  	
  return false;  
}
function submitEdit(event,force) {
	var fedit= document.getElementById('fedit');
	var r=true;
	if (fedit) {
		var fedit= document.getElementById('fedit');

		if (force) fedit.noconstraint.value='Y';
		var oEditor;
		for (var i=0;i<FCKEDITORS.length;i++) {
			oEditor = FCKeditorAPI.GetInstance(FCKEDITORS[i]);
			if (oEditor) oEditor.UpdateLinkedField();
		}
		//bsubmit.onclick.apply(null,[event]);
		if (fedit.onsubmit) r=fedit.onsubmit();
		if (r) {
			if (isIE) {
				fedit.fireEvent("onsubmit");
			} else {
				oEvent = document.createEvent("HTMLEvents");
				oEvent.initEvent("submit",false,false);
				fedit.dispatchEvent(oEvent);
			}
			fedit.submit();			
		}
	}
	return r;
}

function pleaseSave(event) {
  if (document.isChanged && (! document.isSubmitted) && (! document.isCancelled)) {
    if (confirm('[TEXT:Save changes ?]')) {
      var bsubmit= document.getElementById('iSubmit');

      var can=canmodify();//bsubmit.onclick.apply(null,[event]);

      if (can) {
	var fedit= document.getElementById('fedit');
	if (fedit.onsubmit) fedit.onsubmit();
	fedit.submit();
      
      } else {
	alert('[TEXT:Data cannot be saved]');
	return false;
      }
    }
  }
  
  return true;
  
}

var OattrNid=null; //original attrNid
var OattrNtitle=null; //original attrNtitle
var askState=null; // memo displayed state
function  hasTransitionAsk(thestate) {
    if (states && (states.length >0) ) {
	for (var i=0;i<states.length;i++) {
	    if (states[i]==thestate) {
		if (askes[i].length > 0) return true;
		else return false;
	    }
	}
    }
    return false;
}
function askForTransition(event,thestate,thetitle,thecolor) {
  var th=document.getElementById('seltrans');
  var state=getIValue(th);
  
  var wf=document.getElementById('hwfask');
  var nf=document.getElementById('wfask');
  var nfd=document.getElementById('dfask');
  var as=document.getElementById('aSubmit3');
  var i;
  var ask=new Array();
  var tnf=new Array();
  var k=-1; // index for searches
  var xy;
  var nx;
  var h=0;

  //if (askState == state) return ;

  if (!thestate) {
    if (th.tagName=='SELECT') {
      thestate=th.options[th.selectedIndex].getAttribute('action');
      var as1=document.getElementById('aSubmit');
      if (as1) as1.innerHTML=thestate;
    }
  }
  
  if (!thecolor) {
    if (th.tagName=='SELECT') {     
      thecolor=th.options[th.selectedIndex].style.backgroundColor;
      th.style.backgroundColor=thecolor;
    }
  }
  askState=state;
  if (OattrNid == null) {
    OattrNid=new Array();
    OattrNtitle=new Array();
    for (i=0;i<attrNid.length;i++) OattrNid.push(attrNid[i]);// memo  original
    for (i=0;i<attrNtitle.length;i++) OattrNtitle.push(attrNtitle[i]);
  }
  
    
  attrNid=new Array();
  attrNtitle=new Array();  
  for (i=0;i<OattrNid.length;i++) attrNid.push(OattrNid[i]);// restore original
  for (i=0;i<OattrNtitle.length;i++) attrNtitle.push(OattrNtitle[i]);
  

  if (askState) {

      //nfd.style.visibility='hidden';	
    // display or not comment area
    if (state != '-') {document.getElementById('comment').style.visibility='visible';} else document.getElementById('comment').style.visibility='hidden';

    // move button nodes
    for (i=0;i<nf.childNodes.length;i++) {
      if (nf.childNodes[i].id && (nf.childNodes[i].id.substring(0,3)=="TWF")) {
	//tnf.push(nf.childNodes[i]);
	nf.childNodes[i].style.display='none';
      }
    }
    for (i=0;i<tnf.length;i++) {
      // wf.appendChild(tnf[i]);
    }
    for (i=0;i<states.length;i++) {
      if (states[i]==askState) {
	ask=askes[i];
      }
    }

    for (i=0;i<ask.length;i++) {
      twf=document.getElementById('TWF'+ask[i]);
      //nf.appendChild(twf);
      if (twf.style.visibility!='hidden') twf.style.display='';
      twf.style.width='90%';
      k=array_search(twf.id.substr(3),WattrNid);
      if (k >= 0) {
	attrNid.push(WattrNid[k]);
	attrNtitle.push(WattrNtitle[k]);
      }
    }
    if (ask.length > 0) {
      // search table
      ftable=th.parentNode;
      while (ftable && ((ftable.tagName!='TABLE')&&(ftable.tagName!='TBODY'))) ftable=ftable.parentNode;
      if (ftable) {
	yfoot=AnchorPosition_getPageOffsetTop(ftable);
      } else {
	yfoot=50;
      }
      GetXY(event);
      
      //nfd.style.display='none';	
      nfd.style.display='';	// to refresh div
      	
      //nfd.style.top='160px';
      //nf.style.top='300px';
      w=getObjectWidth(nfd);
      nx=Xpos-w+40;
      if (nx < 0) nx=0;
      //nfd.style.left=nx+'px';
     
      if (yfoot < 100) {
	if (ftable) h=getObjectHeight(ftable);
	nfd.style.top=(yfoot+10+h)+'px';
      } else {
	
	//	alert(xy.y+'/'+h+'/'+(xy.y-h));
	hnf=getObjectHeight(nfd);
	//nfd.style.top=(yfoot-hnf)+'px';

      }
      if (isNetscape) { // more beautifull
	  nfd.style.position='fixed';//h-=document.body.scrollTop; // fixed position
	  //  nfd.style.MozOpacity=0.02;
	  // moz_unfade(nfd.id);
	}
      var lg=document.getElementById('legendask');
      if (lg && thetitle) {
	lg.innerHTML=thetitle;
	if (thecolor) {
	  nf.style.borderColor=thecolor;
	  lg.style.borderColor=thecolor;
	  lg.style.borderStyle='none none solid none';
	}
      }
      if (as) {
	as.style.borderColor=thecolor;
	as.innerHTML=thestate;
      }
      //  nfd.style.display='none';	
      nfd.style.display='';	// to refresh div
      //nfd.style.visibility='visible';
    } else {
       nfd.style.display='none';
    }
      return true;
  } else return false;

}
// change for time attribute
function chtime(nid) {
  var t=document.getElementById(nid);
  var hh=document.getElementById('hh'+nid);
  var mm=document.getElementById('mm'+nid);
  var shh,smm,ihh,imm;
  if (t && hh && mm) {
    ihh=parseInt(hh.value * 1)%24;
    if (isNaN(ihh)) ihh=0;
    if (ihh < 10) shh='0'+ihh.toString();
    else shh=ihh.toString();
    hh.value=shh;

    imm=parseInt(mm.value * 1)%60;
    if (isNaN(imm)) imm=0;
    if (imm < 10) smm='0'+imm.toString();
    else smm=imm.toString();
    mm.value=smm;

    t.value=shh+':'+smm;
  }
}

// change for time attribute
function clearTime(nid) {
  var t=document.getElementById(nid);
  var hh=document.getElementById('hh'+nid);
  var mm=document.getElementById('mm'+nid);

  if (t && hh && mm) {
   
    hh.value='';hh.style.backgroundColor='[CORE_BGCOLORHIGH]';
    mm.value='';mm.style.backgroundColor='[CORE_BGCOLORHIGH]';

    t.value=' ';
  }
}
function clearFile(o,nid) {
  var t=document.getElementById(nid);
  if (t) {    
    if (t.value!=' ') {
      t.value=' ';      
    } else {
      t.value=document.getElementById('INIV'+nid).value;      
    }
    updatefilebutton(o,nid);
  }
  
}
function updatefilebutton(o,nid) {
  var t=document.getElementById(nid);
  var tu=document.getElementById('IF_'+nid);
  var p,i;
  var t1,v1,t2,v2;

  if (t) {
    p=t.previousSibling;   
    while (p && (p.tagName!='SPAN') && (p.tagName!='A')) p=p.previousSibling;  
    t2=o.getAttribute('title2');
    v2=o.getAttribute('value2');
    t1=o.getAttribute('title1');
    v1=o.getAttribute('value1');
    if (t.value==' ') {
      if (p) {
	if (p.tagName=='SPAN') p.style.textDecoration='line-through';
	if (p.tagName=='A') {
	    for (i=0;i<p.childNodes.length;i++) {
	      if (p.childNodes[i].tagName=='IMG') 		{
		pi=p.childNodes[i];
		if (! pi.getAttribute('orisrc')) pi.setAttribute('orisrc',pi.src);
		pi.src='Images/delimage.png';
	      }
	    }
	}
      }
      if (tu) resetInputFile(tu);
      o.setAttribute('title',t2);
      o.value=v2;
    } else {
      if (p) {
	if (p.tagName=='SPAN') 	p.style.textDecoration='';
	if (p.tagName=='A') {
	    for (i=0;i<p.childNodes.length;i++) {
	      if (p.childNodes[i].tagName=='IMG') {
		pi=p.childNodes[i];
		if (pi.getAttribute('orisrc')) pi.src=pi.getAttribute('orisrc');
	      }
	    }
	}

      }
      o.setAttribute('title',t1);
      o.value=v1; 
    }
  }
  disableReadAttribute();
}
/**
 * Mimic Unix "basename" command
 */
function basename(filepath, suffix) {
    var separator = '/';
    var len;
    var pos;

    if( suffix != null ) {
	len = filepath.length - suffix.length;
	pos = filepath.lastIndexOf(suffix);
	if( pos == len) {
	    filepath = filepath.substring(0, len);
	}
    }

    /* Change to Windows path separator if
       filepath looks like a Windows path.
       (e.g. "C:\..." or "\\SERVER\...")
    */
    if( filepath.match(/^([A-Z]:\\|\\\\)/) ) {
	separator = "\\";
    }

    pos = filepath.lastIndexOf(separator) + 1;
    if( pos > 0 ) {
	filepath = filepath.substring(pos, filepath.length);
    }
    return filepath;
}
/**
 * Reset a input file element
 */
function resetInputFile(input) {
    input.value = "";

    if( input.value != "" ) {
	/* For IE that does not support setting the value of a
	   input file field, we have to hack around and reconstruct
	   the element from the HTML code which will reset the
	   value attribute to blank.
	   As all our input file elements are a child of a
	   <span> element, it is safe to reconstruct the parent
	   inner HTML content.
	   We store the element id, then reconstruct it, and
	   finally we set back the input var with the new element.
	*/
	var inputId = input.id;
	input.parentNode.innerHTML = input.parentNode.innerHTML;
	input = document.getElementById(inputId);
    }
}
function changeFile(o,nid,check) {
  var t=document.getElementById(nid);
  var tix=document.getElementById('ix_'+nid);
  var p;
  if (t && o) {
      if (check) {
	  var oe=document.getElementById('IFERR'+nid);
	  var ori=document.getElementById('IFORI'+nid);
	  var good=false;

	  var filename = o.value;
	  if( filename ) {
	      filename = basename(filename);
	  }

	  if (! filename) good=false;
	  else if (filename == check) good=true;
	  else {
	      var prefix=filename.substr(0, filename.indexOf('}',check.lastIndexOf('.')-2)+1);
	      var extend=filename.substr(filename.lastIndexOf('.'));
	      var precheck=check.substr(0,check.lastIndexOf('.'));
	      var extcheck=check.substr(check.lastIndexOf('.'));
	      if ((extend == extcheck) && (prefix==precheck)) good=true;
	  }
	  if (! good) {
	      if (!confirm("[TEXT:The submitted file does not comes from the latest version of the document and might have been modified by other users. If you continue, theses changes will be lost.- Select 'Ok' to continue and replace the file with your version.- Select 'Cancel' to go back to the document without changing the file (you'll be able to download the latest version of the file and integrate your changes in it)]")) {
		  resetInputFile(o);
	      } else {
		  if (ori) {
		      ori.value='';
		      var oriname=ori.getAttribute('orivalue');
		      var orip=oriname.lastIndexOf('.');
		      var orib=oriname.substr(0,orip);
		      var oriext=oriname.substr(orip);
		      var newp=filename.lastIndexOf('.');
		      var newb=filename.substr(0,orip+1);
		      var newext=filename.substr(newp);
		      if ((newext == oriext) && (newb == orib+'-')) {
			  // get the same original name
			  ori.value=ori.getAttribute('orivalue');
		      }
		  }
		  if (oe) oe.innerHTML=' [TEXT:It is not the latest document] - ';
	      }
	  } else {	      
	      if (oe) oe.innerHTML='';
	      if (ori) ori.value=ori.getAttribute('orivalue');
	  }
      }
      if ((! t.value) || t.value==' ') t.value=(o.value=='')?document.getElementById('INIV'+nid).value:o.value;
      updatefilebutton(tix,nid);
  }
}

function checkinput(cid,check,iin,resetiin,thname) {    
  var i=document.getElementById(cid);
  if (i) {
    if (!i.disabled) {
      if (check) i.checked=check;
      else i.checked=(!i.checked);
      changeCheckClasses(i,iin,resetiin,thname);
    }
  }
}
// change style classes for check input
function changeCheckClasses(th,iin,resetiin,thname) {
  if (! thname) thname=th.name;
  if (th && thname) {
    var icheck=document.getElementsByName(thname);
    if (icheck.length==0) {
      // other method for IE
      icheck=new Array();
      var ti=th.parentNode.parentNode.parentNode.getElementsByTagName('input');     
      for (var i=0;i<ti.length;i++) {
 	if (ti[i].name && (ti[i].name == thname)) {
	  icheck.push(ti[i]);
 	}
      }
    }

    if (icheck.length==0) return;
    var  needuncheck=false;
    for (var i=0;i<icheck.length;i++) {
      if (icheck[i].checked) icheck[i].parentNode.parentNode.className='checked';
      else icheck[i].parentNode.parentNode.className='nochecked';
    }
    //alert(icheck[0].type);
   
    for (var i=0;i<icheck.length-1;i++) {
      if (icheck[i].checked) needuncheck=true;
    }
    icheck[icheck.length-1].checked=(!needuncheck);
    if (iin) {
      var oi=document.getElementById(iin);
      if (resetiin) oi.value='';
      else {
	for (var i=0;i<icheck.length;i++) {
	  if (icheck[i].checked) {
	    oi.value=icheck[i].value;
	  }
	}
      }
      createOtherEnumInput(th,oi);
    }    
  }
}

function viewOtherEnumInput(selid) {
  var sel=document.getElementById(selid);
  createOtherEnumInput(sel,sel,false);
}

function createOtherEnumInput(radio,ireal,initvalue) {
  var v='';
  if (radio) {
    if (radio.tagName == "SELECT") v=getIValue(radio);
    else if ((radio.type == "checkbox") && (! radio.checked)) v=getIValue(radio);
    else v=radio.value;
  }
  if (initvalue) v='...';      
  
  var oid='other_'+ireal.id;
  var o=document.getElementById(oid);

  if (v=='...') {

    if (! o) {      
      o=document.createElement("input");
      o.id=oid;
      o.type='text';
      if (initvalue) o.value=getIValue(ireal);
      var cibleid='l'+ireal.id+'___';
      var cible=document.getElementById(cibleid);     
      if (! cible) cible=ireal;
      cible.parentNode.appendChild(o);
      if (radio && radio.tagName == "SELECT") addEvent(o,"change", function() {if (radio.multiple) selectinlist(radio,'...',true);addinlist(radio,this.value+' [TEXT:(Other input)]',this.value);o.style.display='none'});
      else addEvent(o,"change", function() {ireal.value=this.value});
      addEvent(o,"keypress", function(event) {if (trackCR(event)) {stopPropagation(event);return false}});
    }
    o.style.display='';
    o.focus();
  } else {
    if (o) {
      o.style.display='none';
      o.value='';
    }
  }
}


// change style classes for check bool input
function changeCheckBoolClasses(th,name) {
  if (th) {
    var icheck=new Array(2);
    var i=0;
    var p=th.previousSibling;
    while (p && (i<2)) {
      if (p.name == name) {	
	icheck[i]=p;
	i++;
      }
      p=p.previousSibling;
    }
    if (i==2) {
      icheck[1].checked=(!th.checked);
      icheck[0].checked=th.checked;
    } else {
      alert('[TEXT:changeCheckBoolClasses Error]');
    }

  }
}

// change checkbox value for boolean style
function changeCheckBoxCheck(oboolid,idx,th) {
  obool=document.getElementById(oboolid);
  obool.checked=(idx!='0');
  var i=0;
  var p=obool.previousSibling;
  while (p && (i<2)) {
    if (p.name == th.name) {	
      if (p.id != th.id) p.checked=false;
      i++;
    }
    p=p.previousSibling;
  }

}

function addinlist(sel,value,key,notselected) {

  if (isNetscape) pos=null;
  else pos=sel.options.length+1;
  if (! key) key=value;
  sel.add(new Option(value,key, false, true),pos);
  if (notselected) {
    sel.options[sel.options.length - 1].selected=false;
  } 
}

function removeinlist(inpsel,key) {
  if (inpsel) {
    for (var k=0;k<inpsel.options.length;k++) {
      if (inpsel.options[k].value==key) inpsel.remove(k--);
    }    
  }
}
function selectinlist(inpsel,key,unselect) {
  if (inpsel) {
    unselect=(unselect)?true:false;
    for (var k=0;k<inpsel.options.length;k++) {
      if (inpsel.options[k].value==key) inpsel.options[k].selected=(!unselect);
    }    
  }
}

function  nodereplacestr(n,s1,s2) {
  
  var kids=n.childNodes;
  var ka;
  var avalue;
  var regs1;
  var rs1;
  var tmp;
    var attnames = new Array('onclick','href','onmousedown','onmouseover','onfocus','id','name','onchange','oncontextmenu','src','onkeypress');
  // for regexp
  rs1 = s1.replace('[','\\[');
  rs1 = rs1.replace(']','\\]');
  regs1 = new RegExp(rs1,'g');
  
  for (var i=0; i< kids.length; i++) {     
    if (kids[i].nodeType==3) { 
      // Node.TEXT_NODE
      
	if (kids[i].data.search(rs1) != -1) {
	  tmp=kids[i].data; // need to copy to avoid recursive replace
	  
	  kids[i].data = tmp.replace(s1,s2);
	}
    } else if (kids[i].nodeType==1) { 
      // Node.ELEMENT_NODE
	
	// replace  attributes defined in attnames array
	  for (iatt in attnames) {
	    
	    attr = kids[i].getAttributeNode(attnames[iatt]);
	    if ((attr != null) && (attr.value != null) && (attr.value != 'null'))  {
	      
	      
	      if (attr.value.search(rs1) != -1) {				
		avalue=attr.value.replace(regs1,s2);

		if (isNetscape) attr.value=avalue;
		else if ((attr.name == 'onclick') || (attr.name == 'onmousedown') || (attr.name == 'onmouseover')) kids[i][attr.name]=new Function(avalue); // special for IE5.5+
		else attr.value=avalue;
	      }
	    }
	  }
      nodereplacestr(kids[i],s1,s2);
    } 
  }
}



//-------------------------------------------------------------
// select tr (row table) 
var seltr=false; 
var indextr=-1;
function addtr(trid, tbodyid) {
  
  var ntr;
  with (document.getElementById(trid)) {
    // need to change display before because IE doesn't want after clonage
    style.display='';

    ntr = cloneNode(true);
    style.display='none';
    if (isNetscape) {
      // bug :: Mozilla don't clone textarea values
      var newTa = ntr.getElementsByTagName('textarea');
      for (var i=0; i < newTa.length; i++){ 
	
	newTa[i].setAttribute('value',getElementsByTagName('textarea')[i].value);
	// -- this next line is for N7 + Mozilla
	newTa[i].defaultValue = getElementsByTagName('textarea')[i].value;
      }
    }
  }
  
  ntr.id = '';
  ntable = document.getElementById(tbodyid);
  ntable.appendChild(ntr);
    
    if (indextr==-1) indextr=ntable.childNodes.length;
    else indextr++;
  nodereplacestr(ntr,'-1]',']'); // replace name [-1] by []
  nodereplacestr(ntr,'-1',indextr);
  nodereplacestr(ntr,'_1x_',indextr);
  resizeInputFields(); // need to revaluate input width
 
  if (seltr && (seltr.parentNode == ntr.parentNode))  {
    seltr.parentNode.insertBefore(ntr,seltr);
    resetTrInputs(ntr);
  } else {
    var ltr = ntable.getElementsByTagName('tr');
    var ltrfil=new Array();
    for (var i=0;i<ltr.length ;i++) {
      if ((ltr[i].parentNode.id == tbodyid) || (ltr[i].parentNode.parentNode.id == tbodyid)) ltrfil.push(ltr[i]);
    }
    if (ltrfil.length > 1) ltrfil[ltrfil.length-2].parentNode.insertBefore(ntr,ltrfil[ltrfil.length-2]);
  }
  return ntr;
  
}

// use to delete an article
function deltr(tr) {
  if (indextr==-1) indextr=tr.parentNode.childNodes.length;
  tr.parentNode.removeChild(tr);

  return;
}

function resetInputsByName(name) {
  if (! isNetscape) return;
  var la=document.getElementsByName(name);
  if (la) {
    for (var i=0; i< la.length; i++) { 
	    la[i].parentNode.insertBefore(la[i],la[i].nextSibling);      
	  }
  }
}

function resetTrInputs(tr) {
  if (! isNetscape) return;
  var tin = tr.getElementsByTagName('input');
  
  for (var i=0; i< tin.length; i++) { 
    if (tin[i].name) resetInputsByName(tin[i].name);
  }

  // add select input also
  tin = tr.getElementsByTagName('select');
  
  for (var i=0; i< tin.length; i++) { 
    if (tin[i].name) resetInputsByName(tin[i].name);
  }
  // add select input also
  tin = tr.getElementsByTagName('textarea');
  
  for (var i=0; i< tin.length; i++) { 
    if (tin[i].name) resetInputsByName(tin[i].name);
  }

}
// up tr order 
function uptr(trnode) {

  var pnode = trnode.previousSibling;
  var textnode=false;

  while (pnode && (pnode.nodeType != 1)) pnode = pnode.previousSibling; // case TEXT attribute in mozilla between TR ??

  if (pnode)  {
    trnode.parentNode.insertBefore(trnode,pnode);
    
  }  else {
    trnode.parentNode.appendChild(trnode); // latest (cyclic)
  }
  resetTrInputs(trnode);
  return;  
}

// down tr order 
function downtr(trnode) {

  var nnode = trnode.nextSibling;

  while (nnode && (nnode.nodeType != 1)) nnode = nnode.nextSibling; // case TEXT attribute in mozilla between TR ??

  if (nnode ) {
      nnnode = nnode.nextSibling; 
      while (nnnode && (nnnode.nodeType != 1)) nnnode = nnnode.nextSibling; // case TEXT attribute in mozilla between TR ??

      if (nnnode) 
         trnode.parentNode.insertBefore(trnode,nnnode);
      else 
         trnode.parentNode.appendChild(trnode); // latest
  } else {
      trnode.parentNode.insertBefore(trnode,trnode.parentNode.firstChild); // latest (cyclic)
  }


  resetTrInputs(trnode);
  return;  
}

// use to delete an article
var specDeltr=false;
function delseltr() {
  if (seltr) {
    if (indextr==-1) indextr=seltr.parentNode.childNodes.length;
    seltr.parentNode.removeChild(seltr);  
    if (specDeltr) {
      eval(specDeltr);
      try {
      }
      catch(exception) {
	alert(exception);
      }
    }
  }
  unseltr();
  return;
  
}
var specDuptr=false;
function duptr() {
  var dsel;
  var tbodysel;
  var i;
  if (seltr) {
    tbodysel=seltr.parentNode;
    tbodyselid=tbodysel.id;
    tnewid='tnew'+tbodyselid.substr(5);
    if (document.getElementById(tnewid)) {
      ntr=addtr(tnewid,tbodyselid);
      afterCloneBug(seltr,ntr);    
    } else {
      // direct clone tr
      csel=seltr.cloneNode(true);
      csel.style.backgroundColor='';
      seltr.parentNode.insertBefore(csel,seltr);
      visibilityinsert('trash','hidden');
      // after clone (correct bug)
      afterCloneBug(seltr,csel);
    }
    disableReadAttribute();
    if (specDuptr) {
      eval(specDuptr);
      try {
      }
      catch(exception) {
	alert(exception);
      }
    }
  }  
}

function afterCloneBug(o1,o2) {
  var ti1,ti2,t;
  var itag=new Array('input','textarea','select');

  for (t in itag) {
    ti1= o1.getElementsByTagName(itag[t]);
    ti2= o2.getElementsByTagName(itag[t]);
    for ( i=0; i< ti1.length; i++) {
      setIValue(ti2[i],getIValue(ti1[i]));
    }
  }
}

// change input (id) value (v) in node n
function chgInputValue(nid,id,v) {
  
  var itag=new Array('input','textarea','select');
  var ti,t;
  var n=document.getElementById(nid);

  if (n) {
    for (t in itag) {
      ti=n.getElementsByTagName(itag[t]);
      for (var i=0; i< ti.length; i++) {
	pos=ti[i].name.indexOf('[');
	if (pos==-1) ni=ti[i].name;
	else ni=ti[i].name.substr(0,pos);
	if (ni==id) {
	  setIValue(ti[i],v);
	}
      }
    
    }
  }
  
  
}
function visibilityinsert(n,d) {
  var ti = document.getElementsByName(n);
  for (var i=0; i< ti.length; i++) { 
    ti[i].style.visibility=d;
  }
}

function selecttr(o,tr) {

  visibilityinsert('trash','hidden');
  visibilityinsert('unselect','hidden');
  var ti = tr.parentNode.getElementsByTagName('input');
  //var ti = tr.parentNode.getElementsByTagName('img');
  for (var i=0; i< ti.length; i++) { 
    if (ti[i].name=='unselect') ti[i].style.visibility='visible';
  }
  var ti = tr.parentNode.getElementsByTagName('textarea');
  for (var i=0; i< ti.length; i++) { 
    ti[i].rows=1;
    if (ti[i].id && document.getElementById('exp'+ti[i].id)) document.getElementById('exp'+ti[i].id).style.display='none';
  }
  if (seltr) {
    seltr.className='';
    
  }   
  o=o.previousSibling;
  while (o && (o.nodeType != 1)) o = o.previousSibling; // case TEXT attribute in mozilla between TR 
  if (!o) alert('[TEXT:no trash image]');
  else o.style.visibility='visible';

  seltr=tr;

  seltr.className='selecta';


  return;  
}

//unselect selected
function unseltr() {

  if (seltr) {
    seltr.className='';
    
    visibilityinsert('insertup','hidden');
  }
  visibilityinsert('trash','hidden');
  visibilityinsert('unselect','hidden');
  seltr=false;

  return;  
}
var specMovetr=null;
function movetr(tr) {

  var trnode= seltr;
  var pnode = tr;
  if (seltr) {  

    while (pnode && (pnode.nodeType != 1)) pnode = pnode.previousSibling; // case TEXT attribute in mozilla between TR ??
    if (pnode)  {
      trnode.parentNode.insertBefore(trnode,pnode);
    
    }  else {
      //trnode.parentNode.appendChild(trnode); // latest (cyclic)
    }
    
    resetTrInputs(trnode);
    if (specMovetr) {
      eval(specMovetr);
      try {
      }
      catch(exception) {
	alert(exception);
      }
    }
  }
  return;  
}




//-----------------------------------------
function submitinputs(faction, itarget) {
  var fedit = document.fedit;
  //  resetInputs();
  
  with (document.modifydoc) {
    var editAction=action;
    var editTarget=target;
    wf=subwindow(100,390,itarget,'about:blank');
    enableall();  
    target=itarget;
    action=faction;
    submit();
    restoreall();
    target=editTarget;
    action=editAction;
        
    return wf;
  }
}


function vconstraint(cButton,famid,attrid) {
  var inp  = cButton.previousSibling;
  var index='';

  var domindex=''; // needed to set values in arrays
  // search the input button in previous element

  var inid;
  var wf;

  
  inid= cButton.id.substr(3);
  inp=document.getElementById(inid);

  if ((! inp)||(inp==null)) {
    alert('[TEXT:vconstraint input not found id=]'+inid);
  }
  
  if (inp.name.substr(inp.name.length-1,1) == ']') {
    // it is an attribute in array
      index=getRowNumber(cButton,'tarray');
    domindex = inp.id.substring(attrid.length);    
  }

  
  wf=submitinputs('[CORE_STANDURL]&app=FDL&action=VCONSTRAINT&famid='+famid+'&attrid='+attrid+'&index='+index+'&domindex='+domindex,'wchoose');
  if (wf) {
	  var xy= getAnchorWindowPosition(inp.id);
	  if (isNaN(window.screenX)){
		  xy.y+=15; // add supposed decoration height
		  // add body left width for IE sometimes ...
		  if (parent.ffolder)  xy.x += parent.ffolder.document.body.clientWidth;
	  }
	  wf.moveTo(xy.x, xy.y+10);
  }
}

function viewoption(aid,index,fid,said) {
  nfid=document.getElementById(fid);
  naid=document.getElementById(aid+index);
  nval=document.getElementById(said);
  pdivopt=document.getElementById('pdiv_'+said);

  if (nfid && naid) {
    docid=naid.value;
    if (parseInt(docid) > 0) {
      val=encodeURI(nval.value);
      nfid.src='[CORE_STANDURL]&app=FDL&action=EDITOPTION&id='+docid+'&aid='+said+'&opt='+val;
      nfid.style.display='';
      pdivopt.style.display='none';
    } else {
      alert('[TEXT:Choose document before set options]');
    }
  }
}
function canceloption(said) {
  nfid=self.parent.document.getElementById('if_'+said);
  pdivopt=self.parent.document.getElementById('pdiv_'+said);

  if (nfid && pdivopt) {
      pdivopt.style.display='';
      nfid.style.display='none';
      nfid.src='about:blank';    
  }
}


// to adjust height of body in edit card in fixed positionning
function fixedPosition() {
  var fspan=document.getElementById('fixspanhead');
  var ftable=document.getElementById('fixtablehead');
  var xy;
  var h;


  if (isIE && ((document.body.scrollHeight) <= document.body.clientHeight)) {    
    if (fspan && ftable) {
      ftable.style.position='static';
      fspan.style.display='none';
    }
    fspan=document.getElementById('fixspanfoot');
    ftable=document.getElementById('fixtablefoot');
    if (fspan && ftable) {
      ftable.style.position='static';
      fspan.style.display='none';
    }
  } else {;     
    if (fspan && ftable) {
      xy=getAnchorPosition(ftable.id);
      h=parseInt(getObjectHeight(ftable))-xy.y;
      if (h>0) {
	fspan.style.height=getObjectHeight(ftable);
	fspan.style.top=xy.y;
      }
    }
    fspan=document.getElementById('fixspanfoot');
    ftable=document.getElementById('fixtablefoot');

    if (fspan && ftable) {
      fspan.style.height=parseInt(getObjectHeight(ftable))+'px';;
    
    }
  }
}

function focusFirst() {
  
  var fedit= document.getElementById('fedit');
  if (fedit) {
    for (var i=0;i<fedit.elements.length;i++) {
      
      switch (fedit.elements[i].type) {
      case 'text':
      case 'select-one':
      case 'select-multiple':
      case 'textarea':
      case 'FIELDSET':
	if ((! fedit.elements[i].disabled)&&(fedit.elements[i].style.display != 'none')&&(fedit.elements[i].style.visibility != 'hidden')) {
	  try {
	    fedit.elements[i].focus();
	    return;
	  } catch(exception) {
	  }
	}
	break;
      case 'hidden':
      case 'file':
      case 'button':
      case 'submit':
      case 'radio':
      case 'undefined':
      case '':
	break;
      default:		  
	;
      }
    }
  }
}
if (isNetscape) addEvent(window,"load",fixedPosition);
//addEvent(window,"load",scrutemdocs);
// move inputs buttons from node to node
function mvbuttons(idnode1, idnode2) {
  var node1=document.getElementById(idnode1);
  var node2=document.getElementById(idnode2);
  var ti;
  var fc;
  if (node1 && node2) {
     ti= node1.getElementsByTagName("input");  
     fc=node2.firstChild;
     while (ti.length>0) {
       node2.insertBefore(ti[0],fc);
     }
  }

  
}


function mvbuttonsState() {
  var isub=document.getElementById('iSubmit');
  if (isub) isub.style.display='none';

  mvbuttons('editstatebutton','editbutton');
  
}

function mvSaveAnchor() {
  var isub=document.getElementById('aSubmit');
  var isub2=document.getElementById('aSubmit2');
  if (isub) {
    isub.style.display='none';    
    isub.parentNode.insertBefore(isub2,isub);
    isub2.innerHTML=isub.innerHTML;
  }
}
function applyFirstSelect(event) {
  var th=document.getElementById('seltrans');
  if (th) {
    if (th.tagName=='SELECT') {
      askForTransition(event);
    }
  }
}

function preview(faction,ntarget) {
  var fedit = document.fedit;
  //resetInputs();
  
  with (document.modifydoc) {
    var editAction=action;
    var editTarget=target;
    if (! ntarget) ntarget='preview';
    wf=subwindowm(300,600,ntarget,'about:blank');
    enableall();  
    var na=document.getElementById('newart');
    if (na) {
      disabledInput(na,true);        
      var nt=document.getElementById('newtxt');
      disabledInput(nt,true);
    }
    target=ntarget;
    action=faction;

    submittextarea();
    submit();
    target=editTarget;
    action=editAction;
    restoreall()
    
    if (na) {
      disabledInput(na,false);            
      disabledInput(nt,false);
    }    
  }
}

function quicksave() {
  if (canmodify()) {
    with (document.modifydoc) {    
      var editTarget=target;
      if (isNaN(id.value) ) {
	alert('[TEXT:quick save not possible]');	
      } else {
	enableall();  
	var na=document.getElementById('newart');
	if (na) {
	  disabledInput(na,true);        
	  var nt=document.getElementById('newtxt');
	  disabledInput(nt,true);
	}
	target='fhsave';
	var oldredirect=document.modifydoc.noredirect.value;
	document.modifydoc.noredirect.value=1;
	document.modifydoc.quicksave.value=1;
	// for htmlarea
	submittextarea();	
    

	submit();
	document.modifydoc.noredirect.value=oldredirect;
	document.modifydoc.quicksave.value=0;
	document.isChanged=false;
	target=editTarget;
	restoreall()
    
	  if (na) {
	    disabledInput(na,false);            
	    disabledInput(nt,false);
	  }    
	viewwait(true);    
	return true;
      }
    }
  }
  return false;
}

function submittextarea() {
  // for htmlarea
  return;
  for (var i=0;i< editors.length; i++) {
    editors[i]._formSubmit();
  }
}
function viewquick(event,view) {
  if (! event) event=window.event;
  if (document.modifydoc.id.value > 0) {
    var ctrlKey = event.ctrlKey;
  
    if (view && ctrlKey) {
      document.getElementById('iQuicksave').style.display='';
      document.getElementById('iSubmit').style.display='none';
    }
    if (!view) {
      document.getElementById('iQuicksave').style.display='none';
      document.getElementById('iSubmit').style.display='';
    }
  }
}

// quick save of fckeditors
function trackKeysQuickSave(event) {
  var intKeyCode,ctrlKey;

  if (!event) event=window.event;
 
  intKeyCode = event.which;
  if (!intKeyCode) intKeyCode= event.keyCode;
  //alert(intKeyCode);
  ctrlKey = event.ctrlKey;
  if (((intKeyCode == 115)||(intKeyCode == 83)) && ( ctrlKey)) {
    // Ctrl-S
    if (quicksave) quicksave();
    else if (window.parent.quicksave) window.parent.quicksave();
    
    //  window.parent.quicksave();  
    if (stopPropagation) stopPropagation(event);
    else if (window.parent.stopPropagation) window.parent.stopPropagation(event);    
  } else {
    document.isChanged=true;
  }
}
addEvent(document,"keypress",trackKeysStop); // only stop propagation
addEvent(document,"keydown",trackKeys);
//addEvent(document,"keypress",trackKeys);

// ~~~~~~~~~~~~~~~~~ for ARRAY inputs ~~~~~~~~~~~~~
function trackKeysStop(event) {
  return(trackKeys(event,true));
}
function trackKeys(event,onlystop)
{
  var intKeyCode;
  var stop=false;
  var tm;
  if (isNetscape) {
    intKeyCode = event.which;
    if (!intKeyCode) intKeyCode= event.keyCode;
    altKey = event.altKey
    ctrlKey = event.ctrlKey
   }  else {
    intKeyCode = window.event.keyCode;
    altKey = window.event.altKey;
    ctrlKey = window.event.ctrlKey
   }
  //window.status=intKeyCode + ':'+altKey+ ':'+ctrlKey;
  if (!onlystop) {
    if (((intKeyCode == 83)||(intKeyCode == 22)) && (altKey || ctrlKey)) {
      // Ctrl-S
      quicksave(); 
      stop=true;
    }
  }
  if ((!onlystop) && seltr ) {
    if (((intKeyCode == 86)||(intKeyCode == 22)) && (altKey || ctrlKey)) {
      // Ctrl-V
      duptr();
      stop=true;
    }
    
    if (((intKeyCode == 68)||(intKeyCode == 100)) && (altKey || ctrlKey)) {
      // Ctrl-D
       delseltr();
      stop=true;
    }
    if ( (intKeyCode == 38) && (altKey || ctrlKey)) {
      // Ctrl-Up
      tm=seltr.previousSibling; 
      while (tm && (tm.nodeType != 1)) tm = tm.previousSibling;
      if (tm) movetr(tm);
      stop=true;
    }
    if ((intKeyCode == 40) && (altKey || ctrlKey)) {
      // Ctrl-Down
      tm=seltr.nextSibling;
      while (tm && (tm.nodeType != 1)) tm = tm.nextSibling;
      tm=tm.nextSibling;
      while (tm && (tm.nodeType != 1)) tm = tm.nextSibling;
      if (tm) movetr(tm);
      stop=true;
    }
  }
  if (onlystop ) {
    if (altKey || ctrlKey) {
      if ((seltr && ((intKeyCode == 100) || 
		     (intKeyCode == 118))) ||
	  (intKeyCode == 115)) {
	stop=true;
      }
    }
  }



  if ( stop) {
    stopPropagation(event);
    return false;
  } 
    
  return true;
}
var dro=null; // clone use to move
var idro=null; // real tr to move
var hidro=null; // height of idro
var ytr=0;
var draggo=false;


function adraggo(event) {
  if (dro) {
    if (idro) {
      idro.style.visibility='hidden'; 
      var ti=dro.getElementsByTagName('input');    
      for (var i=0;i<ti.length;i++) { // to avoid conflict with others inputs
	ti[i].id='';
	ti[i].name='';
	ti[i].disabled=true;
      }
      
      idro.parentNode.appendChild(dro); 
      visibilityinsert('trash','hidden');
    }
    //    dragtr(event); 
    draggo=true;
  }
}

function increaselongtext(oid) {
  var o=document.getElementById(oid);
  var ip=document.getElementById('exp'+oid);

  if (o) {
    if ((o.scrollHeight-3) > o.clientHeight) {
      o.rows=9;
      if (ip) ip.style.display='';
    }
    
  }
}
function adrag(event,o) {
  sdrag(event); // in case of already in drag
  GetXY(event);
  dro=o.parentNode.parentNode.cloneNode(true);
  dro.style.position='absolute';
  dro.className='move';
  dro.style.width=getObjectWidth(o.parentNode.parentNode);
  idro=o.parentNode.parentNode;
  hidro=getObjectHeight(idro);
  dro.style.top=Ypos-Math.round(hidro/2);
  ytr=Ypos;  
  addEvent(document,"mousemove",dragtr); 
  stopPropagation(event);

  setTimeout('adraggo()',300); 
  //adraggo(event);
}
function sdrag(event) {
  var dytr; //delta
  if (dro && draggo) {
    if (dro.parentNode) dro.parentNode.removeChild(dro);
    GetXY(event); 
    dytr=Ypos-ytr;
    if (dytr > 0) dytr=dytr-(hidro/2);
    dtr=Math.round(dytr/hidro);
    //alert(hidro+'/'+dytr+'/'+dytr/hidro+'/'+dtr);
    
    trmo=idro;
    if (dtr > 0) {
      while (trmo && (dtr >= 0)) {
	trmo=trmo.nextSibling;
	while (trmo && (trmo.nodeType != 1)) trmo = trmo.nextSibling; // case TEXT attribute in mozilla between TR
	dtr--;
      }
      if (trmo) {
	seltr=idro;
	movetr(trmo);
      }
    } else if (dtr < 0) {
      while (trmo && (dtr < -1)) {
	trmo=trmo.previousSibling;
	while (trmo && (trmo.nodeType != 1)) trmo = trmo.previousSibling; // case TEXT attribute in mozilla between TR
	dtr++;
      }
      if (trmo) {
	seltr=idro;
	movetr(trmo);
      }
    }
  }
  if (idro) idro.style.visibility='visible';
  dro=null;
  idro=null;
  draggo=false;
  delEvent(document,"mousemove",dragtr);   
  stopPropagation(event);

  
}


function dragtr(event) {  
  if (dro && draggo) {
    GetXY(event); 
    dro.style.top=Ypos-Math.round(hidro/2);
    //    dro.style.left=Xpos-10;
    // window.status='drag='+Ypos+'x'+Xpos;
  }
  return false;
}




function textautovsize(event,o) {
  if (! event) event=window.event;

  var i=1;
  var hb=o.clientHeight;
  var hs=o.scrollHeight;

  if (hs > hb) {
    o.parentNode.style.height=hs+'px';
    o.style.height=hs+'px';
  }
  
}

// Supprime les espaces inutiles en début et fin de la chaîne passée en paramètre.
function trim(aString) {
  var regExpBeginning = /^\s+/;
  var regExpEnd       = /\s+$/;
  return aString.replace(regExpBeginning, '').replace(regExpEnd, '');
}

function documentsubmit(f) {
  if (document.isSubmitted) return false;
  if (!canmodify()) return false;
  document.isSubmitted=true;
  enableall();
  if (parent) if (parent.flist) f.catgid.value=parent.flist.catgid;
  if (f.iSubmit) f.iSubmit.disabled=true;
  var asub=document.getElementById('aSubmit');
  if (asub) {
    asub.innerHTML='[TEXT:Save in progress]';
    asub.style.cursor='default';
    asub.title='';
    document.getElementById('iCancel').style.display='none';
  }
  viewwait(true);
  setbodyopacity(0.5);
  return true
}


// use when construct elink
function elinkvalue(attrid) {
  var v,d;  
  d=document.getElementById('mdocid_isel_'+attrid);
  if (d) { // special case of docid multiple
    v=getIValue(d);
    return v;
  }

  d=document.getElementById(attrid);
  if (d) return trim(d.value);

  return '';
}
