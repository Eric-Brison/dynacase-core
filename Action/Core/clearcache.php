<?php
// $Id: clearcache.php,v 1.1 2002/04/15 14:19:59 eric Exp $
// $Log: clearcache.php,v $
// Revision 1.1  2002/04/15 14:19:59  eric
// ajout clear cache objet
//



function clearcache(&$action) {

  //  session_unset();
      session_unregister("CacheObj");
  
  redirect($action,"CORE","HEAD",$action->GetParam("CORE_STANDURL"));
}

?>
