<?php

function submitreqpasswd(&$action) {
  include_once('FDL/Lib.Dir.php');

  $submitted_login = GetHttpVars('form_login');
  $submitted_email = GetHttpVars('form_email');

  $action->lay->set('FORM_SEND_OK', False);
  $action->lay->set('FORM_SEND_ERROR_INVALID_ARGS', False);
  $action->lay->set('FORM_SEND_ERROR_UNKNOWN', False);

  $userdoc = getUserDoc($action, $submitted_login, $submitted_email);
  if( $userdoc == NULL ) {
    $action->lay->set('FORM_SEND_ERROR_INVALID_ARGS', True);
    return;
  }

  $ret = sendCallback($action, $userdoc, 'AUTHENT/Layout/submitreqpasswd_mail.xml');
  if( $ret != "" ) {
    $action->lay->set('FORM_SEND_ERROR_UNKNOWN', True);
    return;
  }

  $action->lay->set('FORM_SEND_OK', True);
  return;
}

function getUserDoc($action, $login="", $email="") {
  $dbaccess = $action->getParam('FREEDOM_DB');

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
  $res = getChildDoc($dbaccess,
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
  include_once('WHAT/Class.UserToken.php');
  include_once("FDL/sendmail.php");

  $us_mail = $userdoc['us_mail'];
  $us_fname = $userdoc['us_fname'];
  $us_lname = $userdoc['us_lname'];

  if( $us_mail == "" ) {
    error_log(__CLASS__."::".__FUNCTION__." "."Empty us_mail for user ".$userdoc['id']);
    return "Empty us_mail for user ".$userdoc['id'];
  }

  $from = "";
  $subject= "";

  $token = new UserToken();
  $token->userid = $userdoc['id'];
  $token->token = $token->genToken();
  $token->setExpiration();

  $err = $token->add();
  if( $err != "" ) {
    error_log(__CLASS__."::".__FUNCTION__." "."Error token->add() : ".$err);
    return $err;
  }
  $err = $token->modify();
  if( $err != "" ) {
    error_log(__CLASS__."::".__FUNCTION__." "."Error token->modify() : ".$err);
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

  $mimemail = new Mail_Mime("\r\n");
  $mimemail->setHTMLBody($content);

  $ret = sendmail(
		  $us_mail,
		  $from,
		  NULL,
		  NULL,
		  $subject,
		  $mimemail,
		  NULL
		  );
  if( $ret != "" ) {
    # $action->exitError("Error: sendmail() returned with $ret");
    return "Error: sendmail() returned with $ret";
  }

  return "";
}

?>