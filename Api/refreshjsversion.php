<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * regenerate js version file
 *
 * @param string $filename the file which contain new login or ACLs
 * @author Anakeen
 * @version $Id: refreshjsversion.php,v 1.2 2005/06/10 13:05:18 eric Exp $
 * @package FDL
 * @subpackage WSH
 */
/**
 */

$usage = new ApiUsage();
$usage->setDefinitionText("regenerate js version file");
$usage->verify();

$nv = getJsVersion();

$fjs = DEFAULT_PUBDIR . "/CORE/wversion.js.in";

$fc = file_get_contents($fjs);
$fc = str_replace("%VERSION%", $nv, $fc);

$cible = DEFAULT_PUBDIR . "/CORE/wversion.js";
$fj = fopen($cible, "w");
fputs($fj, $fc);
fclose($fj);

global $action;
$action->parent->param->Set("WVERSION", $nv + 1);
print "$cible [$nv]\n";
