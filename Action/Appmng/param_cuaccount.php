<?php
// ---------------------------------------------------------------
// $Id: param_cuaccount.php,v 1.1 2002/07/29 11:15:18 marc Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/param_cuaccount.php,v $
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
function param_cuaccount(&$action) {
  // -----------------------------------
    

    
  $action->lay->Set("userid",$action->user->id);
    return;
  
  
}
?>
