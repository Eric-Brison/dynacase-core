
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
			var nt=document.getElementById('newstate');
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
	while (pnode && ((pnode.nodeType != 1) || (pnode.name != '_se_keys[]'))) pnode = pnode.previousSibling;

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
	var so, i;
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
	var ctypes,i;
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
	}

	// search brother select input
	pnode=p.nextSibling;
	while (pnode && ((pnode.nodeType != 1) || (pnode.tagName != 'TD'))) pnode = pnode.nextSibling;

 
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
		if ((ctype=='') || (ctype.indexOf(atype)>=0)) {
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
	while (pnode && ((pnode.nodeType != 1) || (pnode.tagName != 'TD'))) pnode = pnode.nextSibling;
	// now enum
	if ((atype=='enum') || (atype=='enumlist')) {
		se=document.getElementById('selenum'+aid);
		if (se && pnode) {
			pnode.innerHTML='';
			sec=se.cloneNode(true);
			sec.name='_se_keys[]';
			sec.id='';
			pnode.appendChild(sec);
		}
	} else if(atype == 'docid') {
		se=document.getElementById('thekey');
		if (se && pnode) {
			if(!egaloperator) {
				sec=se.cloneNode(true);
				sec.name='_se_keys[]';
				sec.id='';
				pnode.innerHTML='';
				pnode.appendChild(sec);
			}
			else {
				var famid;
				if(document.getElementById('famid')) {
					famid = document.getElementById('famid').value;
				}
				if(famid) {
					var html = '<input type="hidden"  name="_se_keys[]" id="'+aid+'" value="">';
					html += '<input autocomplete="off" autoinput="1" onfocus="activeAuto(event,'+famid+',this,\'\',\''+aid+'\',\'\')"   onchange="addmdocs(\'_'+aid+'\')" type="text" name="_ilink_'+aid+'" id="ilink_'+aid+'" value="">';
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
		if (se && pnode) {
			sec=se.cloneNode(true);
			sec.name='_se_keys[]';
			sec.id='';
			pnode.innerHTML='';
			pnode.appendChild(sec);
		}
	}
  
}
