<?php
// $Id: login.php,v 1.1 2002/01/08 12:41:33 eric Exp $
// $Log: login.php,v $
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.4  2001/09/28 12:36:12  eric
// suppression ligne inutile
//
// Revision 1.3  2001/09/10 16:26:24  eric
// modification bouton login et autre icone
//
// Revision 1.2  2000/10/26 15:32:17  yannick
// Remise à niveau
//
// Revision 1.1  2000/10/12 08:06:55  yannick
// Ajout du traitement de l'ampoule
//
// Revision 1.2  2000/10/11 12:27:38  yannick
// Gestion de l'authentification
//
// Revision 1.1.1.1  2000/10/05 17:29:10  yannick
// Importation
//


function login(&$action) {

// This function is used to show curent user if set
// TODO

  if (!isset($action->user)) {
    $action->lay->set("USER","");
    $action->lay->set("ONOUT",$action->parent->GetImageUrl("byellow.gif"));
    $action->lay->set("ONOVER",$action->parent->GetImageUrl("bgreen.gif"));
    $action->lay->set("ALTLOGINOUT","login");
    $action->lay->set("ACTION","");
  } else {
    $action->lay->set("USER",$action->user->firstname." ".$action->user->lastname);
    $action->lay->set("ONOUT",$action->parent->GetImageUrl("bgreen.gif"));
    $action->lay->set("ONOVER",$action->parent->GetImageUrl("bred.gif"));
    $action->lay->set("ALTLOGINOUT","logout");
    $action->lay->set("ACTION","LOGOUT");
    $action->lay->set("OUT","");
  }
}

?>
