<?php

/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */


var $defaultview="FDL:VIEWEMESSAGE";

function viewemessage($target="_self",$ulink=true,$abstract=false) {
  include_once("FDL/Lib.Dir.php");
  $this->viewdefaultcard($target,$ulink,$abstract);


  $from=$this->getValue("emsg_from");
  if (preg_match("/<([^>]*)>/",$from,$erg)) {
    $from=$erg[1];
  }
  $this->lay->set("hasphoto",false);
  $filter1="us_mail='".pg_escape_string($from)."'";
  $filter2="us_homemail='".pg_escape_string($from)."'";
  $filter[]="$filter1 or $filter2";
  $tdir=getChildDoc($this->dbaccess,0,"0",1,$filter,1,"LIST","USER");
  if (count($tdir)==1) {

    $vphoto=$tdir[0]->getValue("us_photo");
    if ($vphoto) {
      $photo=$tdir[0]->GetHtmlAttrValue("us_photo");
      $this->lay->set("photo",$photo);
      $this->lay->set("hasphoto",($photo!=""));
    }
  }
  $hashtml=($this->getValue("emsg_htmlbody")!="");

  $this->lay->set("hashtml",$hashtml);
  
  $this->lay->set("TO",false);
  $this->lay->set("CC",false);

  $recips=$this->getTValue("emsg_recipient");
  $reciptype=$this->getTValue("emsg_sendtype");
  $tto=array();
  $tcc=array();
  $tbcc=array();
  foreach ($recips as $k=>$addr) {
    $addr=str_replace(array("<",">"),array("&lt;","&gt;"),$addr);
    if ($reciptype[$k]=="cc") $tcc[]=$addr;
    elseif ($reciptype[$k]=="bcc") $tbcc[]=$addr;
    else $tto[]=$addr;
  }

  if (count($tto)>0) {
    $this->lay->set("TO",implode("; ",$tto));
  }
  if (count($tcc)>0) {
    $this->lay->set("CC",implode("; ",$tcc));
  }
  

}

/**
 * force no edition
 */
function control($aclname) {
  if (($this->id > 0) && ($this->doctype!='C') &&($aclname=="edit")&&($this->getParamValue("emsg_editcontrol")!="freeedit")) return _("electronic messages cannot be modified");
  else return parent::control($aclname);
}

?>