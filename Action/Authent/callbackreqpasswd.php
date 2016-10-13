<?php
/*
 * Callback page when requesting a password re-initialization
 * @author Anakeen
 * @package FDL
*/
function callbackreqpasswd(Action & $action)
{
    include_once ('FDL/Lib.Dir.php');
    include_once ('WHAT/Class.UserToken.php');
    // $action->parent->AddCssRef('AUTHENT:callbackreqpasswd.css');
    $action->parent->AddCssRef('AUTHENT:loginform.css', true);
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/resizeimg.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/geometry.js");
    $action->parent->addJsRef("AUTHENT:loginform.js", true);
    $action->lay->set('CALLBACK_OK', False);
    $action->lay->set('CALLBACK_NOT_OK', False);
    $action->lay->set('ON_ERROR_CONTACT', $action->getParam('SMTP_FROM'));
    $au = new ActionUsage($action);
    $token = $au->addRequiredParameter("token", "token number");
    $uid = $au->addOptionalParameter("uid", "user id");
    $pwd1 = $au->addOptionalParameter("pwd1", "new password 1");
    $pwd2 = $au->addOptionalParameter("pwd2", "new password 2");
    $au->verify();
    // Retrieve token from database
    $utok = new UserToken($action->dbaccess, $token);
    
    if (!$utok->isAffected()) {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "no element found for token " . $token);
        $action->exitError(_("Cannot access interface to change password"));
        return "";
    }
    // If this token has expired, remove all expired tokens
    if (!openAuthenticator::verifyOpenExpire($utok)) {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Token " . $utok->token . " has expired (expire = " . $utok->expire . ")");
        $action->exitError(_("Cannot access interface to change password"));
        return "";
    }
    
    if (!$utok->context || !openAuthenticator::verifyOpenAccess($utok)) {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "context not valid");
        $action->exitError(_("Cannot access interface to change password"));
        return "";
    }
    $context = unserialize($utok->context);
    if (empty($context["app"]) || empty($context["action"])) {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "context not valid");
        $action->exitError(_("Cannot access interface to change password"));
    }
    // Retrieve the IUSER document associated with the token
    $u = new Account('', $utok->userid);
    if (!$u->isAffected()) {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "new Doc(" . $utok->userid . ") returned with error : " . $utok->userid);
        $action->exitError(_("Cannot access interface to change password"));
        return "";
    }
    
    $action->lay->eset("uid", $u->id);
    $action->lay->eset("token", $token);
    $finish = false;
    $action->lay->eset("username", $u->getDisplayName($u->id));
    $err = '';
    if ($uid == $utok->userid) {
        if ($pwd1 != '' && $pwd2 == $pwd1) {
            // verify force
            
            /**
             * @var \Dcp\Family\IUSER $udoc
             */
            $udoc = new_doc($action->dbaccess, $u->fid);
            $udoc->disableEditControl();
            if ($udoc->isAlive()) {
                $err = $udoc->testForcePassword($pwd1);
                if ($err == '') {
                    $u->password_new = $pwd1;
                    $err = $u->modify();
                    if ($err == "") {
                        $udoc->addHistoryEntry(_("Change password by token"));
                    }
                    // Delete the token in the database
                    $err = $utok->delete();
                    if ($err != "") {
                        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "utok->delete() returned with error : " . $err);
                    }
                    authLog(sprintf("Change password succeeded for %s [%d]", $u->login, $u->id));
                    $finish = true;
                }
            }
            $udoc->enableEditControl();
        } else {
            if ($pwd1 == '') {
                $err = _("password must not be empty");
            } else {
                $err = _("the two passwords must be the same");
            }
        }
    } else {
        if ($uid) {
            $err = _("acking detection : clear token");
            $utok->delete();
        }
    }
    
    if ($err) authLog(sprintf("Fail to  change password for %s [%d] : %s", $u->login, $u->id, $err));
    else if (!$finish) authLog(sprintf("Try to  change password for %s [%d]", $u->login, $u->id));
    $action->lay->set("errortxt", $err);
    $action->lay->set("ERROR", ($err != ''));
    $action->lay->set("finish", $finish);
    
    return "";
}

function authLog($txt)
{
    $log = new Log("", "Authent", "ChangePassword");
    $facility = constant(getParam("AUTHENT_LOGFACILITY", "LOG_AUTH"));
    $log->wlog("S", $txt, NULL, $facility);
}
