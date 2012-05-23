<?php
/*
 * Mail template document
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
/**
 * Mail template document
 * @class _MAILTEMPLATE
 */
class _MAILTEMPLATE extends Doc
{
    /**
     * @end-method-ignore
     */
    public $ifiles = array();
    public $sendercopy = true;
    public $keys = array();
    
    function preEdition()
    {
        global $action;
        
        if ($mailfamily = $this->getValue("tmail_family", getHttpVars("TMAIL_FAMILY"))) {
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
     * @param \Doc $doc documengt to send
     * @param array $keys extra keys used for template
     * @return string error - empty if no error -
     */
    public function sendDocument(Doc & $doc, $keys = array())
    {
        global $action;
        
        include_once ("FDL/sendmail.php");
        include_once ("FDL/Lib.Vault.php");
        $err = '';
        if ($doc->isAffected()) {
            $this->keys = $keys;
            
            $multi_mix = new Fdl_Mail_mimePart('', array(
                'content_type' => 'multipart/mixed'
            ));
            $multi_rel = $multi_mix->addSubpart('', array(
                'content_type' => 'multipart/related'
            ));
            
            $tdest = $this->getAValues("tmail_dest");
            
            $dest = array(
                "to" => array() ,
                "cc" => array() ,
                "bcc" => array() ,
                "from" => array()
            );
            $from = trim($this->getValue("tmail_from"));
            if ($from) {
                $tdest[] = array(
                    "tmail_copymode" => "from",
                    "tmail_desttype" => $this->getValue("tmail_fromtype") ,
                    "tmail_recip" => $from
                );
            }
            $wdoc = null;
            if ($doc->wid) {
                /**
                 * @var WDoc $wdoc
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
                            $doc->addComment($err);
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
                                $wdoc->addComment($err);
                                return $err;
                            }
                            $mail = $wdoc->getRValue($aid);
                        }
                        break;

                    case 'E': // text parameter
                        $aid = strtok($v["tmail_recip"], " ");
                        if (!$doc->getAttribute($aid)) {
                            $action->log->error(sprintf(_("Send mail error : Parameter %s doesn't exists") , $aid));
                            $doc->addComment(sprintf(_("Send mail error : Parameter %s doesn't exists") , $aid));
                            return sprintf(_("Send mail error : Parameter %s doesn't exists") , $aid);
                        }
                        $mail = $doc->getparamValue($aid);
                        break;

                    case 'WE': // workflow text parameter
                        if ($wdoc) {
                            $aid = strtok($v["tmail_recip"], " ");
                            if (!$wdoc->getAttribute($aid)) {
                                $action->log->error(sprintf(_("Send mail error : Parameter %s doesn't exists") , $aid));
                                $wdoc->addComment(sprintf(_("Send mail error : Parameter %s doesn't exists") , $aid));
                                return sprintf(_("Send mail error : Parameter %s doesn't exists") , $aid);
                            }
                            $mail = $wdoc->getparamValue($aid);
                        }
                        break;

                    case 'D': // user relations
                        
                    case 'WD': // user relations
                        if ($type == 'D') $udoc = $doc;
                        elseif ($wdoc) $udoc = $wdoc;
                        if ($udoc) {
                            $aid = strtok($v["tmail_recip"], " ");
                            if (!$udoc->getAttribute($aid)) {
                                $action->log->error(sprintf(_("Send mail error : Attribute %s not found") , $aid));
                                $doc->addComment(sprintf(_("Send mail error : Attribute %s not found") , $aid));
                                return sprintf(_("Send mail error : Attribute %s not found") , $aid);
                            }
                            $vdocid = $udoc->getValue($aid); // for array of users
                            $vdocid = str_replace('<BR>', "\n", $vdocid);
                            if (strpos($vdocid, "\n")) {
                                $tvdoc = $this->_val2array($vdocid);
                                $tmail = array();
                                $it = new DocumentList();
                                $it->addDocumentIdentificators($tvdoc);
                                /**
                                 * @var _IUSER|_IGROUP|_ROLE $aDoc
                                 */
                                foreach ($it as $aDoc) {
                                    $umail = '';
                                    if (method_exists($aDoc, "getMail")) $umail = $aDoc->getMail();
                                    if (!$umail) $umail = $aDoc->getValue('us_mail', '');
                                    if (!$umail) $umail = $aDoc->getValue('grp_mail', '');
                                    if ($umail) $tmail[] = $umail;
                                }
                                $mail = implode(",", $tmail);
                            } else {
                                if (strpos($aid, ':')) $mail = $udoc->getRValue($aid);
                                else {
                                    $mail = $udoc->getRValue($aid . ':us_mail');
                                    if (!$mail) $mail = $udoc->getRValue($aid . ':grp_mail');
                                }
                            }
                        }
                        break;

                    case 'P':
                        $aid = strtok($v["tmail_recip"], " ");
                        if (!getParam($aid)) {
                            $action->log->error(sprintf(_("Send mail error : Parameter %s doesn't exists") , $aid));
                            $doc->addComment(sprintf(_("Send mail error : Parameter %s doesn't exists") , $aid));
                            return sprintf(_("Send mail error : Parameter %s doesn't exists") , $aid);
                        }
                        $mail = getParam($aid);
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
                $subject = $this->generateMailInstance($doc, $this->getValue("tmail_subject"));
                $subject = str_replace(array(
                    "\n",
                    "\r",
                    "<BR>"
                ) , array(
                    " ",
                    " ",
                    ", "
                ) , html_entity_decode($subject, ENT_COMPAT, "UTF-8"));
                $pfout = $this->generateMailInstance($doc, $this->getValue("tmail_body"));
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
                if ($from == "") $from = getMailAddr($action->user->id);
                if ($from == "") $from = getParam('SMTP_FROM');
                if ($from == "") $from = $action->user->login . '@' . $_SERVER["HTTP_HOST"];
                
                if (trim($to . $cc . $bcc) == "") {
                    $action->log->info(sprintf(_("Send mail info : can't send mail %s: no sendee found") , $subject));
                    return sprintf(_("Send mail info : can't send mail %s: no sendee found") , $subject);
                } //nobody to send data
                if ($this->sendercopy && getParam("FDL_BCC") == "yes") {
                    $umail = getMailAddr($this->userid);
                    if ($umail != "") $bcc.= (trim($bcc) == "" ? "" : ",") . $umail;
                }
                
                $multi_rel->addSubpart($pfout, array(
                    'content_type' => 'text/html',
                    'charset' => 'UTF-8',
                    'encoding' => 'quoted-printable'
                ));
                // ---------------------------
                // add inserted image
                foreach ($this->ifiles as $k => $v) {
                    if (file_exists(DEFAULT_PUBDIR . "/$v")) {
                        
                        $multi_rel->addSubpart('', array(
                            'body_file' => $v,
                            'content_type' => sprintf("image/%s", fileextension($v)) ,
                            'charset' => 'UTF-8',
                            'filename' => $k,
                            'dfilename' => $k,
                            'encoding' => 'base64',
                            'name_encoding' => 'quoted-printable',
                            'filename_encoding' => 'quoted-printable',
                            'disposition' => 'inline',
                            'cid' => $k
                        ));
                    }
                }
                //send attachment
                $ta = $this->getTValue("tmail_attach");
                foreach ($ta as $k => $v) {
                    $err = $this->checkAttributeExistsInRelation(strtok($v, " ") , getLatestTDoc($this->dbaccess, $doc->initid));
                    if ($err) {
                        $action->log->error($err);
                        $doc->addComment($err);
                        return $err;
                    }
                    $vf = $doc->getRValue(strtok($v, " "));
                    if ($vf) {
                        $tvf = $this->_val2array($vf);
                        foreach ($tvf as $vf) {
                            if ($vf) {
                                $fileinfo = $this->getFileInfo($vf);
                                if ($fileinfo["path"]) {
                                    
                                    $multi_mix->addSubpart('', array(
                                        'body_file' => $fileinfo['path'],
                                        'content_type' => $fileinfo['mime_s'],
                                        'charset' => 'UTF-8',
                                        'filename' => $fileinfo['name'],
                                        'dfilename' => $fileinfo['name'],
                                        'encoding' => 'base64',
                                        'name_encoding' => 'quoted-printable',
                                        'filename_encoding' => 'quoted-printable',
                                        'disposition' => 'attachment'
                                    ));
                                }
                            }
                        }
                    }
                }
                
                $err = sendmail($to, $from, $cc, $bcc, $subject, $multi_mix);
                
                $savecopy = $this->getValue("tmail_savecopy") == "yes";
                if (($err == "") && $savecopy) createSentMessage($to, $from, $cc, $bcc, $subject, $multi_mix, $doc);
                $recip = "";
                if ($to) $recip.= sprintf(_("sendmailto %s") , $to);
                if ($cc) $recip.= ' ' . sprintf(_("sendmailcc %s") , $cc);
                if ($bcc) $recip.= ' ' . sprintf(_("sendmailbcc %s") , $bcc);
                
                if ($err == "") {
                    $doc->addComment(sprintf(_("send mail %s with template %s") , $recip, $this->title) , HISTO_INFO, "SENDMAIL");
                    $action->log->info(sprintf(_("Mail %s sent to %s") , $subject, $recip));
                    addWarningMsg(sprintf(_("send mail %s") , $recip));
                } else {
                    $doc->addComment(sprintf(_("cannot send mail %s with template %s : %s") , $recip, $this->title, $err) , HISTO_ERROR);
                    $action->log->error(sprintf(_("cannot send mail %s to %s : %s") , $subject, $recip, $err));
                    addWarningMsg(sprintf(_("cannot send mail %s") , $err));
                }
            }
            return $err;
        }
        /**
         * update template with document values
         * @param Doc $doc
         * @param string $tpl template content
         * @return string
         */
        private function generateMailInstance(Doc & $doc, $tpl)
        {
            global $action;
            $tpl = str_replace("&#x5B;", "[", $tpl); // replace [ convverted in Doc::setValue()
            $doc->lay = new Layout("", $action, $tpl);
            
            $ulink = ($this->getValue("tmail_ulink") == "yes");
            $doc->viewdefaultcard("mail", $ulink, false, true);
            foreach ($this->keys as $k => $v) $doc->lay->set($k, $v);
            $body = $doc->lay->gen();
            $body = preg_replace(array(
                "/SRC=\"([^\"]+)\"/e",
                "/src=\"([^\"]+)\"/e"
            ) , "\$this->srcfile('\\1')", $body);
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
                        
                        $dests[$td][$kDest] = preg_replace(sprintf('/(^|,|\s)(%s)/', preg_quote($aSumail["inmail"])) , sprintf('\1\2, "%s" <%s>', $suName, $aSumail["sumail"]) , $dests[$td][$kDest]);
                    }
                }
            }
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
                    $url = $this->getParam($url);
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
                $cid = "cid" . $reg[1] . '.' . fileextension($info->path);
            }
            
            if (!in_array(strtolower(fileextension($src)) , $vext)) return "";
            
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
?>