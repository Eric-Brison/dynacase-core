<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: usercard_init.php,v 1.5 2003/08/18 15:47:04 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: usercard_init.php,v 1.5 2003/08/18 15:47:04 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Usercard/usercard_init.php,v $
// ---------------------------------------------------------------
include_once("FDL/import_file.php");
// -----------------------------------
function usercard_init(&$action) {
  // -----------------------------------

  

  add_import_file($action, 
    		    $action->GetParam("CORE_PUBDIR")."/USERCARD/init.freedom");
    
}
?>
