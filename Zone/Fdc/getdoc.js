
/**
 * @author Anakeen
 */

function getdocvalues(docid,pattrid) {
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
    r.open("GET", corestandurl+'&app=FDC&action=GETDOCVALUES&id='+docid+'&attrid='+pattrid,false);
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
	    return;
	  }

	  var values=xmlres.getElementsByTagName("value");
	  var attrid;
	  var value;
	  var o=new Object();
	  for (var i=0;i<values.length;i++) {
	    attrid=values[i].getAttribute("attrid");
	    if (attrid) {
	      if (values[i].firstChild)  value=values[i].firstChild.nodeValue;
	      else value='';
	      o[attrid]=value;
	    }
	  }
	  // displayobject(o);
	  return o;
	  
	}
      }
      else {
	//alert('no xml\n'+r.responseText);
      } 
    }    
  }  	
  return false;  
}

function getdocvalue(docid,attrid) {
  var ovalues=getdocvalues(docid,attrid);

  if (ovalues) {
    if (ovalues[attrid]) return ovalues[attrid];
  }
  return false;
}

/** 
 * return a single value for an array od docids
 */
function getdocsvalue(tdocid,pattrid) {
  var r;
  var corestandurl='?sole=Y';
  // branch for native XMLHttpRequest object
  if (window.XMLHttpRequest) {
    r = new XMLHttpRequest(); 
  } else if (window.ActiveXObject) {
    // branch for IE/Windows ActiveX version     
    r = new ActiveXObject("Microsoft.XMLHTTP");
  }
  if (r) {     
    var docids=tdocid.join('|');
    var docid;
    r.open("GET", corestandurl+'&app=FDC&action=GETDOCSVALUE&ids='+docids+'&attrid='+pattrid,false);
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
	    return;
	  }

	  var values=xmlres.getElementsByTagName("value");
	  var attrid;
	  var value;
	  var o=new Object();
	  for (var i=0;i<values.length;i++) {
	    attrid=values[i].getAttribute("attrid");
	    docid=parseInt(values[i].getAttribute("docid"));
	    if ((! isNaN(docid)) && (docid > 0)) {
	      if (values[i].firstChild)  value=values[i].firstChild.nodeValue;
	      else value='';
	      o[docid]=value;
	    }
	  }
	  //	   displayobject(o);
	  return o;
	  
	}
      }
      else {
	//alert('no xml\n'+r.responseText);
      } 
    }    
  }  	
  return false;  
}


function displayobject(o) {
  var nn='';
  for (var n in o) nn +=n + ':' + o[n] +"\n";
  alert(nn);
}
