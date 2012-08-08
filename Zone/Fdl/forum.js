
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

function forum_openclose(event, eid, forceopen) {
  if (eid==entry_edit) return;
  if (!document.getElementById('fc_'+eid)) return;
  var forum = document.getElementById('fc_'+eid);
  var fimg  = document.getElementById('fi_'+eid);
  if (forum.style.display!='none' && !forceopen) {
    forum.style.display = 'none';
    fimg.src =  '[IMGF:forum_close.gif:0,0,0|COLOR_BLACK]';
  } else {
    forum.style.display = 'block';
    fimg.src =  '[IMGF:forum_open.gif:0,0,0|COLOR_BLACK]';
  }
}


var entry_edit = -1;
var entry_hide = -1;
var entry_change = false;
function forum_edit(event, docid, ref, eid, link) {

  if (!document.getElementById('forum_editform')) {
    alert('pas de forum_editform');
    return;
  }

  forum_cancelEdit(event);
  forum_openclose(event, ref, true);

  var text = '';
  if (eid>0 && document.getElementById('ft_'+eid)) {
    document.getElementById('ft_'+eid).style.display = 'none';
    text = document.getElementById('ft_'+eid).innerHTML;
    entry_hide = eid;
  }
  document.getElementById('foredit_eid').value = eid;
  document.getElementById('foredit_link').value = link;
  document.getElementById('foredit_text').value = text;

  var f_edit = document.getElementById('forum_editform');
  var mark = document.getElementById('fm_'+ref);
  mark.appendChild(f_edit);
  f_edit.style.display = 'block';
  document.getElementById('foredit_text').focus();

  entry_edit = ref;
  stopPropagation(event);
} 

function forum_sendmail(event, addr, docid, eid) {
  stopPropagation(event);
}

function forum_opacity(oid, value) {
  if (!document.getElementById(oid)) return;
  var o = document.getElementById(oid);
  if (isIE) o.style.filter = 'alpha(opacity=' + value + ')';
  else o.style.opacity = value/100;
}


function forum_change(event) {
  entry_change=true;
  addEvent(window,"beforeunload", forum_alertEdit);
  return;
}
function forum_alertEdit(event) {
  if (! event) event=window.event;
  if (entry_change && entry_edit!=-1)  event.returnValue='[TEXT:forum modification are not saved]';
}

function forum_cancelEdit(event) {
  if (! event) event=window.event;
  if (entry_edit!=-1 && entry_change) {
    var ok = confirm('[TEXT:save forum edition] ?');
    if (ok) {
      forum_saveEdit(event);
      return true;
    } 
  }
  forum_clean(event);
  return false;
}

function forum_saveEdit(event) {

  var corestandurl='?sole=Y&';
  enableSynchro();

  var docid = document.getElementById('foredit_docid').value;
  var eid   = document.getElementById('foredit_eid').value;
  var link  = document.getElementById('foredit_link').value;
  var text  = document.getElementById('foredit_text').value;

  var f_edit = document.getElementById('forum_editform');

  var mark = document.getElementById('f_X');
  mark.appendChild(f_edit);
  f_edit.style.display = 'none';

  var url = corestandurl+'app=FDL&action=FDL_FORUMADDENTRY';
  var params = '&docid='+docid+'&start='+entry_edit+'&eid='+eid+'&lid='+link+'&text='+encodeURI(text);

  var ptag = document.getElementById('f_'+entry_edit).parentNode;
  requestUrlSend(document.getElementById('f_'+entry_edit).parentNode, url+params);
  disableSynchro();
  
  forum_clean();
  return;
}
function forum_delete(event, docid, eid, prev) {

  var cprev = prev;
  if (prev==-1) cprev='X';

  var corestandurl='?sole=Y&';
  var url = corestandurl+'app=FDL&action=FDL_FORUMDELENTRY&docid='+docid+'&eid='+eid+'&start='+prev;

  enableSynchro();
  requestUrlSend(document.getElementById('f_'+cprev),url);
  disableSynchro();
  
  stopPropagation(event);
  return;
}

function forum_clean(event) {
  if (entry_hide>0 && document.getElementById('ft_'+entry_hide)) {
    document.getElementById('ft_'+entry_hide).style.display = 'block';
  }
  entry_hide = -1;
  entry_edit = -1;
  entry_change = false;
  var f_edit = document.getElementById('forum_editform');
  if (f_edit) f_edit.style.display = 'none';
  return;
}


var lob = false;
function forum_over(event, eid) {
  var ob = document.getElementById('f_'+eid);
  if (!ob) return;
  if (lob) lob.className = 'forum';
  ob.className = 'forum forum_over';
  lob = ob;
  stopPropagation(event);
}

function forum_out(event, eid) {
  var ob = document.getElementById('f_'+eid);
  if (!ob) return;
  ob.className = 'forum';
  lob = false;
  stopPropagation(event);
}

