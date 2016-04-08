<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * set applicative parameter value
 *
 * analyze sub-directories presents in STYLE directory
 * @subpackage WSH
 */
/**
 */
include_once ("Class.QueryDb.php");
$usage = new ApiUsage();

$usage->setDefinitionText("set applicative parameter value");
$parname = $usage->addRequiredParameter("param", "parameter name"); // parameter name
$parval = $usage->addOptionalParameter("value", "parameter value to set"); // parameter value (option)
$paruser = GetHttpVars("userid"); // parameter user id (option)
$parapp = $usage->addOptionalParameter("appname", "Parameter's application's name"); // parameter app name (option)
$usage->verify();

$appid = 0;
if ($parapp != "") {
    /** @var Application $core */
    global $core;
    $appid = $core->GetIdFromName($parapp);
}

$dbaccess = getDbAccess();
$param = new QueryDb($dbaccess, "Param");
$param->AddQuery("name='$parname'");
if ($appid) $param->AddQuery("appid=$appid");
$list = $param->Query(0, 2);
if ($param->nb == 0) {
    printf(_("Attribute %s not found\n") , $parname);
} elseif ($param->nb > 1) {
    printf(_("Attribute %s found is not alone\nMust precise request with appname arguments\n") , $parname);
} else {
    /** @var Param $p */
    $p = $list[0];
    $p->val = $parval;
    $err = $p->modify();
    if ($err != "") printf(_("Attribute %s not modified : %s\n") , $parname, $err);
    else printf(_("Attribute %s modified to %s") , $parname, $parval);
}
