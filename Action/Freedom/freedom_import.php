<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Import document descriptions
 *
 * @author Anakeen
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
 * @global string $file Http var : documents to export
 * @global string $log Http var : log file output
 */
function freedom_import(Action & $action)
{
    // -----------------------------------
    global $_FILES;
    
    $usage = new ActionUsage($action);
    $usage->setText("Import documents");
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
    if (isset($_FILES["file"])) {
        if ($_FILES["file"]["error"]) {
            $action->exitError(_("No description import file"));
        }
        $filename = $_FILES["file"]['name'];
        $csvfile = $_FILES["file"]['tmp_name'];
        $ext = substr($filename, strrpos($filename, '.') + 1);
        rename($csvfile, $csvfile . ".$ext");
        $csvfile.= ".$ext";
    } else {
        $action->exitError(_("No description import file"));
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
    $oImport->importDocuments($action, $csvfile, $analyze);
    $oImport->writeHtmlCr($action->lay);
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    
    if (isset($_FILES["file"])) @unlink($csvfile); // tmp file
    
}
?>
