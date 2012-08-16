<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Return Help Files
 *
 * @author Anakeen
 * @version $Id: family_help.php,v 1.4 2007/09/04 09:09:10 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */

include_once ("Lib.Http.php");
include_once ("FDL/Class.Doc.php");

function family_help(&$action)
{
    
    $docid = GetHttpVars("id");
    
    $pdffile = getFamilyHelpFile($action, $docid);
    if ($pdffile) {
        $name = basename($pdffile);
        Http_DownloadFile($pdffile, "$name", "application/pdf");
    } else {
        $errtext = sprintf(_("file for %s not found.") , $name);
        $action->ExitError($errtext);
    }
}

function getFamilyHelpFile(&$action, $docid)
{
    $dbaccess = $action->GetParam("FREEDOM_DB");
    if (!is_numeric($docid)) $docid = getFamIdFromName($dbaccess, $docid);
    
    $doc = new_Doc($dbaccess, $docid);
    if ($doc->isAlive()) {
        $name = $doc->name;
        if ($name != "") {
            $pdffile = $action->GetParam("CORE_PUBDIR") . "/Docs/$name.pdf";
            if (file_exists($pdffile)) {
                return $pdffile;
            }
        }
    }
    return false;
}
?>