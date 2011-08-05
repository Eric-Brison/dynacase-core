
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

//          ---------------------------------------------------------------------------------
// for idoc type documents 




function editidoc(idattr,xmlid,idocfam,zone) {

  var xml_element = document.getElementById(xmlid);
  var fxml = document.getElementById('fidoc');


  subwindow([FDL_VD2SIZE],[FDL_HD2SIZE],idattr,'');    
  /*
    if (!exist){
    alert("exist pas");
    }
    if (exist){
    alert("existe");
    }
  */
  fxml.famid.value=idocfam;
  fxml.attrid.value=idattr;
  fxml.type_attr.value='idoc';


  fxml.xml.value=xml_element.value;
  fxml.action ="[CORE_STANDURL]&app=FREEDOM&action=FREEDOM_IEDIT2";
  if (zone)  fxml.action =  fxml.action + '&zone='+zone;
  fxml.target=idattr;

  fxml.submit();

}


function hasOpener() {
  if (window.opener) {
    if (!window.opener.closed) return true;
  }
  if (confirm('[TEXT:Master document has been closed.\nClose window ?]')) window.close();
  return false;
}



///////for workflow familly dcocuments/////////////////////
function edit_transition(id){
var i=0;
var nom;
var noms_etats="";
var iddoc=id;

var tr =document.getElementById('tbodywor_etat');
liste=tr.getElementsByTagName("input");

for (var i=0;i<liste.length;i++){
hey=liste[i].id;
if (hey.indexOf("wor_nometat")==0){


	result=hey.split("wor_nometat");
	idetat=document.getElementById("wor_idetat"+result[1]);
	//alert(idetat.value);
	if (idetat.value!=""){
		noms_etats=noms_etats.concat(liste[i].value);
		noms_etats=noms_etats.concat(":"+idetat.value+",");
	}
	else{
	alert("utilisez l'aide a la saisie pour l'etat "+liste[i].value);
	}
}
}
//pour la derniere virgule en trop
noms_etats=noms_etats.substring(0,noms_etats.length-1);
//alert(noms_etats);



var typetrans=document.getElementById('listidoc_wor_tt');

valuestt="";
for (i=0;i<typetrans.length;i++){
	if (typetrans.options[i].value!=""){
	valuestt=valuestt.concat(typetrans.options[i].text);
	valuestt=valuestt.concat("*");
	valuestt=valuestt.concat(typetrans.options[i].id);
	//alert(typetrans.options[i].text);
	}
	else{
	alert("veuillez editer le nouveau type transition pour qu'il soit pris en compte");
	}

if ((i+1)!=typetrans.length){
valuestt=valuestt.concat(",");
}
}
//alert(valuestt);


subwindowm(600,600,'editransition',"[CORE_STANDURL]&app=FREEDOM&action=EDITRANSITION&docid="+iddoc+"&state="+noms_etats+"&tt="+valuestt);

}

function search_args(famid){
  //window.parent.document.getElementById("frameset").cols="50%,50%";
var id=document.getElementById("ai_idaction");
var attrid=document.getElementById("idattr").value;
var titre=document.getElementById("ba_title").value;
var nom=document.getElementById("ai_action").value;
//alert(xml_initial.value);
subwindowm(600,600,"deux","[CORE_STANDURL]&app=FREEDOM&action=RECUP_ARGS&docid="+id.value+"&titre="+titre+"&nom_act="+nom+"&attrid="+attrid+"&famid="+famid);

}


function view_second_frame(){
var  frameset =parent.document.getElementById("frameset");
frameset.cols="50%,50%";

//frameset.firstChild.nextSibling.noresize=0;
//document.getElementById("deux").noresize=0;
//parent.document.getElementById("deux").frameborder=1;
frameset.frameborder=1;

}




function doing(func,Args){

var tabArgs =Args.split(",");
//alert(tabArgs);
//alert(tabArgs.length);
try{

func.apply(null,tabArgs);
}
catch (e){
alert(e);
//alert("la fonction javascript associe a l'evenement  n'existe pas");
}

}

