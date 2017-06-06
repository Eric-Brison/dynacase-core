<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Import directory with document descriptions
 *
 * @author Anakeen
 * @version $Id: freedom_import_dir.php,v 1.5 2007/01/19 16:23:32 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

namespace Dcp\Core;
include_once ("FDL/import_tar.php");
include_once ('FDL/Class.XMLSplitter.php');

class importXml
{
    protected $analyze = false;
    protected $policy = "update";
    protected $dirid = 0;
    protected $verifyAttributeAccess = true;
    public function analyzeOnly($analyze)
    {
        $this->analyze = $analyze;
    }
    
    public function setPolicy($policy)
    {
        if (!$policy) {
            $policy = "update";
        }
        $this->policy = $policy;
    }
    
    public function setImportDirectory($dirid)
    {
        $this->dirid = $dirid;
    }
    /**
     * @param boolean $verifyAttributeAccess
     */
    public function setVerifyAttributeAccess($verifyAttributeAccess)
    {
        $this->verifyAttributeAccess = $verifyAttributeAccess;
    }
    /**
     * @param string $xmlFile file path
     * @return array log infortmations about import
     * @throws Exception
     * @throws \Dcp\Exception
     */
    public function importSingleXmlFile($xmlFile)
    {
        $splitdir = uniqid(getTmpDir() . "/xmlsplit");
        @mkdir($splitdir);
        if (!is_dir($splitdir)) {
            throw new \Dcp\Exception("IMPC0002", $splitdir);
        }
        self::splitXmlDocument($xmlFile, $splitdir);
        
        self::extractFilesFromXmlDirectory($splitdir);
        
        $log = $this->importXmlDirectory($splitdir);
        system(sprintf("/bin/rm -fr %s ", $splitdir));
        // print "look : $splitdir\n";
        return $log;
    }
    
    public function importZipFile($zipFile)
    {
        $splitdir = uniqid(getTmpDir() . "/xmlsplit");
        @mkdir($splitdir);
        if (!is_dir($splitdir)) {
            throw new \Dcp\Exception("IMPC0003", $splitdir);
        }
        self::unZipXmlDocument($zipFile, $splitdir);
        //print "Split OK in $splitdir";
        self::extractFilesFromXmlDirectory($splitdir);
        
        $log = $this->importXmlDirectory($splitdir);
        system(sprintf("/bin/rm -fr %s ", $splitdir));
        //print "look : $splitdir\n";
        return $log;
    }
    
    public static function unZipXmlDocument($zipfiles, $splitdir)
    {
        $err = "";
        $zipfiles = realpath($zipfiles);
        $ll = exec(sprintf("cd %s && unzip %s", $splitdir, $zipfiles) , $out, $retval);
        if ($retval != 0) {
            throw new \Dcp\Exception("IMPC0004", $zipfiles, $ll);
        }
        return $err;
    }
    /**
     * read a directory to import all xml files
     * @param string $splitdir
     * @return array log info
     */
    public function importXmlDirectory($splitdir)
    {
        $tlog = array();
        if ($handle = opendir($splitdir)) {
            $files = array();
            while (false !== ($file = readdir($handle))) {
                if ($file[0] != "." && is_file("$splitdir/$file")) {
                    $ext = substr($file, strrpos($file, '.') + 1);
                    if ($ext == "xml") {
                        $files[] = $file;
                    }
                }
            }
            asort($files);
            foreach ($files as $file) {
                $this->importXmlFileDocument("$splitdir/$file", $log);
                $tlog[] = $log;
            }
            closedir($handle);
        }
        
        return $tlog;
    }
    
