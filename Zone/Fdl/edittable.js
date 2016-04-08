
/**
 * @author Anakeen
 */


var isNetscape = navigator.appName=="Netscape";


  


// use to delete an article
function delart(th) {


  th.parentNode.parentNode.parentNode.removeChild(th.parentNode.parentNode);

  return;
  
}
// up article order 
function upart(th) {

  var trnode= th.parentNode.parentNode;
  var pnode = trnode.previousSibling;
  var textnode=false;



  while (pnode && (pnode.nodeType != 1)) pnode = pnode.previousSibling; // case TEXT attribute in mozilla between TR ??

  if (pnode)  {
    trnode.parentNode.insertBefore(trnode,pnode);
    
  }  else {
    trnode.parentNode.appendChild(trnode); // latest (cyclic)
  }


  return;  
}
// down article order 
function downart(th) {

  var trnode= th.parentNode.parentNode;
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



  return;  
}

/**
 * Generate sequence of ids (mimic jQueryUI's uniqueId())
 */
var seqId = (function() {
    var seq = 0;
    return function() {
        return ++seq;
    };
})();
/**
 * Rename elements with 'tpl!' prefix name by stripping the prefix
 * and also apply a new id generated from the new name and
 * a sequential id
 * @param elmt
 */
function renameTplPrefix(elmt) {
    var prefix = 'tpl!';
    var name = undefined;
    var pos;
    var i;
    if (elmt.nodeType == 1 /* Node.ELEMENT_NODE */) {
        name = elmt.getAttribute('name');
        if (name && name.substr(0, prefix.length) == prefix) {
            name = name.substr(prefix.length);
            /* Set new name without prefix */
            elmt.setAttribute('name', name);
            /* Generate new id from new name with sequential id appended */
            pos = name.indexOf("[");
            if (pos >= 0) {
                name = name.substr(0, pos);
            }
            elmt.setAttribute('id', name + '_' + seqId());
        }
    }
    if (elmt.childNodes.length > 0) {
        for (i = 0; i < elmt.childNodes.length; i++) {
            renameTplPrefix(elmt.childNodes[i]);
        }
    }
}

// use to add a sumple texr
function addrow(newrowId,tableId) {
  
  var ntr;
  var elmt = document.getElementById(newrowId);
  if (!elmt) {
      return;
  }
  // need to change display before because IE doesn't want after clonage
  elmt.style.display='';

  ntr = elmt.cloneNode(true);
  renameTplPrefix(ntr);
  elmt.style.display='none';

  ntr.id = '';

  
  ntable = document.getElementById(tableId);
  ntable.appendChild(ntr);

}

function chgivalue(iname, nval) {
  var is=document.getElementsByName(iname+"[]");


  if (is.length > 0)  is[is.length -1].value=nval;
}


// replace s1 by s2 in node n
function  nodereplacestrold(n,s1,s2) {
  
  var kids=n.childNodes;
  var ka;
  var avalue;
  var rs1;
  var attnames = new Array('onclick','href','onmousedown');
  // for regexp
    rs1 = s1.replace('[','\\[');
  rs1 = rs1.replace(']','\\]');
  
  for (var i=0; i< kids.length; i++) {     
    if (kids[i].nodeType==3) { 
      // Node.TEXT_NODE
	
	if (kids[i].data.search(rs1) != -1) {
	  kids[i].data = kids[i].data.replace(s1,s2);
	}
    } else if (kids[i].nodeType==1) { 
      // Node.ELEMENT_NODE
	
	// replace  attributes defined in attnames array
	  for (iatt in attnames) {
	    
	    attr = kids[i].getAttributeNode(attnames[iatt]);
	    if ((attr != null) && (attr.value != null) && (attr.value != 'null'))  {
	      
	      
	      if (attr.value.search(rs1) != -1) {
		
		avalue=attr.value.replace(s1,s2);
		
		if ((attr.name == 'onclick') || (attr.name == 'onmousedown')) kids[i][attr.name]=new Function(avalue); // special for IE5.5+
		else attr.value=avalue;
	      }
	    }
	  }
      nodereplacestrold(kids[i],s1,s2);
    } 
  }
}






function disabledInput(from, value) {
  
    var tin=from.getElementsByTagName("input");
    for (var i=0; i< tin.length; i++) { 
      tin[i].disabled=value;
    }
    tin=from.getElementsByTagName("textarea");
    for (var i=0; i< tin.length; i++) { 
      tin[i].disabled=value;
    }
    tin=from.getElementsByTagName("select");
    for (var i=0; i< tin.length; i++) { 
      tin[i].disabled=value;
    }
}






// nid is the node id which containt one item of each inputs name
function resetInputs(nid) {

  if (isNetscape) { // bug in mozilla 1.1 : when the order of input is different than node order

    var n=document.getElementById(nid);
    if (n) {
      var tin=n.getElementsByTagName("input");
      for (var i=0; i< tin.length; i++) { 
	resetInputsByName(tin[i].name);
      }
      tin=n.getElementsByTagName("textarea");
      for (var i=0; i< tin.length; i++) { 
	resetInputsByName(tin[i].name);
      }
      tin=n.getElementsByTagName("select");
      for (var i=0; i< tin.length; i++) { 
	resetInputsByName(tin[i].name);
      }
    }
  }
}



function getChildIndex(ch) {
  var childId=ch.id;

  
  var childs=ch.parentNode.childNodes;
  var k=0;
  
  for (var i=0; i< childs.length; i++) { 
    
    if (childs[i].id == childId) return k;
    if (childs[i].nodeType == 1) k++;
  }

  return -1;

  
}
