<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * submitreqpasswd function for receiving password re-initialization
 * request and send the user a mail containing a new generated
 * password
 *
 * @author Anakeen
 * @version $Id: submitreqpasswd.php,v 1.4 2009/01/16 13:33:00 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

function submitreqpasswd(Action & $action)
{
    include_once ('FDL/Lib.Dir.php');
    include_once ('FDL/freedom_util.php');
    
    $submitted_login = GetHttpVars('form_login');
    $submitted_email = GetHttpVars('form_email');
    
    $action->parent->AddCssRef('AUTHENT:submitreqpasswd.css');
    
    $action->lay->set('FORM_SEND_OK', False);
    $action->lay->set('FORM_SEND_ERROR_INVALID_ARGS', False);
    $action->lay->set('FORM_SEND_ERROR_UNKNOWN', False);
    $action->lay->set('FORM_SEND_ERROR_EXTERNAL_AUTH', False);
    $action->lay->set('ON_ERROR_CONTACT', $action->getParam('SMTP_FROM'));
    
    $userdoc = retrieveUserDoc($action, $submitted_login, $submitted_email);

    if ($userdoc == NULL) {
        $action->lay->set('FORM_SEND_ERROR_INVALID_ARGS', True);
        return;
    }
    
    $providerList = getAuthProviderList();
    $ldapUserFamId = getIdFromName($action->dbaccess, 'LDAPUSER');
    if (!in_array('freedom', $providerList) || ($ldapUserFamId !== false && $userdoc->fromid == $ldapUserFamId)) {
        $action->lay->set('FORM_SEND_ERROR_EXTERNAL_AUTH', True);
        return;
    }
    
    $ret = sendCallback($action, $userdoc, 'AUTHENT/Layout/submitreqpasswd_mail.xml');
    if ($ret != "") {
        error_log(__FUNCTION__ . " $ret");
        $action->lay->set('FORM_SEND_ERROR_UNKNOWN', True);
        return;
    }
    $log = new Log("", "Authent", "ChangePassword");
    $facility = constant(getParam("AUTHENT_LOGFACILITY", "LOG_AUTH"));
    $txt=sprintf("ask change password for %s [%d]", $userdoc->getAccount()->login, $userdoc->getAccount()->id);
    $log->wlog("S", $txt, NULL, $facility);
    $action->lay->set('FORM_SEND_OK', True);
    return;
}
/**
 * @param Action $action
 * @param string $login
 * @param string $email
 * @return _IUSER|null
 */
function retrieveUserDoc(Action $action, $login = "", $email = "")
{
    
    if (!$login && !$email) {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Undefined email and login args.");
        return NULL;
    }
    
    $s = new SearchDoc($action->dbaccess, "IUSER");
    if ($login != "") {
        $s->addFilter("us_login = '%s'", $login);
    }
    if ($email != "") {
        $s->addFilter("us_mail = '%s'", $email);
    }
    
    $s->setObjectReturn();
    $s->noViewControl();
    $s->search();
    if ($s->count() <= 0) {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Empty search result");
        return NULL;
    }
    
    if ($s->count() > 1) {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Result contains more than 1 element");
        return NULL;
    }
    /**
     * @var _IUSER $uDoc
     */
    $uDoc = $s->nextDoc();
    $email = $uDoc->getMail();
    if ($email == "") {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Empty us_mail for docid '" . $uDoc->id . "'");
        return NULL;
    }
    
    return $uDoc;
}

function sendCallback(Action $action, _IUSER $userdoc)
{
    include_once ('WHAT/Class.UserToken.php');
    include_once ("FDL/sendmail.php");
    
    $us_mail = $userdoc->getMail();
    $uid = $userdoc->getValue("us_whatid");
    if ($us_mail == "") {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Empty us_mail for user " . $uid);
        return "Empty us_mail for user " . $uid;
    }
    
    $token = new UserToken();
    $token->userid = $uid;
    $token->token = $token->genToken();
    $token->setExpiration();
    $token->expendable = 1;
    $err = $token->add();
    if ($err != "") {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error token->add() : " . $err);
        return $err;
    }
    $err = $token->modify();
    if ($err != "") {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error token->modify() : " . $err);
        return $err;
    }
    
    $callback_token = $token->getToken();
    /**
     * @var _MAILTEMPLATE $mt
     */
    $mt = new_doc($action->dbaccess, $action->GetParam("AUTHENT_MAILASKPWD"));
    if (!$mt->isAlive()) {
        return sprintf("Cannot found mail template from AUTHENT_MAILASKPWD parameter");
    }
    if (!is_a($mt, '_MAILTEMPLATE')) {
        return sprintf("AUTHENT_MAILASKPWD parameter not reference a mail template");
    }
    $keys = array(
        "LINK_CHANGE_PASSWORD" => sprintf("%sguest.php?app=AUTHENT&action=CALLBACKREQPASSWD&token=%s", $action->GetParam("CORE_EXTERNURL") , $callback_token)
    );
    $err = $mt->sendDocument($userdoc, $keys);
    
    return $err;
}
?>