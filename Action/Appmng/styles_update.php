<?php
// ---------------------------------------------------------------
// $Id: styles_update.php,v 1.1 2002/01/08 12:41:33 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/Attic/styles_update.php,v $
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
// $Log: styles_update.php,v $
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.1  2001/02/06 11:40:11  marianne
// Prise en compte des styles, parametres et actions
//
//
// ---------------------------------------------------------------
include_once("Class.TableLayout.php");
include_once("Class.QueryDb.php");

// -----------------------------------
function styles_update(&$action) {
// -----------------------------------


  $stylesel=GetHttpVars("stylesel");
  $style = new Style("",$stylesel);
  $action->log->info("Update ".$style->name);
  $style->UpdateStyle();

  redirect($action,"APPMNG","STYLESLIST");
}
?>
