<?php

global $app_desc, $action_desc, $app_acl;

$app_desc = array(
    "name" => "ACCESS", //Name
    "short_name" => N_("Access"), //Short name
    "description" => N_("What Access Management"), //long description
    "access_free" => "N", //Access free ? (Y,N)
    "icon" => "access.gif", //Icon
    "displayable" => "Y", //Should be displayed on an app list (Y,N)
    "iorder" => 10, // install order
    "tag" => "CORE"
);

$app_acl = array(
    array(
        "name" => "ADMIN",
        "description" => N_("Admin Access"),
        "admin" => TRUE),
    array(
        "name" => "OBJECT",
        "description" => N_("object control"),
        "group_default" => "Y"));

$action_desc = array(
    array(
        "name" => "USER_ACCESS",
        "toc_order" => 4,
        "toc" => "Y",
        "acl" => "ADMIN",
        "short_name" => N_("User Access"),
        "root" => "Y"
    ),
    array(
        "name" => "GROUP_ACCESS",
        "toc_order" => 3,
        "toc" => "Y",
        "acl" => "ADMIN",
        "short_name" => N_("Group Access"),
        "layout" => "user_access.xml"
    ),

    array(
        "name" => "ROLE_ACCESS",

        "toc_order" => 2,
        "toc" => "Y",
        "acl" => "ADMIN",
        "short_name" => N_("Role Access"),
        "layout" => "user_access.xml"
    ),
    array(
        "name" => "ACCESS_USER_CHG",
        "acl" => "ADMIN"
    ),
    array(
        "name" => "USER_PAGE",
        "acl" => "ADMIN"
    ),
    array(
        "name" => "APPL_ACCESS",
        "toc" => "Y",
        "toc_order" => 1,
        "acl" => "ADMIN",
        "layout" => "user_access.xml",
        "short_name" => N_("Application Access")
    ),
    array(
        "name" => "MODIFY",
        "acl" => "ADMIN",
        "short_name" => N_("Modify any access")
    ),
    array(
        "name" => "MODIFY_OBJECT",
        "acl" => "OBJECT",
        "short_name" => N_("Modify object access")
    ),
    array(
        "name" => "ACCESS_APPL_CHG",
        "acl" => "ADMIN"
    ),
    array(
        "name" => "APPL_PAGE",
        "acl" => "ADMIN"
    ),
    array(
        "name" => "DOWNLOAD",
        "acl" => "ADMIN"
    ),
    array(
        "name" => "UPLOAD",
        "acl" => "ADMIN"
    ),
    array(
        "name" => "IMPORT_EXPORT",
        "toc" => "Y",
        "toc_order" => 5,
        "acl" => "ADMIN",
        "short_name" => N_("Import/Export")
    ),
    array(
        "name" => "EDIT",
        "short_name" => N_("Edit any access"),
        "acl" => "ADMIN"
    ),
    array(
        "name" => "EDIT_OBJECT_USER",
        "acl" => "OBJECT",
        "short_name" => N_("Edit object access by user"),
        "function" => "edit_oid",
        "layout" => "edit.xml",
        "script" => "edit.php"
    ),
    array(
        "name" => "EDIT_OBJECT",
        "acl" => "OBJECT",
        "short_name" => N_("Edit object access")
    )
);

?>
