
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

var INPROGRESSATTR=false;
var ATTRCIBLE=null;
var ATTRREADCIBLE=null; // the element  replaced by input
var INPUTINPROGRESS=false; // true when an input is already done
var ATTRREQ=null;
var DIVATTR=document.createElement("span");
var CINPUTDOCID=false; // current docid

var INPUTCHANGED=false;
var corestandurl=window.location.pathname+'?sole=Y';

function reqEditAttr() {
  INPROGRESSATTR=false; 
  document.body.style.cursor='auto';
  var o=ATTRCIBLE;
 
  if (ATTRREQ.readyState == 4) {
    // only if "OK"
    if (ATTRREQ.status == 200) {
      // ...processing statements go here...
      if (ATTRREQ.responseXML) {
	reqNotifyEditAttr(ATTRREQ.responseXML);

      } else {
	//alert('no xml\n'+ATTRREQ.responseText);
	return;
      } 	  
    } else {
      alert("There was a problem retrieving the XML data:\n" +
	    ATTRREQ.statusText);
      return;
    }
  } 
}
function reqNotifyEditAttr(xmlres) {
  var o=ATTRCIBLE;
  if (xmlres) {
    var elts = xmlres.getElementsByTagName("status");

    if (elts.length == 1) {
      var elt=elts[0];
      var code=elt.getAttribute("code");
      var delay=elt.getAttribute("delay");
      var c=elt.getAttribute("count");
      var w=elt.getAttribute("warning");
      var f=elt.getAttribute("focus");

      if (w != '') alert(w);
      if (code != 'OK') {
	//	    alert('code not OK\n'+ATTRREQ.responseText);
	if (ATTRREADCIBLE) ATTRREADCIBLE.style.display='';	    
	if (o) o.style.display='none';
	return;
      }
      elts = xmlres.getElementsByTagName("branch");
      elt=elts[0].firstChild.nodeValue;
      // alert(elt);
      if (o) {

	if (ATTRREADCIBLE) {
	   ATTRREADCIBLE.style.display='none';
	}
	if (c > 0) o.style.display='';
	o.style.left = 0;
	o.style.top  = 0;
	o.innerHTML=elt;
	var oi=o.getElementsByTagName('textarea');
	var di=o.getElementsByTagName('div');
	if (oi.length > 0) {
	  var oi1=oi[0];
	  oi1.style.width=o.style.width;
	  oi1.style.height=o.style.height;
	}
	if (oi.length == 0) {
	  oi=o.getElementsByTagName('input');
	  if (oi.length > 0) {
	    var oi1=oi[0];
	    oi1.style.width=o.style.width;
	  }

	  
	}
	if (oi1) {
	  oi1.style.fontSize=getCssStyle(ATTRREADCIBLE,'fontSize');
	  oi1.style.fontFamily=getCssStyle(ATTRREADCIBLE,'fontFamily');
	}
	if (di.length > 0) {
	  var di1=di[0];
	  if (di1.style.position=='absolute') {
	    di1.style.left=AnchorPosition_getPageOffsetLeft(o);//o.offsetLeft;
	    di1.style.top=AnchorPosition_getPageOffsetTop(o)+parseInt(o.style.height)-2;//o.offsetTop;
	    di1.style.width=o.style.width;
	  }
	}
	
	
	elt=document.getElementById(f);
	if (elt) {
	  elt.focus();
	  INPUTINPROGRESS=true;
	} else {
	  INPUTINPROGRESS=false;
	  INPUTCHANGED=false;
	}
      }	else {
	INPUTINPROGRESS=false;
	INPUTCHANGED=false;
      }
      var actions=xmlres.getElementsByTagName("action");	  
      var actcode=new Array();
      var actarg=new Array();
      for (var i=0;i<actions.length;i++) {
	actcode[i]=actions[i].getAttribute("code");
	actarg[i]=actions[i].getAttribute("arg");
      }
      if (window.receiptActionNotification) window.receiptActionNotification(actcode,actarg);
      if (window.parent && window.parent.receiptActionNotification) window.parent.receiptActionNotification(actcode,actarg);
      if (window.opener && window.opener.receiptActionNotification) window.opener.receiptActionNotification(actcode,actarg);
      ATTRCIBLE=false;
      if (! INPUTINPROGRESS) ATTRREADCIBLE=false;
	  
    } else {
      alert('no status\n'+ATTRREQ.responseText);
      return;
    }
      
  } 
}


