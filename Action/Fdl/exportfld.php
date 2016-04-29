<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Export Document from Folder
 *
 * @author Anakeen
 * @version $Id: exportfld.php,v 1.44 2009/01/12 13:23:11 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Lib.Dir.php");
include_once ("FDL/Lib.Util.php");
include_once ("FDL/Class.DocAttr.php");
include_once ("VAULT/Class.VaultFile.php");
include_once ("FDL/import_file.php");
/**
 * Exportation of documents from folder or searches
 * @param Action &$action current action
 * @param string $aflid Folder identifier to use if no "id" http vars
 * @param string $famid Family restriction to filter folder content
 * @param string $outputPath where put export, if wfile outputPath is a directory
 * @param bool $exportInvisibleVisibilities set to true to export invisible attribute also
 * @throws Dcp\Exception
 * @throws Exception
 * @global string $fldid Http var : folder identifier to export
 * @global string $wprof Http var : (Y|N) if Y export associated profil also
 * @global string $wfile Http var : (Y|N) if Y export attached file export format will be tgz
 * @global string $wident Http var : (Y|N) if Y specid column is set with identifier of document
 * @global string $wutf8 Http var : (Y|N) if Y encoding is utf-8 else iso8859-1
 * @global string $wcolumn Http var :  if - export preferences are ignored
 * @global string $eformat Http var :  (I|R|F) I: for reimport, R: Raw data, F: Formatted data
 * @global string $selection Http var :  JSON document selection object
 * @return void
 */
