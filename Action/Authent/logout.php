<?php
include_once('Class.Session.php');
include_once('Class.User.php');
include_once('Lib.Http.php');

function logout(&$action) {

   $action->session->Close();
   
   redirect($action,"CORE","",$action->GetParam("CORE_ROOTURL"));

}
      
?>
