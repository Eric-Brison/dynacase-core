<?php
/**
 * View logo
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_logo.php,v 1.6 2007/03/12 17:35:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
 /**
 */




function freedom_logo(&$action) 
{
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/resizeimg.js");
  $action->lay->Set("appicon",$action->GetImageUrl($action->parent->icon));
  $action->lay->Set("apptitle",$action->parent->description);

}

?>
