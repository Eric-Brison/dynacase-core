<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: app_update.php,v 1.2 2003/08/18 15:46:41 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage APPMNG
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: app_update.php,v 1.2 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/app_update.php,v $
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
// $Log: app_update.php,v $
// Revision 1.2  2003/08/18 15:46:41  eric
// phpdoc
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.4  2001/07/25 12:51:12  eric
// ajout fonction updateall
//
// Revision 1.3  2000/11/02 18:39:08  marc
// OK
//
// Revision 1.2  2000/11/02 18:35:14  marc
// Creation (log info : application )
//
// Revision 1.1.1.1  2000/10/16 08:52:39  yannick
// Importation initiale
//
//
//
// ---------------------------------------------------------------
include_once("Class.TableLayout.php");
include_once("Class.QueryDb.php");

// -----------------------------------
function app_update(&$action) {
// -----------------------------------


  $appsel=GetHttpVars("appsel");
  $application = new Application("",$appsel);
  $action->log->info("Update ".$application->name);
  $application->Set($application->name, $action->parent);
  $application->UpdateApp();

  redirect($action,"APPMNG","");
}
// -----------------------------------
function app_updateAll(&$action) {
// -----------------------------------


  $application = new Application();
  $application->UpdateAllApp();

  redirect($action,"APPMNG","");
}
?>
