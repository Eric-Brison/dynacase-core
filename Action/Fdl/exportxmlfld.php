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
include_once ("FDL/Class.SearchDoc.php");
/**
 * Exportation as xml of documents from folder or searches
 * @param Action &$action current action
 * @global string $fldid Http var : folder identificator to export
 * @global string $wfile Http var : (Y|N) if Y export attached file export format will be tgz
 * @global string $flat Http var : (Y|N) if Y specid column is set with identificator of document
 * @global string $eformat Http var :  (X|Y) I:  Y: only one xml, X: zip by document with files
 * @global string $log Http var :  log file output
 * @global string $selection Http var :  JSON document selection object
 * @param string $afldid folder identificator to export
 * @param string $famid restrict to specific family for folder
 * @param SearchDoc $specSearch use this search instead folder
 * @param string $outputFile put result into this file instead download it
 * @param string $eformat X : zip (xml inside), Y: global xml file
 * @param string $eformat X : zip (xml inside), Y: global xml file
 */
function exportxmlfld(Action & $action, $aflid = "0", $famid = "", SearchDoc $specSearch = null, $outputFile = '', $eformat = "", $wident = 'Y', Fdl_DocumentSelection $aSelection = null)
{
    if (ini_get("max_execution_time") < 3600) ini_set("max_execution_time", 3600); // 60 minutes
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $fldid = $action->getArgument("id", $aflid);
    $wprof = false; // no profil
    $wfile = (substr(strtolower($action->getArgument("wfile", "N")) , 0, 1) == "y"); // with files
    $wident = (substr(strtolower($action->getArgument("wident", $wident)) , 0, 1) == "y"); // with numeric identificator
    $flat = (substr(strtolower($action->getArgument("flat")) , 0, 1) == "y"); // flat xml
    if (!$eformat) $eformat = strtoupper($action->getArgument("eformat", "X")); // export format
    $selection = $action->getArgument("selection"); // export selection  object (JSON)
    $log = $action->getArgument("log"); // log file
    $configxml = $action->getArgument("config");
    $flog = false;
    if ($log) {
        $flog = fopen($log, "w");
        if (!$flog) {
            exportExit($action, sprintf(_("cannot write log in %s") , $log));
        }
        fputs($flog, sprintf("EXPORT BEGIN OK : %s\n", Doc::getTimeDate(0, true)));
        fputs($flog, sprintf("EXPORT OPTION FLAT : %s\n", ($flat) ? "yes" : "no"));
        fputs($flog, sprintf("EXPORT OPTION WFILE : %s\n", ($wfile) ? "yes" : "no"));
        fputs($flog, sprintf("EXPORT OPTION CONFIG : %s\n", ($configxml) ? "yes" : "no"));
    }
    // constitution options for filter attributes
    $exportAttribute = array();
    if ($configxml) {
        if (!file_exists($configxml)) exportExit($action, sprintf(_("config file %s not found") , $configxml));
        
        $xml = @simplexml_load_file($configxml);
        
        if ($xml === false) {
            exportExit($action, sprintf(_("parse error config file %s : %s") , $configxml, print_r(libxml_get_last_error() , true)));
        }
        /**
         * @var SimpleXmlElement $family
         */
        foreach ($xml->family as $family) {
            $afamid = @current($family->attributes()->name);
            if (!$afamid) exportExit($action, sprintf(_("Config file %s : family name not set") , $configxml));
            $fam = new_doc($dbaccess, $afamid);
            if ((!$fam->isAlive()) || ($fam->doctype != 'C')) exportExit($action, sprintf(_("Config file %s : family name [%s] not match a know family") , $configxml, $afamid));
            $exportAttribute[$fam->id] = array();
            foreach ($family->attribute as $attribute) {
                $aid = @current($attribute->attributes()->name);
                
                if (!$aid) exportExit($action, sprintf(_("Config file %s : attribute name not set") , $configxml));
                $oa = $fam->getAttribute($aid);
                if (!$oa) exportExit($action, sprintf(_("Config file %s : unknow attribute name %s") , $configxml, $aid));
                $exportAttribute[$fam->id][$oa->id] = $oa->id;
                $exportAttribute[$fam->id][$oa->fieldSet->id] = $oa->fieldSet->id;
            }
        }
    }
    // set the export's search
    $exportname = '';
    if ($specSearch) {
        $s = $specSearch;
        $s->setObjectReturn();
        $s->reset();
    } elseif ((!$fldid) && ($selection || $aSelection)) {
        if ($aSelection) {
            $os = $aSelection;
        } else {
            $selection = json_decode($selection);
            include_once ("DATA/Class.DocumentSelection.php");
            $os = new Fdl_DocumentSelection($selection);
        }
        $ids = $os->getIdentificators();
        $s = new SearchDoc($dbaccess);
        
        $s->addFilter(getSqlCond($ids, "id", true));
        $s->setObjectReturn();
        $exportname = "selection";
    } else {
        if (!$fldid) exportExit($action, _("no export folder specified"));
        
        $fld = new_Doc($dbaccess, $fldid);
        if ($fldid && (!$fld->isAlive())) exportExit($action, sprintf(_("folder/search %s not found") , $fldid));
        if ($famid == "") $famid = $action->getArgument("famid");
        $exportname = str_replace(array(
            " ",
            "'",
            '/'
        ) , array(
            "_",
            "",
            "-"
        ) , $fld->title);
        //$tdoc = getChildDoc($dbaccess, $fldid,"0","ALL",array(),$action->user->id,"TABLE",$famid);
        $s = new SearchDoc($dbaccess, $famid);
        $s->setObjectReturn();
        
        $s->dirid = $fld->id;
    }
    $s->search();
    $err = $s->searchError();
    if ($err) exportExit($action, $err);
    
    $foutdir = uniqid(getTmpDir() . "/exportxml");
    if (!mkdir($foutdir)) exportExit($action, sprintf("cannot create directory %s", $foutdir));
    //$fname=sprintf("%s/FDL/Layout/fdl.xsd",DEFAULT_PUBDIR);
    //copy($fname,"$foutdir/fdl.xsd");
    $xsd = array();
    $count = 0;
    if ($flog) {
        fputs($flog, sprintf("EXPORT OPTION ID : %s <%s>\n", $fldid, $fld->getTitle()));
    }
    
    while ($doc = $s->nextDoc()) {
        //print $doc->exportXml();
        if ($doc->doctype != 'C') {
            $ftitle = str_replace(array(
                '/',
                '\\',
                '?',
                '*',
                ':'
            ) , '-', $doc->getTitle());
            /*
             * The file name should not exceed MAX_FILENAME_LEN bytes and, as the string is in UTF-8,
             * we must take care not to cut in the middle of a multi-byte char.
            */
            $suffix = sprintf("{%d}.xml", $doc->id);
            $maxBytesLen = MAX_FILENAME_LEN - strlen($suffix);
            $fname = sprintf("%s/%s%s", $foutdir, mb_strcut($ftitle, 0, $maxBytesLen, 'UTF-8') , $suffix);
            
            $err = $doc->exportXml($xml, $wfile, $fname, $wident, $flat, $exportAttribute);
            // file_put_contents($fname,$doc->exportXml($wfile));
            if ($err) exportExit($action, $err);
            $count++;
            if ($flog) fputs($flog, sprintf("EXPORT DOC OK : <%s> [%d]\n", $doc->getTitle() , $doc->id));
            if (!isset($xsd[$doc->fromid])) {
                /**
                 * @var DocFam $fam
                 */
                $fam = new_doc($dbaccess, $doc->fromid);
                $fname = sprintf("%s/%s.xsd", $foutdir, strtolower($fam->name));
                file_put_contents($fname, $fam->getXmlSchema());
                $xsd[$doc->fromid] = true;
            }
        }
    }
    
    if ($flog) {
        fputs($flog, sprintf("EXPORT COUNT OK : %d\n", $count));
        fputs($flog, sprintf("EXPORT END OK : %s\n", Doc::getTimeDate(0, true)));
        fclose($flog);
    }
    
    if ($eformat == "X") {
        
        if ($outputFile) $zipfile = $outputFile;
        else $zipfile = uniqid(getTmpDir() . "/xml") . ".zip";
        system(sprintf("cd %s && zip -r %s -- * > /dev/null", escapeshellarg($foutdir) , escapeshellarg($zipfile)) , $ret);
        if (is_file($zipfile)) {
            system(sprintf("rm -fr %s", $foutdir));
            Http_DownloadFile($zipfile, "$exportname.zip", "application/x-zip", false, false, true);
        } else {
            exportExit($action, _("Zip Archive cannot be created"));
        }
    } elseif ($eformat == "Y") {
        if ($outputFile) $xmlfile = $outputFile;
        else $xmlfile = uniqid(getTmpDir() . "/xml") . ".xml";
        
        $fh = fopen($xmlfile, 'x');
        if ($fh === false) {
            exportExit($action, sprintf("%s (Error creating file '%s')", _("Xml file cannot be created") , htmlspecialchars($xmlfile)));
        }
        /* Print XML header */
        $xml_head = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<documents date="%s" author="%s" name="%s">

EOF;
        $xml_head = sprintf($xml_head, htmlspecialchars(strftime("%FT%T")) , htmlspecialchars(User::getDisplayName($action->user->id)) , htmlspecialchars($exportname));
        $xml_footer = "</documents>";
        
        $ret = fwrite($fh, $xml_head);
        if ($ret === false) {
            exportExit($action, sprintf("%s (Error writing to file '%s')", _("Xml file cannot be created") , htmlspecialchars($xmlfile)));
        }
        fflush($fh);
        /* chdir into dir containing the XML files
         * and concatenate them into the output file
        */
        $cwd = getcwd();
        $ret = chdir($foutdir);
        if ($ret === false) {
            exportExit($action, sprintf("%s (Error chdir to '%s')", _("Xml file cannot be created") , htmlspecialchars($foutdir)));
        }
        
        if ($s->count() > 0) {
            $cmd = sprintf("cat -- *xml | grep -v '<?xml version=\"1.0\" encoding=\"UTF-8\"?>' >> %s", escapeshellarg($xmlfile));
            system($cmd, $ret);
        }
        
        $ret = chdir($cwd);
        if ($ret === false) {
            exportExit($action, sprintf("%s (Error chdir to '%s')", _("Xml file cannot be created") , htmlspecialchars($cwd)));
        }
        /* Print XML footer */
        $ret = fseek($fh, 0, SEEK_END);
        if ($ret === - 1) {
            exportExit($action, sprintf("%s (Error fseek '%s')", _("Xml file cannot be created") , htmlspecialchars($xmlfile)));
        }
        
        $ret = fwrite($fh, $xml_footer);
        if ($ret === false) {
            exportExit($action, sprintf("%s (Error writing to file '%s')", _("Xml file cannot be created") , htmlspecialchars($xmlfile)));
        }
        fflush($fh);
        fclose($fh);
        
        if (is_file($xmlfile)) {
            system(sprintf("rm -fr %s", escapeshellarg($foutdir)));
            
            if (!$outputFile) {
                Http_DownloadFile($xmlfile, "$exportname.xml", "text/xml", false, false, true);
            }
        } else {
            exportExit($action, _("Xml file cannot be created"));
        }
    }
}
function exportExit(Action & $action, $err)
{
    $log = $action->getArgument("log");
    if ($log) {
        if (file_put_contents($log, "EXPORT " . _("ERROR :") . $err) === false) {
            $err = sprintf(_("Cannot write to log %s") , $log) . "\n" . $err;
        }
    }
    $action->exitError($err);
}
?>
