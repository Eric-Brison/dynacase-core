<?php
/**
 * Add, modify or delete WHAT application
 * 
 *
 * @param string $appname internal name of the application
 * @param string $method may be "init","reinit","update","delete"
 * @author Anakeen 2003
 * @version $Id: appadmin.php,v 1.5 2003/08/18 15:46:41 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage WSH
 */
/**
 */
// ---------------------------------------------------------------
// $Id: appadmin.php,v 1.5 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Api/appadmin.php,v $
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

$appname = GetHttpVars("appname");
$method = GetHttpVars("method","init");

echo " $appname...$method";
$app=new Application();
$Null = "";
$app->Set($appname,$Null);
if ($method == "reinit") $app->InitApp($appname,false);
if ($method == "update") $app->InitApp($appname,true);
if ($method == "delete") $app->DeleteApp();

?>