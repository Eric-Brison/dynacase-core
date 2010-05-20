<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: unlockfile.php,v 1.10 2007/09/27 13:57:11 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: unlockfile.php,v 1.10 2007/09/27 13:57:11 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Fdl/unlockfile.php,v $
// ---------------------------------------------------------------


include_once("FDL/Class.Doc.php");
function unlockfile(&$action) 
{
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);
  $rzone = GetHttpVars("rzone"); // special zone when finish edition
  $rvid = GetHttpVars("rvid"); // special zone when finish edition
  $auto = (GetHttpVars("auto","N")=="Y"); // just auto unlock
  $autoclose = (GetHttpVars("autoclose","N")=="Y"); // close window after



  $doc= new_Doc($dbaccess,$docid);




  $err=$doc->UnLock($auto);
  if ($err != "") $action->ExitError($err);
  

  if (! $auto)      $action->AddLogMsg(sprintf(_("%s has been unlocked"),$doc->title));
  
  $action->AddActionDone("UNLOCKFILE",$doc->id);
    
  if ($autoclose) {    
    header('Content-type: text/xml; charset=utf-8');
  } else {
    $opt="";
    if ($rzone!="") $opt="&zone=$rzone";
    if ($rvid!="") $opt="&vid=$rvid";
    redirect($action,"FDL","FDL_CARD$opt&id=".$doc->id,$action->GetParam("CORE_STANDURL"));
  }
  
}



?>
