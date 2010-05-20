<?php
/**
 * extjs main interface
 *
 * @author Anakeen 2000 
 * @version $Id: onefam_modpref.php,v 1.8 2006/10/05 09:22:38 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("ONEFAM/onefam_gettreefamily.php");

function onefam_ext(&$action) {
        $action->lay->set('DEBUG', false);
  if(  isset($_REQUEST['debug']) && ($_REQUEST['debug'] == 'yes') ) {
     
        $action->lay->set('DEBUG', true);
    }
    $tree=onefam_getDataTreeFamily($action);;
    $action->lay->set('FAMILYTREE', json_encode($tree));
    $action->lay->set('caneditmasterfamilies', ($action->canExecute("ONEFAM_EXT_GETMASTERPREF")?"false":"true"));
    $action->lay->set('canedituserfamilies', ($action->canExecute("ONEFAM_EXT_GETPREF")?"false":"true"));
    
    
}