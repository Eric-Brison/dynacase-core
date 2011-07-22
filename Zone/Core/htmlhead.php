<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: htmlhead.php,v 1.4 2005/01/21 17:47:40 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
 /**
 */

// $Id: htmlhead.php,v 1.4 2005/01/21 17:47:40 eric Exp $


include_once('Class.QueryDb.php');
include_once('Class.Application.php');

function htmlhead(&$action) {


  $title = GetHttpVars("title");
  $action->lay->set("TITLE", $title);


}
?>
