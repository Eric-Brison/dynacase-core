<?php
// $Id: htmlhead.php,v 1.1 2002/01/08 12:41:34 eric Exp $


include_once('Class.QueryDb.php');
include_once('Class.Application.php');

function htmlhead(&$action) {


    
  $action->lay->set("APP_TITLE", _($action->parent->description));


}
?>
