<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Callback page when requesting a password re-initialization
 *
 * @author Anakeen 2009
 * @version $Id: callbackreqpasswd.php,v 1.5 2009/01/16 13:33:00 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

function callbackreqpasswd(&$action)
{
    include_once ('FDL/Lib.Dir.php');
    include_once ('WHAT/Class.UserToken.php');

    $action->parent->AddCssRef('AUTHENT:callbackreqpasswd.css');

    $action->lay->set('CALLBACK_OK', False);
    $action->lay->set('CALLBACK_NOT_OK', False);
    $action->lay->set('ON_ERROR_CONTACT', $action->getParam('SMTP_FROM'));

    $token = getHttpVars('token');
    // Retrieve token from database
    $utok = new UserToken($action->dbaccess, $token);
    if (!is_object($utok)) {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "new UserToken(" . $token . ") returned with error : " . $utok);
        $action->lay->set('CALLBACK_NOT_OK', True);
        return "";
    }
    if (!$utok->isAffected()) {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "no element found for token " . $token);
        $action->lay->set('CALLBACK_NOT_OK', True);
        return "";
    }
    // If this token has expired, remove all expired tokens
    $now = time();
    $expire = FrenchDateToUnixTs($utok->expire);
    if ($now > $expire) {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Token " . $utok->token . " has expired (expire = " . $utok->expire . ")");
        $action->lay->set('CALLBACK_NOT_OK', True);
        $utok->deleteExpired();
        return "";
    }
    
    $freedomdb = $action->getParam('FREEDOM_DB');
    if ($freedomdb == "") {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "FREEDOM_DB is empty");
        $action->lay->set('CALLBACK_NOT_OK', True);
        return "";
    }
    // Retrieve the IUSER document associated with the token
    $iuser = new_Doc($freedomdb, $utok->userid);
    if (!is_object($iuser)) {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "new Doc(" . $userid . ") returned with error : " . $iuser);
        $action->lay->set('CALLBACK_NOT_OK', True);
        return "";
    }
    // Reset the password for the IUSER
    $err = $iuser->disableEditControl();
    if ($err != "") {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "error disabling edit controls on document : " . $err);
        $action->lay->set('CALLBACK_NOT_OK', True);
        return "";
    }
    
    $password = mkpasswd();
    $err = $iuser->setPassword($password);
    if ($err != "") {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "setPassword() returned error : " . $err);
        $action->lay->set('CALLBACK_NOT_OK', True);
        return "";
    }
    // Send the new pasword by mail
    $err = sendResponse($action, $iuser, 'AUTHENT/Layout/callbackreqpasswd_mail.xml', $password);
    if ($err != "") {
        $action->lay->set('CALLBACK_NOT_OK', True);
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "sendResponse() returned with error : " . $err);
        return "";
    }
    
    $action->lay->set('CALLBACK_OK', True);
    // Delete the token in the database
    $err = $utok->delete();
    if ($err != "") {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "utok->delete() returned with error : " . $err);
    }
    
    return "";
}

function sendResponse($action, $userdoc, $layoutPath, $password)
{
    include_once ('WHAT/Class.UserToken.php');
    include_once ("FDL/sendmail.php");
    
    $us_mail = $userdoc->getValue('us_mail');
    $us_fname = $userdoc->getValue('us_fname');
    $us_lname = $userdoc->getValue('us_lname');
    
    if ($us_mail == "") {
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Empty us_mail for user " . $userdoc->getValue('id'));
        return "Empty us_mail for user " . $userdoc->getValue('id');
    }
    
    $from = $action->getParam('SMTP_FROM');
    $subject = $action->getParam('AUTHENT_CALLBACKREQPASSWD_MAIL_SUBJECT');
    
    $layout = new Layout($layoutPath, $action);
    if ($layout == NULL) {
        return "error creating new Layout from $layoutPath";
    }
    
    $layout->set('US_MAIL', $us_mail);
    $layout->set('US_FNAME', $us_fname);
    $layout->set('US_LNAME', $us_lname);
    $layout->set('PASSWORD', $password);
    
    $content = $layout->gen();
    
    $mimemail = new Fdl_Mail_Mime("\r\n");
    $mimemail->setHTMLBody($content);
    
    $ret = sendmail($us_mail, $from, NULL, NULL, $subject, $mimemail, NULL);
    if ($ret != "") {
        return "Error: sendmail() returned with $ret";
    }
    
    return "";
}
?>