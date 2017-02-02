<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * submitreqpasswd function for receiving password re-initialization
 * request and send the user a mail containing a new generated
 * password
 *
 * @author Anakeen
 * @package FDL
 * @subpackage
 */
/**
 * @param Action $action
 * @throws \Dcp\Core\Exception
 */
function submitreqpasswd(Action & $action)
{
    include_once ('FDL/Lib.Dir.php');
    include_once ('FDL/freedom_util.php');
    
    $submitted_login = $action->getArgument('form_login');
    $submitted_email = $action->getArgument('form_email');
    
    $lang = $action->getArgument("lang");
    
    setLanguage($lang);
    
    $action->parent->AddCssRef('AUTHENT:loginform.css', true);
    $action->parent->AddCssRef('AUTHENT:submitreqpasswd.css');
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/resizeimg.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/geometry.js");
    $action->parent->addJsRef("AUTHENT:loginform.js");
    
    $action->lay->set('FORM_SEND_OK', False);
    $action->lay->set('FORM_SEND_ERROR_INVALID_ARGS', False);
    $action->lay->set('FORM_SEND_ERROR_UNKNOWN', False);
    $action->lay->set('FORM_SEND_ERROR_EXTERNAL_AUTH', False);
    $action->lay->set('ON_ERROR_CONTACT', $action->getParam('SMTP_FROM', ___("Address not configured","authent")));
    $action->lay->eSet("lang", $lang);
    
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
    
    $ret = sendCallback($action, $userdoc);
    if ($ret != "") {
        error_log(__FUNCTION__ . " $ret");
        $action->lay->set('FORM_SEND_ERROR_UNKNOWN', True);
        return;
    }
    $log = new Log("", "Authent", "ChangePassword");
    $facility = constant(getParam("AUTHENT_LOGFACILITY", "LOG_AUTH"));
    $txt = sprintf("ask change password for %s [%d]", $userdoc->getAccount()->login, $userdoc->getAccount()->id);
    $log->wlog("S", $txt, NULL, $facility);
    $action->lay->set('FORM_SEND_OK', True);
    return;
}
/**
 * @param Action $action
 * @param string $login
 * @param string $email
 * @return \Dcp\Family\Iuser|null
 */
function retrieveUserDoc(Action $action, $login = "", $email = "")
{
    
    $action->lay->set('MAILMULTIPLE', false);
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
    $s->overrideViewControl();
    $s->search();
    if ($s->count() <= 0) {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Empty search result");
        return NULL;
    }
    
    if ($s->count() > 1) {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Result contains more than 1 element");
        
        $action->lay->set('MAILMULTIPLE', true);
        return NULL;
    }
    /**
     * @var \Dcp\Family\IUSER $uDoc
     */
    $uDoc = $s->getNextDoc();
    $email = $uDoc->getMail();
    if ($email == "") {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Empty us_mail for docid '" . $uDoc->id . "'");
        return NULL;
    }
    
    return $uDoc;
}

function sendCallback(Action $action, \Dcp\family\IUser $userdoc)
{
    include_once ('WHAT/Class.UserToken.php');
    include_once ("FDL/sendmail.php");
    
    $us_mail = $userdoc->getMail();
    $uid = $userdoc->getRawValue("us_whatid");
    if ($us_mail == "") {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Empty us_mail for user " . $uid);
        return "Empty us_mail for user " . $uid;
    }
    
    $token = new UserToken();
    $token->userid = $uid;
    $token->token = $token->genToken();
    $token->setExpiration();
    $token->expendable = 1;
    $token->context = serialize(array(
        "app" => "AUTHENT",
        "action" => "CALLBACKREQPASSWD"
    ));
    $token->description=___("Forget password", "authent");
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
     * @var \Dcp\Family\MAILTEMPLATE $mt
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
