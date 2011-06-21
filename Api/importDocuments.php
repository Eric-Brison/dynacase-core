<?php
/**
 * importation of documents
 *
 * @author Anakeen 2002
 * @version $Id: freedom_import.php,v 1.9 2008/11/13 16:49:16 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage WSH
 */
/**
 */


global $appl,$action;

include_once("FDL/import_file.php");
include_once("FREEDOM/freedom_import.php");


$usage=new ApiUsage();
$usage->setText("Import documents from description file");
$usage->addNeeded("file","the description file path");
$usage->addOption("analyze","analyze only",array("yes","no"));
$usage->addOption("archive","description file is an standard archive (not xml)",array("yes","no"));
$usage->addOption("log","log file output");
$usage->addOption("htmlmode","analyze report mode in html",array("yes","no"));
$usage->addOption("reinitattr","reset attribute before import family update",array("yes","no"));
$usage->addOption("to","email address to send report");
$usage->verify();

$filename=$action->getArgument("file");
if (! file_exists($filename)) {
    $action->ExitError(sprintf(_("import file %s not found"), $filename));
}
$logfile=$action->getArgument("log");
if ($logfile) {
    if (file_exists($logfile) && (! is_writable($logfile))) {
        $action->ExitError(sprintf(_("log file %s not writable"), $logfile));
    }
    if (! file_exists($logfile)) {
        $f = @fopen($logfile, 'a');
        if ($f===false) {
            $action->ExitError(sprintf(_("log file %s not writable"), $logfile));
        }
        fclose($f);
    }
}
$analyze=($action->getArgument("analyze","no")=="yes");
setHttpVar('analyze', $analyze?'Y':'N');
$htmlmode=($action->getArgument("htmlmode","yes")=="yes");
setHttpVar('htmlmode', $htmlmode?'Y':'N');
$archive=($action->getArgument("archive","no")=="yes");

$to = $action->getArgument("to");
$cr=importDocuments($action, $filename, $analyze, $archive);

$filetmp=false;
if ((!$logfile) && $to) {
    $logfile = tempnam(getTmpDir(), 'logimport');
    $filetmp=true;
}
if ($logfile) {
    if ($htmlmode) {
        writeHTMLImportLog($logfile, $cr);
    } else {
        writeImportLog($logfile, $cr);
    }
}
// mode HTML

if ($to) {
    include_once("FDL/sendmail.php");

    $themail = new Fdl_Mail_mime();
   
      $themail->setHTMLBody(file_get_contents($logfile),false);
    
    $from=getMailAddr($action->user->id);
    if ($from == "")  $from = getParam('SMTP_FROM');
    if ($from == "")  $from = $action->user->login.'@'.php_uname('n');

    $subject=sprintf(_("result of import  %s"), basename(GetHttpVars("file")));
    $err=sendmail($to,$from,$cc,$bcc,$subject,$themail);
    if ($err) error_log("import sending mail: Error:$err");
    if ($filetmp) unlink($logfile);
} else {
    if (GetHttpVars("htmlmode") == "Y") print $out;
}






?>