function exportfld(Action & $action, $aflid = "0", $famid = "", $outputPath = "", $exportInvisibleVisibilities = false)
{
    $dbaccess = $action->dbaccess;
    
    $usage = new ActionUsage($action);
    
    $wprof = ($usage->addOptionalParameter("wprof", "With profil", array(
        "Y",
        "N"
    ) , "N") == "Y");
    $wfile = ($usage->addOptionalParameter("wfile", "With files", array(
        "Y",
        "N"
    ) , "N") == "Y");
    $wident = ($usage->addOptionalParameter("wident", "With document numeric identifiers", array(
        "Y",
        "N"
    ) , "Y") == "Y");
    
    $fileEncoding = $usage->addOptionalParameter("code", "File encoding", array(
        "utf8",
        "iso8859-15"
    ) , "utf8");
    $profilType = $usage->addOptionalParameter("wproftype", "Profil option type", array(
        \Dcp\ExportDocument::useAclAccountType,
        \Dcp\ExportDocument::useAclDocumentType
    ) , \Dcp\ExportDocument::useAclAccountType);
    $wutf8 = ($fileEncoding !== "iso8859-15");
    
    $nopref = ($usage->addOptionalParameter("wcolumn", "if - export preferences are ignored") == "-"); // no preference read
    $eformat = $usage->addOptionalParameter("eformat", "Export format", array(
        "I",
        "R",
        "F",
        "X",
        "Y"
    ) , "I");
    $selection = $usage->addOptionalParameter("selection", "export selection  object (JSON)");
    $statusOnly = ($usage->addHiddenParameter("statusOnly", "Export progress status") != ""); // export selection  object (JSON)
    $exportId = $usage->addHiddenParameter("exportId", "Export status id"); // export status id
    if (!$aflid && !$selection && !$statusOnly) {
        $fldid = $usage->addRequiredParameter("id", "Folder identifier");
    } else {
        $fldid = $usage->addOptionalParameter("id", "Folder identifier", array() , $aflid);
    }
    
    $csvSeparator = $usage->addOptionalParameter("csv-separator", "character to delimiter fields - generaly a comma", function ($values, $argName, ApiUsage $apiusage)
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
            if (mb_strlen($values) === 0) {
                return sprintf("empty separator is not allowed [%s] ", $values);
            }
        }
        return '';
    }
    , ";");
    
    $csvEnclosure = $usage->addOptionalParameter("csv-enclosure", "character to enclose fields - generaly double-quote", function ($values, $argName, ApiUsage $apiusage)
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
    $usage->verify();
    
    if ($statusOnly) {
        
        header('Content-Type: application/json');
        $action->lay->noparse = true;
        $action->lay->template = json_encode($action->read($exportId));
        return;
    }
    setMaxExecutionTimeTo(3600);
    $exportCollection = new Dcp\ExportCollection();
    $exportCollection->setExportStatusId($exportId);
    $exportCollection->setOutputFormat($eformat);
    $exportCollection->setExportProfil($wprof);
    $exportCollection->setExportDocumentNumericIdentiers($wident);
    $exportCollection->setUseUserColumnParameter(!$nopref);
    $exportCollection->setOutputFileEncoding($wutf8 ? Dcp\ExportCollection::utf8Encoding : Dcp\ExportCollection::latinEncoding);
    $exportCollection->setVerifyAttributeAccess(!$exportInvisibleVisibilities);
    $exportCollection->setProfileAccountType($profilType);
    
    if ((!$fldid) && $selection) {
        $selection = json_decode($selection);
        include_once ("DATA/Class.DocumentSelection.php");
        include_once ("FDL/Class.SearchDoc.php");
        $os = new Fdl_DocumentSelection($selection);
        $ids = $os->getIdentificators();
        $exportCollection->recordStatus(_("Retrieve documents from database"));
        $s = new SearchDoc($dbaccess);
        $s->setObjectReturn(true);
        $s->addFilter(getSqlCond($ids, "id", true));
        $s->setOrder("fromid, id");
        $s->search();
        $fname = "selection";
    } else {
        if (!$fldid) $action->exitError(_("no export folder specified"));
        
        $fld = new_Doc($dbaccess, $fldid);
        if ($famid == "") $famid = GetHttpVars("famid");
        $fname = str_replace(array(
            " ",
            "'"
        ) , array(
            "_",
            ""
        ) , $fld->getTitle());
        
        $exportCollection->recordStatus(_("Retrieve documents from database"));
        
        $s = new SearchDoc($dbaccess, $famid);
        $s->setObjectReturn(true);
        $s->setOrder("fromid, id");
        $s->useCollection($fld->initid);
        $s->search();
    }
    
    $exportCollection->setDocumentlist($s->getDocumentList());
    $exportCollection->setExportFiles($wfile);
    //usort($tdoc, "orderbyfromid");
    if ($outputPath) {
        if ($wfile) {
            if (!is_dir($outputPath)) {
                mkdir($outputPath);
            }
            $foutname = $outputPath . "/fdl.zip";
        } else {
            $foutname = $outputPath;
        }
    } else {
        $foutname = uniqid(getTmpDir() . "/exportfld");
    }
    
    if (file_exists($foutname)) {
        $action->exitError(sprintf("export is not allowed to override existing file %s") , $outputPath);
    }
    
    $exportCollection->setOutputFilePath($foutname);
    $exportCollection->setCvsSeparator($csvSeparator);
    $exportCollection->setCvsEnclosure($csvEnclosure);
    $action->setParamU("EXPORT_CSVSEPARATOR", $csvSeparator);
    $action->setParamU("EXPORT_CSVENCLOSURE", $csvEnclosure);
    
    try {
        $exportCollection->export();
        if (is_file($foutname)) {
            switch ($eformat) {
                case Dcp\ExportCollection::xmlFileOutputFormat:
                    $fname.= ".xml";
                    $fileMime = "text/xml";
                    break;

                case Dcp\ExportCollection::xmlArchiveOutputFormat:
                    $fname.= ".zip";
                    $fileMime = "application/x-zip";
                    break;

                default:
                    if ($wfile) {
                        
                        $fname.= ".zip";
                        $fileMime = "application/x-zip";
                    } else {
                        $fname.= ".csv";
                        $fileMime = "text/csv";
                    }
            }
            $exportCollection->recordStatus(_("Export done") , true);
            if (!$outputPath) {
                Http_DownloadFile($foutname, $fname, $fileMime, false, false, true);
            }
        }
    }
    catch(Dcp\Exception $e) {
        throw $e;
    }
}
/**
 * @param Action $action
 * @param $exportId
 * @param $msg
 * @param bool $endStatus
 * @see Dcp\ExportCollection::recordStatus()
 * @deprecated use Dcp\ExportCollection::recordStatus() instead
 */
function recordStatus(Action & $action, $exportId, $msg, $endStatus = false)
{
    $action->register($exportId, array(
        "status" => $msg,
        "end" => $endStatus
    ));
}
/**
 * Removes content of the directory (not sub directory)
 *
 * @param string $dirname the directory name to remove
 * @return boolean True/False whether the directory was deleted.
 * @deprecated To delete (not used)
 */
function deleteContentDirectory($dirname)
{
    if (!is_dir($dirname)) return false;
    $dcur = realpath($dirname);
    $darr = array();
    $darr[] = $dcur;
    if ($d = opendir($dcur)) {
        while ($f = readdir($d)) {
            if ($f == '.' || $f == '..') continue;
            $f = $dcur . '/' . $f;
            if (is_file($f)) {
                unlink($f);
                $darr[] = $f;
            }
        }
        closedir($d);
    }
    
    return true;
}
