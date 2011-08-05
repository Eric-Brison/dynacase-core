
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */


function viewidoc(xmlid,idocfam) {

var xml_element = document.getElementById(xmlid);
var fxml = document.getElementById('fviewidoc');
fxml.xml.value=xml_element.value;
fxml.famid.value=idocfam;
fxml.target=xmlid;
fxml.submit();
}

function viewidoc_in_frame(idframe,xmlid,idocfam){
//cette meme fonction se trouve ds viewicard.js et editcard.js

var iframe=document.getElementById('iframe_'+idframe);
iframe.style.display='inline';
var xml_element = document.getElementById(xmlid);
var fxml = document.getElementById('fviewidoc');
fxml.xml.value=xml_element.value;
fxml.famid.value=idocfam;
fxml.target='iframe_'+idframe;
fxml.submit();
  var iclose=document.getElementById('ivc_'+idframe);
  if (iclose) iclose.style.display='';

}


function viewidoc_in_popdoc(event,idframe,xmlid,idocfam){
  //cette meme fonction se trouve ds viewicard.js et editcard.js

  popdoc(event, '');
  var xml_element = document.getElementById(xmlid);
  var fxml = document.getElementById('fviewidoc');
  fxml.xml.value=xml_element.value;
  fxml.famid.value=idocfam;
  fxml.target="POPDOC_ifrm";
  fxml.submit();
  var iclose=document.getElementById('ivc_'+idframe);
  if (iclose) iclose.style.display='';
  
}


function close_frame(idframe){
//cette meme fonction se trouve ds fdl_card.xml,viewicard.xml et editcard.js
  var iframe=document.getElementById('iframe_'+idframe);
  if (iframe) iframe.style.display='none';

  var iclose=document.getElementById('ivc_'+idframe);
  if (iclose) iclose.style.display='none';

}
