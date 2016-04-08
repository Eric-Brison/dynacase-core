<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Mail;
/**
 * Compose a mail with body and attachments and send it using the SMTP
 * server referenced by Dynacase's parameters.
 *
 * The (MIME) structure of the mail is:
 *
 *     MIME[multipart/mixed]
 *     |-(1)-> MIME[multipart/related]
 *     |       |-(1.1)-> A 'text/html' or 'text/plain' body (::setBody())
 *     |       '-(1.2)-> Images related to body (::addBodyRelatedAttachment())
 *     '-(2)-> n x attachments (::addAttachment())
 *
 * Sample usage:
 *
 *     --8<--
 *     $message = new \Dcp\Mail\Message();
 *
 *     $message->setFrom(new \Dcp\Mail\Address('john.steed@example.net', 'John Steed (The Avengers)'));
 *     $message->addTo(new \Dcp\Mail\Address('emma.peel@example.net', 'Emma Peel (The Avengers)'));
 *     $message->addCc(new \Dcp\Mail\Address('mother@eexample.net', 'Mother'));
 *
 *     $message->setSubject('Our next mission');
 *
 *     $message->setBody(new \Dcp\Mail\Body('<html>A picture is worth a thousand words: <img src="cid:img01" /></html>', 'text/html'));
 *     $message->addBodyRelatedAttachment(new \Dcp\Mail\RelatedAttachment('/tmp/next-mission.png', 'next-mission.png', 'image/png', 'img01'));
 *
 *     $message->addAttachment(new \Dcp\Mail\Attachment('/tmp/plan.pdf', 'The plan.pdf', 'application/pdf'));
 *
 *     $err = $message->send();
 *     if ($err != '') {
 *         throw new \Exception(sprintf("Error sending message: %s", $err));
 *     }
 *     -->8--
 *
 * @package Dcp\Mail
 */
