<?php
// ---------------------------------------------------------------
// $Id: gate_savegeo.php,v 1.1 2003/04/07 12:33:04 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Core/gate_savegeo.php,v $
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






// -----------------------------------
// -----------------------------------
function gate_savegeo(&$action) {
// -----------------------------------

  $geometry    = GetHttpVars("geometry");    // the six geometries frame
  
  $action->parent->param->Set("GATE_GEO",implode(",",$geometry),
			      PARAM_USER.$action->user->id,$action->parent->id);



  redirect($action,"CORE","GATE_EDIT",
	   $action->GetParam("CORE_STANDURL"));

}
?>
