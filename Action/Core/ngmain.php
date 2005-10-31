<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: ngmain.php,v 1.2 2005/10/31 14:05:56 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */


// update paramv set val='CORE:NGHEADER:55|CORE:NGMAIN:*|CORE:NGFOOTER:40' where name='CORE_FRONTPAGE';

function ngmain(&$action) {
  $action->parent->addCssRef("WGCAL:WGCAL.CSS",true);
  $action->parent->addCssRef("CORE:NG.CSS",true);
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
}
?>

