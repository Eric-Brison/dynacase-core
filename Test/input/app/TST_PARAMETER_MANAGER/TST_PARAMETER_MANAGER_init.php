<?php
/*
 * @author Anakeen
 * @package Dcp\Pu
*/

global $app_const;

$app_const = array(
    "INIT" => "yes",
    "VERSION" => "1.2.3-4",
    "PARENT_USER_GLOBAL_PARAMETER" => array(
        "val" => "PARENT_USER_GLOBAL_PARAMETER_VALUE",
        "descr" => "PARENT_USER_GLOBAL_PARAMETER_DESC",
        "global" => "Y",
        "user" => "Y"
    ),
    "PARENT_USER_PARAMETER" => array(
        "val" => "PARENT_USER_PARAMETER",
        "descr" => "PARENT_USER_PARAMETER",
        "global" => "N",
        "user" => "Y"
    ),
    "PARENT_GLOBAL_PARAMETER" => array(
        "val" => "PARENT_GLOBAL_PARAMETER",
        "descr" => "PARENT_GLOBAL_PARAMETER",
        "global" => "Y",
        "user" => "N"
    ),
    "PARENT_PARAMETER" => array(
        "val" => "PARENT_PARAMETER",
        "descr" => "PARENT_PARAMETER",
        "global" => "N",
        "user" => "N"
    )
);
