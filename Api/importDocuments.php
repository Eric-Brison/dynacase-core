<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * importation of documents
 *
 * @author Anakeen 2002
 * @version $Id: freedom_import.php,v 1.9 2008/11/13 16:49:16 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage WSH
 */
/**
 */

global $appl, $action;

include_once ("FDL/import_file.php");
include_once ("FREEDOM/freedom_import.php");

$usage = new ApiUsage();
$usage->setText("Import documents from description file");
$filename = $usage->addNeeded("file", "the description file path");
$analyze = $usage->addOption("analyze", "analyze only", array(
    "yes",
    "no"
) , "no");
$archive = $usage->addOption("archive", "description file is an standard archive (not xml)", array(
    "yes",
    "no"
) , "no");
$logfile = $usage->addOption("log", "log file output");
$htmlmode = $usage->addOption("htmlmode", "analyze report mode in html", array(
    "yes",
    "no"
) , "yes");
$reinit = $usage->addOption("reinitattr", "reset attribute before import family update", array(
    "yes",
    "no"
));
$to = $usage->addOption("to", "email address to send report");
$usage->verify();

if (!file_exists($filename)) {
    $action->ExitError(sprintf(_("import file %s not found") , $filename));
}
if ($logfile) {
    if (file_exists($logfile) && (!is_writable($logfile))) {
        $action->ExitError(sprintf(_("log file %s not writable") , $logfile));
    }
    if (!file_exists($logfile)) {
        $f = @fopen($logfile, 'a');
        if ($f === false) {
            $action->ExitError(sprintf(_("log file %s not writable") , $logfile));
        }
        fclose($f);
    }
}
setHttpVar('analyze', ($analyze == "yes") ? 'Y' : 'N');
setHttpVar('htmlmode', ($htmlmode == "yes") ? 'Y' : 'N');
$archive = ($archive == "yes");

$cr = importDocuments($action, $filename, $analyze, $archive);

$filetmp = false;
if ((!$logfile) && $to) {
    $logfile = tempnam(getTmpDir() , 'logimport');
    $filetmp = true;
}
if ($logfile) {
    if ($htmlmode == "yes") {
        writeHTMLImportLog($logfile, $cr);
    } else {
        writeImportLog($logfile, $cr);
    }
}
// mode HTML
if ($to) {
    include_once ("FDL/sendmail.php");
    
    $themail = new Fdl_Mail_mime();
    
    $themail->setHTMLBody(file_get_contents($logfile) , false);
    
    $from = getMailAddr($action->user->id);
    if ($from == "") $from = getParam('SMTP_FROM');
    if ($from == "") $from = $action->user->login . '@' . php_uname('n');
    
    $subject = sprintf(_("result of import  %s") , basename(GetHttpVars("file")));
    $err = sendmail($to, $from, $cc, $bcc, $subject, $themail);
    if ($err) error_log("import sending mail: Error:$err");
    if ($filetmp) unlink($logfile);
} else {
    if (GetHttpVars("htmlmode") == "Y") print $out;
}
?>