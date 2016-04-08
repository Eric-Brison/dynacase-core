<?php
/*
 * @author Anakeen
 * @package DCP
*/

global $app_desc, $action_desc, $app_acl;

$app_desc = array(
    "name" => "TST_ACCESS",
    "short_name" => "Tst Access",
    "description" => "Test Access Permission",
    "displayable" => "Y",
);

$app_acl = array(
    array(
        "name" => "TST_ACCESS_ACL_1",
        "description" => "Test Access ACL #1",
    ) ,
    array(
        "name" => "TST_ACCESS_ACL_2",
        "description" => "Test Access ACL #2",
    )
);

$action_desc = array(
    array(
        "name" => "TST_ACCESS_ACTION_1",
        "toc" => "Y",
        "acl" => "TST_ACCESS_ACL_1",
        "short_name" => "Test Access Action #1",
        "root" => "Y"
    ),
    array(
        "name" => "TST_ACCESS_ACTION_2",
        "toc" => "Y",
        "acl" => "TST_ACCESS_ACL_2",
        "short_name" => "Test Access Action #2",
    ),
    array(
        "name" => "TST_ACCESS_ACTION_FREE",
        "toc" => "Y",
        "acl" => Action::ACCESS_FREE,
        "short_name" => "Test Access Action Access Free",
    )
);
?>