<?php
/**
 * Display main interface for address book
 *
 * @author Anakeen 2005 
 * @version $Id: faddbook_frame.php,v 1.3 2005/11/24 13:48:17 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
 /**
 */

function faddbook_frame(&$action) {


  $f1=$action->getParam("USERCARD_FIRSTFAM","USER");
  $f2=$action->getParam("USERCARD_SECONDFAM");

  $action->lay->set("F1",$f1);

  $action->lay->set("F2",$f2);        
  $action->lay->set("HasF2",($f2 != ""));

}
?>
