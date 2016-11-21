<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Functions to send document by email
 *
 * @author Anakeen
 * @version $Id: mailcard.php,v 1.83 2008/12/16 15:52:35 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/sendmail.php");
// -----------------------------------
function mailcard(Action & $action)
{
    $docid = GetHttpVars("id");
    $cr = GetHttpVars("cr"); // want a status
    $dbaccess = $action->dbaccess;
    $doc = new_Doc($dbaccess, $docid);
    // control sending
    $err = $doc->control('send');
    if ($err != "") $action->exitError($err);
    $famMail = new_doc($dbaccess, 'MAIL');
    $err = $famMail->control('create');
    if ($err != "") $action->exitError($err);
    
    $tmailto = $tmailcc = $tmailbcc = array();
    $mailfrom = GetHttpVars("_mail_from");
    
    foreach (array(
        "plain",
        "link"
    ) as $format) {
        $tmailto[$format] = array();
        $tmailcc[$format] = array();
        $tmailbcc[$format] = array();
    }
    
    $tuid = array(); // list of user id to notify
    $mt = GetHttpVars("to"); // simple arguments (can be use with wsh
    if ($mt == "") {
        $rtype = GetHttpVars("_mail_copymode", "");
        $raddr = GetHttpVars("_mail_recip", "");
        $idraddr = GetHttpVars("_mail_recipid", "");
        $tformat = GetHttpVars("_mail_sendformat", "");
        if ((is_array($raddr)) && (count($raddr) > 0)) {
            foreach ($raddr as $k => $v) {
                $v = trim($v);
                if ($v != "") {
                    if ($tformat[$k] == "") $tformat[$k] = "plain";
                    switch ($rtype[$k]) {
                        case "cc":
                            $tmailcc[$tformat[$k]][$v] = $v;
                            break;

                        case "bcc":
                            $tmailbcc[$tformat[$k]][$v] = $v;
                            break;

                        default:
                            $tmailto[$tformat[$k]][$v] = $v;
                            if ($idraddr[$k] > 0) $tuid[] = $idraddr[$k];
                            break;
                        }
                }
            }
        }
    } else {
        // other notation
        $tmailto["plain"][0] = $mt;
        $oldcc = GetHttpVars("cc");
        if ($oldcc) $tmailcc["plain"][0] = $oldcc;
        $oldbcc = GetHttpVars("bcc");
        if ($oldbcc) $tmailbcc["plain"][0] = $oldbcc;
        if ($mailfrom == "") $mailfrom = GetHttpVars("from");
    }
    
    $sendedmail = false;
    foreach (array(
        "plain",
        "link"
    ) as $format) {
        
        $mailto = implode(",", $tmailto[$format]);
        $mailcc = implode(",", $tmailcc[$format]);
        $mailbcc = implode(",", $tmailbcc[$format]);
        // correct trim --->
        setHttpVar("_mail_to", $mailto);
        setHttpVar("_mail_cc", $mailcc);
        setHttpVar("_mail_bcc", $mailbcc);
        setHttpVar("_mail_from", $mailfrom);
        if ($format == "link") setHttpVar("_mail_format", "htmlnotif");
        if (($mailto != "") || ($mailcc != "") || ($mailbcc != "")) {
            $err = sendmailcard($action);
            $sendedmail = true;
        }
    }
    
    if ($cr == "Y") {
        if ($err != "") $action->exitError(sprintf(_("the document %s has not be sended :\n %s") , $doc->title, $err));
        elseif (!$sendedmail) $action->addWarningMsg(sprintf(_("the document %s has not been sended : no recipient") , $doc->title));
    }
    
    foreach ($tuid as $uid) {
        if ($uid > 0) {
            $tu = getTDoc($dbaccess, $uid);
            $wuid = getv($tu, "us_whatid");
            $doc->addUTag($wuid, "TOVIEW");
        }
    }
    
    redirect($action, GetHttpVars("redirect_app", "FDL") , GetHttpVars("redirect_act", "FDL_CARD&latest=Y&refreshfld=Y&id=" . $doc->id) , $action->GetParam("CORE_STANDURL"));
}
// -----------------------------------
function sendmailcard(Action & $action)
{
    
    $sendcopy = true;
    $addfiles = array();
    $userinfo = null;
    $err = sendCard($action, GetHttpVars("id") , GetHttpVars("_mail_to", '') , GetHttpVars("_mail_cc", "") , GetHttpVars("_mail_subject") , GetHttpVars("zone") , GetHttpVars("ulink", "N") == "Y", GetHttpVars("_mail_cm", "") , GetHttpVars("_mail_from", "") , GetHttpVars("_mail_bcc", "") , GetHttpVars("_mail_format", "html") , $sendcopy, $addfiles, $userinfo, GetHttpVars("_mail_savecopy", "no") == "yes");
    
    if ($err != "") return $err;
    // also change state sometime with confirmmail action
    $state = GetHttpVars("state");
    
    if ($state != "") {
        
        $docid = GetHttpVars("id");
        
        $dbaccess = $action->dbaccess;
        $doc = new_Doc($dbaccess, $docid);
        if ($doc->wid > 0) {
            if ($state != "-") {
                /**
                 * @var WDoc $wdoc
                 */
                $wdoc = new_Doc($dbaccess, $doc->wid);
                $wdoc->Set($doc);
                $err = $wdoc->ChangeState($state, _("email sended") , true);
                if ($err != "") $action->addWarningMsg($err);
            }
        } else {
            $action->AddLogMsg(sprintf(_("the document %s is not related to a workflow") , $doc->title));
        }
    }
    return '';
}

