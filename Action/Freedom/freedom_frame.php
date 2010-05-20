<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_frame.php,v 1.4 2005/03/24 15:06:56 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */


//
// ---------------------------------------------------------------

// -----------------------------------
function freedom_frame(&$action) {
// -----------------------------------
  $mode=$action->getParam('FREEDOM_VIEWFRAME',"navigator");

  if ($mode=="folder") {
    $action->lay=new Layout("FREEDOM/Layout/freedom_frame_folder.xml", $action);
  }

  $dirid=GetHttpVars("dirid",0); // root directory
  
  $action->lay->Set("dirid",$dirid);

}
?>
