<?php
// $Id: username.php,v 1.1 2002/01/08 12:41:33 eric Exp $
// $Log: username.php,v $
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.3  2000/10/11 13:04:40  yannick
// gestion du login/logout
//
// Revision 1.2  2000/10/11 12:27:38  yannick
// Gestion de l'authentification
//
// Revision 1.1.1.1  2000/10/05 17:29:10  yannick
// Importation
//


function username(&$action) {

// This function is used to show curent user if set
// TODO
  if ($action->parent->Exists("AUTHENT")) {
    $act_login=new Action();
    $act_login->Set("LOGIN",$action->parent,$action->session);
    $action->lay->set("OUT",$act_login->execute());
  } else {
    $action->lay->set("OUT","");
  }

}
?>