function sendCard(Action & $action, $docid, $to, $cc, $subject, $zonebodycard = "", // define mail layout
$ulink = false, // don't see hyperlink
$comment = "", $from = "", $bcc = "", $format = "html", // define view action
$sendercopy = true, // true : a copy is send to the sender according to the Freedom user parameter
$addfiles = array() , $userinfo = null, $savecopy = false)
{
    if (is_null($userinfo)) {
        $notifySendMail = \ApplicationParameterManager::getParameterValue('CORE', 'CORE_NOTIFY_SENDMAIL');
    } else {
        $notifySendMail = $userinfo ? \Dcp\Family\Mailtemplate::NOTIFY_SENDMAIL_ALWAYS : \Dcp\Family\Mailtemplate::NOTIFY_SENDMAIL_NEVER;
    }
    // -----------------------------------
    $viewonly = (GetHttpVars("viewonly", "N") == "Y");
    if ((!$viewonly) && ($to == "") && ($cc == "") && ($bcc == "")) return _("mail dest is empty");
    // -----------------------------------
    global $ifiles;
    global $tfiles;
    global $tmpfile;
    global $vf;
    global $doc;
    global $pubdir;
    global $action;
    
    $ifiles = array();
    $tfiles = array();
    $tmpfile = array();
    $mixed = true; // to see file as attachement
    $err = '';
    $pfout = $ppdf = '';
    // set title
    setHttpVar("id", $docid); // for view zone
    if (GetHttpVars("_mail_format") == "") setHttpVar("_mail_format", $format);
    
    $dbaccess = $action->dbaccess;
    $doc = new_Doc($dbaccess, $docid);
    
    $ftitle = str_replace(array(
        " ",
        "/",
        ")",
        "("
    ) , "_", $doc->title);
    $ftitle = str_replace("'", "", $ftitle);
    $ftitle = str_replace("\"", "", $ftitle);
    $ftitle = str_replace("&", "", $ftitle);
    
    $to = str_replace("\"", "'", $to);
    $from = str_replace("\"", "'", $from);
    $cc = str_replace("\"", "'", $cc);
    $bcc = str_replace("\"", "'", $bcc);
    
    $vf = newFreeVaultFile($dbaccess);
    $pubdir = DEFAULT_PUBDIR;
    $szone = false;
    $sgen = $sgen1 = '';
    
    $message = new \Dcp\Mail\Message();
    
    if ($sendercopy && $action->getParam("FDL_BCC") == "yes") {
        $umail = getMailAddr($action->user->id);
        if ($umail != "") {
            if ($bcc != "") $bcc = "$bcc,$umail";
            else $bcc = "$umail";
        }
    }
    if ($from == "") {
        $from = getMailAddr($action->user->id);
        if ($from == "") $from = getParam('SMTP_FROM');
        if ($from == "") $from = $action->user->login . '@' . php_uname('n');
    }
    
    if ($subject == "") $subject = $ftitle;
    $subject = str_replace("\"", "'", $subject);
    
    $layout = "maildoc.xml"; // the default
    if ($format == "htmlnotif") {
        $layout = "mailnotification.xml";
        $zonebodycard = "FDL:MAILNOTIFICATION:S";
    }
    
    if ($zonebodycard == "") $zonebodycard = $doc->defaultmview;
    if ($zonebodycard == "") $zonebodycard = $doc->defaultview;
    $binary = false;
    if (preg_match("/[A-Z]+:[^:]+:S/", $zonebodycard, $reg)) $szone = true; // the zonebodycard is a standalone zone ?
    if (preg_match("/[A-Z]+:[^:]+:T/", $zonebodycard, $reg)) setHttpVar("dochead", "N"); // the zonebodycard without head ?
    if (preg_match("/[A-Z]+:[^:]+:B/", $zonebodycard, $reg)) $binary = true;
    
    if ($binary) {
        
        $binfile = $doc->viewDoc($zonebodycard);
        if (!is_file($binfile)) $err = $binfile;
        
        if ($err == "") {
            $mime = $ext = '';
            $engine = $doc->getZoneTransform($zonebodycard);
            if ($engine) {
                include_once ("FDL/Lib.Vault.php");
                $outfile = uniqid(getTmpDir() . "/conv") . ".$engine";
                $err = convertFile($binfile, $engine, $outfile, $info);
                if ($err == "") {
                    $mime = getSysMimeFile($outfile, basename($outfile));
                    $ext = getExtension($mime);
                    $binfile = $outfile;
                }
            } else {
                $mime = getSysMimeFile($binfile, basename($binfile));
                $ext = getExtension($mime);
                if (!$ext) {
                    $tplfile = $doc->getZoneFile($zonebodycard);
                    $ext = getFileExtension($tplfile);
                }
            }
            
            $message->addAttachment(new \Dcp\Mail\Attachment($binfile, sprintf("%s.%s", $doc->title, $ext) , $mime));
            
            $zonebodycard = "FDL:EMPTY";
        }
    }
    
    if ($err == "") {
        setHttpVar("target", "mail");
        if (preg_match("/html/", $format, $reg)) {
            
            if ($action->GetParam("CORE_URLINDEX") != "") {
                $turl = parse_url($action->GetParam("CORE_URLINDEX"));
                $url = $turl["scheme"] . '://' . $turl["host"];
                if (isset($turl["port"])) $url.= ':' . $turl["port"];
                if (isset($turl["path"])) $url.= $turl["path"] . "/";
                $baseurl = $url;
                $absurl = $action->GetParam("CORE_URLINDEX");
            } else {
                $absurl = $action->GetParam("CORE_ABSURL");
                $baseurl = $action->GetParam("CORE_ABSURL");
            }
            
            if ($szone) {
                
                $doc->viewDoc($zonebodycard, "mail", $ulink, false, true);
                
                $doc->lay->Set("absurl", $absurl);
                $doc->lay->Set("baseurl", $baseurl);
                $sgen = $doc->lay->gen();
                if ($comment != "") {
                    $comment = nl2br($comment);
                    $sgen = preg_replace("'<body([^>]*)>'i", "<body \\1><P>$comment<P><HR>", $sgen);
                }
            } else {
                // contruct HTML mail
                $docmail = new Layout(getLayoutFile("FDL", $layout) , $action);
                
                $docmail->Set("TITLE", $doc->title);
                $docmail->Set("ID", $doc->id);
                $docmail->Set("zone", $zonebodycard);
                $docmail->Set("absurl", $absurl);
                $docmail->Set("baseurl", $baseurl);
                if ($comment != "") {
                    $docmail->setBlockData("COMMENT", array(
                        array(
                            "boo"
                        )
                    ));
                    $docmail->set("comment", nl2br($comment));
                }
                
                $sgen = $docmail->gen();
            }
            if ($viewonly) {
                echo $sgen;
                exit;
            }
            
            $sgen1 = preg_replace_callback('/src="(FDL\/geticon[^"]+)"/i', function ($matches)
            {
                return imgvaultfile($matches[1]);
            }
            , $sgen);
            
            $sgen1 = preg_replace_callback(array(
                '/SRC="([^"]+)"/',
                '/src="([^"]+)"/'
            ) , function ($matches)
            {
                return srcfile($matches[1]);
            }
            , $sgen1);
            
            $pfout = uniqid(getTmpDir() . "/" . $doc->id);
            $fout = fopen($pfout, "w");
            
            fwrite($fout, $sgen1);
            
            fclose($fout);
        }
        
        if (preg_match("/pdf/", $format, $reg)) {
            // ---------------------------
            // contruct PDF mail
            if ($szone) {
                $sgen = $doc->viewDoc($zonebodycard, "mail", false);
            } else {
                
                $docmail2 = new Layout(getLayoutFile("FDL", $layout) , $action);
                
                $docmail2->Set("zone", $zonebodycard);
                $docmail2->Set("TITLE", $doc->title);
                
                $sgen = $docmail2->gen();
            }
            $sgen2 = preg_replace_callback('/src="([^"]+)"/i', function ($matches)
            {
                return realfile($matches[1]);
            }
            , $sgen);
            
            $ppdf = uniqid(getTmpDir() . "/" . $doc->id) . ".pdf.html";
            $fout = fopen($ppdf, "w");
            $sgen2 = preg_replace('/\xE2\x82\xAC/', '&euro;', $sgen2);
            fwrite($fout, $sgen2);
            fclose($fout);
        }
        // ---------------------------
        // construct message's body
        if (preg_match("/html/", $format, $reg)) {
            $body = file_get_contents($pfout);
            $mailBody = new \Dcp\Mail\Body($body, 'text/html');
            $message->setBody($mailBody);
        } else if ($format == "pdf") {
            $mailBody = new \Dcp\Mail\Body($comment, 'text/plain');
            $message->setBody($mailBody);
        }
        
        if ($format != "pdf") {
            // ---------------------------
            // insert attached files
            if (preg_match_all("/(href|src)=\"cid:([^\"]*)\"/i", $sgen, $match)) {
                $tcids = $match[2]; // list of file references inserted in mail
                $afiles = $doc->GetFileAttributes();
                $taids = array_keys($afiles);
                if (count($afiles) > 0) {
                    foreach ($tcids as $vaf) {
                        $tf = explode("+", $vaf);
                        if (count($tf) == 1) {
                            $aid = $tf[0];
                            $index = - 1;
                        } else {
                            $aid = $tf[0];
                            $index = $tf[1];
                        }
                        if (in_array($aid, $taids)) {
                            if ($afiles[$aid]->repeat) $va = $doc->getMultipleRawValues($aid, "", $index);
                            else $va = $doc->getRawValue($aid);
                            
                            if ($va != "") {
                                $ticon = explode("|", $va);
                                $mime = empty($ticon[0]) ? false : $ticon[0];
                                $vid = empty($ticon[1]) ? false : $ticon[1];
                                
                                if ($vid != "") {
                                    /**
                                     * @var VaultFileInfo $info
                                     */
                                    if ($vf->Retrieve($vid, $info) == "") {
                                        
                                        $cidindex = $vaf;
                                        if (($mixed) && ($afiles[$aid]->type != "image")) $cidindex = $info->name;
                                        $message->addBodyRelatedAttachment(new \Dcp\Mail\RelatedAttachment($info->path, $info->name, ($info->mime_s ? $info->mime_s : $mime) , $cidindex));
                                    }
                                }
                            }
                        }
                    }
                }
            }
            // ---------------------------
            // add icon image
            if (preg_match("/html/", $format, $reg)) {
                if (!$szone) {
                    $va = $doc->icon;
                    if ($va != "") {
                        $ticon = explode("|", $va);
                        $mime = empty($ticon[0]) ? false : $ticon[0];
                        $vid = empty($ticon[1]) ? false : $ticon[1];
                        
                        if ($vid != "") {
                            /**
                             * @var VaultFileInfo $info
                             */
                            if ($vf->Retrieve($vid, $info) == "") {
                                $mailAttach = new \Dcp\Mail\RelatedAttachment($info->path, $info->name, ($info->mime_s ? $info->mime_s : $mime) , 'icon');
                                $message->addBodyRelatedAttachment($mailAttach);
                            }
                        } else {
                            $icon = $doc->getIcon();
                            if (file_exists($pubdir . "/$icon")) {
                                $mailAttach = new \Dcp\Mail\RelatedAttachment(sprintf("%s/%s", $pubdir, $icon) , 'icon', sprintf("image/%s", fileextension($icon)) , 'icon');
                                $message->addBodyRelatedAttachment($mailAttach);
                            }
                        }
                    }
                }
            }
            // ---------------------------
            // add inserted image
            foreach ($ifiles as $v) {
                if (file_exists($pubdir . "/$v")) {
                    $mailAttach = new \Dcp\Mail\RelatedAttachment(sprintf("%s/%s", $pubdir, $v) , basename($v) , sprintf("image/%s", fileextension($v)) , $v);
                    $message->addBodyRelatedAttachment($mailAttach);
                }
            }
            
            foreach ($tfiles as $k => $v) {
                if (file_exists($v)) {
                    $mailAttach = new \Dcp\Mail\RelatedAttachment($v, $k, trim(shell_exec(sprintf("file --mime -b %s", escapeshellarg($v)))) , $k);
                    $message->addBodyRelatedAttachment($mailAttach);
                }
            }
            // Other files,
            if (count($addfiles) > 0) {
                foreach ($addfiles as $vf) {
                    if (count($vf) == 3) {
                        $fview = $vf[0];
                        $fname = $vf[1];
                        $fmime = $vf[2];
                        
                        $fgen = $doc->viewDoc($fview, "mail");
                        $fpname = getTmpDir() . "/" . str_replace(array(
                            " ",
                            "/",
                            "(",
                            ")"
                        ) , "_", uniqid($doc->id) . $fname);
                        if ($fp = fopen($fpname, 'w')) {
                            fwrite($fp, $fgen);
                            fclose($fp);
                        }
                        $fpst = stat($fpname);
                        if (is_array($fpst) && $fpst["size"] > 0) {
                            $message->addAttachment(new \Dcp\Mail\Attachment($fpname, $fname, $fmime));
                        }
                    }
                }
            }
        }
        if (preg_match("/pdf/", $format, $reg)) {
            // try PDF
            $fps = uniqid(getTmpDir() . "/" . $doc->id) . "ps";
            $fpdf = uniqid(getTmpDir() . "/" . $doc->id) . "pdf";
            /*
             * Remove CSS rules as they are not interpreted by html2ps
             * (and can cause html2ps to choke on some strings and
             * print out raw CSS instructions in the resulting output
             * file).
            */
            $html = file_get_contents($ppdf);
            if ($html !== false) {
                $tmp = tempnam(dirname($ppdf) , basename($ppdf) . ".cleanup");
                if ($tmp !== false) {
                    $html = preg_replace('#<style[^>]*>.*?</style\s*>#s', '', $html);
                    if (file_put_contents($tmp, $html) !== false) {
                        if (rename($tmp, $ppdf) === false) {
                            unlink($tmp);
                        }
                    } else {
                        unlink($tmp);
                    }
                }
            }
            $cmdpdf = sprintf("recode u8..l9 %s && html2ps -U -i 0.5 -b %s/ %s > %s && ps2pdf %s %s", escapeshellarg($ppdf) , // recode
            escapeshellarg($pubdir) , escapeshellarg($ppdf) , escapeshellarg($fps) , // html2ps
            escapeshellarg($fps) , escapeshellarg($fpdf) // ps2pdf
            );
            system(($cmdpdf) , $status);
            if ($status == 0) {
                $message->addAttachment(new \Dcp\Mail\Attachment($fpdf, sprintf("%s.pdf", $doc->title) , 'application/pdf'));
            } else {
                $action->addlogmsg(sprintf(_("PDF conversion failed for %s") , $doc->title));
            }
        }
        /*
        $err = sendmail($to, $from, $cc, $bcc, $subject, $multi_mix);
        */
        $message->setFrom($from);
        $message->addTo($to);
        $message->addCc($cc);
        $message->addBcc($bcc);
        $message->setSubject($subject);
        $err = $message->send();
        if ($err == "") {
            if ($savecopy) {
                createSentMessage($to, $from, $cc, $bcc, $subject, $message, $doc);
            }
            if ($cc != "") $lsend = sprintf("%s and %s", $to, $cc);
            else $lsend = $to;
            $doc->addHistoryEntry(sprintf(_("sended to %s") , $lsend));
            $action->addlogmsg(sprintf(_("sending %s to %s") , $doc->title, $lsend));
            if (\Dcp\Family\Mailtemplate::NOTIFY_SENDMAIL_ALWAYS === $notifySendMail) {
                $action->addWarningMsg(sprintf(_("sending %s to %s") , $doc->title, $lsend));
            }
        } else {
            $action->log->warning($err);
            $action->addlogmsg(sprintf(_("%s cannot be sent") , $doc->title));
            if (\Dcp\Family\Mailtemplate::NOTIFY_SENDMAIL_ALWAYS === $notifySendMail || \Dcp\Family\Mailtemplate::NOTIFY_SENDMAIL_ERRORS_ONLY === $notifySendMail) {
                $action->addWarningMsg(sprintf(_("%s cannot be sent") , $doc->title));
                $action->addWarningMsg($err);
            }
        }
        // suppress temporaries files
        if (isset($ftxt) && is_file($ftxt)) unlink($ftxt);
        if (isset($fpdf) && is_file($fpdf)) unlink($fpdf);
        if (isset($fps) && is_file($fps)) unlink($fps);
        if (isset($pfout) && is_file($pfout)) unlink($pfout);
        if (isset($ppdf) && is_file($ppdf)) unlink($ppdf);
        if (isset($binfile) && is_file($binfile)) unlink($binfile);
        
        $tmpfile = array_merge($tmpfile, $tfiles);
        foreach ($tmpfile as $v) {
            if (file_exists($v) && (substr($v, 0, 9) == getTmpDir() . "/")) unlink($v);
        }
    }
    
    return $err;
}

