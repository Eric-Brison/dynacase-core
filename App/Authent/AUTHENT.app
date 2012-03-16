<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

global $app_desc, $action_desc;

$app_desc = array(
    "name" => "AUTHENT", //Name
    "short_name" => "Authent", //Short name
    "description" => "Authentification Application", //long description
    "access_free" => "Y", //Access free ? (Y,N)
    "icon" => "authent.gif", //Icon
    "displayable" => "N", //Should be displayed on an app list (Y,N)
    "iorder" => 10, // install order
    "tag" => "CORE"
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
        "name" => "UNAUTHORIZED"
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
