<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Class.Group.php,v 1.4 2003/08/18 15:46:42 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: Class.Group.php,v 1.4 2003/08/18 15:46:42 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Class/Appmng/Class.Group.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2000
// O*O  Anakeen Development Team
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

$CLASS_USER_PHP = '$Id: Class.Group.php,v 1.4 2003/08/18 15:46:42 eric Exp $';
include_once('Class.DbObj.php');
include_once('Class.QueryDb.php');
include_once('Class.Log.php');
include_once('Class.Application.php');

Class Group extends DbObj
{
var $fields = array ( "iduser","idgroup");

var $id_fields = array ("iduser");

var $dbtable = "groups";


var $sqlcreate = "
create table groups ( iduser      int not null,
                      idgroup    int not null);
create index groups_idx1 on groups(iduser);";

 var $groups = array(); // user groups

function GetGroups()
{
  $query = new QueryDb($this->dbaccess, "Group");

  $query-> AddQuery("iduser='{$this->iduser}'");

  $list = $query->Query();

  if ($query->nb >0) {
    while (list($k,$v) = each($list)) {
      $this->groups[] = $v->idgroup;
    }
  } else {
    return false;
  }

  return true;
}

function PostSelect()
{
  // initialise groups for a user
  $this->GetGroups();
}

function PostUpdate() {
  $this->FreedomCopyGroup();
}
function PostDelete() {
  $this->FreedomCopyGroup();
}
function PostInsert() {
  $this->FreedomCopyGroup();
}
function FreedomCopyGroup() {
  
  $wsh = GetParam("CORE_PUBDIR")."/wsh.php";
  $cmd = $wsh . " --api=freedom_groups";

  exec($cmd);
}
// get direct group and group of group
function GetAllGroups()
{
  $allg = $this->groups;
  while (list($k,$gid) = each($this->groups)) {
    $og = new Group($this->dbaccess, $gid);
    $allg = array_merge($allg, $og-> GetAllGroups());
  }
  $allg = array_unique($allg);

  return $allg;
}
}
?>
