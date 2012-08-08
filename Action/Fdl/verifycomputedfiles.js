
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

var XHT_FILES;

function verifycomputedfiles(docid) {
  var corestandurl='?sole=Y';
  // branch for native XMLHttpRequest object
  if (window.XMLHttpRequest) {
    XHT_FILES = new XMLHttpRequest(); 
  } else if (window.ActiveXObject) {
    // branch for IE/Windows ActiveX version     
    XHT_FILES = new ActiveXObject("Microsoft.XMLHTTP");
  }
  if (XHT_FILES) {     
    XHT_FILES.onreadystatechange = XMLprocessverificationfiles;
    XHT_FILES.open("GET", corestandurl+'&app=FDL&action=VERIFYCOMPUTEDFILES&id='+docid,true);   
    XHT_FILES.send('');
  }  	
  return true;  
}



function XMLprocessverificationfiles() {  
  if (XHT_FILES.readyState == 4) {    
    if (XHT_FILES.status == 200) {
      // ...processing statements go here...
      if (XHT_FILES.responseXML) {
	var xmlres=XHT_FILES.responseXML;
	var elts = xmlres.getElementsByTagName("status");
	if (elts.length == 1) {
	  var elt=elts[0];
	  var code=elt.getAttribute("code");
	  var docid=elt.getAttribute("docid");
	  var w=elt.getAttribute("warning");
	  
	  if (w != '') alert(w);
	  if (code != 'OK') {
	    alert('code not OK\n'+req.responseText);
	    return;
	  }
	  var values=xmlres.getElementsByTagName("file");
	  var needverify=false;
	  var state,vid;
	  if (! XHT_FILES.files) XHT_FILES.files=[];
	  for (var i=0;i<values.length;i++) {
	    state=values[i].getAttribute('status');
	    vid=values[i].getAttribute('id');
	    if ((state=='3')||(state=='2')) needverify=true;
	    XHT_FILES.files[vid]=state;
	  }
	  if (needverify) {
	    setTimeout(function() { verifycomputedfiles(docid); }, 5000);
	  } else {	    
		  var title1,vid,isimg,icon1;
		  var ta,hr,j,r,timg,ii,i,vid,avid;
		  for ( i=0;i<values.length;i++) {
			  state=values[i].getAttribute('status');
			  vid=values[i].getAttribute('id');
			  if ((state=='1') || (parseInt(state) < 0)) {
				  r=new RegExp("vid="+vid,"g");
				  title1=getTagContent(values[i],'title');
				  icon1=getTagContent(values[i],'icon');
				  ta=document.getElementsByTagName("a");

				  for (j=0;j<ta.length;j++) {
					  hr=ta[j].getAttribute('href');
					  avid=ta[j].getAttribute('vid');
					  if (avid==vid) {
						  isimg=false;
						  timg=ta[j].getElementsByTagName("img");		      
						  for (ii=0;ii<timg.length;ii++) {
							  if (r.test(timg[ii].src)) {
								  isimg=true;
								  timg[ii].src=timg[ii].src+'&r=1';
							  }
						  }
						  if (!isimg) {
							  if (icon1) 	ta[j].innerHTML='<img class="mime" src="'+icon1+'"> '+title1;
							  else ta[j].innerHTML=title1;
							  ta[j].setAttribute('href',ta[j].getAttribute('_href_'));
							  ta[j].removeAttribute('onclick');
						  }

					  }
				  }
			  }  
		  }
		  //	    alert('ok');
	  }	    
	}
      }


    } else {
      alert("There was a problem retrieving the XML data:\n" +
	    XHT_FILES.statusText+' code :'+XHT_FILES.status);
      return;
    }
  } 
}

function getTagContent(o,tag) {
  to=o.getElementsByTagName(tag);
  if (to.length==1) {

    return to[0].firstChild.nodeValue;
  }
}
