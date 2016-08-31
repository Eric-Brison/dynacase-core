<?php
namespace Dcp;

class WSHMailError
{
    public $expand = array();
    /**
     * @var \Action
     */
    public $action = null;
    public $msg = '';
    public $from = '';
    public $mailto = '';
    public $subject = '';
    public $prefix = false;
    /**
     * WSHError constructor.
     * @param \Action $action
     * @param $msg
     */
    public function __construct(\Action & $action, $msg)
    {
        $this->reset($action, $msg);
    }
    /**
     * @param \Action $action
     * @param $msg
     */
    public function reset(\Action & $action, $msg)
    {
        $this->expand = array(
            'h' => php_uname('n') ,
            'c' => \ApplicationParameterManager::getParameterValue('CORE', 'CORE_CLIENT')
        );
        $this->action = $action;
        $this->msg = $msg;
        $this->from = '';
        $this->mailto = '';
        $this->subject = '';
        $this->prefix = false;
    }
    /**
     * @param $str
     * @return string
     */
    public function expand($str)
    {
        $res = '';
        $expandNextChar = false;
        $p = 0;
        while ($p < mb_strlen($str)) {
            $c = mb_substr($str, $p, 1);
            if ($expandNextChar) {
                if ($c == '%') {
                    $res.= '%';
                } elseif (isset($this->expand[$c])) {
                    $res.= $this->expand[$c];
                } else {
                    $res.= $c;
                }
                $expandNextChar = false;
            } else {
                if ($c == '%') {
                    $expandNextChar = true;
                } else {
                    $res.= $c;
                }
            }
            $p++;
        }
        return $res;
    }
    /**
     * @return string
     */
    public function autosend()
    {
        $from = getMailAddr($this->action->user->id);
        if ($from == '') {
            $from = \ApplicationParameterManager::getParameterValue('FDL', 'SMTP_FROM');
        }
        if ($from == '') {
            $from = $this->action->user->login . '@' . php_uname('n');
        }
        $this->from = $from;
        
        $mailto = trim(\ApplicationParameterManager::getParameterValue('CORE', 'CORE_WSH_MAILTO'));
        if ($mailto == '') {
            return '';
        }
        $this->mailto = $mailto;
        
        $subject = \ApplicationParameterManager::getParameterValue('CORE', 'CORE_WSH_MAILSUBJECT');
        if ($subject == '') {
            $subject = 'Script error';
        }
        $this->subject = $this->expand($subject);
        
        $msg = $this->msg;
        if (is_string($this->prefix) && strlen($this->prefix) > 0) {
            $msg = $this->prefixize($this->prefix, $msg);
        }
        
        $htmlBody = sprintf('<pre>%s</pre>', htmlspecialchars($msg, ENT_QUOTES));
        
        return $this->send($this->from, $this->mailto, $this->subject, $htmlBody, $msg);
    }
    /**
     * Add a prefix at beginning of each lines
     * @param string $prefix
     * @param string $msg
     * @return string
     */
    public function prefixize($prefix, $msg)
    {
        return $prefix . str_replace("\n", "\n" . $prefix, $msg);
    }
    /**
     * @param string $from Sender's email address (e.g. 'john.doe@example.net')
     * @param string|string[] $mailto Recipient(s) address(es) as a comma-separated list of mail addresses, or and array og mail addresses
     * @param string $subject Subject
     * @param string $htmlBody The main body in HTML format
     * @param string $altTextBody Optional text variant of the main HTML body
     * @param array[] $attachments Optional attachments (e.g. array( array('file' => '/path/to/att.dat', 'name' => 'icon.png', 'mime' => 'image/png' [, 'cid' => 'CIDidentifier'] ), [...] )))
     * @return string Non-empty string with error message on failure, or empty string on success
     */
    public function send($from, $mailto, $subject, $htmlBody, $altTextBody = '', $attachments = array())
    {
        $message = new \Dcp\Mail\Message();
        $message->setFrom($from);
        $message->setSubject($subject);
        $recipients = array();
        if (is_string($mailto)) {
            foreach (preg_split('/\s*,\s*/', $mailto) as $to) {
                if ($to == '') {
                    continue;
                }
                $recipients[] = $to;
            }
        } elseif (is_array($mailto)) {
            foreach ($mailto as $to) {
                $recipients[] = $to;
            }
        }
        foreach ($recipients as $to) {
            $message->addTo($to);
        }
        $message->setBody(new \Dcp\Mail\Body($htmlBody, 'text/html'));
        if (is_string($altTextBody) && strlen($altTextBody) > 0) {
            $message->setAltBody(new \Dcp\Mail\Body($altTextBody, 'text/plain'));
        }
        if (is_array($attachments)) {
            foreach ($attachments as $att) {
                if (!is_array($att) || !isset($att['file']) || !isset($att['name']) || !isset($att['mime'])) {
                    continue;
                }
                if (isset($att['cid'])) {
                    $message->addBodyRelatedAttachment(new \Dcp\Mail\RelatedAttachment($att['file'], $att['name'], $att['mime']));
                } else {
                    $message->addAttachment(new \Dcp\Mail\Attachment($att['file'], $att['name'], $att['mime']));
                }
            }
        }
        if (($err = $message->send()) !== '') {
            error_log(__METHOD__ . " " . sprintf("Error sending mail: %s", $err));
        }
        return $err;
    }
    /**
     * @param string[] $expand
     */
    public function addExpand($expand = array())
    {
        if (!is_array($expand)) {
            return;
        }
        foreach ($expand as $k => $v) {
            if (!is_string($v)) {
                continue;
            }
            $this->expand[$k] = $v;
        }
    }
}
