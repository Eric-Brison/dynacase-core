<?php

// ---------------------------------------------------------------
// $Id: logo.php,v 1.4 2003/02/12 09:50:02 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Core/logo.php,v $
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
function logo(&$action) {
  global $PHP_AUTH_USER;
  global $CacheObj;

  $CacheObj=array();
  session_unregister("CacheObj"); // clearcache
  $action->lay->set("PHP_AUTH_USER",$PHP_AUTH_USER);    

  $action->lay->set("navigator",$action->Read("navigator"));  
  $action->lay->set("navversion",$action->Read("navversion")); 
  global $zou;
  $zou="1";
  session_register("zou");

}
?>