    public function importXmlFileDocument($xmlfile, &$log)
    {
        static $families = array();
        $dbaccess = getDbAccess();
        $log = array(
            "err" => "",
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
            "action" => "-"
        );
        
        if (!is_file($xmlfile)) {
            $err = sprintf(_("Xml import file %s not found") , $xmlfile);
            $log["err"] = $err;
            return $err;
        }
        $policy = $this->policy;
        $analyze = $this->analyze;
        $splitdir = dirname($xmlfile);
        $tkey = array(
            "title"
        );
        $dom = new \Dcp\Utils\XDOMDocument();
        try {
            $dom->load($xmlfile, 0, $error);
        }
        catch(\Dcp\Utils\XDOMDocumentException $e) {
            $log["action"] = 'ignored';
            $log["err"] = $e->getMessage();
            return $e->getMessage();
        }
        // print $doc->saveXML();
        $root = $dom->documentElement;
        $id = $root->getAttribute("id");
        $name = $root->getAttribute("name");
        $key = $root->getAttribute("key");
        $folders = $root->getAttribute("folders");
        if ($key) {
            $tkey = explode(',', $key);
            foreach ($tkey as & $v) {
                $v = trim($v);
            }
        }
        
        $family = $root->tagName;
        $famid = getFamIdFromName($dbaccess, $family);
        if (!isset($families[$famid])) {
            $families[$famid] = new_doc($dbaccess, $famid);
        }
        //print("family : $family $id $name $famid\n");
        
        /**
         * @var \DocFam[] $families
         */
        $la = $families[$famid]->getNormalAttributes();
        $tord = array();
        $tdoc = array(
            "DOC",
            $famid,
            ($id) ? $id : $name,
            ''
        );
        
        $rootAttrs = $root->attributes;
        
        foreach ($rootAttrs as $rname => $ra) {
            $v = $root->getAttribute($rname);
            if ($v) {
                $tord[] = "extra:$rname";
                $tdoc[] = $v;
            }
        }
        
        $msg = '';
        /**
         * @var \BasicAttribute $v
         */
        foreach ($la as $k => & $v) {
            $n = $dom->getElementsByTagName($v->id);
            $val = array();
            /**
             * @var \DomElement $item
             */
            foreach ($n as $item) {
                switch ($v->type) {
                    case 'array':
                        break;

                    case 'docid':
                    case 'account':
                        $id = $item->getAttribute("id");
                        if (!$id) {
                            $logicalName = $item->getAttribute("name");
                            $name = $item->getAttribute("name");
                            if ($name) {
                                if (strpos($name, ',') !== false) {
                                    $names = explode(',', $name);
                                    $lids = array();
                                    foreach ($names as $lname) {
                                        $lids[] = getIdFromName($dbaccess, $lname);
                                    }
                                    $id = implode(",", $lids);
                                } else {
                                    $id = getIdFromName($dbaccess, $name);
                                }
                            }
                            if (!$id) {
                                // search from title
                                if ($item->nodeValue) {
                                    $afamid = $v->format;
                                    $id = getIdFromTitle($dbaccess, $item->nodeValue, $afamid);
                                    if (!$id) {
                                        $msg.= sprintf(_("No identifier found for relation '%s' %s in %s file") . "\n", $logicalName ? $logicalName : $item->nodeValue, $v->id, $xmlfile);
                                    }
                                }
                            }
                        }
                        if ($v->getOption("multiple") == "yes") {
                            $id = str_replace(',', '\n', $id);
                            if ($v->inArray()) $id = str_replace(array(
                                '\\n',
                                "\n",
                            ) , "<BR>", $id);
                        }
                        $val[] = $id;
                        break;

                    case 'image':
                    case 'file':
                        $href = $item->getAttribute("href");
                        if ($href) {
                            $val[] = $href;
                        } else {
                            $vid = $item->getAttribute("vid");
                            $mime = $item->getAttribute("mime");
                            $title = $item->getAttribute("title");
                            if ($vid) {
                                $val[] = "$mime|$vid|$title";
                            } else {
                                $val[] = '';
                            }
                        }
                        break;

                    case 'htmltext':
                        $val[] = str_replace("\n", " ", str_replace(">\n", ">", $item->nodeValue));
                        break;

                    default:
                        $val[] = $item->nodeValue;
                    }
                    //  print $v->id.":".$item->nodeValue."\n";
                    
            }
            $tord[] = $v->id;
            $tdoc[] = implode("\n", $val);
        }
        //$log = csvAddDoc($dbaccess, $tdoc, $importdirid, $analyze, $splitdir, $policy, $tkey, $prevalues, $tord);
        $o = new \importSingleDocument();
        if ($tkey) $o->setKey($tkey);
        if ($tord) $o->setOrder($tord);
        $o->analyzeOnly($analyze);
        $o->setPolicy($policy);
        $o->setFilePath($splitdir);
        $o->setVerifyAttributeAccess($this->verifyAttributeAccess);
        if ($folders) {
            $folders = str_replace(',', ' ', $folders);
            $tfolders = explode(' ', $folders);
            foreach ($tfolders as $k => $aFolder) {
                if (!$aFolder) unset($tfolders[$k]);
            }
            
            if ($tfolders) {
                $o->setTargetDirectories($tfolders);
            }
        } elseif (!empty($opt["dirid"])) {
            $o->setTargetDirectory($opt["dirid"]);
        }
        
        $o->import($tdoc);
        $log = $o->getImportResult();
        
        if ($msg) {
            $log["err"].= "\n" . $msg;
            $log["action"] = "ignored";
        }
        return '';
    }
    public static function splitXmlDocument($xmlfiles, $splitdir)
    {
        $xs = new \XMLSplitter($splitdir);
        $xs->split($xmlfiles);
        return '';
    }
    public static function extractFilesFromXmlDirectory($splitdir)
    {
        if ($handle = opendir($splitdir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file[0] != ".") {
                    if (!is_dir("$splitdir/$file")) {
                        self::extractFileFromXmlDocument("$splitdir/$file");
                    }
                }
            }
            closedir($handle);
        }
    }
    protected static function fputsError($fd, $str)
    {
        $len = fputs($fd, $str);
        if ($len === false || $len != strlen($str)) {
            $metadata = stream_get_meta_data($fd);
            $filename = ((is_array($metadata) && isset($metadata['uri'])) ? $metadata['uri'] : '*unknown*file*');
            fclose($fd);
            throw new \Dcp\Exception("IMPC0012", $filename);
        }
        return $len;
    }
    /**
     * extract encoded base 64 file from xml and put it in local media directory
     * the file is rewrite without encoded data and replace by href attribute
     * @param $file
     * @throws \Dcp\Exception
     */
    public static function extractFileFromXmlDocument($file)
    {
        static $mediaindex = 0;
        $dir = dirname($file);
        if (!file_exists($file)) {
            throw new \Dcp\Exception("IMPC0001", $file);
        }
        $mediadir = "media";
        if (!is_dir("$dir/$mediadir")) mkdir("$dir/$mediadir");
        $f = fopen($file, "r");
        if ($f === false) {
            throw new \Dcp\Exception("IMPC0009", $file);
        }
        $nf = fopen($file . ".new", "w");
        if ($nf === false) {
            throw new \Dcp\Exception("IMPC0010", $file . ".new");
        }
        try {
            while (!feof($f)) {
                $buffer = fgets($f, 4096);
                $mediaindex++;
                if (preg_match("/<([a-z_0-9-]+)[^>]*mime=\"[^\"]+\"(.*)>(.*)/", $buffer, $reg)) {
                    if ((substr($reg[2], -1) != "/") && (substr($reg[2], -strlen($reg[1]) - 3) != '></' . $reg[1])) { // not empty tag
                        $tag = $reg[1];
                        if (preg_match("/<([a-z_0-9-]+)[^>]*title=\"([^\"]+)\"/", $buffer, $regtitle)) {
                            $title = \XMLSplitter::unescapeEntities($regtitle[2]);
                        } else if (preg_match("/<([a-z_0-9-]+)[^>]*title='([^']+)'/", $buffer, $regtitle)) {
                            $title = \XMLSplitter::unescapeEntities($regtitle[2]);
                        } else $title = "noname";
                        if (strpos($title, DIRECTORY_SEPARATOR) !== false) {
                            throw new \Dcp\Exception("IMPC0005", DIRECTORY_SEPARATOR, $title);
                        }
                        $mediaIndexDir = sprintf("%s/%s/%d", $dir, $mediadir, $mediaindex);
                        if (!file_exists($mediaIndexDir)) {
                            if (mkdir($mediaIndexDir) === false) {
                                throw new \Dcp\Exception("IMPC0006", $mediaIndexDir);
                            }
                        }
                        if (!is_dir($mediaIndexDir)) {
                            throw new \Dcp\Exception("IMPC0007", $mediaIndexDir);
                        }
                        $rfin = sprintf("%s/%d/%s", $mediadir, $mediaindex, $title);
                        $fin = sprintf("%s/%s", $dir, $rfin);
                        $fi = fopen($fin, "w");
                        if ($fi === false) {
                            throw new \Dcp\Exception("IMPC0008", $fi);
                        }
                        if (preg_match("/(.*)(<$tag [^>]*)>/", $buffer, $regend)) {
                            self::fputsError($nf, $regend[1] . $regend[2] . ' href="' . \XMLSplitter::escapeEntities($rfin) . '">');
                        }
                        if (preg_match("/>([^<]*)<\/$tag>(.*)/", $buffer, $regend)) {
                            // end of file
                            self::fputsError($fi, $regend[1]);
                            self::fputsError($nf, "</$tag>");
                            self::fputsError($nf, $regend[2]);
                        } else {
                            // find end of file
                            self::fputsError($fi, $reg[3]);
                            $findtheend = false;
                            while (!feof($f) && (!$findtheend)) {
                                $buffer = fgets($f, 4096);
                                if (preg_match("/(.*)<\/$tag>(.*)/", $buffer, $regend)) {
                                    self::fputsError($fi, $regend[1]);
                                    self::fputsError($nf, "</$tag>");
                                    self::fputsError($nf, $regend[2]);
                                    $findtheend = true;
                                } else {
                                    self::fputsError($fi, $buffer);
                                }
                            }
                        }
                        fclose($fi);
                        self::base64Decodefile($fin);
                    } else {
                        self::fputsError($nf, $buffer);
                    }
                } else if (preg_match("/&lt;img.*?src=\"data:[^;]*;base64,(.*)/", $buffer, $reg)) {
                    if (preg_match("/&lt;img.*?title=\"([^\"]+)\"/", $buffer, $regtitle)) {
                        $title = $regtitle[1];
                    } else if (preg_match("/&lt;img.*?title='([^']+)'/", $buffer, $regtitle)) {
                        $title = $regtitle[1];
                    } else $title = "noname";
                    if (strpos($title, DIRECTORY_SEPARATOR) !== false) {
                        throw new \Dcp\Exception("IMPC0005", DIRECTORY_SEPARATOR, $title);
                    }
                    $mediaIndexDir = sprintf("%s/%s/%d", $dir, $mediadir, $mediaindex);
                    if (!file_exists($mediaIndexDir)) {
                        if (mkdir($mediaIndexDir) === false) {
                            throw new \Dcp\Exception("IMPC0006", $mediaIndexDir);
                        }
                    }
                    if (!is_dir($mediaIndexDir)) {
                        throw new \Dcp\Exception("IMPC0007", $mediaIndexDir);
                    }
                    $rfin = sprintf("%s/%d/%s", $mediadir, $mediaindex, $title);
                    $fin = sprintf("%s/%s", $dir, $rfin);
                    $fi = fopen($fin, "w");
                    if ($fi === false) {
                        throw new \Dcp\Exception("IMPC0008", $fi);
                    }
                    if (preg_match("/(.*)(&lt;img.*?)src=\"data:[^;]*;base64,/", $buffer, $regend)) {
                        $chaintoput = $regend[1] . $regend[2] . ' src="file://' . $rfin . '"';
                        self::fputsError($nf, $chaintoput);
                    }
                    if (preg_match("/&lt;img.*?src=\"data:[^;]*;base64,([^\"]*)\"(.*)/", $buffer, $regend)) {
                        // end of file
                        self::fputsError($fi, $regend[1]);
                        self::fputsError($nf, $regend[2]);
                    } else {
                        // find end of file
                        self::fputsError($fi, $reg[1]);
                        $findtheend = false;
                        while (!feof($f) && (!$findtheend)) {
                            $buffer = fgets($f, 4096);
                            if (preg_match("/([^\"]*)\"(.*)/", $buffer, $regend)) {
                                self::fputsError($fi, $regend[1]);
                                self::fputsError($nf, $regend[2]);
                                $findtheend = true;
                            } else {
                                self::fputsError($fi, $buffer);
                            }
                        }
                    }
                    fclose($fi);
                    self::base64Decodefile($fin);
                } else {
                    self::fputsError($nf, $buffer);
                }
            }
        }
        catch(\Exception $e) {
            fclose($f);
            fclose($nf);
            throw $e;
        }
        fclose($f);
        fclose($nf);
        if (rename($file . ".new", $file) === false) {
            throw new \Dcp\Exception("IMPC0011", $file . ".new", $file);
        }
    }
    
    public static function base64Decodefile($filename)
    {
        $tmpdest = uniqid(getTmpDir() . "/fdlbin");
        $chunkSize = 1024 * 30;
        $src = fopen($filename, 'rb');
        $dst = fopen($tmpdest, 'wb');
        while (!feof($src)) {
            fwrite($dst, base64_decode(fread($src, $chunkSize)));
        }
        fclose($dst);
        fclose($src);
        rename($tmpdest, $filename);
    }
}
