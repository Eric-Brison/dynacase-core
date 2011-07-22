<?php
/**
 * Set RSS usable for all users
 *
 * @author Anakeen 2000 
 * @version $Id: setsysrss.php,v 1.1 2006/11/27 11:43:04 marc Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
 /**
 */
include_once("FDL/Class.Doc.php");

function setsysrss(&$action) {
  // Set the globals elements

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);        // document to edit
  $rss = new_Doc($dbaccess,$docid);
  if (is_object($rss) && $rss->isAffected()) {
    if ($rss->getValue("gui_sysrss")=="yes") {
      $rss->setValue("gui_sysrss", "no");
      $msg = _("rss unavaible for users");
    } else {
      $rss->setValue("gui_isrss", "yes");
      $rss->setValue("gui_sysrss", "yes");
      $msg = _("rss avaible for users");
    }
    AddWarningMsg($msg);
    $rss->modify(true,array("gui_isrss", "gui_sysrss"),true);
  }
  redirect($action,"FDL","FDL_CARD&id=$docid",$action->GetParam("CORE_STANDURL"));
}
?>
