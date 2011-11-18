<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Import document descriptions
 *
 * @author Anakeen 2000
 * @version $Id: freedom_import.php,v 1.13 2008/02/27 11:43:08 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/import_file.php");
/**
 * Import documents
 * @param Action &$action current action
 * @global file Http var : documents to export
 * @global log Http var : log file output
 */
function freedom_import(Action & $action)
{
    // -----------------------------------
    global $_FILES;
    $log = $action->getArgument("log"); // log file
    if (ini_get("max_execution_time") < 3600) ini_set("max_execution_time", 3600); // 60 minutes
    if (isset($_FILES["file"])) {
        $filename = $_FILES["file"]['name'];
        $csvfile = $_FILES["file"]['tmp_name'];
        $ext = substr($filename, strrpos($filename, '.') + 1);
        rename($csvfile, $csvfile . ".$ext");
        $csvfile.= ".$ext";
    } else {
        $filename = GetHttpVars("file");
        $csvfile = $filename;
    }
    $oImport = new ImportDocument();
    $cr = $oImport->importDocuments($action, $csvfile);
    
    $oImport->writeImportLog($log);
    $oImport->writeHtmlCr($action->lay);
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    
    if (isset($_FILES["file"])) @unlink($csvfile); // tmp file
    
}
?>
