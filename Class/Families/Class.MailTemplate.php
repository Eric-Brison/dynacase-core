<?php
/*
 * Mail template document
 * @author Anakeen
 * @package FDL
*/
/**
 * Mail template document
 */
namespace Dcp\Core;
class MailTemplate extends \Dcp\Family\Document
{
    /**
     * always show a user notification
     */
    const NOTIFY_SENDMAIL_ALWAYS = 'always';
    /**
     * only show a notification if an error occured
     */
    const NOTIFY_SENDMAIL_ERRORS_ONLY = 'errors only';
    /**
     * never show a notification
     */
    const NOTIFY_SENDMAIL_NEVER = 'never';
    /**
     * show notification according to CORE_NOTIFY_SENDMAIL parameter
     */
    const NOTIFY_SENDMAIL_AUTO = 'auto';

    public $ifiles = array();
    public $sendercopy = true;
    public $keys = array();

    protected $notifySendMail = self::NOTIFY_SENDMAIL_AUTO;

    function preEdition()
    {
        global $action;
        
        if ($mailfamily = $this->getRawValue("tmail_family", getHttpVars("TMAIL_FAMILY"))) {
            $action->parent->AddJsRef("?app=FDL&action=FCKDOCATTR&famid=" . $mailfamily);
        }
    }
    /**
     * Check if the relation is correct and the attribute does exists
     *
     * @param string $values Relation to check
     * @param array $doc Field and values of document attributes
     * @return string Error if attribute not found, else empty string
     */
    private function checkAttributeExistsInRelation($values, array $doc)
    {
        $tattrid = explode(":", $values);
        if (count($tattrid) == 1) { //no relation
            if (!array_key_exists($tattrid[0], $doc)) return sprintf(_("Send mail error : Attribute %s not found.") , $tattrid[0]);
            return "";
        }
        $lattrid = array_pop($tattrid); // last attribute
        foreach ($tattrid as $v) {
            if (!array_key_exists($v, $doc)) return sprintf(_("Send mail error : Relation to attribute %s not found. Incorrect relation key: %s") , $lattrid, $v);
            $docids = getLatestDocIds($this->dbaccess, array(
                $doc[$v]
            ));
            if (!$docids) {
                return sprintf(_("Send mail error : Relation to attribute %s not found. Relation key %s does'nt link to a document") , $lattrid, $v);
            }
            $doc = getTDoc($this->dbaccess, array_pop($docids));
            if (!$doc) return sprintf(_("Send mail error : Relation to attribute %s not found. Relation key %s does'nt link to a document") , $lattrid, $v);
        }
        if (!array_key_exists($lattrid, $doc)) return sprintf(_("Send mail error : Attribute %s not found.") , $lattrid);
        return "";
    }
    /**
     * send document by email using this template
     * @param \Doc $doc document to send
     * @param array $keys extra keys used for template
     * @return string error - empty if no error -
     */
    public function sendDocument(\Doc & $doc, $keys = array())
    {
        global $action;
        
        include_once ("FDL/sendmail.php");
        include_once ("FDL/Lib.Vault.php");
        $err = '';
        if (!$doc->isAffected()) {
            return $err;
        }
        $this->keys = $keys;
        
        $message = new \Dcp\Mail\Message();
        
        $tdest = $this->getArrayRawValues("tmail_dest");
        
        $dest = array(
            "to" => array() ,
            "cc" => array() ,
            "bcc" => array() ,
            "from" => array()
        );
        $from = trim($this->getRawValue("tmail_from"));
        if ($from) {
            $tdest[] = array(
                "tmail_copymode" => "from",
                "tmail_desttype" => $this->getRawValue("tmail_fromtype") ,
                "tmail_recip" => $from
            );
        }
        $wdoc = null;
        if ($doc->wid) {
            /**
             * @var \WDoc $wdoc
             */
            $wdoc = new_doc($this->dbaccess, $doc->wid);
        }
        $udoc = null;
        foreach ($tdest as $k => $v) {
            $toccbcc = $v["tmail_copymode"];
            $type = $v["tmail_desttype"];
            $mail = '';
            switch ($type) {
                case 'F': // fixed address
                    $mail = $v["tmail_recip"];
                    break;

                case 'A': // text attribute
                    $aid = strtok($v["tmail_recip"], " ");
                    $err = $this->checkAttributeExistsInRelation($aid, getLatestTDoc($this->dbaccess, $doc->initid));
                    if ($err) {
                        $action->log->error($err);
                        $doc->addHistoryEntry($err);
                        return $err;
                    }
                    $mail = $doc->getRValue($aid);
                    break;

                case 'WA': // workflow text attribute
                    if ($wdoc) {
                        $aid = strtok($v["tmail_recip"], " ");
                        $err = $this->checkAttributeExistsInRelation($aid, getLatestTDoc($this->dbaccess, $wdoc->initid));
                        if ($err) {
                            $action->log->error($err);
                            $wdoc->addHistoryEntry($err);
                            return $err;
                        }
                        $mail = $wdoc->getRValue($aid);
                    }
                    break;

                case 'E': // text parameter
                    $aid = strtok($v["tmail_recip"], " ");
                    if (!$doc->getAttribute($aid)) {
                        $action->log->error(sprintf(_("Send mail error : Parameter %s doesn't exists") , $aid));
                        $doc->addHistoryEntry(sprintf(_("Send mail error : Parameter %s doesn't exists") , $aid));
                        return sprintf(_("Send mail error : Parameter %s doesn't exists") , $aid);
                    }
                    $mail = $doc->getFamilyParameterValue($aid);
                    break;

                case 'WE': // workflow text parameter
                    if ($wdoc) {
                        $aid = strtok($v["tmail_recip"], " ");
                        if (!$wdoc->getAttribute($aid)) {
                            $action->log->error(sprintf(_("Send mail error : Parameter %s doesn't exists") , $aid));
                            $wdoc->addHistoryEntry(sprintf(_("Send mail error : Parameter %s doesn't exists") , $aid));
                            return sprintf(_("Send mail error : Parameter %s doesn't exists") , $aid);
                        }
                        $mail = $wdoc->getFamilyParameterValue($aid);
                    }
                    break;

                case 'DE': // param user relation
                    
                case 'D': // user relations
                    
                case 'WD': // user relations
                    if ($type == 'D' || $type == 'DE') {
                        $udoc = $doc;
                    } elseif ($wdoc) {
                        $udoc = $wdoc;
                    }
                    if ($udoc) {
                        $aid = strtok($v["tmail_recip"], " ");
                        if (!$udoc->getAttribute($aid) && !array_key_exists(strtolower($aid) , $udoc->getParamAttributes())) {
                            $action->log->error(sprintf(_("Send mail error : Attribute %s not found") , $aid));
                            $doc->addHistoryEntry(sprintf(_("Send mail error : Attribute %s not found") , $aid));
                            return sprintf(_("Send mail error : Attribute %s not found") , $aid);
                        }
                        if ($type == 'DE') {
                            $vdocid = $udoc->getFamilyParameterValue($aid);
                        } else {
                            $vdocid = $udoc->getRawValue($aid); // for array of users
                            
                        }
                        $vdocid = str_replace('<BR>', "\n", $vdocid);
                        if (strpos($vdocid, "\n")) {
                            $tvdoc = $this->rawValueToArray($vdocid);
                            $tmail = array();
                            $it = new \DocumentList();
                            $it->addDocumentIdentifiers($tvdoc);
                            /**
                             * @var \Dcp\Family\IUSER|\Dcp\Family\IGROUP|\Dcp\Family\ROLE $aDoc
                             */
                            foreach ($it as $aDoc) {
                                $umail = '';
                                if (method_exists($aDoc, "getMail")) {
                                    $umail = $aDoc->getMail();
                                }
                                if (!$umail) {
                                    $umail = $aDoc->getRawValue('us_mail', '');
                                }
                                if (!$umail) {
                                    $umail = $aDoc->getRawValue('grp_mail', '');
                                }
                                if ($umail) {
                                    $tmail[] = $umail;
                                }
                            }
                            $mail = implode(",", $tmail);
                        } else {
                            if (strpos($aid, ':')) {
                                $mail = $udoc->getRValue($aid);
                            } else {
                                if ($type == "DE") {
                                    /**
                                     * @var \Dcp\Family\IUSER|\Dcp\Family\IGROUP|\Dcp\Family\ROLE $aDoc
                                     */
                                    $aDoc = new_Doc("", $vdocid);
                                    $mail = '';
                                    if (method_exists($aDoc, "getMail")) {
                                        $mail = $aDoc->getMail();
                                    }
                                    if (!$mail) {
                                        $mail = $aDoc->getRawValue('us_mail', '');
                                    }
                                    if (!$mail) {
                                        $mail = $aDoc->getRawValue('grp_mail', '');
                                    }
                                } else {
                                    $mail = $udoc->getRValue($aid . ':us_mail');
                                    if (!$mail) {
                                        $mail = $udoc->getRValue($aid . ':grp_mail');
                                    }
                                }
                            }
                        }
                    }
                    break;

                case 'P':
                    $aid = strtok($v["tmail_recip"], " ");
                    if (!getParam($aid)) {
                        $action->log->error(sprintf(_("Send mail error : Parameter %s doesn't exists") , $aid));
                        $doc->addHistoryEntry(sprintf(_("Send mail error : Parameter %s doesn't exists") , $aid));
                        return sprintf(_("Send mail error : Parameter %s doesn't exists") , $aid);
                    }
                    $mail = getParam($aid);
                    break;

                case 'RD':
                    $recipDocId = $v['tmail_recip'];
                    if (preg_match('/^(?P<id>\d+)/', $v['tmail_recip'], $m)) {
                        /**
                         * Extract document's id from tmail_recip value
                         */
                        $recipDocId = $m['id'];
                    }
                    /**
                     * @var \IMailRecipient|\Doc $recipientDoc
                     */
                    $recipientDoc = new_Doc($this->dbaccess, $recipDocId, true);
                    if (!is_object($recipientDoc) || !$recipientDoc->isAlive()) {
                        $err = sprintf(_("Send mail error: recipient document '%s' does not exists.") , $recipDocId);
                        $action->log->error($err);
                        $doc->addHistoryEntry($err);
                        return $err;
                    }
                    if (!is_a($recipientDoc, 'IMailRecipient')) {
                        $err = sprintf(_("Send mail error: recipient document '%s' does not implements IMailRecipient interface.") , $recipDocId);
                        $action->log->error($err);
                        $doc->addHistoryEntry($err);
                        return $err;
                    }
                    $mail = $recipientDoc->getMail();
                    break;
            }
            if ($mail) $dest[$toccbcc][] = str_replace(array(
                "\n",
                "\r"
            ) , array(
                ",",
                ""
            ) , $mail);
        }
        $subject = $this->generateMailInstance($doc, $this->getRawValue("tmail_subject"));
        $subject = str_replace(array(
            "\n",
            "\r",
            "<BR>"
        ) , array(
            " ",
            " ",
            ", "
        ) , html_entity_decode($subject, ENT_COMPAT, "UTF-8"));
        $pfout = $this->generateMailInstance($doc, $this->getRawValue("tmail_body") , $this->getAttribute("tmail_body"));
        // delete empty address
        $dest['to'] = array_filter($dest['to'], create_function('$v', 'return!preg_match("/^\s*$/", $v);'));
        $dest['cc'] = array_filter($dest['cc'], create_function('$v', 'return!preg_match("/^\s*$/", $v);'));
        $dest['bcc'] = array_filter($dest['bcc'], create_function('$v', 'return!preg_match("/^\s*$/", $v);'));
        $dest['from'] = array_filter($dest['from'], create_function('$v', 'return!preg_match("/^\s*$/", $v);'));
        
        $this->addSubstitutes($dest);
        
        $to = implode(',', $dest['to']);
        $cc = implode(',', $dest['cc']);
        $bcc = implode(',', $dest['bcc']);
        $from = implode(',', $dest['from']); // only one value expected for from
        if ($from == "") {
            $from = getMailAddr($action->user->id);
        }
        if ($from == "") {
            $from = getParam('SMTP_FROM');
        }
        if ($from == "") {
            $from = $action->user->login . '@' . (isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "");
        }
        
        if (trim($to . $cc . $bcc) == "") {
            $action->log->info(sprintf(_("Send mail info : can't send mail %s: no sendee found") , $subject));
            $doc->addHistoryEntry(sprintf(_("Send mail info : can't send mail %s: no sendee found") , $subject) , HISTO_NOTICE);
            return "";
        } //nobody to send data
        if ($this->sendercopy && getParam("FDL_BCC") == "yes") {
            $umail = getMailAddr($this->userid);
            if ($umail != "") $bcc.= (trim($bcc) == "" ? "" : ",") . $umail;
        }
        
        $body = new \Dcp\Mail\Body($pfout, 'text/html');
        $message->setBody($body);
        // ---------------------------
        // add inserted image
        foreach ($this->ifiles as $k => $v) {
            if (file_exists($v)) {
                $message->addBodyRelatedAttachment(new \Dcp\Mail\RelatedAttachment($v, $k, sprintf("image/%s", fileextension($v)) , $k));
            }
        }
        //send attachment
        $ta = $this->getMultipleRawValues("tmail_attach");
        foreach ($ta as $k => $v) {
            $err = $this->checkAttributeExistsInRelation(strtok($v, " ") , getLatestTDoc($this->dbaccess, $doc->initid));
            if ($err) {
                $action->log->error($err);
                $doc->addHistoryEntry($err);
                return $err;
            }
            $vf = $doc->getRValue(strtok($v, " "));
            if ($vf) {
                $tvf = $this->rawValueToArray($vf);
                foreach ($tvf as $vf) {
                    if ($vf) {
                        $fileinfo = $this->getFileInfo($vf);
                        if ($fileinfo["path"]) {
                            $message->addAttachment(new \Dcp\Mail\Attachment($fileinfo['path'], $fileinfo['name'], $fileinfo['mime_s']));
                        }
                    }
                }
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
        
        $savecopy = $this->getRawValue("tmail_savecopy") == "yes";
        if (($err == "") && $savecopy) {
            createSentMessage($to, $from, $cc, $bcc, $subject, $message, $doc);
        }
        $recip = "";
        if ($to) {
            $recip.= sprintf(_("sendmailto %s") , $to);
        }
        if ($cc) {
            $recip.= ' ' . sprintf(_("sendmailcc %s") , $cc);
        }
        if ($bcc) {
            $recip.= ' ' . sprintf(_("sendmailbcc %s") , $bcc);
        }

        if (self::NOTIFY_SENDMAIL_AUTO === $this->notifySendMail) {
            $notifySendMail = \ApplicationParameterManager::getParameterValue('CORE', 'CORE_NOTIFY_SENDMAIL');
            if (is_null($notifySendMail)) {
                $notifySendMail = self::NOTIFY_SENDMAIL_ALWAYS;
            }
        } else {
            $notifySendMail = $this->notifySendMail;
        }
        
        if ($err == "") {
            $doc->addHistoryEntry(sprintf(_("send mail %s with template %s") , $recip, $this->title) , HISTO_INFO, "SENDMAIL");
            $action->log->info(sprintf(_("Mail %s sent to %s") , $subject, $recip));
            if (self::NOTIFY_SENDMAIL_ALWAYS === $notifySendMail) {
                addWarningMsg(sprintf(_("send mail %s"), $recip));
            }
        } else {
            $doc->addHistoryEntry(sprintf(_("cannot send mail %s with template %s : %s") , $recip, $this->title, $err) , HISTO_ERROR);
            $action->log->error(sprintf(_("cannot send mail %s to %s : %s") , $subject, $recip, $err));
            if (self::NOTIFY_SENDMAIL_ALWAYS === $notifySendMail ||
                self::NOTIFY_SENDMAIL_ERRORS_ONLY === $notifySendMail) {
                addWarningMsg(sprintf(_("cannot send mail %s"), $err));
            }
        }
        return $err;
    }

    /**
     * determine if a notification should be displayed to the user
     *
     * @param string $notifySendMail one of the NOTIFY_SENDMAIL_* const
     * @return string error if the value is invalid, empty string in case of success
     */
    public function setNotification($notifySendMail)
    {
        $allowedValues = [
            self::NOTIFY_SENDMAIL_ALWAYS,
            self::NOTIFY_SENDMAIL_ERRORS_ONLY,
            self::NOTIFY_SENDMAIL_NEVER,
            self::NOTIFY_SENDMAIL_AUTO
        ];

        if (! in_array($notifySendMail, $allowedValues)) {
            throw new Exception("MAIL0001", $notifySendMail, implode("' , '", $allowedValues));
        } else {
            $this->notifySendMail = $notifySendMail;
        }
        return '';
    }
    /**
     * update template with document values
     * @param \Doc $doc
     * @param string $tpl template content
     * @param \NormalAttribute|bool $oattr
     * @return string
     */
    private function generateMailInstance(\Doc & $doc, $tpl, $oattr = false)
    {
        global $action;
        $tpl = str_replace("&#x5B;", "[", $tpl); // replace [ convverted in Doc::setValue()
        $doc->lay = new \Layout("", $action, $tpl);
        
        $ulink = ($this->getRawValue("tmail_ulink") == "yes");
        /* Expand layout's [TAGS] */
        $doc->viewdefaultcard("mail", $ulink, false, true);
        foreach ($this->keys as $k => $v) $doc->lay->set($k, $v);
        $body = $doc->lay->gen();
        $body = preg_replace_callback(array(
            "/SRC=\"([^\"]+)\"/",
            "/src=\"([^\"]+)\"/"
        ) , function ($matches)
        {
            return $this->srcfile($matches[1]);
        }
        , $body);
        /* Expand remaining HTML constructions */
        if ($oattr !== false && $oattr->type == 'htmltext') {
            $body = $doc->getHtmlValue($oattr, $body, "mail", $ulink);
        }
        return $body;
    }
    /**
     * add substitute account mail addresses
     * @param array $dests
     */
    private function addSubstitutes(array & $dests)
    {
        $sql = "SELECT incumbent.login as inlogin, incumbent.mail as inmail, substitut.firstname || ' ' || substitut.lastname as suname , substitut.mail as sumail from users as incumbent, users as substitut where substitut.id=incumbent.substitute and incumbent.substitute is not null and incumbent.mail is not null and substitut.mail is not null;";
        simpleQuery($this->dbaccess, $sql, $substituteMails);
        foreach (array(
            "to",
            "cc",
            "bcc"
        ) as $td) {
            foreach ($dests[$td] as $kDest => $aDest) {
                foreach ($substituteMails as $aSumail) {
                    $suName = str_replace('"', '', sprintf(_("%s (as substitute)") , $aSumail["suname"]));
                    $dests[$td][$kDest] = str_replace(sprintf('<%s>', $aSumail["inmail"]) , sprintf('<%s>, "%s" <%s>', $aSumail["inmail"], $suName, $aSumail["sumail"]) , $aDest);
                    
                    $dests[$td][$kDest] = preg_replace(sprintf('/(^|,|\s)(%s)/', preg_quote($aSumail["inmail"], "/")) , sprintf('\1\2, "%s" <%s>', $suName, $aSumail["sumail"]) , $dests[$td][$kDest]);
                }
            }
        }
    }
    
    private function getUniqId()
    {
        static $unid = 0;
        if (!$unid) $unid = date('Ymdhis');
        return $unid;
    }
    private function srcfile($src)
    {
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
        $cid = $src;
        
        if (preg_match("/.*app=FDL.*action=EXPORTFILE.*vid=([0-9]*)/", $src, $reg)) {
            $info = vault_properties($reg[1]);
            $src = $info->path;
            $cid = "cid" . $this->getUniqId() . $reg[1] . '.' . fileextension($info->path);
        } elseif (preg_match('!file/(?P<docid>\d+)/(?P<vid>\d+)/(?P<attrid>[^/]+)/(?P<index>[^/]+)/(?P<fname>[^?]+)!', $src, $reg)) {
            $info = vault_properties($reg['vid']);
            $src = $info->path;
            $cid = "cid" . $this->getUniqId() . $reg[1] . '.' . fileextension($info->path);
        }
        
        if (!in_array(strtolower(fileextension($src)) , $vext)) {
            return "";
        }
        
        $this->ifiles[$cid] = $src;
        return "src=\"cid:$cid\"";
    }
    /**
     * @begin-method-ignore
     * this part will be deleted when construct document class until end-method-ignore
     */
}
/**
 * @end-method-ignore
 */
