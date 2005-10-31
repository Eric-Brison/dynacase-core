<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: ng_motd.php,v 1.1 2005/10/31 15:33:36 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */

function ng_motd(&$action) {

  $motd = $action->getParam("CORE_MOTD", "");

  $motdv = ($motd==""?"&lt;br&gt;":$motd);

  $canEdit = false; // true;
  $action->lay->set("canEdit", $canEdit);
  $action->lay->set("havemotd", ($canEdit || $motd!="" ? true : false));

  $action->lay->set("motd", $motd);
  $action->lay->set("motdv", $motdv);

  $action->lay->set("appid", $action->parent->GetIdFromName("CORE"));

}
?>