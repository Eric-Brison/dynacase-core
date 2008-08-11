<?php

function submitreqpasswd(&$action) {
  $submited_login = GetHttpVars('login');
  $submited_email = GetHttpVars('email');

  $action->lay->set('FORM_SEND_OK', False);
  $action->lay->set('FORM_SEND_ERROR_INVALID_ARGS', False);
  $action->lay->set('FORM_SEND_ERROR_UNKNOWN', False);

  $userdoc = getUserDoc($action, $submitted_login, $submitted_email);
  if( $userdoc == NULL ) {
    $action->lay->set('FORM_SEND_ERROR_INVALID_ARGS' True);
    return;
  }

  $ret = sendCallback($action, $userdoc, 'AUTHENT/callbackreqpasswd_mail.xml');
  if( $ret != "" ) {
    $action->lay->set('FORM_SEND_ERROR_UNKNOWN', True);
    return;
  }

  $action->lay->set('FORM_SEND_OK', True);
  return;
}

function getUserDoc($action, $login="", $email="") {
  $filter = array();

  if( $login != "" ) {
    $filter[] = "us_login = '".pg_escape_string($login)."'";
  }
  if( $email != "" ) {
    $filter[] = "us_mail = '".pg_escape_string($email)."'";
  }

  if( count($filter) <= 0 ) {
    error_log(__CLASS__."::".__FUNCTION__." "."Undefined email and login args.");
    return NULL;
  }
  $res = getChildDoc($action->dbaccess,
		     0,
		     '0', 'ALL',
		     $filter,
		     1,
		     'TABLE',
		     'IUSER'
		     );
  if( count($res) <= 0 ) {
    error_log(__CLASS__."::".__FUNCTION__." "."Empty search result");
    return NULL;
  }

  if( count($res) > 1 ) {
    error_log(__CLASS__."::".__FUNCTION__." "."Result contains more than 1 element");
    return NULL;
  }

  $email = $res[0]['us_mail'];

  if( $email == "" ) {
    error_log(__CLASS__."::".__FUNCTION__." "."Empty us_mail for docid '".$res[0]['id']."'");
    return NULL;
  }

  return $res[0];
}

function sendCallback($action, $userdoc, $layoutPath) {
  include_once('AUTHENT/Class.UserToken.php');

  $to = $userdoc['us_mail'];
  $fname = $userdoc['us_fname'];
  $lname = $userdoc['us_lname'];

  if( $to == "" ) {
    error_log(__CLASS__."::".__FUNCTION__." "."Empty us_mail for user ".$userdoc['id']);
    return "Empty us_mail for user ".$userdoc['id'];
  }

  $from = "";
  $subject= "";

  $token = new UserToken();
  $token->setUserId($userdoc['id']);
  $token->genToken();
  $err = $token->modify();
  if( $err != "" ) {
    return $err;
  }

  $callback_token = $token->getToken();

  $layout = new Layout($layoutPath, $action);
  if( $layout == NULL ) {
    return "error creating new Layout from $layoutPath";
  }

  $layout->set('MAIL_HEADERS', $mail_headers);

  $layout->set('US_MAIL', $us_mail);
  $layout->set('US_FNAME', $us_fname);
  $layout->set('US_LNAME', $us_lname);
  $layout->set('CALLBACK_TOKEN', $callback_token);

  $content = $layout->gen();

  $mimemail = new Mail_mime("\r\n");
  $mimemail->setHTMLBody($content);

  $ret = sendmail(
		  $to,
		  $from,
		  NULL,
		  NULL,
		  $subject,
		  &$mimemail,
		  NULL
		  );
  if( $ret != "" ) {
    # $action->exitError("Error: sendmail() returned with $ret");
    return "Error: sendmail() returned with $ret";
  }
}

?>