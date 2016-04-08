<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Send document mail with SMTP protocol
 *
 * @author Anakeen
 * @version $Id: sendmail.php,v 1.4 2007/10/10 16:15:35 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
/**
 * Send mail via smtp server
 * @param string $to mail addresses (, separate)
 * @param string $cc mail addresses (, separate)
 * @param string $bcc mail addresses (, separate)
 * @param string $from mail address
 * @param string $subject mail subject
 * @param Mail_mime|\Dcp\Mail\Message &$mimemail mail mime object
 * @return string error message : if no error: empty if no error
 */
function sendmail($to, $from, $cc, $bcc, $subject, &$mimemail, $multipart = null)
{
    if (is_a($mimemail, \Dcp\Mail\Message::className)) {
        return __sendmail_Dcp_Mail_Message($to, $from, $cc, $bcc, $subject, $mimemail);
    }
    
    require_once 'PEAR.php';
    
    include_once ('Mail/mime.php');
    include_once ('Net/SMTP.php');
    $rcpt = array_merge(explode(',', $to) , explode(',', $cc) , explode(',', $bcc));
    
    $host = getParam('SMTP_HOST', 'localhost');
    $port = getParam('SMTP_PORT', 25);
    $login = getParam('SMTP_LOGIN');
    $password = getParam('SMTP_PASSWORD');
    
    if (is_a($mimemail, 'Mail_Mime')) {
        $mimemail->setFrom($from);
        if ($cc != '') $mimemail->addCc($cc);
    }
    
    $xh['To'] = $to;
    /* Create a new Net_SMTP object. */
    if (!($smtp = new Net_SMTP($host, $port))) {
        die("Unable to instantiate Net_SMTP object\n");
    }
    $smtp->setDebug(false);
    /* Connect to the SMTP server. */
    if (PEAR::isError($e = $smtp->connect())) {
        return ("smtp connect:" . $e->getMessage());
    }
    
    if ($login) {
        if (PEAR::isError($e = $smtp->auth($login, $password))) {
            return ("smtp login:" . $e->getMessage());
        }
    }
    /* Send the 'MAIL FROM:' SMTP command. */
    $smtp_from = $from;
    if (preg_match('/<(?<from>[^>]*)>/', $from, $reg)) {
        $smtp_from = $reg['from'];
    }
    if (PEAR::isError($smtp->mailFrom($smtp_from))) {
        return ("Unable to set sender to <$smtp_from>");
    }
    /* Address the message to each of the recipients. */
    foreach ($rcpt as $v) {
        $v = trim($v);
        if ($v) {
            if (preg_match("/<([^>]*)>/", $v, $reg)) {
                $v = $reg[1];
            }
            if (PEAR::isError($res = $smtp->rcptTo($v))) {
                return ("Unable to add recipient <$v>: " . $res->getMessage());
            }
        }
    }
    setlocale(LC_TIME, 'C');
    
    $data = '';
    
    if (is_a($mimemail, 'Fdl_Mail_mimePart')) {
        
        $mm = new Mail_Mime();
        $mm->_build_params['head_charset'] = 'UTF-8';
        
        $mm->setFrom($from);
        if ($cc != '') {
            $mm->addCc($cc);
        }
        /**
         * @var Fdl_Mail_mimePart $mimemail
         */
        $email = $mimemail->encode();
        if (PEAR::isError($email)) {
            $err = sprintf("Error encoding Fdl_Mail_mimePart : %s", $email->message);
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . $err);
            return $err;
        }
        
        $txtHeaders = $mm->txtHeaders(array_merge($email['headers'], array(
            'To' => $to,
            'Subject' => $subject,
            'Date' => strftime("%a, %d %b %Y %H:%M:%S %z", time()) ,
            'Message-Id' => sprintf("<%s@%s>", strftime("%Y%M%d%H%M%S-", time()) . rand(1, 65535) , $host) ,
            'User-Agent' => sprintf("Dynacase Platform %s", getParam('VERSION'))
        )));
        
        $data = $txtHeaders . $mm->_build_params['eol'] . $email['body'];
    } else {
        
        $body = $mimemail->get();
        
        $xh['Date'] = strftime("%a, %d %b %Y %H:%M:%S %z", time());
        // $xh['Content-type']= "multipart/related";
        $xh['Subject'] = $subject;
        $xh['Message-Id'] = '<' . strftime("%Y%M%d%H%M%S-", time()) . rand(1, 65535) . "@$host>";
        
        $xh['User-Agent'] = sprintf("Dynacase Platform %s", getParam('VERSION'));
        $data = "";
        $h = $mimemail->headers($xh);
        if ($multipart) $h['Content-Type'] = str_replace("mixed", $multipart, $h['Content-Type']);
        
        foreach ($h as $k => $v) {
            $data.= "$k: $v\r\n";
        }
        
        $data.= "\r\n" . $body;
    }
    /* Set the body of the message. */
    if (PEAR::isError($smtp->data($data))) {
        return ("Unable to send data");
    }
    /* Disconnect from the SMTP server. */
    $smtp->disconnect();
    return '';
}
function __sendmail_Dcp_Mail_Message($to, $from, $cc, $bcc, $subject, \Dcp\Mail\Message & $message)
{
    $message->setFrom($from);
    $message->addTo($to);
    $message->addCc($cc);
    $message->addBcc($bcc);
    $message->setSubject($subject);
    return $message->send();
}
/**
 * record message sent from freedom
 */
