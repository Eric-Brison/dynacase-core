<?php
// ---------------------------------------------------------------
// $Id: footer.php,v 1.1 2003/05/19 09:59:12 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Core/footer.php,v $
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
function footer(&$action) {
  // -----------------------------------

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");


  $plugzone = $action->getParam("CORE_PLUGZONE");

  $action->lay->set("plugzone","");
  if ($plugzone != "") {
    if ( ereg("([A-Z]+):([^:]+)", $plugzone, $reg)) {
      $appplug=$reg[1];
      $actplug=$reg[2];
      if ($idappplug=$action->parent->Exists($appplug)) {
      $permission = new Permission($action->dbaccess, array($action->user->id,$idappplug));

      if ($permission->isAffected() && (count($permission->privileges) > 0)) {
	  // can see the plug
	  $action->lay->set("plugzone","[ZONE $plugzone]");
	}
      }
      
    }
  }

}

// EOF