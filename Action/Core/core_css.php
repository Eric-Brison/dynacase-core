<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: core_css.php,v 1.5 2005/03/04 17:20:02 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: core_css.php,v 1.5 2005/03/04 17:20:02 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Core/core_css.php,v $
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


function core_css(&$action) {
  
  $layout=getHttpVars("layout");

  if (ereg("([A-Z_-]+):([^:]+):{0,1}[A-Z]{0,1}", $layout, $reg)) {
    $lfile= getLayoutFile($reg[1],strtolower($reg[2]));
    if ($lfile) $action->lay = new Layout(getLayoutFile($reg[1],strtolower($reg[2])), $action);
  }

  setHeaderCache();

}