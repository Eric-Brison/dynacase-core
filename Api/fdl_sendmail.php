<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Send mail using freedom sendmail
 *
 * @author Anakeen
 * @version $Id: fdl_sendmail.php,v 1.1 2007/01/19 16:24:03 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

$usage = new ApiUsage();

$usage->setDefinitionText("Send mail using freedom sendmail");
$to = $usage->addOptionalParameter("to", "email to send to");
$cc = $usage->addOptionalParameter("cc", "cc");
$bcc = $usage->addOptionalParameter("bcc", "bcc");
$subject = $usage->addOptionalParameter("subject", "subject");
$file = $usage->addOptionalParameter("file", "file (can be a file name or stdin");
$htmlmode = $usage->addOptionalParameter("htmlmode", "activacte htmlmode");

$usage->verify();
/**
 * @var Action $action
 */
$from = getMailAddr($action->user->id);
if ($from == "") $from = getParam('SMTP_FROM');
if ($from == "") $from = $from = $action->user->login . '@' . php_uname('n');

include_once ("FDL/sendmail.php");
include_once ("WHAT/Lib.FileMime.php");

$tmpfile = '';
$message = new \Dcp\Mail\Message();
$data = '';
if ($file && $file == 'stdin') {
    $data = file_get_contents('php://stdin');
    $tmpfile = tempnam(getTmpDir() , 'fdl_sendmail_stdin');
    file_put_contents($tmpfile, $data);
    if ($htmlmode == 'Y') {
        $mime = 'text/html';
    } else {
        $mime = getSysMimeFile($tmpfile);
        $ext = getExtension($mime);
        if ($ext != '') {
            if (rename($tmpfile, $tmpfile . '.' . $ext) !== false) {
                $tmpfile = $tmpfile . '.' . $ext;
            }
        }
    }
    $file = $tmpfile;
} elseif ($file) {
    $mime = getSysMimeFile($file);
} else {
    $mime = 'application/x-empty';
}
if ($file) {
    if (preg_match('|text/html|', $mime)) {
        $message->setBody(new \Dcp\Mail\Body(file_get_contents($file) , $mime));
    } elseif (preg_match('|text|', $mime)) {
        $message->setBody(new \Dcp\Mail\Body(file_get_contents($file) , $mime));
    } else {
        $message->addAttachment(new \Dcp\Mail\Attachment($file, basename($file) , $mime));
    }
}

if ($subject == "" && $file) {
    $subject = basename($file);
}
if ($subject == "") {
    $subject = _("no subject");
}
$err = sendmail($to, $from, $cc, $bcc, $subject, $message);
if ($err) {
    print "Error:$err\n";
}
if ($tmpfile != '') {
    unlink($tmpfile);
}
