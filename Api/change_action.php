<?php
/**
 * Change an attribute of an WHAT Action
 * 
 *
 * @param string $appname internal name of the application
 * @param string $actname internal name of the action
 * @param string $attribute internal name of the field of the action
 * @param string $value new value for the attribute
 * @author Anakeen 2003
 * @version $Id: change_action.php,v 1.2 2003/08/18 15:46:41 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage WSH
 */
/**
 */

// ---------------------------------------------------------------
// $Id: change_action.php,v 1.2 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Api/change_action.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2001
// O*O  Anakeen development team
//  O   dev@anakeen.com
// ---------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------
include_once("Class.Application.php");

$appname = GetHttpVars("appname", "");
$actionname = GetHttpVars("actname", "");
$attribute = GetHttpVars("attribute", "");
$value = GetHttpVars("value", "");

if ($appname == "" || $actionname == "" || $attribute  == "") return false;
$app=new Application();
$null = "";
$app->Set($appname,$null);
if ($app->id > 0) {
  $action = new Action($app->dbaccess);
  $action->Set($actionname, $app);
  if ($action->id > 0) {
    reset($action->fields);
    while (list($k,$v) = each($action->fields)) {
      if ($v == $attribute) {
	$action->$attribute = $value;
	$action->Modify();
	return true;
      }
    }
  }
}
return false;
?>
