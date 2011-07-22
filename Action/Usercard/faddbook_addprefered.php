<?php
/**
 * Add a Prefered personns
 *
 * @author Anakeen 2005
 * @version $Id: faddbook_addprefered.php,v 1.5 2005/11/24 13:48:17 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage USERCARD
 */
 /**
 */
function faddbook_addprefered(&$action) {

  $cid = GetHttpVars("cid", -1);
  if ($cid==-1) return;
  $cpref = $action->getParam("FADDBOOK_PREFERED", "");
  $tc = explode("|", $cpref);
  $found = false;
  foreach ($tc as $k => $v) if ($v==$cid) $found = true;
  if (!$found) {
    $tc[] = $cid;
    $stc = implode("|", $tc);
    $action->parent->param->set("FADDBOOK_PREFERED", $stc, PARAM_USER.$action->user->id, $action->parent->id);
  }
  Redirect($action, $action->parent->name, "FADDBOOK_PREFERED");
}
?>
