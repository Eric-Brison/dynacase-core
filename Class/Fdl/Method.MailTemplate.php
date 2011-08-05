<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Mail template document
 *
 * @author Anakeen 2009
 * @version $Id: Method.MailTemplate.php,v 1.11 2009/01/16 12:47:38 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */
/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
Class _MAILTEMPLATE extends Doc
{
    /*
     * @end-method-ignore
    */
    public $ifiles = array();
    public $sendercopy = true;
    
    function preEdition()
    {
        global $action;
        
        if ($mailfamily = $this->getValue("tmail_family", getHttpVars("TMAIL_FAMILY"))) {
            $action->parent->AddJsRef("?app=FDL&action=FCKDOCATTR&famid=" . $mailfamily);
        }
    }
    /**
     * send document by email using this template
     * @return string error - empty if no error -
     */
    function sendDocument(&$doc, $keys = array())
    {
        global $action;
        
        include_once ("FDL/sendmail.php");
        include_once ("FDL/Lib.Vault.php");
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
            
            if ($doc->wid) {
                $wdoc = new_doc($this->dbaccess, $doc->wid);
            }
            
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
                        $mail = $doc->getRValue($aid);
                        break;

                    case 'WA': // workflow text attribute
                        if ($wdoc) {
                            $aid = strtok($v["tmail_recip"], " ");
                            $mail = $wdoc->getRValue($aid);
                        }
                        break;

                    case 'E': // text parameter
                        $aid = strtok($v["tmail_recip"], " ");
                        $mail = $doc->getparamValue($aid);
                        break;

                    case 'WE': // workflow text parameter
                        if ($wdoc) {
                            $aid = strtok($v["tmail_recip"], " ");
                            $mail = $wdoc->getparamValue($aid);
                        }
                        break;

                    case 'D': // user relations
                        
                    case 'WD': // user relations
                        if ($type == 'D') $udoc = & $doc;
                        elseif ($wdoc) $udoc = & $wdoc;
                        if ($udoc) {
                            $aid = strtok($v["tmail_recip"], " ");
                            $vdocid = $udoc->getValue($aid); // for array of users
                            $vdocid = str_replace('<BR>', "\n", $vdocid);
                            if (strpos($vdocid, "\n")) {
                                $tvdoc = $this->_val2array($vdocid);
                                $tmail = array();
                                foreach ($tvdoc as $docid) {
                                    $umail = $udoc->getDocValue($docid, 'us_mail', '');
                                    if (!$umail) $umail = $udoc->getDocValue($docid, 'grp_mail', '');
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
                
                if ($this->sendercopy && getParam("FDL_BCC") == "yes") {
                    $umail = getMailAddr($this->userid);
                    if ($umail != "") $dest['bcc'][] = $umail;
                }
                
                $to = implode(',', array_filter($dest['to'], create_function('$v', 'return!preg_match("/^\s*$/", $v);')));
                $cc = implode(',', array_filter($dest['cc'], create_function('$v', 'return!preg_match("/^\s*$/", $v);')));
                $bcc = implode(',', array_filter($dest['bcc'], create_function('$v', 'return!preg_match("/^\s*$/", $v);')));
                $from = trim(implode(',', array_filter($dest['from'], create_function('$v', 'return!preg_match("/^\s*$/", $v);'))));
                
                if ($from == "") $from = getMailAddr($action->user->id);
                if ($from == "") $from = getParam('SMTP_FROM');
                if ($from == "") $from = $action->user->login . '@' . $_SERVER["HTTP_HOST"];
                
                if (trim($to . $cc . $bcc) == "") return ""; //nobody to send data
                $multi_rel->addSubpart($pfout, array(
                    'content_type' => 'text/html',
                    'charset' => 'UTF-8',
                    'encoding' => 'quoted-printable'
                ));
                // ---------------------------
                // add inserted image
                
                foreach ($this->ifiles as $k => $v) {
                    if (file_exists($pubdir . "/$v")) {
                        
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
                    $doc->addComment(sprintf(_("send mail %s with template %s") , $recip, $this->title));
                    addWarningMsg(sprintf(_("send mail %s") , $recip));
                } else {
                    $doc->addComment(sprintf(_("cannot send mail %s with template %s : %s") , $recip, $this->title, $err) , HISTO_ERROR);
                    addWarningMsg(sprintf(_("cannot send mail %s") , $err));
                }
                return $err;
            }
        }
        
        function generateMailInstance(&$doc, $tpl)
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
        
        function specRefresh2()
        {
            $err = $this->senddocument(new_doc($this->dbaccess, 24470, true));
            return $err;
        }
        
        function srcfile($src)
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
    /*
     * @end-method-ignore
    */
?>