<?php
/**
 * Import document descriptions
 *
 * @author Anakeen 2000
 * @version $Id: freedom_import.php,v 1.13 2008/02/27 11:43:08 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */


include_once("FDL/import_file.php");





/**
 * Import documents
 * @param Action &$action current action
 * @global file Http var : documents to export
 * @global log Http var : log file output
 */
function freedom_import(Action &$action) {
    // -----------------------------------
    global $_FILES;
    $log = $action->getArgument("log"); // log file
    if (ini_get("max_execution_time") < 3600) ini_set("max_execution_time",3600); // 60 minutes

    if (isset($_FILES["file"])) {
        $filename=$_FILES["file"]['name'];
        $csvfile=$_FILES["file"]['tmp_name'];
        $ext=substr($filename,strrpos($filename,'.')+1);
        rename($csvfile,$csvfile.".$ext");
        $csvfile.=".$ext";
    } else {
        $filename=GetHttpVars("file");
        $csvfile=$filename;
    }
     
    $cr=importDocuments($action,$csvfile);

    writeImportLog($log, $cr);
    writeHtmlCr($action->lay, $cr);
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");

    if (isset($_FILES["file"])) @unlink($csvfile); // tmp file
}
function writeHTMLImportLog($log, array $cr) {
    if ($log) {
        $flog=fopen($log, "w");
        if (! $flog) {
            $action->addWarningMsg(sprintf(_("cannot write log in %s"),$log));
        } else {
            global $action;
            $lay=new Layout(getLayoutFile("FREEDOM","freedom_import.xml"),$action);
            writeHtmlCr($lay, $cr);
            fputs($flog,$lay->gen());
            fclose($flog);
        }
    }
}

function writeHtmlCr(Layout &$lay,array $cr) {
    foreach ( $cr as $k => $v ) {
        $cr[$k]["taction"] = _($v["action"]); // translate action
        $cr[$k]["order"] = $k; // translate action
        $cr[$k]["svalues"] = "";
        $cr[$k]["msg"] = nl2br($v["msg"]);
        if (is_array($v["values"])) {
            foreach ( $v["values"] as $ka => $va ) {
                $cr[$k]["svalues"] .= "<LI" . (($va == "/no change/") ? ' class="no"' : '') . ">[$ka:$va]</LI>"; //
            }
        }
    }
    $nbdoc = count(array_filter($cr, "isdoc"));
    $lay->SetBlockData("ADDEDDOC", $cr);
    $lay->Set("nbdoc", $nbdoc);
    $lay->Set("nbprof", count(array_filter($cr, "isprof")));
}


function isdoc($var) {
    return (($var["action"]=="added") ||  ($var["action"]=="updated"));
}
function isprof($var) {
    return (($var["action"]=="modprofil") );
}

function importDocuments(Action &$action, $file, $onlyAnalyze=false, $archive=false) {
    if ($archive) {
        include_once("FREEDOM/freedom_ana_tar.php");
        $untardir=getTarExtractDir($action,basename($file));
        $mime=getSysMimeFile($file, basename($file));
        //print_r(array($untardir, $file, $mime));
        $status=extractTar($file, $untardir,$mime);
        if ($status != 0) {
            $err= sprintf(_("cannot extract archive %s: status : %s"),$file, $status);
            $cr[]=array("err"=>$err);
            return $cr;
        }
        $onlycsv=hasfdlpointcsv($untardir);
        $famid=7; // file
        $dfldid=2; // folder
        $dirid=0; // directory to place imported doc
        $cr=import_directory($action,$untardir,$dirid,$famid,$dfldid,$onlycsv,$onlyAnalyze);
    } else {
        $ext=substr($file,strrpos($file,'.')+1);
        $begtime=Doc::getTimeDate(0,true);
        if ($ext=="xml") {
            include_once("FREEDOM/freedom_import_xml.php");
            $cr= freedom_import_xml($action,   $file);
        } else if ($ext=="zip") {
            include_once("FREEDOM/freedom_import_xml.php");
            $cr= freedom_import_xmlzip($action,   $file);
        } else {
            $cr=add_import_file($action,$file);
        }
    }
        return $cr;
}
/**
 * record a log file from import results
 *
 * @param string $log output file path
 * @param array $cr the import results
 */
function writeImportLog($log, array $cr) {
    if ($log) {
        $flog=fopen($log, "w");
        if (! $flog) {
            addWarningMsg(sprintf(_("cannot write log in %s"),$log));
        } else {
            fputs($flog,sprintf("IMPORT BEGIN OK : %s\n",$begtime));
            $countok=0;
            $counterr=0;
            foreach ($cr as $k=>$v) {
                $chg="";
                if (is_array($v["values"])) {
                    foreach ($v["values"] as $ka=>$va) {
                        if ($va!="/no change/")  $chg.= "{".$ka.":".str_replace("\n","-",$va).'}';
                    }
                }
                fputs($flog,sprintf("IMPORT DOC %s : [title:%s] [id:%d] [action:%s] [changes:%s] [message:%s] %s\n",
                $v["err"]?"KO":"OK",
                $v["title"],$v["id"],
                $v["action"], $chg,str_replace("\n","-",$v["msg"]),
                $v["err"]?('[error:'.str_replace("\n","-",$v["err"]).']'):""));
                if ($v["err"]) $counterr++;
                else $countok++;
            }
            fputs($flog,sprintf("IMPORT COUNT OK : %d\n",$countok));
            fputs($flog,sprintf("IMPORT COUNT KO : %d\n",$counterr));
            fputs($flog,sprintf("IMPORT END OK : %s\n",Doc::getTimeDate(0,true)));
            fclose($flog);
        }
    }
}

?>
