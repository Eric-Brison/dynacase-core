<?php
/*
 * @author Anakeen
 * @package DCP
*/

global $app_desc, $action_desc, $app_acl;

$app_desc = array(
    "name" => "INHERIT_ACL_A",
    "short_name" => "Tst Inherit Acl A",
    "description" => "Test Apps Inherited Acls",
    "displayable" => "Y",
);

$app_acl = array(
    array(
        "name" => "ACL_A_1",
        "description" => "Test ACL #1",
        "group_default" => "Y"
    ) ,
    array(
        "name" => "ACL_A_2",
        "description" => "Test ACL #2",
        "group_default" => "N"
    )
);

$action_desc = array(
    array(
        "name" => "ACTION_A_1",
        "acl" => "ACL_A_1",
        "short_name" => "Test Action #1",
    ) ,
    array(
        "name" => "ACTION_A_2",
        "acl" => "ACL_A_2",
        "short_name" => "Test Action #2"
    )
);
