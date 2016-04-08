<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Folio List Containt
 *
 * @author Anakeen
 * @version $Id: foliolist.php,v 1.16 2007/10/19 15:20:14 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ('FREEDOM/freedom_view.php');
include_once ('FREEDOM/Lib.portfolio.php');
/**
 * View a containt of portfolio separator
 * @param Action &$action current action
 * @global string $dirid Http var : separator identifier to see
 * @global string $folioid Http var : portfolio of separator
 */
function foliolist(Action & $action)
{
    // Get all the params
    $dirid = GetHttpVars("dirid"); // directory to see
    $folioid = GetHttpVars("folioid"); // portfolio id
    $refreshtab = (GetHttpVars("refreshtab", "N") == "Y"); // need refresh tabs
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/resizeimg.js");
    
    $filter = array();
    if (($dirid == $folioid) || ($folioid == 0)) {
        $filter[] = "doctype != 'S'";
        $filter[] = "doctype != 'D'";
    }
    $dir = new_Doc($action->dbaccess, $dirid);
    if (($dir->doctype == 'S')) {
        if ($dir->usefor == 'G') {
            // recompute search to restriction to local folder
            // only for filters
            $dir->id = "";
            $dir->initid = "";
            $dir->doctype = 'T';
            $dir->setValue("SE_IDFLD", $folioid);
            $dir->setValue("SE_SUBLEVEL", "1");
            $dir->setValue("SE_ORDERBY", "title");
            $dir->Add();
            $dir->preRefresh();
            $dir->Modify();
            SetHttpVar("dirid", $dir->initid); // redirect dirid to new temporary search
            
        } else {
            // recompute search to add current father folder
            //     $dir->id="";
            //     $dir->initid="";
            //     $dir->doctype='T';
            if (($folioid > 0) && ($dir->getRawValue("SE_IDCFLD") != $folioid)) {
                $dir->setValue("SE_IDCFLD", $folioid);
                $dir->setValue("SE_ORDERBY", "title");
                //     $dir->Add();
                $dir->preRefresh();
                $dir->Modify();
            }
            //    SetHttpVar("dirid",$dir->initid); // redirect dirid to new temporary search
            
            
        }
    }
    
    $pfctx = portfolio_get_context($dir);
    $action->lay->set("LISTICON", $pfctx['listtype'] == 'icon');
    
    if ($folioid) {
        $dir = new_Doc($action->dbaccess, $folioid);
    }
    
    $action->lay->set("docid", $dir->initid);
    $pfctx = portfolio_get_context($dir);
    $action->lay->set("FRAMELISTWIDTH", '180');
    if (isset($pfctx['framelistwidth'])) {
        $action->lay->set("FRAMELISTWIDTH", $pfctx['framelistwidth']);
    }
    
    setHttpVar("sqlorder", "title");
    $action->parent->SetVolatileParam("FREEDOM_VIEW", "icon");
    $nbdoc = viewfolder($action, false, true, false, 100, $filter);
    if ($nbdoc > 1) $action->lay->set("docs", _("documents"));
    else $action->lay->set("docs", _("document"));
    
    $action->lay->set("refreshtab", $refreshtab);
}
