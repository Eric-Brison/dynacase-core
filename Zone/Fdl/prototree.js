
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

include_js('WHAT/Layout/prototype.js');
function viewtree(event,where,url) {
  if (! where) return 0;

  var element = (event.target) ? event.target : ((event.srcElement) ? event.srcElement : null);//event.element();
  //  if (! event.element) element=Event.extend(event).target;      
  // else  element = event.element();

  if ($(where).visible()) {
    $(where).hide();
    if (element && (element.nodeType == 1)) element.setAttribute('src','Images/b_right.png');
  } else {
    if ($(where).empty()) {      
      var theAjax=   new Ajax.Request(url, {
	onComplete: function(transport) {
	    // yada yada yada
	    $(where).value=transport.responseText;
	    var rep=transport.responseXML;
	
	    var branc=rep.getElementsByTagName("branch");
	    if (branc && (branc.length>0)) {
	      var elt=branc[0].firstChild.nodeValue;
	      if (where) {
		//alert('insert');
		$(where).innerHTML=elt;
		$(where).show();
		if (element && (element.nodeType == 1)) element.setAttribute('src','Images/b_down.png');
	    
	      }
	    }	
	  }
	});
    } else {
      $(where).show();
      if (element && (element.nodeType == 1)) element.setAttribute('src','Images/b_down.png');
    }
  } 
}

function  reloadtree(event,where,url) {

  var element;
    if (! where) return 0;

    if (! event.element) element=Event.extend(event).target;      
    else  element = event.element();
    
  
    if (element && (element.nodeType == 1)) element.setAttribute('src','Images/b_wait.png');

    $(where).hide();
    $(where).innerHTML=''; 
    viewtree(event,where,url);
    event.stop();
	//Event.stop(event);
}
