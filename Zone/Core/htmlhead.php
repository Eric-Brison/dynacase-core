<?php
// $Id: htmlhead.php,v 1.2 2002/09/12 08:40:49 eric Exp $


include_once('Class.QueryDb.php');
include_once('Class.Application.php');

function htmlhead(&$action) {


  $title = GetHttpVars("title");
    
  $action->lay->set("TITLE", $title);


}
?>
