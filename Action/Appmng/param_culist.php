<?php
/**
 * Edition of user parameters
 *
 * @author Anakeen 2000 
 * @version $Id: param_culist.php,v 1.4 2006/02/17 10:36:53 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage APPMNG
 */
 /**
 */

function param_culist(&$action) {
    

  // reopen a new session to update parameters cache
  $action->parent->session->close();
    
  $action->register("PARAM_ACT","PARAM_CULIST");
  $action->lay->Set("userid",$action->user->id);
    return;
  
  
}
?>
