<?php
/**
 * set applicative parameter value
 *
 * analyze sub-directories presents in STYLE directory
 * @author Anakeen 2004
 * @version $Id: set_param.php,v 1.1 2004/08/05 09:31:22 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage WSH
 */
/**
 */
include_once("Class.QueryDb.php");

$parname = GetHttpVars("param"); // parameter name
$parval = GetHttpVars("value"); // parameter value
$paruser = GetHttpVars("userid"); // parameter user id (option)
$parapp = GetHttpVars("appid"); // parameter app id (option)



$param = new QueryDb($dbaccess,"Param");
$param->AddQuery("name='$parname'");
$list=$param->Query(0,2);
if (count($list)==0) {
  printf(_("Attribute %s not found\n"),$parname);
} elseif (count($list) > 1) {
  printf(_("Attribute %s found is not alone\nMust precise request with userid or appid arguments\n"),$parname);  
} else {
  $p = $list[0];
  $p->val = $parval;
  $err=$p->modify();
  if ($err != "") printf(_("Attribute %s not modified : %s\n"),$parname,$err);
  else printf(_("Attribute %s modified to %s"),$parname,$parval);
}


?>