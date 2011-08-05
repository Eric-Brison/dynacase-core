
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

function showInfo(id) {
  var elt;
  for (var i=0; i<lOnglet.length; i++) {
    if (elt = document.getElementById(lOnglet[i])) {
      elt.style.display = 'none';
      document.getElementById('o'+lOnglet[i]).className = 'fabog';
      if (lOnglet[i]==id) {
	elt.style.display = '';
	document.getElementById('o'+lOnglet[i]).className = 'fabogsel';
      }	
    }
  }
  return true;
}
function showAttributes(event,o,tid) {
  var elt,i;
  var to,ltr;
  to=document.getElementById('ttabs');
  if (to) {
    ltr=to.getElementsByTagName('span');
    for (i=0;i<ltr.length ;i++) {
      ltr[i].className='';
    }    
  }
  o.className='tabsel';
  to=document.getElementById('tothers');
  if (to) {
    ltr=to.getElementsByTagName('tr');
    for (i=0;i<ltr.length ;i++) {
      if (ltr[i].className == 'tro') ltr[i].style.display='none';
    }    
  }
  for (i=0; i<tid.length; i++) {
    if (elt = document.getElementById('TR'+tid[i])) {
      elt.style.display = '';
    }
  }
  return true;
}

function viewFirstTab(event) {
  var elt,i;
  var to,ltr;
  to=document.getElementById('ttabs');
  if (to) {
    ltr=to.getElementsByTagName('span');
    if (ltr.length > 0) {
      ltr[0].onclick.apply(ltr[0],[event]);
      ltr[0].className='tabsel';
    }
  }
}
function viewTab(event,idtab) {
  var o;

  o=document.getElementById(idtab);
  if (o) {
	o.onclick.apply(o,[event]);
	o.className='tabsel';
  }
}

