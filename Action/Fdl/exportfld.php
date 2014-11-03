<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Export Document from Folder
 *
 * @author Anakeen
 * @version $Id: exportfld.php,v 1.44 2009/01/12 13:23:11 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
function exportfld(Action & $action, $aflid = "0", $famid = "", $outputPath = "")
{
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $usage = new ActionUsage($action);
    
    $wprof = ($usage->addOptionalParameter("wprof", "With profil", array(
        "Y",
        "N"
    ) , "N") == "Y");
    $wfile = ($usage->addOptionalParameter("wfile", "With files", array(
        "Y",
        "N"
    ) , "N") == "Y");
    $wident = ($usage->addOptionalParameter("wident", "With document numerix identifiers", array(
        "Y",
        "N"
    ) , "Y") == "Y");
    
    $fileEncoding = $usage->addOptionalParameter("code", "File encoding", array(
        "utf8",
        "iso8859-15"
    ) , "utf8");
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
    $exportCollection->setOutputFileEncoding($wutf8 ? Dcp\ExportCollection::utf8Encoding : Dcp\ExportCollection::latinEncoding);
    
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
    $foutdir = '';
    if ($outputPath) {
        $foutname = $outputPath;
    } else {
        if ($wfile) {
            $foutname = uniqid(getTmpDir() . "/exportfld") . ".zip";
        } else {
            if ($eformat == Dcp\ExportCollection::xmlFileOutputFormat) {
                $foutname = uniqid(getTmpDir() . "/exportfld") . ".xml";
            } else {
                $foutname = uniqid(getTmpDir() . "/exportfld") . ".csv";
            }
        }
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
            Http_DownloadFile($foutname, $fname, $fileMime, false, false);
        }
    }
    catch(Dcp\Exception $e) {
        throw $e;
    }
    
    return;
    // set encoding
    \Dcp\WriteCsv::$enclosure = $csvEnclosure;
    \Dcp\WriteCsv::$separator = $csvSeparator;
    \Dcp\WriteCsv::$encoding = $fileEncoding;
    
    $exportDoc = new \Dcp\ExportDocument();
    $exportDoc->setCsvEnclosure($csvEnclosure);
    $exportDoc->setCsvSeparator($csvSeparator);
    $ef = array(); //   files to export
    if ($s->count() > 0) {
        
        $doc = createDoc($dbaccess, 0);
        // compose the csv file
        $tmoredoc = array();
        
        recordStatus($action, $exportId, _("Record system families"));
        $famData = array();
        while ($doc = $s->getNextDoc()) {
            
            if ($doc->doctype == "C") {
                $wname = "";
                $cvname = "";
                $cpname = "";
                $fpname = "";
                /**
                 * @var Docfam $doc
                 */
                // it is a family
                if ($wprof) {
                    if ($doc->profid != $doc->id) {
                        $fp = getTDoc($dbaccess, $doc->profid);
                        $tmoredoc[$fp["id"]] = $fp;
                        if ($fp["name"] != "") $fpname = $fp["name"];
                        else $fpname = $fp["id"];
                    } else {
                        exportProfil($fout, $dbaccess, $doc->profid);
                    }
                    if ($doc->cprofid) {
                        $cp = getTDoc($dbaccess, $doc->cprofid);
                        if ($cp["name"] != "") $cpname = $cp["name"];
                        else $cpname = $cp["id"];
                        $tmoredoc[$cp["id"]] = $cp;
                    }
                    if ($doc->ccvid > 0) {
                        $cv = getTDoc($dbaccess, $doc->ccvid);
                        if ($cv["name"] != "") $cvname = $cv["name"];
                        else $cvname = $cv["id"];
                        $tmskid = $doc->rawValueToArray($cv["cv_mskid"]);
                        
                        foreach ($tmskid as $kmsk => $imsk) {
                            if ($imsk != "") {
                                $msk = getTDoc($dbaccess, $imsk);
                                if ($msk) $tmoredoc[$msk["id"]] = $msk;
                            }
                        }
                        
                        $tmoredoc[$cv["id"]] = $cv;
                    }
                    
                    if ($doc->wid > 0) {
                        $wdoc = new_doc($dbaccess, $doc->wid);
                        if ($wdoc->name != "") $wname = $wdoc->name;
                        else $wname = $wdoc->id;
                        $tattr = $wdoc->getAttributes();
                        foreach ($tattr as $ka => $oa) {
                            if ($oa->type == "docid") {
                                $tdid = $wdoc->getMultipleRawValues($ka);
                                foreach ($tdid as $did) {
                                    if ($did != "") {
                                        $m = getTDoc($dbaccess, $did);
                                        if ($m) {
                                            $tmoredoc[$m["id"]] = $m;
                                            if (!empty($m["cv_mskid"])) {
                                                $tmskid = $doc->rawValueToArray($m["cv_mskid"]);
                                                foreach ($tmskid as $kmsk => $imsk) {
                                                    if ($imsk != "") {
                                                        $msk = getTDoc($dbaccess, $imsk);
                                                        if ($msk) $tmoredoc[$msk["id"]] = $msk;
                                                    }
                                                }
                                            }
                                            if (!empty($m["tm_tmail"])) {
                                                $tmskid = $doc->rawValueToArray(str_replace('<BR>', "\n", $m["tm_tmail"]));
                                                foreach ($tmskid as $kmsk => $imsk) {
                                                    if ($imsk != "") {
                                                        $msk = getTDoc($dbaccess, $imsk);
                                                        if ($msk) $tmoredoc[$msk["id"]] = $msk;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        $tmoredoc[$doc->wid] = getTDoc($dbaccess, $doc->wid);
                    }
                    if ($cvname || $wname || $cpname || $fpname) {
                        $famData[] = array(
                            "BEGIN",
                            "",
                            "",
                            "",
                            "",
                            $doc->name
                        );
                        
                        if ($fpname) {
                            $famData[] = array(
                                "PROFID",
                                $fpname
                            );
                        }
                        if ($cvname) {
                            $famData[] = array(
                                "CVID",
                                $cvname
                            );
                        }
                        if ($wname) {
                            $famData[] = array(
                                "WID",
                                $wname
                            );
                        }
                        if ($doc->cprofid) {
                            $famData[] = array(
                                "CPROFID",
                                $cpname
                            );
                        }
                        $famData[] = array(
                            "END"
                        );
                    }
                }
            }
        }
        
        $s->rewind();
        $rc = $s->count();
        $c = 0;
        while ($doc = $s->getNextDoc()) {
            $c++;
            if ($c % 20 == 0) {
                recordStatus($action, $exportId, sprintf(_("Record documents %d/%d") , $c, $rc));
            }
            if ($doc->doctype != "C") {
                $exportDoc->csvExport($doc, $ef, $fout, $wprof, $wfile, $wident, $wutf8, $nopref, $eformat);
            }
        }
        $more = new DocumentList();
        $more->addDocumentIdentifiers(array_keys($tmoredoc));
        foreach ($more as $doc) {
            $exportDoc->csvExport($doc, $ef, $fout, $wprof, $wfile, $wident, $wutf8, $nopref, $eformat);
        }
        foreach ($famData as $aRow) {
            \Dcp\WriteCsv::fput($fout, $aRow);
        }
    }
    fclose($fout);
    if ($wfile) {
        $err = '';
        foreach ($ef as $info) {
            $source = $info["path"];
            $ddir = $foutdir . '/' . $info["ldir"];
            if (!is_dir($ddir)) mkdir($ddir);
            $dest = $ddir . '/' . $info["fname"];
            if (!@copy($source, $dest)) $err.= sprintf(_("cannot copy %s") , $dest);
        }
        if ($err) $action->addWarningMsg($err);
        system(sprintf("cd %s && zip -r fdl * > /dev/null", escapeshellarg($foutdir)) , $ret);
        if (is_file("$foutdir/fdl.zip")) {
            if (!$outputPath) {
                $foutname = $foutdir . "/fdl.zip";
                recordStatus($action, $exportId, _("Export done") , true);
                
                Http_DownloadFile($foutname, "$fname.zip", "application/x-zip", false, false);
                //if (deleteContentDirectory($foutdir)) rmdir($foutdir);
                
            } else {
                recordStatus($action, $exportId, _("Export done") , true);
                return;
            }
        } else {
            $action->exitError(_("Zip Archive cannot be created"));
        }
    } else {
        
        recordStatus($action, $exportId, _("Export done") , true);
        if (!$outputPath) {
            Http_DownloadFile($foutname, "$fname.csv", "text/csv", false, false);
            unlink($foutname);
        }
    }
    
    recordStatus($action, $exportId, _("Export done") , true);
    if (!$outputPath) {
        exit;
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

function orderbyfromid($a, $b)
{
    
    if ($a["fromid"] == $b["fromid"]) return 0;
    if ($a["fromid"] > $b["fromid"]) return 1;
    
    return -1;
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
/**
 * @param $fout
 * @param $dbaccess
 * @param $docid
 * @deprecated To delete
 */
function exportProfil($fout, $dbaccess, $docid)
{
    if (!$docid) return;
    // import its profile
    $doc = new_Doc($dbaccess, $docid); // needed to have special acls
    $doc->acls[] = "viewacl";
    $doc->acls[] = "modifyacl";
    if ($doc->name != "") $name = $doc->name;
    else $name = $doc->id;
    
    $q = new QueryDb($dbaccess, "DocPerm");
    $q->AddQuery("docid=" . $doc->profid);
    $acls = $q->Query(0, 0, "TABLE");
    
    $tpu = array();
    $tpa = array();
    if ($acls) {
        foreach ($acls as $va) {
            $up = $va["upacl"];
            $uid = $va["userid"];
            
            foreach ($doc->acls as $acl) {
                $bup = ($doc->ControlUp($up, $acl) == "");
                if ($bup) {
                    if ($uid >= STARTIDVGROUP) {
                        $vg = new Vgroup($dbaccess, $uid);
                        $qvg = new QueryDb($dbaccess, "VGroup");
                        $qvg->AddQuery("num=$uid");
                        $tvu = $qvg->Query(0, 1, "TABLE");
                        $uid = $tvu[0]["id"];
                    }
                    
                    $tpu[] = $uid;
                    if ($bup) $tpa[] = $acl;
                    else $tpa[] = "-" . $acl;
                }
            }
        }
    }
    // add extended Acls
    if ($doc->extendedAcls) {
        $extAcls = array_keys($doc->extendedAcls);
        $aclCond = GetSqlCond($extAcls, "acl");
        simpleQuery($dbaccess, sprintf("select * from docpermext where docid=%d and %s", $doc->profid, $aclCond) , $eAcls);
        
        foreach ($eAcls as $aAcl) {
            $uid = $aAcl["userid"];
            if ($uid >= STARTIDVGROUP) {
                $vg = new Vgroup($dbaccess, $uid);
                $qvg = new QueryDb($dbaccess, "VGroup");
                $qvg->AddQuery("num=$uid");
                $tvu = $qvg->Query(0, 1, "TABLE");
                $uid = $tvu[0]["id"];
            }
            $tpa[] = $aAcl["acl"];
            $tpu[] = $uid;
        }
    }
    
    if (count($tpu) > 0) {
        $data = array(
            "PROFIL",
            $name,
            "",
            ""
        );
        //fputs_utf8($fout, "PROFIL;" . $name . ";;");
        foreach ($tpu as $ku => $uid) {
            if ($uid > 0) $uid = getUserLogicName($dbaccess, $uid);
            //fputs_utf8($fout, ";" . $tpa[$ku] . "=" . $uid);
            $data[] = sprintf("%s=%s", $tpa[$ku], $uid);
        }
        \Dcp\WriteCsv::fput($fout, $data);
        // fputs_utf8($fout, "\n");
        
    }
}
/**
 * @param $dbaccess
 * @param $uid
 * @return mixed
 * @deprecated To delete
 */
function getUserLogicName($dbaccess, $uid)
{
    $u = new Account("", $uid);
    if ($u->isAffected()) {
        $du = getTDoc($dbaccess, $u->fid);
        if (($du["name"] != "") && ($du["us_whatid"] == $uid)) return $du["name"];
    }
    return $uid;
}
