<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * export USER login and acl
 * the result is printed on stdout
 *
 * @author Anakeen
 * @version $Id: export_useracl.php,v 1.2 2003/08/18 15:46:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage WSH
 */
/**
 */
// ---------------------------------------------------------------
// $Id: export_useracl.php,v 1.2 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Api/export_useracl.php,v $
// ---------------------------------------------------------------
include_once ("Lib.Http.php");
include_once ("ACCESS/download.php");

$usage = new ApiUsage();
$usage->setDefinitionText("Export USER login and acl");
$usage->verify();
// use ACCESS because of its own Layout
$appl = new Application();
$appl->Set("ACCESS", $core);

download($action)
?>