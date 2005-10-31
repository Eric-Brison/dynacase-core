<?php

include_once("FDL/Lib.Dir.php");
include_once("FDL/Lib.Util.php");

function ng_myportal($fid=-1) {
  global $action;

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $fid = ($fid==-1? $action->user->fid : $fid );
  $filter = array();
  $filter[] = "ngp_ownerid=".$fid;
  $portal = getChildDoc($dbaccess, 0, 0, "ALL", $filter, $action->user->id, "LIST", "NPORTAIL");
  if (count($portal)==0) {

    $np = createDoc($dbaccess, "NPORTAIL");
    $np->setValue("ngp_ownerid", $fid);
    $thisuser = new_Doc($dbaccess, $fid);
    $np->setValue("ngp_owner", $thisuser->getTitle());
    $np->Add();
    $np->Modify();

    return $np;
  } else {
    return $portal[0];
  }
}

?>