function srcfile($src)
{
    global $ifiles;
    $vext = array(
        "gif",
        "png",
        "jpg",
        "jpeg",
        "bmp"
    );
    if (substr($src, 0, 3) == "cid") return "src=\"$src\"";
    if (substr($src, 0, 4) == "http") {
        $chopped_src = '';
        // Detect HTTP URLs pointing to myself
        foreach (array(
            'CORE_URLINDEX',
            'CORE_PUBURL'
        ) as $url) {
            $url = getParam($url);
            if (strlen($url) <= 0) {
                continue;
            }
            if (strcmp(substr($src, 0, strlen($url)) , $url) == 0) {
                // Chop the URL base part, and leave only the args/vars
                $chopped_src = substr($src, strlen($url));
                break;
            }
        }
        if ($chopped_src == '') {
            return sprintf('src="%s"', $src);
        }
        $src = $chopped_src;
    }
    
    if (preg_match("/(.*)(app=FDL.*action=EXPORTFILE.*)$/", $src, $reg)) {
        return imgvaultfile(str_replace('&amp;', '&', $reg[2]));
    } elseif (preg_match('!^file/(?P<docid>\d+)/(?P<vid>\d+)/(?P<attrid>[^/]+)/(?P<index>[^/]+)/(?P<fname>[^?]+)!', $src, $reg)) {
        return imgvaultfile($src);
    }
    
    if (!in_array(strtolower(fileextension($src)) , $vext)) return "";
    
    $ifiles[$src] = $src;
    return "src=\"cid:$src\"";
}
function imgvaultfile($src)
{
    global $tfiles;
    $newfile = copyvault($src);
    if ($newfile) {
        $src = "img" . count($tfiles);
        $tfiles[$src] = $newfile;
        return "src=\"cid:$src\" ";
    }
    return "";
}
function copyvault($src)
{
    include_once ('FDL/exportfile.php');
    
    if (preg_match("/(.*)(app=FDL.*action=EXPORTFILE.*)docid=([^&]*).*&attrid=([^&]*).*&index=([^&]*)/", $src, $reg)) {
        $fileDoc = new_doc("", $reg[3]);
        $filePath = getExportFileDocumentPath($fileDoc, $reg[4], $reg[5]);
        
        if ($filePath) {
            $newfile = uniqid(getTmpDir() . "/img");
            if (!copy($filePath, $newfile)) {
                return "";
            }
            return $newfile;
        }
    }
    if (preg_match("|^FDL/geticon\\.php\\?vaultid=(?P<vid>\\d+)|", $src, $reg)) {
        $info = vault_properties($reg['vid']);
        $newfile = uniqid(getTmpDir() . "/img");
        if (!copy($info->path, $newfile)) {
            return "";
        }
        return $newfile;
    }
    if (preg_match('!^file/(?P<docid>\d+)/(?P<vid>\d+)/(?P<attrid>[^/]+)/(?P<index>[^/]+)/(?P<fname>[^?]+)!', $src, $reg)) {
        
        $fileDoc = new_doc("", $reg['docid']);
        $filePath = getExportFileDocumentPath($fileDoc, $reg['attrid'], $reg['index']);
        
        if ($filePath) {
            $newfile = uniqid(getTmpDir() . "/img");
            if (!copy($filePath, $newfile)) {
                return "";
            }
            return $newfile;
        }
    }
    
    return "";
}

