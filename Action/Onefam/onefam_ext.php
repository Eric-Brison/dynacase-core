<?php
/**
 * extjs main interface
 *
 * @author Anakeen 2000 
 * @version $Id: onefam_modpref.php,v 1.8 2006/10/05 09:22:38 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage 
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("ONEFAM/onefam_gettreefamily.php");
include_once("ONEFAM/onefam_ext_displayconfig.php");

function onefam_ext(Action &$action) {
        $action->lay->set('DEBUG', false);
  if(  isset($_REQUEST['debug']) && ($_REQUEST['debug'] == 'yes') ) {
     
        $action->lay->set('DEBUG', true);
    }
    $tree=onefam_getDataTreeFamily($action);
    $config=onefam_ext_getdisplayconfig($action);
    $action->lay->set('APPLABEL', $action->parent->description?_($action->parent->description):$action->parent->name);
    $action->lay->set('FAMILYTREE', json_encode($tree));
    $action->lay->set('EXT_DISPLAYCONFIG', ($config));
    $action->lay->set('caneditmasterfamilies', ($action->canExecute("ONEFAM_EXT_GETMASTERPREF")?"false":"true"));
    $action->lay->set('canedituserfamilies', ($action->canExecute("ONEFAM_EXT_GETPREF")?"false":"true"));
    
    
}