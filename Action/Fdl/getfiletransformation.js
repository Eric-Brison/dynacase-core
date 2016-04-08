
/**
 * @author Anakeen
 */

var XHR_TID;


var BEGDATEPDF=new Date();
function verifytid(tid) {
  var corestandurl='?sole=Y';
  // branch for native XMLHttpRequest object
  if (window.XMLHttpRequest) {
    XHR_TID = new XMLHttpRequest(); 
  } else if (window.ActiveXObject) {
    // branch for IE/Windows ActiveX version     
    XHR_TID = new ActiveXObject("Microsoft.XMLHTTP");
  }
  if (XHR_TID) {  
    var nd=new Date();
    XHR_TID.onreadystatechange = XMLprocesstid;
    XHR_TID.open("GET", corestandurl+'&app=FDL&action=GETFILETRANSSTATUS&tid='+tid,true);   
    XHR_TID.send('');
  }  	
  return true;  
}

function XMLprocesstid() {  
  if (XHR_TID.readyState == 4) {    
    if (XHR_TID.status == 200) {
      // ...processing statements go here...
      if (XHR_TID.responseXML) {
	var xmlres=XHR_TID.responseXML;
	var elts = xmlres.getElementsByTagName("status");
	if (elts.length == 1) {
	  var elt=elts[0];
	  var code=elt.getAttribute("code");
	  var w=elt.getAttribute("warning");
	  
	  if (w != '') alert(w);
	  if (code != 'OK') {
	    alert('code not OK\n'+XHR_TID.responseText);
	    return;
	  }
	  //	  alert(XHR_TID.responseText);
	  var values=xmlres.getElementsByTagName("tid");
	  var needverify=false;
	  var state;
	  var otid=values[0];
	  var message;
	  state=otid.getAttribute('status');
	  if ((state!='D') && (state!='K')) needverify=true;	
	  tid=otid.getAttribute('id');
	  var e=document.getElementById('status');
	  if (e) e.innerHTML=state;
	  values=xmlres.getElementsByTagName("statusmsg");
	  message=values[0].firstChild.nodeValue;

	  e=document.getElementById('message');
	  if (e) e.innerHTML=message;
	  
	  if (needverify) {
	    var so=document.getElementById('counter');
	    var nd=new Date();
	    var ndt=nd.getTime();

	    nd.setTime(ndt - BEGDATEPDF.getTime());
	    if (so) so.innerHTML=nd.getUTCHours()+'h ' +nd.getUTCMinutes()+'min ' +nd.getUTCSeconds()+'s';
	    setTimeout(function() { verifytid(tid) }, 4000);
	  } else {
	    document.getElementById('loading').style.display='none';
	    if (state=='D') window.location.href=window.location.href+'&tid='+tid;
	  }
	}
      }


    } else {
      alert("There was a problem retrieving the XML data:\n" +
	    XHR_TID.statusText+' code :'+XHR_TID.status);
      return;
    }
  } 
}
