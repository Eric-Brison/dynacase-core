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
function exportfld(Action & $action, $aflid = "0", $famid = "", $outputfolder = "")
{
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $fldid = GetHttpVars("id", $aflid);
    $wprof = (GetHttpVars("wprof", "N") == "Y"); // with profil
    $wfile = (GetHttpVars("wfile", "N") == "Y"); // with files
    $wident = (GetHttpVars("wident", "Y") == "Y"); // with numeric identifier
    $wutf8 = (GetHttpVars("code", "utf8") == "utf8"); // with numeric identifier
    $nopref = (GetHttpVars("wcolumn") == "-"); // no preference read
    $eformat = GetHttpVars("eformat", "I"); // export format
    $selection = GetHttpVars("selection"); // export selection  object (JSON)
    $statusOnly = (GetHttpVars("statusOnly") != ""); // export selection  object (JSON)
    $exportId = GetHttpVars("exportId"); // export status id
    if ($statusOnly) {
        
        header('Content-Type: application/json');
        $action->lay->noparse = true;
        $action->lay->template = json_encode($action->read($exportId));
        return;
    }
    setMaxExecutionTimeTo(3600);
    if ($eformat == "X") {
        // XML redirect
        include_once ("FDL/exportxmlfld.php");
        exportxmlfld($action, $aflid, $famid);
    }
    if ((!$fldid) && $selection) {
        $selection = json_decode($selection);
        include_once ("DATA/Class.DocumentSelection.php");
        include_once ("FDL/Class.SearchDoc.php");
        $os = new Fdl_DocumentSelection($selection);
        $ids = $os->getIdentificators();
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
        ) , $fld->title);
        
        recordStatus($action, $exportId, _("Retrieve documents from database"));
        
        $s = new SearchDoc($dbaccess, $famid);
        $s->setObjectReturn(true);
        $s->setOrder("fromid, id");
        $s->useCollection($fld->initid);
        $s->search();
    }
    //usort($tdoc, "orderbyfromid");
    $foutdir = '';
    if ($wfile) {
        if ($outputfolder) $foutdir = $outputfolder;
        else $foutdir = uniqid(getTmpDir() . "/exportfld");
        if (!mkdir($foutdir)) exit();
        
        $foutname = $foutdir . "/fdl.csv";
    } else {
        $foutname = uniqid(getTmpDir() . "/exportfld") . ".csv";
    }
    
    $fout = fopen($foutname, "w");
    // set encoding
    if (!$wutf8) fputs_utf8($fout, "", true);
    
    $ef = array(); //   files to export
    if ($s->count() > 0) {
        
        $send = "\n"; // string to be writed in last
        $doc = createDoc($dbaccess, 0);
        // compose the csv file
        $tmoredoc = array();
        
        recordStatus($action, $exportId, _("Record system families"));
        
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
                        $send.= "BEGIN;;;;;" . $doc->name . "\n";
                        if ($fpname) $send.= "PROFID;" . $fpname . "\n";
                        if ($cvname) $send.= "CVID;" . $cvname . "\n";
                        if ($wname) $send.= "WID;" . $wname . "\n";
                        if ($doc->cprofid) $send.= "CPROFID;" . $cpname . "\n";
                        $send.= "END;\n";
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
                exportonedoc($doc, $ef, $fout, $wprof, $wfile, $wident, $wutf8, $nopref, $eformat);
            }
        }
        $more = new DocumentList();
        $more->addDocumentIdentifiers(array_keys($tmoredoc));
        foreach ($more as $doc) {
            exportonedoc($doc, $ef, $fout, $wprof, $wfile, $wident, $wutf8, $nopref, $eformat);
        }
        /*foreach ($tdoc as $k => $zdoc) {
            if (!empty($cachedoc[$zdoc["fromid"]])) $doc = $cachedoc[$zdoc["fromid"]];
            else {
                $cachedoc[$zdoc["fromid"]] = createDoc($dbaccess, $zdoc["fromid"], false);
                $doc = $cachedoc[$zdoc["fromid"]];
            }
            
            $doc->Affect($zdoc, true);
            
            if ($doc->doctype != "C") {
                exportonedoc($doc, $ef, $fout, $wprof, $wfile, $wident, $wutf8, $nopref, $eformat);
            }
        }*/
        
        fputs_utf8($fout, $send);
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
            if (!$outputfolder) {
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
        
        Http_DownloadFile($foutname, "$fname.csv", "text/csv", false, false);
        unlink($foutname);
    }
    
    recordStatus($action, $exportId, _("Export done") , true);
    exit;
}

