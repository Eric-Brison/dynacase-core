<?php
// ---------------------------------------------------------------
// $Id: param_ulist.php,v 1.1 2002/05/23 16:14:40 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/param_ulist.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2000
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
// -----------------------------------
function param_ulist(&$action) {
  // -----------------------------------
    
  $userid=GetHttpVars("userid");

    
  $action->register("PARAM_ACT","PARAM_ULIST&userid=$userid");
  $u = new User();
    $list = $u-> GetUserList("TABLE");

  // select the wanted user
    while (list($k,$v)=each($list)) {
	if ($v["id"] == $userid) $list[$k]["selected"]="selected";
	else $list[$k]["selected"]="";
    }
  $action->lay->SetBlockData("SELUSER",$list);
    return;
  
  
}
?>
