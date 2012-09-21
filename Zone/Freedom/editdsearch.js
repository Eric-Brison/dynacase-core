
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */


// use when submit to avoid first unused item
function deletenew() {
	if (canmodify(true)) {
		resetInputs('newcond');
		var na=document.getElementById('newcond');
		if (na) na.parentNode.removeChild(na);
		na=document.getElementById('newstate');
		if (na) na.parentNode.removeChild(na);
	}
  
}
  

function sendsearch(faction,artarget) {
	var fedit = document.fedit;
	resetInputs('newcond');
  
	with (document.modifydoc) {
		var editAction=action;
		var editTarget=target;

		enableall();
		var na=document.getElementById('newcond');
		
		if (na) {
			disabledInput(na,true);
		    nt=document.getElementById('newstate');
			if (nt)   disabledInput(nt,true);
		}
		if ((!artarget) &&  (window.parent.fvfolder)) artarget='fvfolder';
		else if ((!artarget) &&  (window.parent.flist)) {
			artarget='flist';
			faction=faction + '&ingeneric=yes';
		} else  if (!artarget) artarget='_blank';
		target=artarget;
		action=faction;
		submit();
		target=editTarget;
		action=editAction;

    
		if (na) {
			disabledInput(na,false);
			if (nt) disabledInput(nt,false);
		}
    
		}
}
function callFunction(event,th) {
	var pnode=getPrevElement(th.parentNode);
	var ex=document.getElementById('example');
	if (pnode) {
		pnode.innerHTML='<input  type="text"  size="20" name="_se_keys[]">';
		pnode.appendChild(ex);
		ex.style.display='';
	}
  
}
function setKey(event,th) {
	var pnode;

	pnode=th.previousSibling;
	while (pnode!=null && ((pnode.nodeType != 1) || (pnode.name != '_se_keys[]'))) pnode = pnode.previousSibling;

	pnode.value = th.options[th.selectedIndex].value;

  
}

function getNextElement(th) {
	var pnode;
	pnode=th.nextSibling;
	while (pnode && (pnode.nodeType != 1)) pnode = pnode.nextSibling;
	return pnode;
  
}

function getPrevElement(th) {
	var pnode;
	pnode=th.previousSibling;
	while (pnode && (pnode.nodeType != 1)) pnode = pnode.previousSibling;
	return pnode;
  
}

function filterfunc2(th) {
	var so=null, i;
	var pnode = th.parentNode.previousSibling;
	while (pnode && ((pnode.nodeType != 1) || (pnode.tagName != 'TD'))) pnode = pnode.previousSibling;
	for (i=0;i<pnode.childNodes.length;i++) {
		if (pnode.childNodes[i].tagName=='SELECT') {
			so=pnode.childNodes[i];
		}
	}
	if(so) {
		filterfunc(so);
	}
}