function attributeSendAsync(event,menuurl,cible,newval) {
  if (INPROGRESSATTR) return false; // one request only
    // branch for native XMLHttpRequest object
    if (window.XMLHttpRequest) {
        ATTRREQ = new XMLHttpRequest(); 
    } else if (window.ActiveXObject) {
      // branch for IE/Windows ActiveX version
      ATTRREQ = new ActiveXObject("Microsoft.XMLHTTP");
    }
    if (ATTRREQ) {
        ATTRREQ.onreadystatechange = reqEditAttr ;

        ATTRREQ.open("POST", menuurl,true); 
	ATTRREQ.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); 
	ATTRCIBLE=cible;

	if (newval) ATTRREQ.send('value='+encodeURI(newval));
	else ATTRREQ.send('');
	
	
	INPROGRESSATTR=true;
	document.body.style.cursor='progress';	
	return true;
    }    
}

function attributeSend(event,menuurl,cible,newval) {
  if (INPROGRESSATTR) return false; // one request only
    // branch for native XMLHttpRequest object
    if (window.XMLHttpRequest) {
        ATTRREQ = new XMLHttpRequest(); 
    } else if (window.ActiveXObject) {
      // branch for IE/Windows ActiveX version
      ATTRREQ = new ActiveXObject("Microsoft.XMLHTTP");
    }
    var BOUNDARY='--------Eric TYYOUPLABOOM7893';
    if (ATTRREQ) {
      //ATTRREQ.onreadystatechange = reqEditAttr ;

        ATTRREQ.open("POST", menuurl,false); 
	//ATTRREQ.setRequestHeader("Content-type", "application/x-www-form-urlencoded");  
	ATTRREQ.setRequestHeader("Content-Type", "multipart/form-data; boundary=\"" + BOUNDARY +"\"");
	ATTRCIBLE=cible;
	if (newval) { 
	  var bs = new String("\r\n--" + BOUNDARY + "\r\n");
	  bs += "Content-Disposition: form-data; name=\"value\"\r\n\r\n";
	  bs += newval;
	  bs += "\r\n";
	  ATTRREQ.send(bs);
	} else ATTRREQ.send('');
	
	
	INPROGRESSATTR=false;
	
	if(ATTRREQ.status == 200) {
	   
	  if (ATTRREQ.responseXML) reqNotifyEditAttr(ATTRREQ.responseXML);
	  else {
	    //alert('no xml\n'+ATTRREQ.responseText);
	    return;
	  } 
	}
	
	return true;
    }    
}


// modjsft : default is modattr : the js function to call on save button
function editattr(event,docid,attrid,cible,modjsft) {

  var w,h;
  if (cible) {
    if (INPUTINPROGRESS) {
      if (INPUTCHANGED) {
	var odocid=INPUTCHANGED.getAttribute('docid');
	var oattrid=INPUTCHANGED.getAttribute('attrid');
	var onewval=INPUTCHANGED.value;
	
	modattr(event,odocid,oattrid,onewval);
	INPUTCHANGED=false;
      }
      if (ATTRREADCIBLE) ATTRREADCIBLE.style.display='';
    }
    cible.parentNode.insertBefore(DIVATTR,cible);
    ATTRREADCIBLE=cible;
    w=getObjectWidth(ATTRREADCIBLE);

    if (w < 120) w=120;
    h=getObjectHeight(ATTRREADCIBLE);
    if (h < 20) h=20;
    
    DIVATTR.innerHTML='progress...';
    ATTRREADCIBLE.style.display='none';
    DIVATTR.style.display='';
    DIVATTR.style.height=h+'px';
    DIVATTR.style.width=w+'px';
  }
  CINPUTDOCID=docid;
  var menuurl=corestandurl+'&app=FDL&action=EDITATTRIBUTE&docid='+docid+'&attrid='+attrid+'&modjsft='+modjsft;
  attributeSend(event,menuurl,DIVATTR);
}
function modattr(event,docid,attrid,newval) {

 
    DIVATTR.innerHTML='';
    DIVATTR.style.display='none';
  
    var menuurl=corestandurl+'&app=FDL&action=MODATTRIBUTE&docid='+docid+'&attrid='+attrid;

    attributeSend(event,menuurl,ATTRREADCIBLE,newval);
}
function cancelattr(event,docid,attrid) { 
    DIVATTR.innerHTML='';
    DIVATTR.style.display='none';
  
    var menuurl=corestandurl+'&app=FDL&action=MODATTRIBUTE&docid='+docid+'&attrid='+attrid+'&value=.';

  attributeSend(event,menuurl,ATTRREADCIBLE);
}
function unlockonunload(event) { 
  if (INPUTINPROGRESS &&(CINPUTDOCID > 0) ) {
    var menuurl=corestandurl+'&app=FDL&action=MODATTRIBUTE&docid='+CINPUTDOCID;
    
    attributeSend(event,menuurl,ATTRREADCIBLE);
  }
}

addEvent(window,"unload",unlockonunload)
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
