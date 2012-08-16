<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * List document of a category
 *
 * @author Anakeen
 * @version $Id: generic_tab.php,v 1.29 2008/02/20 16:55:05 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.DocSearch.php");
include_once ("FDL/Lib.Dir.php");

include_once ("FDL/freedom_util.php");
include_once ("GENERIC/generic_util.php");
include_once ("GENERIC/generic_list.php");
// -----------------------------------
function generic_tab(Action & $action)
{
    // Get all the params
    $keyword = GetHttpVars("keyword"); // keyword to search
    $dirid = GetHttpVars("catg", -1); // folder where search
    $tab = GetHttpVars("tab", 1); // tab index
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $famid = getDefFam($action);
    $emptyfld = false;
    if ($famid == "0") $action->exitError(_("cookies seem to be blocked"));
    if (($famid != "") && (!is_numeric($famid))) $famid = getFamIdFromName($dbaccess, $famid);
    
    $fdoc = new DocFam($dbaccess, $famid);
    
    if ($dirid == - 1) $dirid = $fdoc->dfldid;
    if ($dirid == 0) {
        $dirid = getDefU($action, "GENE_PREFSEARCH");
        if (!is_numeric($dirid)) {
            $dirid = getIdFromName($action->getParam('FREEDOM_DB') , $dirid);
        }
    }
    if ($dirid == 0) {
        if ($fdoc->cfldid > 0) {
            $dirid = $fdoc->cfldid;
        } else {
            $dirid = $fdoc->dfldid;
        }
    }
    // hightlight the selected part (ABC, DEF, ...)
    $tabletter = array(
        "",
        "ABC",
        "DEF",
        "GHI",
        "JKL",
        "MNO",
        "PQRS",
        "TUV",
        "WXYZ"
    );
    $sqlfilter = array();
    $dir = new_Doc($dbaccess, $dirid);
    // control open
    if ($dir->defDoctype == 'S') $aclctrl = "execute";
    else $aclctrl = "open";
    if (($err = $dir->Control($aclctrl)) != "") $action->exitError($err);
    
    if (($dir->defDoctype == 'S') && ($tab == 0)) {
        // parmeters for redirect in case of parametrizable search
        setHttpVar("dirid", $dir->initid);
        setHttpVar("sapp", $action->getParam("APPNAME", "GENERIC"));
        setHttpVar("saction", urlencode("GENERIC_LIST&famid=$famid"));
        setHttpVar("sid", "dirid");
        setHttpVar("id", $dir->initid);
    } else {
        /**
         * @var DocSearch $sdoc
         */
        $sdoc = createTmpDoc($dbaccess, 5); // new DocSearch
        if ($dir->id == $fdoc->dfldid) {
            $sdoc->title = sprintf(_("%s all ") , $tabletter[$tab]);
            $sdirid = 0; // search in all DB
            
        } else {
            $sdoc->title = sprintf("%s %s ", $tabletter[$tab], $dir->title);
            $sdirid = $dir->id;
        }
        
        $sdoc->setValue('se_famid', $famid);
        
        $sdoc->Add();
        //    $sqlfilter[]= "locked != -1";
        $wkdoc = createDoc($dbaccess, $famid, false);
        // $sqlfilter[]= "doctype='".$wkdoc->defDoctype."'";
        //    $sqlfilter[] = "usefor != 'D'";
        if ($tabletter[$tab] != "") $sqlfilter[] = "title ~* '^[" . $tabletter[$tab] . "].*'";
        
        $only = (getInherit($action, $famid) == "N");
        
        $query = getSqlSearchDoc($dbaccess, $sdirid, ($only) ? -(abs($famid)) : abs($famid) , $sqlfilter);
        
        $sdoc->AddQuery($query);
        
        setHttpVar("tab", $tab);
        if (!$emptyfld) setHttpVar("dirid", $sdoc->id);
        setHttpVar("catg", $dirid);
    }
    
    generic_list($action);
    //  redirect($action,GetHttpVars("app"),"GENERIC_LIST&tab=$tab&dirid=".$sdoc->id."&catg=$dirid");
    
    
}
?>