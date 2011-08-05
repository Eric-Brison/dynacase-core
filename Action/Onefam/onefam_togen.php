<?php
/**
 * Redirector for generic
 *
 * @author Anakeen 2000 
 * @version $Id: onefam_togen.php,v 1.8 2007/05/28 08:13:57 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("FDL/Lib.Dir.php");


function onefam_togen(&$action) 
{
 
  $famid = GetHttpVars("famid",0); 
  $gonlylist = GetHttpVars("gonlylist"); 
  $gaction = ""; 
  
  if ($famid == 0) $action->exitError(_("Family is not instanciate"));

			
  if ($gonlylist == "yes") {
    $gapp="GENERIC";
    $gaction="GENERIC_TAB&catg=0&tab=0&famid=$famid";
  } else {
    $gapp=$action->GetParam("APPNAME","ONEFAM");
    $gaction="ONEFAM_GENROOT&famid=$famid";
  }
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new_Doc($dbaccess, $famid);
  if (! $doc->isAffected()) $action->exitError(sprintf(_("Family (#%d) is not referenced"),$famid));
  $action->Register("DEFAULT_FAMILY", $famid);

  redirect($action,$gapp, $gaction);
}

?>
