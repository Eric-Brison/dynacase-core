
/**
 * @author Anakeen
 */

function getsessionid(docid,vid) {
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
    r.open("POST", corestandurl+'&app=DAV&action=GETSESSIONID',false);        
    r.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    r.send('vid='+vid+'&docid='+docid); 
    
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
	    alert('code not OK\n'+r.responseText);
	    return;
	  }

	  var values=xmlres.getElementsByTagName("session");
	  var attrid;
	  var value;
	  var o=new Object();
	  for (var i=0;i<values.length;i++) {
	      if (values[i].firstChild)  value=values[i].firstChild.nodeValue;
	      else value='';
	  }
	  return value;
	
	  
	}
      }
      else {
	//alert('no xml\n'+r.responseText);
      } 
    }    
  }  	
  return false;  
}

function getPrivateDavHref(docid,vid,davHost,fileName) {
	var sid=getsessionid(docid,vid);
	return 'asdav://'+davHost+'/freedav/vid-'+sid+'/'+fileName;
}

