<?php
/**
 * View folder containt in icon mode
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_icons.php,v 1.5 2005/08/18 09:16:09 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */




include_once('FREEDOM/freedom_view.php');



// -----------------------------------
// -----------------------------------
function freedom_icons(&$action) {
// -----------------------------------
  // Set the globals elements

  $memo=(getHttpVars("memo","N")=="Y");
  
  if ($memo) $action->parent->param->Set("FREEDOM_VIEW","icon",PARAM_USER.$action->user->id,$action->parent->id);

  viewfolder($action, false);
  


}
?>
