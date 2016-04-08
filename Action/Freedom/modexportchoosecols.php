<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Save export preferences
 *
 * @author Anakeen
 * @version $Id: modexportchoosecols.php,v 1.2 2008/11/14 12:41:43 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Lib.Dir.php");
include_once ("GENERIC/generic_util.php");

function modexportchoosecols(Action & $action)
{
    $dbaccess = $action->dbaccess;
    $docid = GetHttpVars("id");
    $cols = GetHttpVars("ch");
    $clear = (GetHttpVars("clear") == "yes");
    
    $doc = new_doc($dbaccess, $docid);
    if (!$doc->isAlive()) $action->exitError(sprintf(_("family %s not exists") , $docid));
    
    if ($cols == "") $scols = array();
    else $scols = implode(';', $cols);
    
    if ($clear) {
        deleteFamilyParameter($action, $doc->id, "FREEDOM_EXPORTCOLS");
        $action->addWarningMsg(_("export preferences has been cleared"));
    } else {
        setFamilyParameter($action, $doc->id, "FREEDOM_EXPORTCOLS", $scols);
        $action->addWarningMsg(_("export preferences are saved"));
    }
    redirect($action, "FREEDOM", "EDITEXPORTCHOOSECOLS&id=" . $doc->id, $action->GetParam("CORE_STANDURL"));
}
