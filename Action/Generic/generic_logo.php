<?php
/**
 * Display logo
 *
 * @author Anakeen 2000 
 * @version $Id: generic_logo.php,v 1.8 2007/01/04 16:44:23 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("GENERIC/generic_util.php"); 

function generic_logo(&$action) 
{
    $action->lay->Set("apptitle","");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/resizeimg.js");
 
    $famid = getDefFam($action);
    if ($famid > 0) {
      $dbaccess = $action->GetParam("FREEDOM_DB");
      $doc=new_Doc($dbaccess,$famid);
      $action->lay->Set("appicon",$doc->getIcon());
      $action->lay->Set("apptitle",$doc->title);
    }
    



}

?>
