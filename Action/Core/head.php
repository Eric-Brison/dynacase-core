<?php
// $Id: head.php,v 1.5 2002/01/30 15:00:57 eric Exp $
// $Log: head.php,v $
// Revision 1.5  2002/01/30 15:00:57  eric
// correction problème de '
//
// Revision 1.4  2002/01/29 10:26:02  eric
// chg nom de fonction cause conflit
//
// Revision 1.3  2002/01/28 16:56:49  eric
// animation bouton bleu & suppression appel username
//
// Revision 1.2  2002/01/25 14:31:37  eric
// gestion de cache objet - variable de session
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.10  2002/01/04 12:51:45  eric
// correction mineure
//
// Revision 1.9  2001/10/17 09:09:26  eric
// mise en place de i18n via gettext
//
// Revision 1.8  2001/08/30 14:58:36  eric
// changement mise en forme de la frame header
//
// Revision 1.7  2001/08/20 16:45:12  eric
// changement des controles d'accessibilites
//
// Revision 1.6  2001/07/26 09:43:09  eric
// visibilité des icones ssi action root possible
//
// Revision 1.5  2001/06/13 13:51:03  eric
// multi frame support
//
// Revision 1.4  2000/10/23 17:09:17  yannick
// Conneries
//
// Revision 1.3  2000/10/23 14:11:22  yannick
// Gestion des droits
//
// Revision 1.2  2000/10/11 16:31:33  yannick
// Nouvelle gestion de l'init
//
// Revision 1.1.1.1  2000/10/05 17:29:10  yannick
// Importation
//

include_once('Class.QueryDb.php');
include_once('Class.Application.php');

function head(&$action) {

// This function is used to show all available applications
// in the main header
  $query=new QueryDb($action->dbaccess,"Application");
  $query->basic_elem->sup_where=array("available='Y'","displayable='Y'");
  $list = $query->Query(0,0,"TABLE");



  // remove applications that need access perm
  $tab = array();
  if ($query->nb > 0) {
    $i=0;
    while(list($k,$appli)=each($list)) {
      if ($appli["access_free"] == "N") {
        $action->log->debug("Access not free for :".$appli["name"]);
        if (isset($action->user)) {
	  if ($action->user->id != 1) { // no control for user Admin
	    $p = new Permission($action->dbaccess,array($action->user->id, $appli["id"]));
	    //if ($p->id_acl == "") continue;

	    // test if acl of root action is granted
	  
	  
	    // search  acl for root action
	    $queryact=new QueryDb($action->dbaccess,"Action");
	    $queryact->AddQuery("id_application=".$appli["id"]);
	    $queryact->AddQuery("root='Y'");
	    $listact = $queryact->Query();
	    $root_acl_name=$listact[0]->acl;

	    // Get the id acl from acl name
	    $acl=new Acl($action->dbaccess);
	    if ( ! $acl->Set($root_acl_name,$appli["id"])) {
	      $action->log->warning("Acl $root_acl_name not available for App ".$appli["id"]);
	      continue;
	    }

	    if (! $p->HasPrivilege($acl->id)) continue;
	  }
	  
        } else { continue; }
      }
      $appli["description"]= $action->text($appli["description"]); // translate
      $tab[$i++]=$appli;
    }
  }

  $action->lay->set("DATE",strftime("%a %d %B %Y  %H:%M",time()));

  $action->lay->SetBlockCorresp("FUNCTION","NAME","name");
  $action->lay->SetBlockCorresp("FUNCTION","IMAGE","icon");
  $action->lay->SetBlockCorresp("FUNCTION","DESCR","description");

  $action->lay->SetBlockData("FUNCTION",$tab);
  

  // update application name
  global $HTTP_GET_VARS;
  $app = new Application();

  if (isset($HTTP_GET_VARS["capp"]))
    $app->Set($HTTP_GET_VARS["capp"], $action->parent);
  else
    $app->Set($HTTP_GET_VARS["app"], $action->parent);
    
  $action->lay->set("APP_TITLE", _($app->description));
  $action->lay->set("SESSION",$action->session->id);

 $action->lay->set("sid",session_id());

  $jslauch=$action->GetLayoutFile("lauch_action.js");
  $lay = new Layout($jslauch, $action);
  $action->parent->AddJsCode($lay->gen());

  // update username
  userlogin($action);

}


function userlogin(&$action) {

// This function is used to show curent user if set
// TODO

  if (!isset($action->user)) {
    $action->lay->set("USER","");
    $action->lay->set("ONOUT",$action->parent->GetImageUrl("bblue.gif"));
    $action->lay->set("ONOVER",$action->parent->GetImageUrl("bgreen.gif"));
    $action->lay->set("ALTLOGINOUT","login");
    $action->lay->set("ACTION","");
    $action->lay->set("anim","true");
  } else {
    $action->lay->set("USER",$action->user->firstname." ".$action->user->lastname);
    $action->lay->set("ONOUT",$action->parent->GetImageUrl("bgreen.gif"));
    $action->lay->set("ONOVER",$action->parent->GetImageUrl("bred.gif"));
    $action->lay->set("ALTLOGINOUT","logout");
    $action->lay->set("ACTION","LOGOUT");
    $action->lay->set("OUT","");
    $action->lay->set("anim","false");
  }
}

?>
