<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: core_css.php,v 1.4 2005/01/07 16:59:34 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: core_css.php,v 1.4 2005/01/07 16:59:34 eric Exp $
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

   header("Cache-Control: private, max-age=3600"); // use cache client (one hour) for speed optimsation

   header("Expires: ".gmdate ("D, d M Y H:i:s T\n",time()+3600));  // for mozilla
   header("Pragma: "); // HTTP 1.0 
   header("Content-type: text/css");

}