<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Return Help Files
 *
 * @author Anakeen
 * @version $Id: family_help.php,v 1.4 2007/09/04 09:09:10 eric Exp $
 * @package FDL
 */
/**
 */

include_once ("Lib.Http.php");
include_once ("FDL/Class.Doc.php");

function family_help(Action & $action)
{
    
    $docid = GetHttpVars("id");
    
    $pdffile = getFamilyHelpFile($action, $docid);
    if ($pdffile) {
        $name = basename($pdffile);
        Http_DownloadFile($pdffile, "$name", "application/pdf");
    } else {
        $errtext = sprintf(_("file for %s not found.") , $docid);
        $action->ExitError($errtext);
    }
}

function getFamilyHelpFile(Action & $action, $docid)
{
    $dbaccess = $action->dbaccess;
    if (!is_numeric($docid)) $docid = getFamIdFromName($dbaccess, $docid);
    
    $doc = new_Doc($dbaccess, $docid);
    if ($doc->isAlive()) {
        $name = $doc->name;
        if ($name != "") {
            $pdffile = DEFAULT_PUBDIR . "/Docs/$name.pdf";
            if (file_exists($pdffile)) {
                return $pdffile;
            }
        }
    }
    return false;
}
