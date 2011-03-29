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


$usage=new ApiUsage();
$usage->setText("Import documents from description file");
$usage->addNeeded("file","the description file path");
$usage->addOption("analyze","analyze only",array("yes","no"));
$usage->addOption("htmlmode","analyze report mode in html",array("yes","no"));
$usage->addOption("reinitattr","reset attribute before import family update",array("yes","no"));
$usage->addOption("to","email address to send report");
$usage->verify();


$to = $action->getArgument("to");
// mode HTML
$appl=new Application();
$appl->Set("FREEDOM",	     $core);

$action->Set("FREEDOM_IMPORT",$appl);

$out= ($action->execute());
if ($to) {
    include_once("FDL/sendmail.php");

    $themail = new Fdl_Mail_mime();
    $themail->setHTMLBody($out,false);

    $from=getMailAddr($action->user->id);
    if ($from == "")  $from = getParam('SMTP_FROM');
    if ($from == "")  $from = $action->user->login.'@'.php_uname('n');

    $subject=sprintf(_("result of import  %s"), basename(GetHttpVars("file")));
    $err=sendmail($to,$from,$cc,$bcc,$subject,$themail);
    if ($err) error_log("import sending mail: Error:$err");
} else {
    if (GetHttpVars("htmlmode") == "Y") print $out;
}






?>