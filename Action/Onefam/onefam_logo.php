<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: onefam_logo.php,v 1.3 2003/08/18 15:47:03 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: onefam_logo.php,v 1.3 2003/08/18 15:47:03 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Onefam/onefam_logo.php,v $
// ---------------------------------------------------------------



function onefam_logo(&$action) 
{
  $action->lay->Set("appicon",$action->GetImageUrl($action->parent->icon));
  $action->lay->Set("APP_TITLE", _($action->parent->description));
  $action->lay->Set("apptitle",$action->parent->description);


}

?>