function filterfunc(th) {
	var p=th.parentNode;
	var opt=th.options[th.selectedIndex];
	var atype=opt.getAttribute('atype');
	var ismultiple=((opt.getAttribute('ismultiple') == 'yes'));
	var i;
	var pnode,so=false;
	var aid=opt.value;
	var sec,se;
	var needresetselect=false,ifirst=0;
	var ex=document.getElementById('example');
	var lc=document.getElementById('lastcell');

	// move to tfoot to not be removed
	if (ex)  {
		ex.style.display='none';
		lc.appendChild(ex);
		for (i=0;i<ex.options.length;i++) {
		    ex.options[i].selected=false;
		}
	}

	// search brother select input
	pnode=p.nextSibling;
	while (pnode!=null && ((pnode.nodeType != 1) || (pnode.tagName != 'TD'))) pnode = pnode.nextSibling;

 
	for (i=0;i<pnode.childNodes.length;i++) {
		if (pnode.childNodes[i].tagName=='SELECT') {
			so=pnode.childNodes[i];
		}
	}


	// display only matches
	ifirst=-1;
	for (i=0;i<so.options.length;i++) {
		opt=so.options[i];
		ctype=opt.getAttribute('ctype');
		if ( (ismultiple && (ctype=='' || ctype.indexOf('array') >= 0)) || (!ismultiple && ((ctype=='') || (ctype.indexOf(atype)>=0))) ) {
			if (ifirst == -1) ifirst=i;
			opt.style.display='';
			opt.disabled=false;
		} else {
			opt.style.display='none';
			if (opt.selected) needresetselect=true;
			opt.selected=false;
			opt.disabled=true;
		}
	}
	if (needresetselect) {
		so.options[ifirst].selected=true;
	}
	var egaloperator = false;
	if(so.value == '=' || so.value == '!=') {
		egaloperator = true;
	}


	// find key cell
	pnode=pnode.nextSibling;
	while (pnode!=null && ((pnode.nodeType != 1) || (pnode.tagName != 'TD'))) pnode = pnode.nextSibling;
	// now enum
	if ((atype=='enum') || (atype=='enumlist')) {
		se=document.getElementById('selenum'+aid);
		if (se!=null && pnode!=null) {
			pnode.innerHTML='';
			sec=se.cloneNode(true);
			sec.name='_se_keys[]';
			sec.id='';
			pnode.appendChild(sec);
		}
	} else if(atype == 'docid') {
		se=document.getElementById('thekey');
		if (se!=null && pnode!=null) {
			if(!egaloperator) {
				sec=se.cloneNode(true);
				sec.name='_se_keys[]';
				sec.id='';
				pnode.innerHTML='';
				pnode.appendChild(sec);
			}
			else {
				var famid=null;
                var sFamid=document.getElementById('sFamid');
				if(sFamid) {
					famid = sFamid.value;
				}
				if(famid) {
                    var xAid=document.getElementById(aid);
                    if (xAid) {
                        xAid.setAttribute('id','');
                    }
                    xAid=document.getElementById('ilink_'+aid);
                    if (xAid) {
                        xAid.setAttribute('id','');
                        xAid.setAttribute('name','');
                    }
                    var dIndex=aid+getNewDocIDIndex();
					var html = '<input type="hidden"  name="_se_keys[]" attrid="'+dIndex+'"  value="">';
					html += '<input autocomplete="off" autoinput="1" onfocus="recycleDocId(\''+aid+'\',\''+dIndex+'\');activeAuto(event,'+famid+',this,\'\',\''+aid+'\',\'\')"   onchange="addmdocs(\'_'+aid+'\')" type="text"  attrid="ilink_'+dIndex+'" value="">';
					pnode.innerHTML= html;
				}
				else {
					sec=se.cloneNode(true);
					sec.name='_se_keys[]';
					sec.id='';
					pnode.innerHTML='';
					pnode.appendChild(sec);
				}
			}
		}
	} else {
		se=document.getElementById('thekey');
		if (se!=null && pnode!=null) {
			sec=se.cloneNode(true);
			sec.name='_se_keys[]';
			sec.id='';
			pnode.innerHTML='';
			pnode.appendChild(sec);
		}
	}
  
}
var DOCIDINDEX=1000;
function getNewDocIDIndex() {
    return DOCIDINDEX++;
}
function recycleDocId(aid, uniqueAid) {
    var xAid=null;
    var xATitle=null;
    var la=document.getElementsByTagName('input');
    for (var i=0;i<la.length;i++) {
        var attrid=la[i].getAttribute('attrid');
        if (attrid == uniqueAid) {
            xAid=la[i];
        } else if (attrid == 'ilink_'+uniqueAid) {
            xATitle=la[i];
        }
    }
    if (xAid && xATitle) {
        var iAid=document.getElementById(aid);
        if (iAid) {
            iAid.setAttribute('id','');
            iAid.setAttribute('name','');
        }
        iAid=document.getElementById('ilink_'+aid);
        if (iAid) {
            iAid.setAttribute('id','');
            iAid.setAttribute('name','');
        }
        xAid.setAttribute('id',aid);
        xATitle.setAttribute('id','ilink_'+aid);
        xATitle.setAttribute('name','_ilink_'+aid);
    }

}