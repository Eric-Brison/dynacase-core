<?php
/**
 * Modify family preferences
 *
 * @author Anakeen 2007
 * @version $Id: generic_modprefs.php,v 1.2 2007/05/04 16:11:40 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("GENERIC/generic_util.php");
function generic_modprefs(&$action) {
  
  $famid  = GetHttpVars("famid");    // family id
  $dirid  = GetHttpVars("dirid");    // last searched
  $dispo  = GetHttpVars("dispo");    // last searched
  $letters  = GetHttpVars("letters");  // want tab letters
  $inherit  = GetHttpVars("inherit");  // search in inherit
  
  
  
  $dbaccess=$action->getParam("FREEDOM_DB");
  $fdoc=new_doc($dbaccess,$famid);
  if (! $fdoc->isAlive()) {
    $action->exitError(sprintf(_("Family (#%s) not exists"),$famid));    
  }
  
  switch ($dispo) {
  case 1:
    $split='V';
    $visu='abstract';
    break;
  case 2:
    $split='H';
    $visu='column';
    break;
  case 3:
    $split='V';
    $visu='column';
    break;
  case 4:
    $split='H';
    $visu='abstract';
    break;
  }
  if ($dispo > 0) {
    setSplitMode($action,$famid,$split);
    setViewMode($action,$famid,$visu);
  }

  if ($letters==1) setTabLetter($action,$famid,'Y');
  else setTabLetter($action,$famid,'N');
  if ($inherit==1) setInherit($action,$famid,'Y');
  else setInherit($action,$famid,'N');

  $action->lay->set("famtitle",$fdoc->title);
  $action->lay->set("famid",$famid);
  $action->lay->set("dirid",$dirid);
  redirect($action,"GENERIC",
	     "GENERIC_TAB&tab=0&dirid=$dirid&famid=$famid",
	     $action->GetParam("CORE_STANDURL"));
}

?>