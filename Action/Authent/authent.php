<?php
include_once('Class.User.php');

function authenticate() {
  //   Header( "WWW-Authenticate: Basic realm=\"WHAT Connection\", stale=FALSE");
  Header( "WWW-Authenticate: Basic realm=\"WHAT Connection\", stale=true");
  Header( "HTTP/1.0 401 Unauthorized");
  // Header("Location:guest.php");
  echo _("Vous devez entrer un nom d'utilisateur valide et un mot de passe correct pour accéder à cette ressource");
  exit;
}

//print "$PHP_AUTH_USER $SeenBefore $OldAuth";
if(!isset($PHP_AUTH_USER) || ($SeenBefore == 1 && !strcmp($OldAuth, $PHP_AUTH_USER)) ) {

  authenticate();
}
else {
  global $SERVER_NAME;
  Header("Location: http://".$SERVER_NAME."/what/index.php?sole=R");
  exit;
}
?>
