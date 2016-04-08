<?php
/*
 * @author Anakeen
 * @package FDL
*/

global $app_desc, $action_desc;

$app_desc = array(
    "name" => "AUTHENT", //Name
    "short_name" => "Authent", //Short name
    "description" => "Authentification Application", //long description
    "icon" => "authent.gif", //Icon
    "displayable" => "N", //Should be displayed on an app list (Y,N)
    "iorder" => 10, // install order
    "tag" => "CORE SYSTEM"
);

$action_desc = array(
    array(
        "name" => "LOGINFORM",
        "short_name" => "login",
        "root" => "Y"
    ) ,
    array(
        "name" => "CHECKAUTH",
    ) ,
    array(
        "name" => "LOGOUT"
    ) ,
    array(
        "name" => "REQPASSWD"
    ) ,
    array(
        "name" => "SUBMITREQPASSWD"
    ) ,
    array(
        "name" => "CALLBACKREQPASSWD"
    ) ,
    array(
        "name" => "ERRNO_BUG_639"
    )
);
?>
