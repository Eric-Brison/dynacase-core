<?php
/*
 * @author Anakeen
 * @package DCP
*/

global $app_desc, $action_desc, $app_acl;

$app_desc = array(
    "name" => "TST_OPENACCESS",
    "short_name" => "Tst Open Access",
    "description" => "Test Open Access Permission",
    "displayable" => "Y",
);

$app_acl = array(
    array(
        "name" => "TST_JOHN_ACL",
        "description" => "Test Access ACL #1",
    ) ,
    array(
        "name" => "TST_JANE_ACL",
        "description" => "Test Access ACL #2",
    )
);

$action_desc = array(
    array(
        "name" => "TST_OPENACCESS_ACTION_1",
        "toc" => "Y",
        "acl" => "TST_JOHN_ACL",
        "short_name" => "Test Access Action #1",
        "root" => "Y"
    ),
    array(
        "name" => "TST_OPENACCESS_ACTION_2",
        "toc" => "Y",
        "acl" => "TST_JANE_ACL",
        "short_name" => "Test Access Action #2",
    ),
    array(
        "name" => "TST_OPENACCESS_ACTION_FREE",
        "toc" => "Y",
        "acl" => Action::ACCESS_FREE,
        "short_name" => "Test Access Action Access Free",
    ),
    array(
        "name" => "TST_OPENACCESS_ACTION_OPEN1",
        "openaccess" => "Y",
        "acl" => "TST_JANE_ACL",
        "short_name" => "Test Access Action Access Open",
    ),
    array(
        "name" => "TST_OPENACCESS_ACTION_OPEN2",
        "openaccess" => "Y",
        "acl" => "TST_JOHN_ACL",
        "short_name" => "Test Access Action Access Open",
    )
);