function recordStatus(Action & $action, $exportId, $msg, $endStatus = false)
{
    $action->register($exportId, array(
        "status" => $msg,
        "end" => $endStatus
    ));
}
function fputs_utf8($r, $s, $iso = false)
{
    static $utf8 = true;
    if ($iso === true) $utf8 = false;
    
    if ($s) {
        if (!$utf8) fputs($r, utf8_decode($s));
        else fputs($r, $s);
    }
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
        fputs_utf8($fout, "PROFIL;" . $name . ";;");
        
        foreach ($tpu as $ku => $uid) {
            if ($uid > 0) $uid = getUserLogicName($dbaccess, $uid);
            fputs_utf8($fout, ";" . $tpa[$ku] . "=" . $uid);
        }
        fputs_utf8($fout, "\n");
    }
}

function getUserLogicName($dbaccess, $uid)
{
    $u = new Account("", $uid);
    if ($u->isAffected()) {
        $du = getTDoc($dbaccess, $u->fid);
        if (($du["name"] != "") && ($du["us_whatid"] == $uid)) return $du["name"];
    }
    return $uid;
}
function exportonedoc(Doc & $doc, &$ef, $fout, $wprof, $wfile, $wident, $wutf8, $nopref, $eformat)
{
    static $prevfromid = - 1;
    static $lattr;
    static $trans = false;
    static $fromname;
    static $alreadyExported = array();
    
    if (!$doc->isAffected()) return;
    if (in_array($doc->id, $alreadyExported)) return;
    $alreadyExported[] = $doc->id;
    
    if (!$trans) {
        // to invert HTML entities
        $trans = get_html_translation_table(HTML_ENTITIES);
        $trans = array_flip($trans);
        $trans = array_map("utf8_encode", $trans);
    }
    $efldid = '';
    $dbaccess = $doc->dbaccess;
    if ($prevfromid != $doc->fromid) {
        if (($eformat != "I") && ($prevfromid > 0)) fputs_utf8($fout, "\n");
        $adoc = $doc->getFamilyDocument();
        if ($adoc->name != "") $fromname = $adoc->name;
        else $fromname = $adoc->id;
        if (!$fromname) return;
        $lattr = $adoc->GetExportAttributes($wfile, $nopref);
        if ($eformat == "I") fputs_utf8($fout, "//FAM;" . $adoc->title . "(" . $fromname . ");<specid>;<fldid>;");
        foreach ($lattr as $ka => $attr) {
            fputs_utf8($fout, str_replace(SEPCHAR, ALTSEPCHAR, $attr->getLabel()) . SEPCHAR);
        }
        fputs_utf8($fout, "\n");
        if ($eformat == "I") {
            fputs_utf8($fout, "ORDER;" . $fromname . ";;;");
            foreach ($lattr as $ka => $attr) {
                fputs_utf8($fout, $attr->id . ";");
            }
            fputs_utf8($fout, "\n");
        }
        $prevfromid = $doc->fromid;
    }
    reset($lattr);
    if ($doc->name != "") $name = $doc->name;
    else if ($wprof) {
        $err = $doc->setNameAuto(true);
        $name = $doc->name;
    } else if ($wident) $name = $doc->id;
    else $name = '';
    if ($eformat == "I") fputs_utf8($fout, "DOC;" . $fromname . ";" . $name . ";" . $efldid . ";");
    // write values
    foreach ($lattr as $ka => $attr) {
        if ($eformat == 'F') $value = str_replace(array(
            '<BR>',
            '<br/>'
        ) , '\\n', $doc->getHtmlAttrValue($attr->id, '', false, -1, false));
        else $value = $doc->getRawValue($attr->id);
        // invert HTML entities
        if (($attr->type == "image") || ($attr->type == "file")) {
            $tfiles = $doc->vault_properties($attr);
            $tf = array();
            foreach ($tfiles as $f) {
                $ldir = $doc->id . '-' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', unaccent($doc->title)) . "_D";
                $fname = $ldir . '/' . unaccent($f["name"]);
                $tf[] = $fname;
                $ef[$fname] = array(
                    "path" => $f["path"],
                    "ldir" => $ldir,
                    "fname" => unaccent($f["name"])
                );
            }
            $value = implode("\n", $tf);
        } else if ($attr->type == "docid" || $attr->type == "account" || $attr->type == "thesaurus") {
            if ($value != "") {
                if (strstr($value, "\n") || ($attr->getOption("multiple") == "yes")) {
                    $tid = $doc->rawValueToArray($value);
                    $tn = array();
                    foreach ($tid as $did) {
                        $brtid = explode("<BR>", $did);
                        $tnbr = array();
                        foreach ($brtid as $brid) {
                            $n = getNameFromId($dbaccess, $brid);
                            if ($n) $tnbr[] = $n;
                            else $tnbr[] = $brid;
                        }
                        $tn[] = implode('<BR>', $tnbr);
                    }
                    $value = implode("\n", $tn);
                } else {
                    $n = getNameFromId($dbaccess, $value);
                    if ($n) $value = $n;
                }
            }
        } else if ($attr->type == "htmltext") {
            $value = $attr->prepareHtmltextForExport($value);
            if ($wfile) {
                $value = preg_replace_callback('/(<img.*?src=")(((?=.*docid=(.*?)&)(?=.*attrid=(.*?)&)(?=.*index=(-?[0-9]+)))|(file\/(.*?)\/[0-9]+\/(.*?)\/(-?[0-9]+))).*?"/', function ($matches) use (&$ef)
                {
                    if (isset($matches[7])) {
                        $docid = $matches[8];
                        $attrid = $matches[9];
                        $index = $matches[10] == "-1" ? 0 : $matches[10];
                    } else {
                        $docid = $matches[4];
                        $index = $matches[6] == "-1" ? 0 : $matches[6];
                        $attrid = $matches[5];
                    }
                    $doc = new_Doc(getDbAccess() , $docid);
                    $attr = $doc->getAttribute($attrid);
                    $tfiles = $doc->vault_properties($attr);
                    $f = $tfiles[$index];
                    
                    $ldir = $doc->id . '-' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', unaccent($doc->title)) . "_D";
                    $fname = $ldir . '/' . unaccent($f["name"]);
                    $ef[$fname] = array(
                        "path" => $f["path"],
                        "ldir" => $ldir,
                        "fname" => unaccent($f["name"])
                    );
                    return $matches[1] . "file://" . $fname . '"';
                }
                , $value);
            }
        } else {
            $value = preg_replace_callback('/(\&[a-zA-Z0-9\#]+;)/s', function ($matches) use ($trans)
            {
                return strtr($matches[1], $trans);
            }
            , $value);
            // invert HTML entities which ascii code like &#232;
            $value = preg_replace_callback('/\&#([0-9]+);/s', function ($matches)
            {
                return chr($matches[1]);
            }
            , $value);
        }
        fputs_utf8($fout, str_replace(array(
            "\n",
            ";",
            "\r"
        ) , array(
            "\\n",
            ALTSEPCHAR,
            ""
        ) , $value) . ";");
    }
    fputs_utf8($fout, "\n");
    
    if ($wprof) {
        if ($doc->profid == $doc->id) exportProfil($fout, $dbaccess, $doc->id);
        else if ($doc->profid > 0) {
            $name = getNameFromId($dbaccess, $doc->profid);
            $dname = $doc->name;
            if (!$dname) $dname = $doc->id;
            if (!$name) $name = $doc->profid;
            if (!isset($tdoc[$doc->profid])) {
                $tdoc[$doc->profid] = true;
                $pdoc = new_doc($dbaccess, $doc->profid);
                exportonedoc($pdoc, $ef, $fout, $wprof, $wfile, $wident, $wutf8, $nopref, $eformat);
                //	  exportProfil($fout,$dbaccess,$doc->profid);
                
            }
            fputs_utf8($fout, "PROFIL;$dname;$name;;\n");
        }
    }
}
?>
