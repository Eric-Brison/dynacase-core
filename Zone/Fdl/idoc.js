
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

var isNetscape = navigator.appName=="Netscape";


function adda(attrid,id_select) {
//alert(id_select);
  var sal= document.getElementById(id_select);


  var pos;
  var idx=sal.selectedIndex;
  if (idx == -1) idx=0;

  if (isNetscape) pos=null;
  else  pos=idx+1


//var numid=sal.length+1;
var numid=1;
var id = attrid +numid;

while (document.getElementById(id)!=null){
numid=numid+1;
id=attrid+numid;
}
var new_option=new Option(numid+" : nouveau","", false, true);
new_option.setAttribute("id",id);

//alert(id)


  sal.add(new_option,pos);

 // correctlevel();
}


function edit_list_idoc(idocfam,id_select) {
  var sal= document.getElementById(id_select);

  var idx=sal.selectedIndex;
  if (idx == -1) return;
//alert(idocfam);
var idlist=sal.options[idx].id;
//alert(idlist);
//subwindowm(400,400,idlist,'[CORE_STANDURL]&app=FREEDOM&action=FREEDOM_IEDIT');
  editidoc(idlist,idlist,idocfam,'idoclist');
 
}

function viewlistidoc_in_frame(idocfam,id_select,idframe){
var sal= document.getElementById(id_select);

var idx=sal.selectedIndex;
  if (idx == -1) return;
var idlist=sal.options[idx].id;
viewidoc_in_frame(idframe,idlist,idocfam);//ds editcard.js
}

function rema(id_select) {
  var sal= document.getElementById(id_select);

  var idx=sal.selectedIndex;
  if (idx == -1) return;
  sal.remove(idx);

}
function view(idocfam,id_select) {
  var sal= document.getElementById(id_select);

  var idx=sal.selectedIndex;
  if (idx == -1) return;
//alert(idocfam);
var idlist=sal.options[idx].id;
//alert(idlist);
  viewidoc(idlist,idocfam);


}

function selectalls(ids) {
  var s= document.getElementById(ids);
  var i;
  if (! s.multiple) s.multiple=true;

  for (i=0;i<s.length;i++) {

	s.options[i].selected=true;
//	alert(s.options[i].id);
  }
}

function selectall(){
var headings = document.getElementsByTagName("select");

for (var i=0;i<headings.length;i++){
//mettre des conditions sur l'id de headings[i]. il faut que ce soit de la forme listidoc_$attrname
var listidoc="listidoc";
var h = headings[i].id;
var look;

	if ((look=h.substring(0,listidoc.length))==listidoc){

	selectalls(h);
	}

//alert(look);

}
}

function multiple_for_select(){
var headings = document.getElementsByTagName("select");
//alert("ici");
for (var i=0;i<headings.length;i++){
//mettre des conditions sur l'id de headings[i]. il faut que ce soit de la forme listidoc_$attrname
var listidoc="listidoc";
var h = headings[i].id;
var look;

	if ((look=h.substring(0,listidoc.length))==listidoc){
  	var s= document.getElementById(h);
	s.multiple=true;
	
	}

}

}
