<?php
include_once('Class.User.php');
include_once('Class.Session.php');

function authenticate() {
  //   Header( "WWW-Authenticate: Basic realm=\"WHAT Connection\", stale=FALSE");
  Header( "WWW-Authenticate: Basic realm=\"WHAT Connection\"");
  Header( "HTTP/1.0 401 Unauthorized");
  // Header("Location:guest.php");
  echo _("Vous devez entrer un nom d'utilisateur valide et un mot de passe correct pour accéder à cette ressource");
  exit;
}

//print "$PHP_AUTH_USER $SeenBefore $OldAuth";
//if(!isset($PHP_AUTH_USER) || ($SeenBefore == 1 && !strcmp($OldAuth, $PHP_AUTH_USER)) ) {
if(!isset($PHP_AUTH_USER) || ($SeenBefore == 1 ))  {
  authenticate();
}
else {

  Header("Location:../index.php?sole=R");
  exit;
}
?>
