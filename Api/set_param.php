<?php
/**
 * set applicative parameter value
 *
 * analyze sub-directories presents in STYLE directory
 * @author Anakeen 2004
 * @version $Id: set_param.php,v 1.3 2006/04/28 14:31:49 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage WSH
 */
/**
 */
include_once("Class.QueryDb.php");

$parname = GetHttpVars("param"); // parameter name
$parval = GetHttpVars("value"); // parameter value
$paruser = GetHttpVars("userid"); // parameter user id (option)
$parapp = GetHttpVars("appname"); // parameter app name (option)

if ($parapp != "") {
  $appid=$core->GetIdFromName($parapp);
 }

$param = new QueryDb($dbaccess,"Param");
$param->AddQuery("name='$parname'");
if ($appid) $param->AddQuery("appid=$appid");
$list=$param->Query(0,2);
if ($param->nb==0) {
  printf(_("Attribute %s not found\n"),$parname);
} elseif ($param->nb > 1) {
  printf(_("Attribute %s found is not alone\nMust precise request with appname arguments\n"),$parname);  
} else {
  $p = $list[0];
  $p->val = $parval;
  $err=$p->modify();
  if ($err != "") printf(_("Attribute %s not modified : %s\n"),$parname,$err);
  else printf(_("Attribute %s modified to %s"),$parname,$parval);
}


?>