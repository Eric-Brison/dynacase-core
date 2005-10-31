<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: ng_motdchange.php,v 1.1 2005/10/31 15:33:36 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
include_once("XML/RSS.php");
include_once("CORE/Lib.Ng.php");

function ng_motdchange(&$action) {

  $motd = GetHttpVars("motd", "");
  if ($motd!="") {
    $pc = new Param($action->dbaccess,array("CORE_MOTD",PARAM_APP,$action->parent->GetIdFromName("CORE")));
    if ($pc->isAffected()) {
      $pc->val=$motd;
      $pc->Modify();
    }
  }
  $action->parent->session->close();
  Redirect($action, "CORE", "NGMAIN");
}
?>
