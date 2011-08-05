<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Send mail using freedom sendmail
 *
 * @author Anakeen 2007
 * @version $Id: fdl_sendmail.php,v 1.1 2007/01/19 16:24:03 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */

include_once ("FDL/sendmail.php");

$to = GetHttpVars("to");
$cc = GetHttpVars("cc");
$bcc = GetHttpVars("bcc");
$subject = GetHttpVars("subject");
$file = GetHttpVars("file");
$htmlmode = GetHttpVars("htmlmode");

$from = getMailAddr($action->user->id);
if ($from == "") $from = getParam('SMTP_FROM');
if ($from == "") $from = $from = $action->user->login . '@' . php_uname('n');

$themail = new Fdl_Mail_mime();
if ($file && $file != 'stdin') {
    $mime = trim(shell_exec(sprintf("file -ib %s", escapeshellarg($file))));
    if (preg_match("|text/html|", $mime)) {
        $themail->setHTMLBody($file, true);
    } else if (preg_match("|text|", $mime)) {
        $themail->setTxtBody($file, true);
    } else {
        $themail->addAttachment($file, $mime);
    }
} else {
    // stream_set_blocking(STDIN,0);
    if ($file = 'stdin') {
        $out = "";
        $line = true;
        while ($line !== false) {
            $line = fgets(STDIN);
            $out.= "$line\n";
        }
        if ($htmlmode == "Y") $themail->setHTMLBody($out, false);
        else $themail->setTxtBody($out, false);
    }
}

if ($subject == "") $subject = basename($file);
if ($subject == "") $subject = _("no subject");
$err = sendmail($to, $from, $cc, $bcc, $subject, $themail);
if ($err) print "Error:$err\n";
?>