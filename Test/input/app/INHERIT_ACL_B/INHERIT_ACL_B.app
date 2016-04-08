<?php
/*
 * @author Anakeen
 * @package DCP
*/

global $app_desc, $action_desc, $app_acl;

$app_desc = array(
    "name" => "INHERIT_ACL_B",
    "short_name" => "Tst Inherit Acl B",
    "description" => "Test Apps Inherited Acls",
    "displayable" => "Y",
    "childof" => "INHERIT_ACL_A"
);

$app_acl = array(
    array(
        "name" => "ACL_B_1",
        "description" => "Test ACL #1",
        "group_default" => "Y"
    ) ,
    array(
        "name" => "ACL_B_2",
        "description" => "Test ACL #2",
        "group_default" => "N"
    )
);

$action_desc = array(
    array(
        "name" => "ACTION_B_1",
        "acl" => "ACL_A_1",
        "short_name" => "Test Action #1",
    ) ,
    array(
        "name" => "ACTION_B_2",
        "acl" => "ACL_A_2",
        "short_name" => "Test Action #2"
    )
);
