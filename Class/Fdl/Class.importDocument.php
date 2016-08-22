<?php
/*
 * @author Anakeen
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
    
    private $onlyAnalyze = false;
    protected $fileName = '';
    /**
     * @var int folder to insert documents
     */
    private $dirid = 0;
    /**
     * @var string csv separator character
     */
    protected $csvSeparator = ';';
    /**
     * @var string csv enclose character
     */
    protected $csvEnclosure = '';
    /**
     * @var string csv line-break sequence
     */
    protected $csvLinebreak = '\n';
    /**
     * @var string update|add|keep
     */
    protected $policy = "update";
    /**
     * To verify visibility "I" of atttribute
     * @var bool
     */
    protected $verifyAttributeAccess = true;
    
    protected $reset = array();
    /**
     * set strict mode
     * @param bool $strict set to false to accept error when import
     * @return void
     */
    public function setStrict($strict)
    {
        $this->strict = ($strict && true);
    }
    public function setPolicy($policy)
    {
        $this->policy = $policy;
    }
    public function setReset($reset)
    {
        if (is_array($reset)) {
            $this->reset = $reset;
        } elseif (is_string($reset)) {
            $this->reset[] = $reset;
        }
    }
    
    public function setCsvOptions($csvSeparator = ';', $csvEnclosure = '"', $csvLinebreak = '\n')
    {
        $this->csvSeparator = $csvSeparator;
        $this->csvEnclosure = $csvEnclosure;
        $this->csvLinebreak = $csvLinebreak;
    }
    
    public function setTargetDirectory($dirid)
    {
        $this->dirid = $dirid;
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
            $point = 'dcp:importDocument';
            //$action->debug=true;
            $action->savePoint($point);
        }
        $this->onlyAnalyze = $onlyAnalyze;
        $this->fileName = $file;
        try {
            if ($archive) {
                include_once ("FREEDOM/freedom_ana_tar.php");
                $untardir = getTarExtractDir($action, basename($file));
                $mime = getSysMimeFile($file, basename($file));
                //print_r(array($untardir, $file, $mime));
                $err = extractTar($file, $untardir, $mime);
                if ($err !== '') {
                    $err = sprintf(_("cannot extract archive %s: status : %s") , $file, $err);
                    $this->cr[] = array(
                        "err" => $err,
                        "msg" => "",
                        "specmsg" => "",
                        "folderid" => 0,
                        "foldername" => "",
                        "filename" => "",
                        "title" => "",
                        "id" => "",
                        "values" => array() ,
                        "familyid" => 0,
                        "familyname" => "",
                        "action" => " "
                    );
                } else {
                    $onlycsv = hasfdlpointcsv($untardir);
                    $simpleFamilyFile = 7; // file
                    $simpleFamilyFolder = 2; // folder
                    $dirid = $this->dirid; // directory to insert imported doc
                    $this->cr = import_directory($action, $untardir, $dirid, $simpleFamilyFile, $simpleFamilyFolder, $onlycsv, $onlyAnalyze, $this->csvLinebreak);
                }
            } else {
                $ext = substr($file, strrpos($file, '.') + 1);
                $this->begtime = Doc::getTimeDate(0, true);
                if ($ext == "xml") {
                    $iXml = new \Dcp\Core\importXml();
                    $iXml->setPolicy($this->policy);
                    $iXml->setImportDirectory($this->dirid);
                    $iXml->setVerifyAttributeAccess($this->verifyAttributeAccess);
                    $iXml->analyzeOnly($this->onlyAnalyze);
                    $this->cr = $iXml->importSingleXmlFile($file);
                } else if ($ext == "zip") {
                    $iXml = new \Dcp\Core\importXml();
                    $iXml->setPolicy($this->policy);
                    $iXml->setImportDirectory($this->dirid);
                    $iXml->setVerifyAttributeAccess($this->verifyAttributeAccess);
                    $iXml->analyzeOnly($this->onlyAnalyze);
                    $this->cr = $iXml->importZipFile($file);
                } else {
                    $this->cr = $this->importSingleFile($file);
                }
            }
        }
        catch(Exception $e) {
            $this->cr = array(
                array(
                    "title" => "unable to import",
                    "foldername" => "unable to import",
                    "filename" => "unable to import",
                    "familyname" => "unable to import",
                    "action" => "ignored",
                    "id" => "unable to import",
                    "specmsg" => "unable to import",
                    "err" => $e->getMessage()
                )
            );
        }
        
        if ($this->strict) {
            if ($this->getErrorMessage()) {
                error_log("Import aborted :" . $this->getErrorMessage());
                $action->rollbackPoint($point);
            } else {
                $action->commitPoint($point);
            }
        }
        return $this->cr;
    }
    /**
     * @param boolean $verifyAttributeAccess
     */
    public function setVerifyAttributeAccess($verifyAttributeAccess)
    {
        $this->verifyAttributeAccess = $verifyAttributeAccess;
    }
    public function importSingleFile($file)
    {
        $if = new importDocumentDescription($file);
        $if->setImportDirectory($this->dirid);
        $if->analyzeOnly($this->onlyAnalyze);
        $if->setPolicy($this->policy);
        $if->setVerifyAttributeAccess($this->verifyAttributeAccess);
        $if->reset($this->reset);
        $if->setCsvOptions($this->csvSeparator, $this->csvEnclosure, $this->csvLinebreak);
        return $if->import();
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
        $hasError = false;
        $haswarning = false;
        foreach ($this->cr as $k => $v) {
            /*
             * [action]
             * [order]
             * [title]
             * [foldername]
             * [id]
             * [familyid]
             * [familyname]
             * [taction]
             * [msg]
             * [specmsg]
             * [svalues]
             * [err]
            */
            if (!isset($v["msg"])) $v["msg"] = '';
            if (!isset($v["values"])) $v["values"] = null;
            $this->cr[$k]["title"] = htmlspecialchars($v["title"], ENT_QUOTES);
            $this->cr[$k]["foldername"] = htmlspecialchars($v["foldername"], ENT_QUOTES);
            $this->cr[$k]["id"] = htmlspecialchars($v["id"], ENT_QUOTES);
            $this->cr[$k]["familyid"] = htmlspecialchars($v["familyid"], ENT_QUOTES);
            $this->cr[$k]["familyname"] = htmlspecialchars($v["familyname"], ENT_QUOTES);
            $this->cr[$k]["taction"] = htmlspecialchars(_($v["action"]) , ENT_QUOTES); // translate action
            $this->cr[$k]["order"] = htmlspecialchars($k, ENT_QUOTES); // translate action
            $this->cr[$k]["svalues"] = htmlspecialchars("", ENT_QUOTES);
            $this->cr[$k]["msg"] = nl2br(htmlspecialchars($v["msg"], ENT_QUOTES));
            if (is_array($v["values"])) {
                foreach ($v["values"] as $ka => $va) {
                    $this->cr[$k]["svalues"].= sprintf("<LI %s>[%s:%s]</LI>", (($va == "/no change/") ? ' class="no"' : '') , htmlspecialchars($ka, ENT_QUOTES) , htmlspecialchars($va, ENT_QUOTES));
                }
            }
            if ($v["action"] == "ignored") $hasError = true;
            if ($v["action"] == "warning") $haswarning = true;
            $this->cr[$k]["err"] = (($this->cr[$k]["err"] != '') ? "<ul><li>" . join("</li><li>", explode("\n", htmlspecialchars($this->cr[$k]["err"], ENT_QUOTES))) . "</li></ul>" : "");
            $this->cr[$k]["action"] = htmlspecialchars($v["action"], ENT_QUOTES);
            $this->cr[$k]["specmsg"] = htmlspecialchars($v["specmsg"], ENT_QUOTES);
        }
        $nbdoc = count(array_filter($this->cr, array(
            $this,
            "isdoc"
        )));
        $lay->SetBlockData("ADDEDDOC", $this->cr);
        $errmsg = $this->getErrorMessage();
        $lay->set("haserror", $hasError || !empty($errmsg));
        $lay->set("basename", $this->fileName);
        $lay->set("haswarning", $haswarning);
        $lay->Set("nbdoc", $nbdoc);
        $lay->set("analyze", ($this->onlyAnalyze));
        if ($this->onlyAnalyze) {
            $lay->set("processMessage", sprintf(n___("%d document detected", "%d documents detected", $nbdoc) , $nbdoc));
        } else {
            $lay->set("processMessage", sprintf(n___("%d document processed", "%d documents processed", $nbdoc) , $nbdoc));
        }
        
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
                    
                    if (!isset($v["msg"])) $v["msg"] = '';
                    if (!isset($v["values"])) $v["values"] = null;
                    $chg = "";
                    if (is_array($v["values"])) {
                        foreach ($v["values"] as $ka => $va) {
                            if ($va != "/no change/") $chg.= "{" . $ka . ":" . str_replace("\n", "-", $va) . '}';
                        }
                    }
                    fputs($flog, sprintf("IMPORT DOC %s : [title:%s] [id:%d] [action:%s] [changes:%s] [message:%s] [specmsg:%s] %s\n", $v["err"] ? "KO" : "OK", $v["title"], $v["id"], $v["action"], $chg, str_replace("\n", "-", $v["msg"]) , ($v["err"] ? ('[error:' . str_replace("\n", "-", $v["err"]) . ']') : "") , (isset($v['specmsg']) ? str_replace("\n", "-", $v['specmsg']) : '')));
                    if ($v['action'] !== 'ignored') {
                        if ($v["err"]) {
                            $counterr++;
                        } else {
                            $countok++;
                        }
                    }
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
