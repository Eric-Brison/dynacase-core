<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: ng_setmeteo.php,v 1.1 2005/10/31 15:33:36 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
include_once("CORE/Lib.Ng.php");

function ng_setmeteo(&$action) {

  $dep = GetHttpVars("ndep", -1);
  $myportal = ng_myportal();
  if ($dep>0 && $dep<96) {
    $myportal->setValue("ngp_oth_meteodep", $dep);
    $myportal->Modify();
  }
}
?>
