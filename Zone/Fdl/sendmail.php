<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Send document mail with SMTP protocol
 *
 * @author Anakeen 2007
 * @version $Id: sendmail.php,v 1.4 2007/10/10 16:15:35 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
include ('Mail/mime.php');
include ('Net/SMTP.php');
/**
 * Send mail via smtp server
 * @param string $to mail addresses (, separate)
 * @param string $cc mail addresses (, separate)
 * @param string $bcc mail addresses (, separate)
 * @param string $from mail address
 * @param string $subject mail subject
 * @param Mail_mime &$mimemail mail mime object
 * @return string error message : if no error: empty if no error
 */
function sendmail($to, $from, $cc, $bcc, $subject, &$mimemail, $multipart = null)
{
    
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
}
/**
 * redefine class to add explicit CID
 */
class Fdl_Mail_mime extends Mail_mime
{
    // USE TO ADD CID in attachment
    
    /**
     * Adds a file to the list of attachments.
     *
     * @param  string  $file       The file name of the file to attach
     *                             OR the file data itself
     * @param  string  $c_type     The content type
     * @param  string  $name       The filename of the attachment
     *                             Only use if $file is the file data
     * @param  bool    $isFilename Whether $file is a filename or not
     *                             Defaults to true
     * @return mixed true on success or PEAR_Error object
     * @access public
     */
    function addAttachment($file, $c_type = 'application/octet-stream', $name = '', $isfilename = true, $encoding = 'base64', $cid = '', $charset = "UTF-8")
    {
        $filedata = ($isfilename === true) ? $this->_file2str($file) : $file;
        if ($isfilename === true) {
            // Force the name the user supplied, otherwise use $file
            $filename = (!empty($name)) ? $name : basename($file);
        } else {
            $filename = $name;
        }
        if (empty($filename)) {
            return PEAR::raiseError('The supplied filename for the attachment can\'t be empty');
        }
        if (PEAR::isError($filedata)) {
            return $filedata;
        }
        
        $this->_parts[] = array(
            'body' => $filedata,
            'name' => $filename,
            'charset' => $charset,
            'c_type' => $c_type,
            'encoding' => $encoding
        );
        return true;
    }
    
    function addAttachmentInline($file, $c_type = 'application/octet-stream', $name = '', $isfilename = true, $encoding = 'base64', $cid = '', $charset = "UTF-8")
    {
        $filedata = ($isfilename === true) ? $this->_file2str($file) : $file;
        if ($isfilename === true) {
            // Force the name the user supplied, otherwise use $file
            $filename = (!empty($name)) ? $name : basename($file);
        } else {
            $filename = $name;
        }
        if (empty($filename)) {
            return PEAR::raiseError('The supplied filename for the attachment can\'t be empty');
        }
        if (PEAR::isError($filedata)) {
            return $filedata;
        }
        
        $this->_parts[] = array(
            'body' => $filedata,
            'name' => $filename,
            'charset' => $charset,
            'c_type' => $c_type,
            'encoding' => $encoding,
            'disposition' => 'inline',
            'cid' => $cid
        );
        return true;
    }
    /**
     * Adds an attachment subpart to a mimePart object
     * and returns it during the build process.
     *
     * @param  Fdl_Mail_mimePart  $obj The mimePart to add the image to
     * @param  array  $value The attachment information
     * @return Fdl_Mail_mimePart  The image mimePart object
     * @access private
     */
    function &_addAttachmentPart(&$obj, $value)
    {
        $params['content_type'] = $value['c_type'];
        $params['encoding'] = $value['encoding'];
        $params['dfilename'] = $value['name'];
        $params['filename'] = $value['name'];
        $params['charset'] = $value['charset'];
        
        if (isset($value['disposition'])) {
            $params['disposition'] = $value['disposition'];
        } else {
            $params['disposition'] = 'attachment';
        }
        
        if (isset($value['cid'])) {
            $params['cid'] = $value['cid'];
        }
        
        if (isset($value['name_encoding'])) {
            $params['name_encoding'] = $value['name_encoding'];
        } else {
            $params['name_encoding'] = 'quoted-printable';
        }
        
        if (isset($value['filename_encoding'])) {
            $params['filename_encoding'] = $value['filename_encoding'];
        } else {
            $params['filename_encoding'] = 'quoted-printable';
        }
        
        $obj->addSubpart($value['body'], $params);
    }
    function __construct($crlf = "\r\n")
    {
        parent::Mail_mime($crlf);
        $this->_build_params['html_charset'] = 'UTF-8';
        $this->_build_params['text_charset'] = 'UTF-8';
        $this->_build_params['head_charset'] = 'UTF-8';
    }
}

class Fdl_Mail_mimePart extends Mail_mimePart
{
    var $_filename = '';
    
    function Fdl_Mail_mimePart($body = '', $params = array())
    {
        // Keep track of the unaltered/unencoded filename for further use
        if (isset($params['filename'])) {
            $this->_filename = $params['filename'];
        } elseif (isset($params['dfilename'])) {
            $this->_filename = $params['dfilename'];
        }
        
        parent::Mail_mimePart($body, $params);
    }
    /**
     * @param string $body
     * @param array $params
     * @return Fdl_Mail_mimePart
     */
    function &addSubpart($body, $params)
    {
        if (!property_exists('Mail_mimePart', '_body_file') && isset($params['body_file'])) {
            // Mail_mimePart < 1.6.0 has no support for passing a file with $param['body_file']
            $body = file_get_contents($params['body_file']);
            unset($params['body_file']);
        }
        $this->_subparts[] = new Fdl_Mail_mimePart($body, $params);
        return $this->_subparts[count($this->_subparts) - 1];
    }
    
    function setBodyFile($file)
    {
        if (!property_Exists('Mail_mimePart', '_body_file')) {
            // Mail_mimePart < 1.6.0
            $this->_body = file_get_contents($file);
        } else {
            $this->_body_file = $file;
        }
        return $this;
    }
}
/**
 * record message sent from freedom
 */
function createSentMessage($to, $from, $cc, $bcc, $subject, &$mimemail, &$doc = null)
{
    include_once ('WHAT/Lib.Common.php');
    
    $msg = createDoc(getDbAccessFreedom() , "SENTMESSAGE", true);
    if ($msg) {
        $msg->setValue("emsg_from", $from);
        $msg->setValue("emsg_date", Doc::getTimeDate());
        $msg->setValue("emsg_subject", $subject);
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
                    $cid = preg_replace('/^<(.+)>$/', '\1', $part->_headers['Content-ID']);
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
        }
        
        $msg->setValue("emsg_textbody", $mimemail->_txtbody);
        $msg->setValue("emsg_htmlbody", $mimemail->_htmlbody);
        $linkedbody = $mimemail->_htmlbody;
        foreach ($mimemail->_parts as $k => $v) {
            $tmpfile = tempnam(getTmpDir() , 'fdl_attach');
            file_put_contents($tmpfile, $v["body"]);
            $msg->storeFile("emsg_attach", $tmpfile, $v["name"], $k);
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
?>