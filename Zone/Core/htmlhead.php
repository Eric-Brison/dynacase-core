<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: htmlhead.php,v 1.3 2003/08/18 15:46:42 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// $Id: htmlhead.php,v 1.3 2003/08/18 15:46:42 eric Exp $


include_once('Class.QueryDb.php');
include_once('Class.Application.php');

function htmlhead(&$action) {


  $title = GetHttpVars("title");
    
  $action->lay->set("TITLE", $title);


}
?>
