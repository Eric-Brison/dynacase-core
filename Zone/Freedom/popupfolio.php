<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * popup for portfolio list
 *
 * @author Anakeen
 * @version $Id: popupfolio.php,v 1.14 2008/06/03 10:14:13 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */
//
include_once ("FDL/Class.Doc.php");
function popupfolio(&$action)
{
    // -----------------------------------
    // ------------------------------
    // get all parameters
    $dirid = GetHttpVars("dirid"); //
    $folioid = GetHttpVars("folioid"); // portfolio id
    $kdiv = 1; // only one division
    $dbaccess = $action->getParam("FREEDOM_DB");
    
    $dir = new_Doc($dbaccess, $dirid);
    if ($dir->locked == - 1) { // it is revised document
        $ldocid = $dir->latestId();
        if ($ldocid != $dir->id) $dir = new_Doc($dbaccess, $ldocid);
    }
    
    include_once ("FDL/popup_util.php");
    // ------------------------------------------------------
    // definition of popup menu
    popupInit('popupfolio', array(
        'newdoc',
        'newgc',
        'newsgc',
        'insertbasket',
        'searchinsert'
    ));
    
    Popupinvisible('popupfolio', $kdiv, 'insertbasket');
    Popupinvisible('popupfolio', $kdiv, 'searchinsert');
    Popupinvisible('popupfolio', $kdiv, 'newdoc');
    Popupinvisible('popupfolio', $kdiv, 'newgc');
    Popupinvisible('popupfolio', $kdiv, 'newsgc');
    
    if ($dir->doctype == "D") {
        Popupactive('popupfolio', $kdiv, 'newdoc');
        if ($dir->control("modify") == "") {
            Popupactive('popupfolio', $kdiv, 'insertbasket');
            Popupactive('popupfolio', $kdiv, 'searchinsert');
        }
        if (!$action->getParam("FREEDOM_IDBASKET")) Popupinvisible('popupfolio', $kdiv, 'insertbasket');
        
        if ($dir->usefor != "G") {
            $sub = $dir->getAuthorizedFamilies();
            
            $insertgc = true;
            $insertsgc = true;
            if (!$dir->hasNoRestriction()) {
                $keys = array_keys($sub);
                
                $insertgc = (in_array(18, $keys));
                $insertsgc = (in_array(19, $keys));
            }
            if ($insertgc) popupactive('popupfolio', $kdiv, 'newgc');
            if ($insertsgc) popupactive('popupfolio', $kdiv, 'newsgc');
        }
    }
    popupGen($kdiv);
    // set dirid to folio if is in search
    if ($dir->doctype == 'S') $action->lay->set("dirid", $folioid);
    else $action->lay->set("dirid", $dirid);
    
    setFamidInLayout($action);
}
?>