function createSentMessage($to, $from, $cc, $bcc, $subject, &$mimemail, &$doc = null)
{
    include_once ('WHAT/Lib.Common.php');
    $err = '';
    $msg = createDoc(getDbAccessFreedom() , "SENTMESSAGE", true);
    if ($msg) {
        $msg->setValue("emsg_from", $from);
        $msg->setValue("emsg_date", Doc::getTimeDate());
        $msg->setValue("emsg_subject", $subject);
        /**
         * @var Doc $doc
         */
        if ($doc && $doc->id) {
            $msg->setValue("emsg_refid", $doc->id);
            $msg->profid = $doc->profid;
        }
        $trcp = array();
        foreach (explode(',', $to) as $v) {
            if ($v) $msg->addArrayRow("emsg_t_recipient", array(
                "emsg_sendtype" => "to",
                "emsg_recipient" => $v
            ));
        }
        foreach (explode(',', $cc) as $v) {
            if ($v) $msg->addArrayRow("emsg_t_recipient", array(
                "emsg_sendtype" => "cc",
                "emsg_recipient" => $v
            ));
        }
        foreach (explode(',', $bcc) as $v) {
            if ($v) $msg->addArrayRow("emsg_t_recipient", array(
                "emsg_sendtype" => "bcc",
                "emsg_recipient" => $v
            ));
        }
        
        if (is_a($mimemail, 'Fdl_Mail_mimePart')) {
            // Flatten the MIME parts by expanding and removing the mutipart entities
            $partList = array(&$mimemail
            );
            $i = 0;
            while ($i < count($partList)) {
                if (count($partList[$i]->_subparts) <= 0) {
                    $i++;
                    continue;
                }
                $multipart = $partList[$i];
                array_splice($partList, $i, 1);
                foreach ($multipart->_subparts as & $part) {
                    $partList[] = & $part;
                }
                unset($part);
            }
            // Search for a text/plain part and extract it
            $textPart = null;
            foreach ($partList as $i => & $part) {
                if (preg_match("|^text/plain|", $part->_headers['Content-Type'])) {
                    $textPart = $part;
                    array_splice($partList, $i, 1);
                    break;
                }
            }
            unset($part);
            // Search for a text/html part and extract it
            $htmlPart = null;
            foreach ($partList as $i => & $part) {
                if (preg_match("|^text/html|", $part->_headers['Content-Type'])) {
                    $htmlPart = $part;
                    array_splice($partList, $i, 1);
                    break;
                }
            }
            unset($part);
            // Store the text part
            $textBody = '';
            if ($textPart !== null) {
                /**
                 * @var Mail_mimePart $textPart
                 */
                if ($textPart->_body_file != '') {
                    $textBody = file_get_contents($textPart->_body_file);
                } else {
                    $textBody = $textPart->_body;
                }
                $msg->setValue('emsg_textbody', $textBody);
            }
            // Store the HTML part
            $htmlBody = '';
            if ($htmlPart !== null) {
                /**
                 * @var Mail_mimePart $htmlPart
                 */
                if ($htmlPart->_body_file != '') {
                    $htmlBody = file_get_contents($htmlPart->_body_file);
                } else {
                    $htmlBody = $htmlPart->_body;
                }
                $msg->setValue('emsg_htmlbody', $htmlBody);
            }
            // Store the remaining parts
            foreach ($partList as $i => & $part) {
                $tmpfile = tempnam(getTmpDir() , 'fdl_attach');
                if ($part->_body_file != '') {
                    copy($part->_body_file, $tmpfile);
                } else {
                    file_put_contents($tmpfile, $part->_body);
                }
                $msg->storeFile('emsg_attach', $tmpfile, $part->_filename, $i);
                @unlink($tmpfile);
            }
            unset($part);
            
            $err = $msg->add();
            if ($err != '') {
                return $err;
            }
            
            if ($htmlPart !== null && $htmlBody != '') {
                // Re-link the HTML part CIDs
                foreach ($partList as $i => & $part) {
                    $cid = preg_replace('/^<(.+)>$/', '\1', isset($part->_headers['Content-ID']) ? $part->_headers['Content-ID'] : '');
                    if ($cid != '') {
                        $htmlBody = str_replace(sprintf("cid:%s", $cid) , $msg->getfileLink('emsg_attach', $i) , $htmlBody);
                    }
                }
                unset($part);
                
                $msg->disableEditControl();
                $msg->setValue('emsg_htmlbody', $htmlBody);
                $err = $msg->modify(true);
                $msg->enableEditControl();
                
                if ($err != '') {
                    return $err;
                }
            }
            
            return '';
        } elseif (is_a($mimemail, \Dcp\Mail\Message::className)) {
            /**
             * @var \Dcp\Mail\Message $mimemail
             */
            /**
             * @var \Dcp\Mail\DataSource[] $partList
             */
            $partList = array();
            if (isset($mimemail->body)) {
                $partList[] = $mimemail->body;
            }
            if (count($mimemail->bodyRelated) > 0) {
                foreach ($mimemail->bodyRelated as $part) {
                    $partList[] = $part;
                }
            }
            if (isset($mimemail->altBody)) {
                $partList[] = $mimemail->altBody;
            }
            if (count($mimemail->attachments) > 0) {
                foreach ($mimemail->attachments as $part) {
                    $partList[] = $part;
                }
            }
            /**
             * @var \Dcp\Mail\DataSource $textPart
             */
            $textPart = null;
            /**
             * @var \Dcp\Mail\DataSource $htmlPart
             */
            $htmlPart = null;
            /**
             * @var \Dcp\Mail\DataSource[] $otherPartList
             */
            $otherPartList = array();
            foreach ($partList as $i => $part) {
                if (!isset($textPart) && isset($part) && $part->getMimeType() == 'text/plain') {
                    $textPart = $part;
                } elseif (!isset($htmlPart) && isset($part) && $part->getMimeType() == 'text/html') {
                    $htmlPart = $part;
                } else {
                    $otherPartList[] = $part;
                }
            }
            /* Store text part */
            if ($textPart !== null) {
                $data = $textPart->getData();
                $msg->setValue(\Dcp\AttributeIdentifiers\Sentmessage::emsg_textbody, $data);
            }
            /* Store html part */
            if ($htmlPart !== null) {
                $data = $htmlPart->getData();
                $msg->setValue(\Dcp\AttributeIdentifiers\Sentmessage::emsg_htmlbody, $data);
            }
            /* Store remaining parts */
            foreach ($otherPartList as $i => $part) {
                $tmpfile = tempnam(getTmpDir() , 'Body_getFile');
                if ($tmpfile === false) {
                    break;
                }
                if (file_put_contents($tmpfile, $part->getData()) === false) {
                    unlink($tmpfile);
                    break;
                }
                $msg->setFile(\Dcp\AttributeIdentifiers\Sentmessage::emsg_attach, $tmpfile, $part->getName() , $i);
                unlink($tmpfile);
            }
            
            $err = $msg->add();
            if ($err != '') {
                return $err;
            }
            
            if ($htmlPart !== null) {
                $htmlBody = $htmlPart->getData();
                // Re-link the HTML part CIDs
                foreach ($otherPartList as $i => $part) {
                    if (isset($part->cid)) {
                        $htmlBody = str_replace(sprintf("cid:%s", $part->cid) , $msg->getfileLink('emsg_attach', $i) , $htmlBody);
                    }
                }
                
                $msg->disableEditControl();
                $msg->setValue('emsg_htmlbody', $htmlBody);
                $err = $msg->modify(true);
                $msg->enableEditControl();
                
                if ($err != '') {
                    return $err;
                }
            }
            
            return '';
        }
        
        $msg->setValue("emsg_textbody", $mimemail->_txtbody);
        $msg->setValue("emsg_htmlbody", $mimemail->_htmlbody);
        $linkedbody = $mimemail->_htmlbody;
        foreach ($mimemail->_parts as $k => $v) {
            $tmpfile = tempnam(getTmpDir() , 'fdl_attach');
            file_put_contents($tmpfile, $v["body"]);
            $msg->setFile("emsg_attach", $tmpfile, $v["name"], $k);
            @unlink($tmpfile);
        }
        
        $err = $msg->add();
        // relink body
        if ($err == "") {
            $linkedbody = $mimemail->_htmlbody;
            foreach ($mimemail->_parts as $k => $v) {
                $linkedbody = str_replace("cid:" . $v["cid"], $msg->getFileLink("emsg_attach", $k) , $linkedbody);
            }
            $msg->disableEditControl();
            $msg->setValue("emsg_htmlbody", $linkedbody);
            $err = $msg->modify(true);
        }
    }
    return $err;
}
