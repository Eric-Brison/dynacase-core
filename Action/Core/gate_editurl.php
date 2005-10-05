<?php
/**
 * Edit Url for gate
 *
 * @author Anakeen 2000 
 * @version $Id: gate_editurl.php,v 1.3 2005/10/05 14:38:07 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

/
// -----------------------------------
function gate_editurl(&$action) {

  $url = $action->GetParam("GATE_URL");


  // url set
  $turl=explode(",",$url);
  $action->lay->set("urlG11",$turl[0]);
  $action->lay->set("urlG12",$turl[1]);
  $action->lay->set("urlG21",$turl[2]);
  $action->lay->set("urlG22",$turl[3]);
  $action->lay->set("urlG31",$turl[4]);
  $action->lay->set("urlG32",$turl[5]);

}
?>