<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Import document descriptions
 *
 * @author Anakeen
 * @version $Id: freedom_import.php,v 1.13 2008/02/27 11:43:08 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/import_file.php");
/**
 * Import documents
 * @param Action &$action current action
 * @global string $file Http var : documents to export
 * @global string $log Http var : log file output
 */
function freedom_import(Action & $action)
{
    // -----------------------------------
    global $_FILES;
    
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("Import documents");
    $separator = $usage->addOptionalParameter("separator", "character to delimiter fields - generaly a comma", function ($values, $argName, ApiUsage $apiusage)
    {
        if ($values === ApiUsage::GET_USAGE) {
            return sprintf(' use single character or "auto"');
        }
        if (!is_string($values)) {
            return sprintf("must be a character [%s] ", print_r($values, true));
        }
        if ($values != "auto") {
            if (mb_strlen($values) > 1) {
                return sprintf("must be a only one character [%s] ", $values);
            }
        }
        return '';
    });
    $enclosure = $usage->addOptionalParameter("enclosure", "character to enclose fields - generaly double-quote", function ($values, $argName, ApiUsage $apiusage)
    {
        if ($values === ApiUsage::GET_USAGE) {
            return sprintf(' use single character or "auto"');
        }
        if (!is_string($values)) {
            return sprintf("must be a character [%s] ", print_r($values, true));
        }
        if ($values != "auto") {
            if (mb_strlen($values) > 1) {
                return sprintf("must be a only one character [%s] ", $values);
            }
        }
        return '';
    }
    , "");
    
    $archive = ($usage->addOptionalParameter("archive", "is archive", array(
        "yes",
        "no"
    ) , 'no') == "yes");

    $linebreak = $usage->addOptionalParameter("linebreak", "csv linebreak", array() , '\n');
    
    $analyze = ($usage->addOptionalParameter("analyze", "analyze", array(
        "Y",
        "N"
    ) , "N") == "Y"); // just analyze
    $usage->setStrictMode(false);
    $usage->verify();
    
    $action->parent->addJsRef("lib/jquery/jquery.js");
    $action->parent->addJsRef("lib/jquery-ui/js/jquery-ui.js");
    $action->parent->addCssRef("css/dcp/jquery-ui.css");
    $action->parent->addJsRef("lib/jquery-dataTables/js/jquery.dataTables.min.js");
    $action->parent->addCssRef("lib/jquery-dataTables/css/jquery.dataTables_themeroller.css");
    
    setMaxExecutionTimeTo(3600); // 60 minutes
    $csvfile = '';
    $ext = '';
    $filename='';
    if (isset($_FILES["file"])) {
        if ($_FILES["file"]["error"]) {
            $action->exitError(_("No description import file"));
        }
        $filename = $_FILES["file"]['name'];
        $csvfile = $_FILES["file"]['tmp_name'];
        $ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
        if ($ext === "gz") {
            $ext = strtolower(substr($filename, strrpos($filename, '.', -4) + 1));
        }
        rename($csvfile, $csvfile . ".$ext");
        $csvfile.= ".$ext";
    } else {
        $action->exitError(_("No description import file"));
    }
    if ($ext === "tgz" || $ext === "tar.gz") {
        $archive = true;
    } elseif ($ext !== "zip") {
        $archive = false;
    }
    
    if (!in_array($ext, array(
        "csv",
        "ods",
        "xml",
        "zip",
        "tgz",
        "tar.gz"
    ))) {
        $action->exitError(_("Not supported file format"));
    }
    $pseparator = $action->getParam("FREEDOM_CSVSEPARATOR");
    $penclosure = $action->getParam("FREEDOM_CSVENCLOSURE");
    $plinebreak = $action->getParam("FREEDOM_CSVLINEBREAK");

    if (!$analyze) {
        if ($separator) {
            if ($plinebreak != $linebreak) {
                $action->setParamU("FREEDOM_CSVLINEBREAK", $linebreak);
            }
            if ($pseparator != $separator) {
                $action->setParamU("FREEDOM_CSVSEPARATOR", $separator);
            }
            if ($penclosure != $enclosure) {
                $action->setParamU("FREEDOM_CSVENCLOSURE", $enclosure);
            }
        }
    }
    
    $oImport = new ImportDocument();
    if ($separator) {
        $oImport->setCsvOptions($separator, $enclosure, $linebreak);
    }
    if (preg_match("/admin\\.php$/", $_SERVER["SCRIPT_NAME"])) {
        $oImport->setVerifyAttributeAccess(false);
    }
    $oImport->importDocuments($action, $csvfile, $analyze, $archive);
    $oImport->writeHtmlCr($action->lay);
    $action->lay->eset("basename", $filename);
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    
    if (isset($_FILES["file"])) @unlink($csvfile); // tmp file
    
}
