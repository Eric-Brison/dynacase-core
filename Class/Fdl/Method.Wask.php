<?php
/**
 * Ask documents
 *
 * @author Anakeen 2000 
 * @version $Id: Method.SpecialSearch.php,v 1.3 2007/08/01 14:07:12 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
 /**
 */

/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class _WASK extends Doc {
        /*
         * @end-method-ignore
         */
function postConstructor() {
  $this->dacls["answer"]=array("pos"=>31,
			       "description"=>_("need answer"));
  $this->acls[]="answer"; # _("answer")
  }

/**
  * return sql query to search wanted document
  */
function getAskLabels($keys) {
  $tk=array();
  foreach ($keys as $k) {
    $tk[$k]=$this->getAskLabel($k);
  }  
  return $tk;
}

function getAskLabel($keys) {
  $i=array_search($keys,$this->getTValue("was_keys"));
  if ($i!==false) {
    return $this->getTvalue("was_labels","",$i);
  }
  return "";
}

  function DocControl($aclname) {
    return Doc::Control($aclname);
  }

  /**
   * Special control in case of dynamic controlled profil
   */
function Control($aclname) {

  $err= $this->DocControl($aclname);
  if ($err == "") return $err; // normal case

  if ($this->getValue("DPDOC_FAMID") > 0) {
    if ($this->doc) {
      // special control for dynamic users
      if (! isset($this->pdoc)) {
	$pdoc = createTmpDoc($this->dbaccess,$this->fromid);
	$err=$pdoc->Add();
	if ($err != "") return "Wask::Control:".$err; // can't create profil

	$pdoc->setProfil($this->profid, $this->doc);
	$this->pdoc = &$pdoc;
      }     
      $err=$this->pdoc->DocControl($aclname);
    }



  }
  return $err;
}

  
  function Set(&$doc) {
    if (! isset($this->doc) ) {
      $this->doc= &$doc;     
    }
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