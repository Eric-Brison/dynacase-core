<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Class.SubForm.php,v 1.3 2006/06/20 16:18:07 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// ---------------------------------------------------------------------------
// $Id: Class.SubForm.php,v 1.3 2006/06/20 16:18:07 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Class/Form/Class.SubForm.php,v $
// ---------------------------------------------------------------
// $Log: Class.SubForm.php,v $
// Revision 1.3  2006/06/20 16:18:07  eric
// new font size theme
//
// Revision 1.2  2003/08/18 15:46:42  eric
// phpdoc
//
// Revision 1.1  2002/01/08 12:41:34  eric
// first
//
// Revision 1.3  2000/10/26 12:52:13  yannick
// Bug : perte du mot de passe
//
// Revision 1.2  2000/10/23 14:21:27  marc
// Use of select in forms
//
// Revision 1.1  2000/10/19 16:34:45  yannick
// Pour Marc
//
//

$CLASS_SUBFORM_PHP = '$Id: Class.SubForm.php,v 1.3 2006/06/20 16:18:07 eric Exp $';

Class SubForm
{
// This class is used to produce HTML/JS code when you want to
// create a separate window which exchange values with its parent
// window (for instance an edit/update window or a query window)
var $mainjs='
function submit_withpar(height,width,name,[id],url) {
  subwindow(height,width,name,url+\'&[id]=\'+[id]);
}
';

var $jsmaincall='submit_withpar([height],[width],\'[name]\',\'[id]\',\'[url]\')';

var $mainform='
<form name="[name]" method="POST" action="[url]">
[BLOCK PAR]
  <input type="hidden" name="[name]" value="[val]"> [ENDBLOCK PAR]
</form>
';

var $subjs='
function sendform() {
  var p = self.opener.document.forms.[name];
  var lf = self.document.[name];
[BLOCK PAR]
  if( lf.[name] ) { p.[name].value = lf.[name].value; } [ENDBLOCK PAR]

[BLOCK SEL]
  if( lf.[name] ) { p.[name].value = lf.[name].options[lf.[name].selectedIndex].value; } [ENDBLOCK SEL]
  p.submit();
}';  

var $param = array(); // contains all exchanged vars in the form
                      // "key" => "val" , val is the initial value 
                      // of the key.


function SubForm($name,$width=100,$height=100,$mainurl="",$suburl="") {
  $this->name=$name;
  $this->width=$width;
  $this->height=$height;
  $this->mainurl=$mainurl;
  $this->suburl=$suburl;
}

function SetParams($array) {
  $this->param=array_merge($array,$this->param);
}

function SetParam($key,$val="",$type="") {
  $this->param[$key]["val"]=$val;
  $this->param[$key]["typ"]=$type;
}

function SetKey($key) {
  $this->key = $key;
}

function GetMainForm() {
  $lay = new Layout("",null,$this->mainform);
  $tab=array();
  reset($this->param);
  $c = -1;
  while (list($k,$v) = each($this->param)) {
    $tab[$c]["name"]=$k;
    $tab[$c]["val"]=$v["val"];
    $c++;
  }
  $lay->SetBlockData("PAR",$tab);
  $lay->Set("url",$this->mainurl);
  $lay->Set("name",$this->name);
  return($lay->gen());
}
  

function GetMainJs() {
  $lay = new Layout("",null,$this->mainjs);
  $lay->Set("formname",$this->name);
  $lay->Set("id",$this->key);
  return($lay->gen()); 
}

function GetSubJs() {
  $lay = new Layout("",null,$this->subjs);
  $tab=array();
  reset($this->param);
  $isel = $c = -1;
  while (list($k,$v) = each($this->param)) {
    if ($v["typ"] == "sel" ) {
      $isel++;
      $tabsel[$isel]["name"] = $k;
    } else {
      $c++;
      $tab[$c]["name"]=$k;
    }
  }
  if ($isel>-1) {
    $lay->SetBlockData("SEL",$tabsel);
  } else {
    $lay->SetBlockData("SEL",NULL);
  }
  if ($c>-1) {
    $lay->SetBlockData("PAR",$tab);
  } else {
    $lay->SetBlockData("PAR",NULL);
  }
  $lay->Set("name",$this->name);
  return($lay->gen()); 
}

function GetLinkJsMainCall() {
  $lay = new Layout("","",$this->jsmaincall);
  $lay->Set("url",$this->suburl);
  $lay->Set("width",$this->width);
  $lay->Set("height",$this->height);
  $lay->Set("name",$this->name);
  return($lay->gen());
}

function GetEmptyJsMainCall() {
  $lay = new Layout("","",$this->jsmaincall);
  $lay->Set("id","");
  $lay->Set("url",$this->suburl);
  $lay->Set("width",$this->width);
  $lay->Set("height",$this->height);
  $lay->Set("name",$this->name);
  return($lay->gen());
}

// CLASS END
}
?>
