<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: gate_modurl.php,v 1.2 2003/08/18 15:46:41 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: gate_modurl.php,v 1.2 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Core/gate_modurl.php,v $
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
function gate_modurl(&$action) {
// -----------------------------------

  $turl[]    = GetHttpVars("urlG11");    // the six urls 
  $turl[]    = GetHttpVars("urlG12"); 
  $turl[]    = GetHttpVars("urlG21"); 
  $turl[]    = GetHttpVars("urlG22"); 
  $turl[]    = GetHttpVars("urlG31"); 
  $turl[]    = GetHttpVars("urlG32"); 
  

  $action->parent->param->Set("GATE_URL",implode(",",$turl),
			      PARAM_USER.$action->user->id,$action->parent->id);



  redirect($action,"CORE","GATE",
	   $action->GetParam("CORE_STANDURL"));

}
?>
