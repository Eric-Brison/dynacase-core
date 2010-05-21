
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

// $Id: inserthtml.js,v 1.14 2008/09/01 17:28:40 marc Exp $

var ANAKEENBOUNDARY='--------Anakeen www.anakeen.com 2008';

var REQINSERTHTML; // the request

var INSERTINPROGRESS=false;

var THEINSERTCIBLE=false; // object where insert HTML code
var SYNCHRO=false; // send synchro mode
var _SYNCHRO=0; // work variable to memorise previous synchro mode

// send generic request
function requestUrlSend(cible,url, pvars) {
  // pvars => array of object { param:.... value:.... }
  var bsend = '';
  //     if (INSERTINPROGRESS) alert('request aborted:\n'+url);
  if (INSERTINPROGRESS) return false; // one request only

  // branch for native XMLHttpRequest object
  if (window.XMLHttpRequest) {
    REQINSERTHTML = new XMLHttpRequest(); 
  } else if (window.ActiveXObject) {
    // branch for IE/Windows ActiveX version
    isIE = true;
    REQINSERTHTML = new ActiveXObject("Microsoft.XMLHTTP");
  }

  if (REQINSERTHTML) {
      if (! SYNCHRO) REQINSERTHTML.onreadystatechange = XmlInsertHtml;
      
      REQINSERTHTML.open("POST", url, (!SYNCHRO));     	
      if (!pvars) {
	REQINSERTHTML.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); 
      //      REQINSERTHTML.setRequestHeader("Content-Length", "0");
      } else {
       
	var params = '';
	var ispost = false;
	REQINSERTHTML.setRequestHeader("Content-Type", "multipart/form-data; boundary=\"" + ANAKEENBOUNDARY +"\"");
	for (var ip=0; ip<pvars.length; ip++) {
	  bsend += "\r\n--" + ANAKEENBOUNDARY + "\r\n";
	  bsend += "Content-Disposition: form-data; name=\""+pvars[ip].param+"\"\r\n\r\n";
	  bsend += pvars[ip].value;
	  bsend += "\r\n";
	}
     }
      globalcursor('progress');
      THEINSERTCIBLE=cible;
     
     if (bsend.length==0) REQINSERTHTML.send('');
     else REQINSERTHTML.send(bsend);

      if (SYNCHRO) {
	INSERTINPROGRESS=false;
	//	clipboardWait(cible); // not visible in synchro
	unglobalcursor();
	if (REQINSERTHTML.status == 200) {
	  insertXMlResponse(REQINSERTHTML.responseXML);	  
	}
      } else {
	INSERTINPROGRESS=true;	
	clipboardWait(cible);
      }
    }
  return true;
}

function XmlInsertHtml() {
  INSERTINPROGRESS=false; 
  //document.body.style.cursor='auto';
  if (REQINSERTHTML.readyState == 4) {
    unglobalcursor();
    // only if "OK"
    //dump('readyState\n');
    if (REQINSERTHTML.status == 200) {
      // ...processing statements go here...
      insertXMlResponse(REQINSERTHTML.responseXML);
    } else {
      alert("There was a problem retrieving the XML data:\n" +
	    REQINSERTHTML.statusText+' code :'+REQINSERTHTML.status);
      return;
    }
  } 
}

function insertXMlResponse(xmlres) {  
  var o=THEINSERTCIBLE;
  if (xmlres) {            
    var elts = xmlres.getElementsByTagName("status");
    if (elts.length == 1) {
      var elt=elts[0];
      var code=elt.getAttribute("code");
      var delay=elt.getAttribute("delay");
      var c=elt.getAttribute("count");
      var w=elt.getAttribute("warning");

      if (w != '') alert(w);
      if (code != 'OK') {
	alert('code not OK\n'+REQINSERTHTML.responseText);
	return;
      }
      elts = xmlres.getElementsByTagName("branch");
      if (elts && (elts.length>0)) {
	elt=elts[0].firstChild.nodeValue;
	if (o) {
	  //	      if (c > 0)       o.style.display='';
	  o.innerHTML=elt;
	}
      }
      var actions=xmlres.getElementsByTagName("action");
      if (actions.length >0) {
	var actname=new Array();
	var actdocid=new Array();
	for (var i=0;i<actions.length;i++) {
	  actname[i]=actions[i].getAttribute("name");
	  actdocid[i]=actions[i].getAttribute("docid");
	}
	if (sendActionNotification) sendActionNotification(actname,actdocid);
      }

      if (! isNetscape) correctPNG();

    } else {
      if (REQINSERTHTML.responseText!='') insertHTMLResponse(REQINSERTHTML.responseText); 	 
      else  alert('no status for insertXMlResponse\n'+elts.length+'\n'+REQINSERTHTML.responseText);
    }
  } else {
    if (REQINSERTHTML.responseText!='') insertHTMLResponse(REQINSERTHTML.responseText); 	 
    else  alert('no status for insertXMlResponse\n'+elts.length+'\n'+REQINSERTHTML.responseText);
  }
}

function insertHTMLResponse(htmlres) {  
  var o=THEINSERTCIBLE;
  if (htmlres) {      
    if (o) {	      
      o.innerHTML=htmlres;
      var s=o.getElementsByTagName('script');
      if (s.length==0) {
	var myRe = /\<script[^\>]*\>([\w\W]*)\<\/script\>/g;
	myRe.multiline = true;
	var st = '';
	var tsc = myRe.exec(htmlres);
	if (tsc && tsc.length>1)  {
	  for (var it=1; it<tsc.length; it++) {
            eval(tsc[it]);
            // alert(it+' =>  CONTENU = ['+tsc[it]+']');
          }
        }
      }
      var h=document.getElementsByTagName('head');
      var thehead=h[0];
      // alert(h.length);
      for (var i=0;i<s.length;i++) {
	//	document.write('<script>'+s[i].innerHTML+'</script>');
	//	eval('document.activestate=function(event) { return true;}');
	eval(s[i].innerHTML);
      //	alert(s[i].innerHTML);
      //	alert(document.head);
      //	thehead.appendChild(s[i]);
	//	alert(s[i].firstChild.nodeValue);
      }
    }
  }	
}
function clipboardWait(o) {
  //  if (o) o.innerHTML='<table style="width:100%;height:100%"><tr><td align="center"><img style="width:30px"  src="Images/loading.gif"></tr></td></table>';

 if (o) o.innerHTML='<div style="text-align:center"><img style="width:30px"  src="Images/loading.gif"></div>';

}
/**
 * set SYNCHRO to true
 */
function enableSynchro() {
  _SYNCHRO++;
  SYNCHRO=true;
}
/**
 * set SYNCHRO to previous value (false generally)
 */
function disableSynchro() {
  _SYNCHRO--;
  if (_SYNCHRO <= 0) {
    SYNCHRO=false;
    _SYNCHRO=0;
  }
}
