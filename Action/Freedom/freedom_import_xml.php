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

include_once ("FDL/import_tar.php");
include_once ('FDL/Class.XMLSplitter.php');
/**
 * export global xml file
 * @param Action $action main action
 * @param string $filename xml filename to import
 * @return array
 */
function freedom_import_xml(Action & $action, $filename = "")
{
    
    $opt["analyze"] = (substr(strtolower(getHttpVars("analyze", "N")) , 0, 1) == "y");
    $opt["policy"] = getHttpVars("policy", "update");
    $opt["dirid"] = getHttpVars("dirid", getHttpVars("dir", 0));
    
    $dbaccess = $action->dbaccess;
    global $_FILES;
    
    setMaxExecutionTimeTo(300);
    if ($filename == "") {
        if (isset($_FILES["file"])) {
            $filename = $_FILES["file"]['name'];
            $xmlfiles = $_FILES["file"]['tmp_name'];
            $ext = substr($filename, strrpos($filename, '.') + 1);
            rename($xmlfiles, $xmlfiles . ".$ext");
            $xmlfiles.= ".$ext";
        } else {
            $filename = GetHttpVars("file");
            $xmlfiles = $filename;
        }
    } else {
        $xmlfiles = $filename;
    }
    
    $iXml = new \Dcp\Core\importXml();
    $iXml->setPolicy($opt["policy"]);
    $iXml->setImportDirectory($opt["dirid"]);
    $iXml->analyzeOnly($opt["analyze"]);
    return $iXml->importSingleXmlFile($xmlfiles);
}
/**
 * export global xml file
 * @param Action $action main action
 * @param string $filename xml filename to import
 * @return array
 */
function freedom_import_xmlzip(Action & $action, $filename = "")
{
    
    $opt["analyze"] = (substr(strtolower(getHttpVars("analyze", "Y")) , 0, 1) == "y");
    $opt["policy"] = getHttpVars("policy", "update");
    $opt["dirid"] = getHttpVars("dirid", getHttpVars("dir", 0));
    
    global $_FILES;
    setMaxExecutionTimeTo(300);
    if ($filename == "") {
        if (isset($_FILES["file"])) {
            $filename = $_FILES["file"]['name'];
            $zipfiles = $_FILES["file"]['tmp_name'];
            $ext = substr($filename, strrpos($filename, '.') + 1);
            rename($zipfiles, $zipfiles . ".$ext");
            $zipfiles.= ".$ext";
        } else {
            $filename = GetHttpVars("file");
            $zipfiles = $filename;
        }
    } else {
        $zipfiles = $filename;
    }
    
    $iXml = new \Dcp\Core\importXml();
    $iXml->setPolicy($opt["policy"]);
    $iXml->setImportDirectory($opt["dirid"]);
    $iXml->analyzeOnly($opt["analyze"]);
    
    $log = $iXml->importZipFile($zipfiles);
    return $log;
}
/**
 * read a directory to import all xml files
 * @param string $splitdir
 * @param array $opt options analyze (boolean) , policy (string)
 * @deprecated use Dcp\Core\importXml::importXmlDirectory instead
 * @return array
 */
function importXmlDirectory($dbaccess, $splitdir, $opt)
{
    $iXml = new \Dcp\Core\importXml();
    $iXml->setPolicy($opt["policy"]);
    $iXml->setImportDirectory($opt["dirid"]);
    $iXml->analyzeOnly($opt["analyze"]);
    return $iXml->importXmlDirectory($splitdir);
}
/**
 * read a directory to extract all encoded files
 * @param $splitdir
 * @deprecated Dcp\Core\importXml::extractFilesFromXmlDirectory
 * @return string
 */
function extractFilesFromXmlDirectory($splitdir)
{
    try {
        \Dcp\Core\importXml::extractFilesFromXmlDirectory($splitdir);
    }
    catch(Exception $e) {
        return $e->getMessage();
    }
    return "";
}
/**
 * extract encoded base 64 file from xml and put it in local media directory
 * the file is rewrite without encoded data and replace by href attribute
 * @param $file
 * @return string error message empty if no errors
 * @deprecated use Dcp\Core\importXml::extractFileFromXmlDocument
 */
function extractFileFromXmlDocument($file)
{
    try {
        \Dcp\Core\importXml::extractFileFromXmlDocument($file);
    }
    catch(Exception $e) {
        return $e->getMessage();
    }
    return "";
}
/**
 * @param $dbaccess
 * @param $xmlfile
 * @param $log
 * @param $opt
 * @sdeprecated use Dcp\Core\importXml::importXmlFileDocument
 * @return string
 */
function importXmlDocument($dbaccess, $xmlfile, &$log, $opt)
{
    $iXml = new \Dcp\Core\importXml();
    $iXml->setPolicy($opt["policy"]);
    $iXml->setImportDirectory($opt["dirid"]);
    $iXml->analyzeOnly($opt["analyze"]);
    return $iXml->importXmlFileDocument($xmlfile, $log);
}
/**
 * @param $zipfiles
 * @param $splitdir
 * @return string
 * @deprecated  use Dcp\Core\importXml::unZipXmlDocument
 */
function splitZipXmlDocument($zipfiles, $splitdir)
{
    $err = "";
    $zipfiles = realpath($zipfiles);
    $ll = exec(sprintf("cd %s && unzip %s", $splitdir, $zipfiles) , $out, $retval);
    if ($retval != 0) $err = sprintf(_("export Xml : cannot unzip %s : %s") , $zipfiles, $ll);
    return $err;
}
/**
 * @param $xmlfiles
 * @param $splitdir
 * @return string
 * @deprecated use Dcp\Core\importXml::splitXmlDocument
 */
function splitXmlDocument($xmlfiles, $splitdir)
{
    try {
        return Dcp\Core\importXml::splitXmlDocument($xmlfiles, $splitdir);
    }
    catch(\Exception $e) {
        return $e->getMessage();
    }
}
/**
 * @param $filename
 * @deprecated use Dcp\Core\importXml::base64Decodefile instead
 */
function base64_decodefile($filename)
{
    Dcp\Core\importXml::base64Decodefile($filename);
}
