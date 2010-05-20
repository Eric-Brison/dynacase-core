<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: popup.php,v 1.2 2005/09/27 16:16:50 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

function popup(&$action) {
  $folio=GetHttpVars("folio");

  if ($folio) {
    $action->lay->set("ofolio","&folio=$folio");
  } else {
    $action->lay->set("ofolio","");
  }
}
?>