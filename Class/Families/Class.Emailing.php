<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Methods for emailing family
 */
namespace Dcp\Core;
class Emailing extends \Dcp\Family\Batch
{
    /*
     * @end-method-ignore
    */
    var $defaultedit = "FDL:FDL_PUBEDIT";
    var $defaultmview = "FDL:FDL_PUBMAIL:T";
    /**
     * @param string $target
     * @param bool $ulink
     * @param bool $abstract
     * @templateController
     */
    function fdl_pubsendmail($target = "_self", $ulink = true, $abstract = false)
    {
        $this->viewdefaultcard($target, $ulink, $abstract);
        $this->lay->set("V_PUBM_BODY", str_replace("&#x5B;", "[", $this->lay->get("V_PUBM_BODY")));
        
        $uid = getHttpVars("uid");
        if ($uid) {
            $udoc = new_Doc($this->dbaccess, $uid);
            if ($udoc->isAlive()) {
                $listattr = $udoc->GetNormalAttributes();
                $atarget = ""; // must not be mail the same bacuse it is not the doc itself
                foreach ($listattr as $k => $v) {
                    $value = $udoc->getRawValue($v->id);
                    
                    if ($value || ($v->type == "image")) $this->lay->Set(strtoupper($v->id) , $udoc->GetHtmlValue($v, $value, $atarget, $ulink));
                    else $this->lay->Set(strtoupper($v->id) , false);
                }
            }
        }
    }
    /**
     * @param string $target
     * @param bool $ulink
     * @param bool $abstract
     * @templateController
     */
    function fdl_pubprintone($target = "_self", $ulink = true, $abstract = false)
    {
        $this->fdl_pubsendmail($target, $ulink, $abstract);
    }
    /**
     * @templateController
     */
    function fdl_pubedit()
    {
        $this->editattr();
        $famid = $this->getRawValue("PUBM_IDFAM", "IUSER");
        $udoc = createDoc($this->dbaccess, $famid, false);
        if (!$udoc) {
            addWarningMsg(sprintf(_("fdl_pubedit error: family %s not found") , $famid));
            AddLogMsg(sprintf(_("fdl_pubedit error: family %s not found") , $famid));
            return false;
        }
        $listattr = $udoc->GetNormalAttributes();
        $tatt = array();
        foreach ($listattr as $k => $v) {
            $tatt[$k] = array(
                "aid" => "[" . strtoupper($k) . "]",
                "alabel" => str_replace("'", "\\'", $v->getLabel())
            );
        }
        $listattr = $udoc->GetFileAttributes();
        foreach ($listattr as $k => $v) {
            if ($v->type == "image") {
                $tatt[$k] = array(
                    "aid" => "<img src=\"[" . strtoupper($k) . "]\">",
                    "alabel" => str_replace("'", "\\'", $v->getLabel())
                );
            } else {
                $tatt[$k] = array(
                    "aid" => "<a href=\"[" . strtoupper($k) . "]\">" . $v->getLabel() . "</a>",
                    "alabel" => str_replace("'", "\\'", $v->getLabel())
                );
            }
        }
        $this->lay->set("famattr", sprintf(_("%s attribute") , $this->getRawValue("pubm_fam", "personne")));
        $this->lay->setBlockData("ATTR", $tatt);
        return true;
    }
    /**
     * Fusion all document to be printed
     * @param \Action &$action current action
     * @global uid string Http var : user document id (if not all use rpresent in folder)
     * @templateController
     */
    function fdl_pubprint($target = "_self", $ulink = true, $abstract = false)
    {
        global $action;
        // GetAllParameters
        $udocid = GetHttpVars("uid");
        $subject = $this->getRawValue("pubm_title");
        $body = $this->getRawValue("pubm_body");
        $zonebodycard = "FDL:FDL_PUBPRINTONE:S"; // define view zone
        $tlay = array();
        if ($udocid > 0) {
            $t[] = getTDoc($this->dbaccess, $udocid);
        } else {
            $t = $this->getContent();
        }
        
        if (preg_match("/\[[a-z]+_[a-z0-9_]+\]/i", str_replace("&#x5B;", "[", $body))) {
            foreach ($t as $k => $v) {
                $zoneu = $zonebodycard . "?uid=" . $v["id"];
                $tlay[] = array(
                    "doc" => $this->viewDoc($zoneu, "", true) ,
                    "subject" => $v["title"]
                );
            }
        } else {
            $laydoc = $this->viewDoc($zonebodycard, "", true);
            
            foreach ($t as $k => $v) {
                $tlay[] = array(
                    "doc" => $laydoc,
                    "subject" => $v["title"]
                );
            }
        }
        if (count($t) == 0) $action->AddWarningMsg(_("no available persons found"));
        
        $this->lay->setBlockData("DOCS", $tlay);
        $this->lay->set("BGIMG", $this->getHtmlAttrValue("pubm_bgimg"));
    }
    /**
     * Fusion all document to be displayed
     * idem as fdl_pubprint but without new page
     * @param \Action &$action current action
     * @global uid string Http var : user document id (if not all use rpresent in folder)
     * @templateController
     */
    function fdl_pubdisplay($target = "_self", $ulink = true, $abstract = false)
    {
        $this->fdl_pubprint($target, $ulink, $abstract);
    }
    /**
     * @param string $target
     * @param bool $ulink
     * @param bool $abstract
     * @templateController
     */
    function fdl_pubmail($target = "_self", $ulink = true, $abstract = false)
    {
        include_once ("FDL/mailcard.php");
        global $action;
        $subject = $this->getRawValue("pubm_title");
        $body = $this->getRawValue("pubm_body");
        $err = "";
        $t = $this->getContent();
        $mailattr = strtolower($this->getRawValue("PÜBM_MAILATT", "us_mail"));
        
        $tout = array();
        $zonebodycard = "FDL:FDL_PUBSENDMAIL:S";
        if (preg_match("/\[[a-z]+_[a-z0-9_]+\]/i", $body)) {
            foreach ($t as $k => $v) {
                $mail = getv($v, $mailattr);
                if ($mail != "") {
                    $zoneu = $zonebodycard . "?uid=" . $v["id"];
                    $to = $mail;
                    $cc = "";
                    $err = sendCard($action, $this->id, $to, $cc, $subject, $zoneu);
                    $tout[] = array(
                        "name" => $v["title"],
                        "mailto" => $to,
                        "color" => ($err) ? "#ea4c4c" : "#7df89d",
                        "status" => ($err) ? $err : "OK"
                    );
                }
            }
        } else {
            $tmail = array();
            foreach ($t as $k => $v) {
                $mail = getv($v, $mailattr);
                if ($mail != "") $tmail[] = $mail;
            }
            $to = "";
            $bcc = implode(",", $tmail);
            $cc = "";
            $err = sendCard($action, $this->id, $to, $cc, $subject, $zonebodycard, false, "", "", $bcc);
            $tout[] = array(
                "name" => "-",
                "mailto" => $bcc,
                "color" => ($err) ? "#ea4c4c" : "#7df89d",
                "status" => ($err) ? $err : "OK"
            );
        }
        if ($err) $action->AddWarningMsg($err);
        $this->lay->setBlockData("MAILS", $tout);
        $this->viewattr($target, $ulink, $abstract);
    }
    /**
     * Preview of each document to be printed
     * @templateController
     */
    function fdl_pubpreview($target = "_self", $ulink = true, $abstract = false)
    {
        
        $this->lay->set("dirid", $this->id);
    }
    /**
     * Preview of each document to be printed
     * @templateController
     */
    function fdl_pubnavpreview($target = "_self", $ulink = true, $abstract = false)
    {
        
        $t = $this->getContent();
        $tlay = array();
        foreach ($t as $k => $v) {
            $tlay[] = array(
                "udocid" => $v["id"],
                "utitle" => $v["title"]
            );
        }
        
        $this->lay->setBlockData("DOCS", $tlay);
        $this->lay->set("dirid", $this->id);
    }
}