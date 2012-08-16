<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
include_once ("FDL/import_file.php");
class ImportDocument
{
    
    private $begtime = 0;
    /**
     * @var array report
     */
    private $cr = array();
    /**
     * @var bool strict mode
     */
    private $strict = true;
    /**
     * set strict mode
     * @param bool $strict set to false to accept error when import
     * @return void
     */
    public function setStrict($strict)
    {
        $this->strict = ($strict && true);
    }
    /**
     * @param Action $action current action
     * @param string $file filename path to import
     * @param bool $onlyAnalyze if true only analyze not import really
     * @param bool $archive if true to import file like an standard archive
     * @return array analyze report
     */
    public function importDocuments(Action & $action, $file, $onlyAnalyze = false, $archive = false)
    {
        $point = '';
        if ($this->strict) {
            $point = 'importDocument';
            //$action->debug=true;
            $action->savePoint($point);
        }
        if ($archive) {
            include_once ("FREEDOM/freedom_ana_tar.php");
            $untardir = getTarExtractDir($action, basename($file));
            $mime = getSysMimeFile($file, basename($file));
            //print_r(array($untardir, $file, $mime));
            $status = extractTar($file, $untardir, $mime);
            if ($status != 0) {
                $err = sprintf(_("cannot extract archive %s: status : %s") , $file, $status);
                $this->cr[] = array(
                    "err" => $err
                );
            } else {
                $onlycsv = hasfdlpointcsv($untardir);
                $famid = 7; // file
                $dfldid = 2; // folder
                $dirid = 0; // directory to place imported doc
                $this->cr = import_directory($action, $untardir, $dirid, $famid, $dfldid, $onlycsv, $onlyAnalyze);
            }
        } else {
            $ext = substr($file, strrpos($file, '.') + 1);
            $this->begtime = Doc::getTimeDate(0, true);
            if ($ext == "xml") {
                include_once ("FREEDOM/freedom_import_xml.php");
                $this->cr = freedom_import_xml($action, $file);
            } else if ($ext == "zip") {
                include_once ("FREEDOM/freedom_import_xml.php");
                $this->cr = freedom_import_xmlzip($action, $file);
            } else {
                $this->cr = add_import_file($action, $file);
            }
        }
        if ($this->strict) {
            if ($this->getErrorMessage()) {
                $action->rollbackPoint($point);
            } else {
                $action->commitPoint($point);
            }
        }
        return $this->cr;
    }
    /**
     * return all error message concatenated
     * @return string
     */
    public function getErrorMessage()
    {
        $terr = array();
        foreach ($this->cr as $cr) {
            if ($cr["err"]) $terr[] = $cr["err"];
        }
        if (count($terr) > 0) {
            return '[' . implode("]\n[", $terr) . ']';
        } else {
            return '';
        }
    }
    /**
     * write report in file
     * @param string $log filename path to write in
     * @return void
     */
    public function writeHTMLImportLog($log)
    {
        if ($log) {
            $flog = fopen($log, "w");
            if (!$flog) {
                addWarningMsg(sprintf(_("cannot write log in %s") , $log));
            } else {
                global $action;
                $lay = new Layout(getLayoutFile("FREEDOM", "freedom_import.xml") , $action);
                $this->writeHtmlCr($lay);
                fputs($flog, $lay->gen());
                fclose($flog);
            }
        }
    }
    /**
     * internal method use only from freedom_import
     * @param Layout $lay
     * @return void
     */
    public function writeHtmlCr(Layout & $lay)
    {
        foreach ($this->cr as $k => $v) {
            $this->cr[$k]["taction"] = _($v["action"]); // translate action
            $this->cr[$k]["order"] = $k; // translate action
            $this->cr[$k]["svalues"] = "";
            $this->cr[$k]["msg"] = nl2br($v["msg"]);
            if (is_array($v["values"])) {
                foreach ($v["values"] as $ka => $va) {
                    $this->cr[$k]["svalues"].= "<LI" . (($va == "/no change/") ? ' class="no"' : '') . ">[$ka:$va]</LI>"; //
                    
                }
            }
        }
        $nbdoc = count(array_filter($this->cr, array(
            $this,
            "isdoc"
        )));
        $lay->SetBlockData("ADDEDDOC", $this->cr);
        $lay->Set("nbdoc", $nbdoc);
        $lay->Set("nbprof", count(array_filter($this->cr, array(
            $this,
            "isprof"
        ))));
    }
    /**
     * record a log file from import results
     *
     * @param string $log output file path
     */
    public function writeImportLog($log)
    {
        if ($log) {
            $flog = fopen($log, "w");
            if (!$flog) {
                addWarningMsg(sprintf(_("cannot write log in %s") , $log));
            } else {
                fputs($flog, sprintf("IMPORT BEGIN OK : %s\n", $this->begtime));
                $countok = 0;
                $counterr = 0;
                foreach ($this->cr as $v) {
                    $chg = "";
                    if (is_array($v["values"])) {
                        foreach ($v["values"] as $ka => $va) {
                            if ($va != "/no change/") $chg.= "{" . $ka . ":" . str_replace("\n", "-", $va) . '}';
                        }
                    }
                    fputs($flog, sprintf("IMPORT DOC %s : [title:%s] [id:%d] [action:%s] [changes:%s] [message:%s] %s\n", $v["err"] ? "KO" : "OK", $v["title"], $v["id"], $v["action"], $chg, str_replace("\n", "-", $v["msg"]) , $v["err"] ? ('[error:' . str_replace("\n", "-", $v["err"]) . ']') : ""));
                    if ($v["err"]) $counterr++;
                    else $countok++;
                }
                fputs($flog, sprintf("IMPORT COUNT OK : %d\n", $countok));
                fputs($flog, sprintf("IMPORT COUNT KO : %d\n", $counterr));
                fputs($flog, sprintf("IMPORT END OK : %s\n", Doc::getTimeDate(0, true)));
                fclose($flog);
            }
        }
    }
    
    public static function isdoc($var)
    {
        return (($var["action"] == "added") || ($var["action"] == "updated"));
    }
    
    public static function isprof($var)
    {
        return (($var["action"] == "modprofil"));
    }
}
