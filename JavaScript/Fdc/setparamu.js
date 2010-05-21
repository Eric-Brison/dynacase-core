
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

function setparamu(appname,parname,parval) {
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
    r.open("GET", corestandurl+'&app=FDC&action=SETPARAMU&appname='+appname+'&parname='+parname+'&parval='+parval,false);   
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
	  //displayobject(o);
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
  var ovalues=getdocvalues(docid);

  if (ovalues) {
    if (ovalues[attrid]) return ovalues[attrid];
  }
  return false;
}
function displayobject(o) {
  var nn;
  for (var n in o) nn +=n + ':' + o[n] +"\n";
  alert(nn);
}
