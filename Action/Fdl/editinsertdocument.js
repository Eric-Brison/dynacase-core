
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */



function fdl_searchDocs(event,uname,where,famid) {
  var corestandurl=window.location.pathname+'?sole=Y&';
  enableSynchro();

  requestUrlSend(where,corestandurl+'app=FDL&action=SEARCHDOCUMENT&famid='+famid+'&key='+uname+'&noids='+fdl_implodeInputKeyValues('uchange'))
    disableSynchro();
  var n=where.getElementsByTagName('div').length;
  if (n==1) {
    var ld=where.getElementsByTagName('div');
    var d=ld[0];
    d.onclick.apply(d,[event]);
    
  }
}

function fdl_addDoc(event,o,uid,uname,icon) {
  var t=document.getElementById('trtemplate');
  var ntable=document.getElementById('members');
  var ntr;
  if (t) {
    t.style.display='';
    ntr=t.cloneNode(true);
    t.style.display='none';
    ntr.id='';
    ntr.style.display='';
    nodereplacestr(ntr,'jsuname',uname);
    nodereplacestr(ntr,'jsuid',uid);
    nodereplacestr(ntr,'jsicon',icon);
    ntable.appendChild(ntr);
    if (o) o.style.display='none';
    //    alert(ntr.innerHTML);
    document.getElementById('ukey').value='';
    document.getElementById('ukey').focus();
    fld_countDoc();
  }
}

function fdl_implodeInputKeyValues(n) {
  var ti= document.getElementsByTagName("input");    
  var tv = new Array();
  var ni,na;
  var pos;
	
  for (var i=0; i< ti.length; i++) { 
    na=ti[i].name;
    pos=na.indexOf('[');
    if (pos==-1) ni=na;
    else ni=na.substr(0,pos);

    if ((ni == n) && (na.substr(na.length-4,4) != '[-1]')) {
      if (ti[i].value != 'deleted')  tv.push(na.substr(pos+1,na.length-pos-2));
    }
  }
  return(tv.join('|'));
}


function fld_countDoc() {
  var ntable=document.getElementById('members');
  var ti= ntable.getElementsByTagName("tr"); 
  var i,c=0;
  for (i=0;i<ti.length;i++) {
    if (ti[i].style.display!='none') c++;
  }
  
  var sc=document.getElementById('scount');
  sc.innerHTML=c;

}

function fld_deleteDoc(o,initid) {
    o.form['uchange['+initid+']'].value='deleted';
    o.parentNode.parentNode.style.display='none';
    fld_countDoc();
}
