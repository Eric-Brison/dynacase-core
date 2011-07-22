<?php
/**
 * display interface to insert document in portfolio
 *
 * @author Anakeen 2005
 * @version $Id: foliosearch.php,v 1.1 2005/04/06 16:38:58 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
 /**
 */


// -----------------------------------
function foliosearch(&$action) {
  // -----------------------------------

  // Get all the params      
  $docid=GetHttpVars("id",0); // portfolio id

  $action->lay->set("docid",$docid);

  
}

?>