class Message
{
    const className = __CLASS__;
    /**
     * @var Address
     */
    public $from = null;
    /**
     * @var Address[]
     */
    public $to = array();
    /**
     * @var Address[]
     */
    public $cc = array();
    /**
     * @var Address[]
     */
    public $bcc = array();
    public $subject = '';
    /**
     * @var Body
     */
    public $body = null;
    /**
     * @var RelatedAttachment[]
     */
    public $bodyRelated = array();
    /**
     * @var Body
     */
    public $altBody = null;
    /**
     * @var Attachment[]
     */
    public $attachments = array();
    /**
     * Parse a string containing mail addresses and return
     * list of\Dcp\Mail\Address objects
     * @param $str
     * @return \Dcp\Mail\Address[]
     */
    private function stringToAddress($str)
    {
        $addresses = array();
        $parser = new MailAddrParser();
        try {
            $addresses = $parser->parse($str);
        }
        catch(MailAddrParserException $e) {
        }
        return $addresses;
    }
    private function isAddress($obj)
    {
        return is_a($obj, Address::className);
    }
    /**
     * Set 'From:' address used by the mail's body 'From:' field and the SMTP protocol.
     *
     * @param Address|string $address
     */
    public function setFrom($address)
    {
        if ($this->isAddress($address)) {
            $this->from = $address;
        } elseif (is_scalar($address)) {
            $addr = $this->stringToAddress($address);
            if (count($addr) > 0) {
                $this->from = $addr[0];
            }
        }
    }
    /**
     * Add 'To:' recipients.
     *
     * @param Address|string $address
     */
    public function addTo($address)
    {
        if ($this->isAddress($address)) {
            $this->to[] = $address;
        } elseif (is_scalar($address)) {
            foreach ($this->stringToAddress($address) as $addr) {
                $this->to[] = $addr;
            }
        }
    }
    /**
     * Reset list of 'To:' recipients
     */
    public function resetTo()
    {
        $this->to = array();
    }
    /**
     * Add 'Cc:' recipients.
     *
     * @param Address|string $address
     */
    public function addCc($address)
    {
        if ($this->isAddress($address)) {
            $this->cc[] = $address;
        } elseif (is_scalar($address)) {
            foreach ($this->stringToAddress($address) as $addr) {
                $this->cc[] = $addr;
            }
        }
    }
    /**
     * Reset list of 'Cc:' recipients.
     */
    public function resetCc()
    {
        $this->cc = array();
    }
    /**
     * Add 'Bcc:' recipients.
     *
     * @param Address|string $address
     */
    public function addBcc($address)
    {
        if ($this->isAddress($address)) {
            $this->bcc[] = $address;
        } elseif (is_scalar($address)) {
            foreach ($this->stringToAddress($address) as $addr) {
                $this->bcc[] = $addr;
            }
        }
    }
    /**
     * Reset list of 'Bcc:' recipients.
     */
    public function resetBcc()
    {
        $this->bcc = array();
    }
    /**
     * Set mail's 'Subject:' field.
     *
     * @param $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }
    /**
     * Set main mail's body.
     *
     * @param Body $body
     */
    public function setBody(Body & $body)
    {
        $this->body = $body;
    }
    /**
     * Add an attachment related to the body (with the use of cid:xxx URLs).
     *
     * @param RelatedAttachment $attachment
     */
    public function addBodyRelatedAttachment(RelatedAttachment & $attachment)
    {
        $this->bodyRelated[] = $attachment;
    }
    /**
     * Reset list of body's related attachments.
     */
    public function resetBodyRelatedAttachment()
    {
        $this->bodyRelated = array();
    }
    /**
     * Set alternate body (e.g. plain text view of a main HTML body).
     *
     * @param Body $body
     */
    public function setAltBody(Body & $body)
    {
        $this->altBody = $body;
    }
    /**
     * Add attachments.
     *
     * @param Attachment $attachment
     */
    public function addAttachment(Attachment & $attachment)
    {
        $this->attachments[] = $attachment;
    }
    /**
     * Reset list of attachments.
     */
    public function resetAttachment()
    {
        $this->attachments = array();
    }
    /**
     * Send the message
     *
     * @return string error message on failure or empty string if successful
     */
    public function send()
    {
        return $this->_sendWithPHPMailer();
    }
    private function _sendWithPHPMailer()
    {
        include_once ("WHAT/Lib.Common.php");
        $lcConfig = getLocaleConfig();
        $mail = new \PHPMailer();
        /*
         * SMTPAutoTLS was introduced in v5.2.10 and is set to bool(true) by
         * default.
         *
         * We set it back to bool(false) for compatibility and prevent errors
         * with existing SMTP servers that could advertise TLS with non-valid
         * certificates.
         *
         * If a client wants to use TLS, it can explicitly specify it with a
         * "tls://<hostname>" URI in the SMTP_HOST parameter.
        */
        $mail->SMTPAutoTLS = false;
        /*
         * Timeout was changed in v5.2.10 to 300 seconds to conform with RFC2821.
         *
         * We set it back to 10 to keep previous behaviour.
        */
        $mail->Timeout = 10;
        $mail->setLanguage($lcConfig['locale']);
        $mail->isSMTP();
        $host = getParam('SMTP_HOST', 'localhost');
        $port = getParam('SMTP_PORT', 25);
        $mail->Host = $host;
        $mail->Port = $port;
        $login = getParam('SMTP_LOGIN');
        if ($login) {
            $mail->SMTPAuth = true;
            $password = getParam('SMTP_PASSWORD');
            $mail->Username = $login;
            $mail->Password = $password;
        }
        if (isset($this->from)) {
            $mail->From = $this->from->address;
            $mail->FromName = $this->from->name;
        }
        foreach ($this->to as $to) {
            $mail->addAddress($to->address, $to->name);
        }
        foreach ($this->cc as $to) {
            $mail->addCC($to->address, $to->name);
        }
        foreach ($this->bcc as $to) {
            $mail->addBCC($to->address, $to->name);
        }
        $mail->CharSet = "UTF-8";
        $mail->XMailer = sprintf("Dynacase Platform %s", getParam('VERSION'));
        $mail->MessageID = '<' . strftime("%Y%M%d%H%M%S-", time()) . rand(1, 65535) . "@%s>";
        $mail->MessageID = sprintf('<%s%s@%s>', strftime("%Y%M%d%H%M%S-", time()) , rand(1, 65535) , $host);
        $mail->Subject = $this->subject;
        $mail->AllowEmpty = true;
        if (isset($this->body)) {
            if ($this->body->type == 'text/html') {
                $mail->isHTML(true);
            }
            $mail->Body = $this->body->data;
        }
        foreach ($this->bodyRelated as $attachment) {
            $mail->addEmbeddedImage($attachment->file, $attachment->cid, $attachment->name, 'base64', $attachment->type);
        }
        if (isset($this->altBody)) {
            $mail->AltBody = $this->altBody->data;
        }
        foreach ($this->attachments as $attachment) {
            $mail->addAttachment($attachment->file, $attachment->name, 'base64', $attachment->type);
        }
        $ret = $mail->send();
        if ($ret === false) {
            return $mail->ErrorInfo;
        }
        return '';
    }
}