function realfile($src)
{
    global $vf;
    global $doc;
    global $pubdir;
    global $tmpfile;
    $f = false;
    if ($src == "cid:icon") {
        $va = $doc->icon;
    } else {
        if (substr($src, 0, 4) == "cid:") {
            $va = $doc->getRawValue(substr($src, 4));
        } elseif (preg_match("/(.*)(app=FDL.*action=EXPORTFILE.*)$/", $src, $reg)) {
            $va = copyvault(str_replace('&amp;', '&', $reg[2]));
            $tmpfile[] = $va;
        } else {
            $va = $src;
        }
    }
    
    if ($va != "") {
        $ticon = explode("|", $va);
        $vid = empty($ticon[1]) ? false : $ticon[1];
        
        if ($vid != "") {
            $info = null;
            /**
             * @var VaultFileInfo $info
             */
            if ($vf->Retrieve($vid, $info) == "") {
                $f = $info->path;
            }
        } else {
            if (file_exists($va)) {
                $f = $va;
            } elseif (file_exists($pubdir . "/$va")) {
                $f = $pubdir . "/$va";
            } elseif (file_exists($pubdir . "/Images/$va")) {
                $f = $pubdir . "/Images/$va";
            } elseif ((substr($va, 0, 12) == getTmpDir() . '/img') && file_exists($va)) {
                $f = $va;
            }
        }
    }
    if ($f) return "src=\"$f\"";
    return "";
}
