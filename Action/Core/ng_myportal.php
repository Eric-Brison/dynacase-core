<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: ng_myportal.php,v 1.1 2005/10/25 08:39:35 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
include_once('WGCAL/Lib.wTools.php');

function ng_myportal(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");
    
  
  $filter = array();
  $filter[] = "ngp_ownerid=".$action->user->fid;
  $portal = getChildDoc($dbaccess, 0, 0, "ALL", $filter, $action->user->id, "LIST", "NPORTAIL");
  if (count($portal)==0) {

    $np = createDoc($dbaccess, "NPORTAIL");
    $np->setValue("ngp_ownerid", $action->user->fid);
    $np->setValue("ngp_owner", $action->user->title);
    $np->Add();
    $np->Modify();

    $idp=$np->id;

  } else {
    
    $idp=$portal [0]->id;

  }
  redirect($action,"FDL","FDL_CARD&id=$idp");
}
?>