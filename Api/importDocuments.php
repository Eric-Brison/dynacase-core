<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * importation of documents
 *
 * @author Anakeen
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
$usage->setDefinitionText("Import documents from description file");
$filename = $usage->addRequiredParameter("file", "the description file path");
$analyze = $usage->addOptionalParameter("analyze", "analyze only", array(
    "yes",
    "no"
) , "no");
$archive = $usage->addOptionalParameter("archive", "description file is an standard archive (not xml)", array(
    "yes",
    "no"
) , "no");
$logfile = $usage->addOptionalParameter("log", "log file output");
$policy = $usage->addOptionalParameter("policy", "policy import - \n\t\t[update] to auto update same document (the default), \n\t\t[add] to always create new document, \n\t\t[keep] to do nothing if same document already present", array(
    "update",
    "add",
    "keep"
));
$htmlmode = $usage->addOptionalParameter("htmlmode", "analyze report mode in html", array(
    "yes",
    "no"
) , "yes");
$reinit = $usage->addOptionalParameter("reinitattr", "reset attribute before import family update (deprecated)", array(
    "yes",
    "no"
));
$reset = $usage->addOptionalParameter("reset", "reset options", array(
    "default",
    "parameters",
    "attributes",
    "structure",
    "properties",
    "enums"
));
$to = $usage->addOptionalParameter("to", "email address to send report");
$dirid = $usage->addOptionalParameter("dir", "folder where imported documents are put");

$strict = $usage->addOptionalParameter("strict", "don't import if one error detected", array(
    "yes",
    "no"
) , "yes");
$usage->verify();


if ($reinit=="yes") {
    $action->log->deprecated("importDocuments :reinitattr option is deprecated, use --reset=attributes");
}
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

if ($dirid) {
    $dir = new_doc("", $dirid);
    if (!$dir->isAlive()) {
        $action->exitError(sprintf("folder %s not found (dir option)", $dirid));
    }
    $dirid = $dir->id;
    SetHttpVar("dirid", $dirid);
}
$oImport = new ImportDocument();
if ($strict == 'no') $oImport->setStrict(false);

if ($dirid) $oImport->setTargetDirectory($dirid);
$cr = $oImport->importDocuments($action, $filename, $analyze != "no", $archive == "yes");

$filetmp = false;
if ((!$logfile) && $to) {
    $logfile = tempnam(getTmpDir() , 'logimport');
    $filetmp = true;
}
if ($logfile) {
    if ($htmlmode == "yes") {
        $oImport->writeHTMLImportLog($logfile);
    } else {
        $oImport->writeImportLog($logfile);
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
    $err = sendmail($to, $from, $cc = '', $bcc = '', $subject, $themail);
    if ($err) error_log("import sending mail: Error:$err");
    if ($filetmp) unlink($logfile);
}
$err = $oImport->getErrorMessage();
if ($err) $action->ExitError($err);
?>