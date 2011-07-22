<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Method.PostIt.php,v 1.9 2007/08/06 15:42:25 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage 
 */
 /**
 */




/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class _POSTIT extends Doc {
        /*
         * @end-method-ignore
         */
  
var $defaultview= "FDL:VIEWPOSTIT:T";
var $defaultedit= "FDL:EDITPOSTIT:T";
  
  
// -----------------------------------
function viewpostit($target="_self",$ulink=true,$abstract=false) {
  // -----------------------------------

  $tcomment = $this->getTvalue("PIT_COM");
  $tuser = $this->getTvalue("PIT_USER");
  $tdate = $this->getTvalue("PIT_DATE");
  $tcolor = $this->getTvalue("PIT_COLOR");

  $nbcar=strlen($this->getvalue("PIT_COM"));
  if ($nbcar < 60) $fontsize=120;
  elseif ($nbcar < 200) $fontsize=100;
  else  $fontsize=80;
  $tlaycomment=array();
  while (list($k,$v) = each($tcomment)) {
    $tlaycomment[]=array("comments"=>$this->getHtmlValue($this->getAttribute('PIT_COM'),$v,'_blank'),
			 "user"=>$tuser[$k],
			 "date"=>$tdate[$k],
			 "color"=>$tcolor[$k]);
  }

  $this->lay->set("EMPTY",count($tcomment)==0);
  $this->lay->set("fontsize",$fontsize);
  // Out


  $this->lay->SetBlockData("TEXT",	 $tlaycomment);

}
function editpostit() {
  $this->editattr();
}

function getpostittitle($s) {
  return sprintf(_("postit of %s"),$this->getTitle($s));
}
function PostModify() {
  $docid= $this->getValue("PIT_IDADOC");
  if ($docid > 0) {
    $doc= new_Doc($this->dbaccess, $docid);
    if (intval($doc->postitid) == 0) {
      $doc->disableEditControl();
      $doc->postitid=$this->id;
      $doc->modify();
      $doc->enableEditControl();
    }
  }

  $ncom = $this->getValue("PIT_NCOM");
  if ($ncom != "") {

    $tcom=$this->getTValue("PIT_COM");
    $tdate=$this->getTValue("PIT_DATE");
    $tiduser=$this->getTValue("PIT_IDUSER");
    $tcolor=$this->getTValue("PIT_COLOR");

    foreach ($tcom as $k=>$v) {
      if ($v=="") {
	unset($tcom[$k]);
	unset($tdate[$k]);
	unset($tiduser[$k]);
	unset($tcolor[$k]);
      }
    }
    $nk=count($tcom);
    $tcom[$nk]=$ncom;
    $tdate[$nk]=$this->getDate();
    $tiduser[$nk]=$this->getUserId();
    $tcolor[$nk]=$this->getValue("PIT_NCOLOR");

    $this->setValue("PIT_COM",$tcom);
    $this->setValue("PIT_DATE",$tdate);
    $this->setValue("PIT_IDUSER",$tiduser);
    $this->setValue("PIT_COLOR",$tcolor);
    $this->deleteValue("PIT_NCOLOR");
    $this->deleteValue("PIT_NCOM");

    
  }
}

function PostDelete() {
  $docid= $this->getValue("PIT_IDADOC");
  if ($docid > 0) {
    $doc= new_Doc($this->dbaccess, $docid);
    if ($doc->locked == -1) $doc= new_Doc($this->dbaccess, $doc->latestId());
    if (intval($doc->postitid) > 0) {
      $doc->disableEditControl();
      $doc->postitid=0;
      $doc->modify();
      $doc->enableEditControl();
    }
  }
}

function preCreated() {
  
  $tcomment = $this->getValue("PIT_NCOM");
  if ($tcomment=="") return (_("no message : post-it creation aborted"));
}
/**
        * @begin-method-ignore
        * this part will be deleted when construct document class until end-method-ignore
        */
}

/*
 * @end-method-ignore
 */
?>