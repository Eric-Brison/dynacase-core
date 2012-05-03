<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * regenerate js version file
 *
 * @param string $filename the file which contain new login or ACLs
 * @author Anakeen 2002
 * @version $Id: refreshjsversion.php,v 1.2 2005/06/10 13:05:18 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage WSH
 */
/**
 */

$usage = new ApiUsage();
$usage->setText("regenerate js version file");
$usage->verify();

$nv = getJsVersion();

$fjs = getParam("CORE_PUBDIR") . "/CORE/wversion.js.in";

$fc = file_get_contents($fjs);
$fc = str_replace("%VERSION%", $nv, $fc);

$cible = getParam("CORE_PUBDIR") . "/CORE/wversion.js";
$fj = fopen($cible, "w");
fputs($fj, $fc);
fclose($fj);

$action->parent->param->Set("WVERSION", $nv);
print "$cible [$nv]\n";
?>