<?php

function unauthorized(&$action) {
  $action->session->DeleteSession();
  
  session_name('session');
  session_start();
  session_unset();
  session_destroy();
  $_SESSION = array();
  session_commit();
  
  $action->lay->set("msg", "Vous n'êtes pas autorisé à consulter cette ressource.");

  echo $action->lay->gen();
  exit(0);
}

?>