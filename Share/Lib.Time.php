<?php
/**
 * Set of usefull Time functions
 *
 * @author Anakeen 2000
 * @version $Id: Lib.Time.php,v 1.2 2003/08/18 15:46:42 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
/**
 */
// ---------------------------------------------------------------
// $Id: Lib.Time.php,v 1.2 2003/08/18 15:46:42 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Share/Lib.Time.php,v $
// ---------------------------------------------------------------
//    O   Anakeen - 2000
//   O*O  Anakeen Developement Team
//    O   dev@anakeen.com
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


function hour2sec($h=0, $m=0, $s=0) {
  return (($h * 3600) + ($m * 60) + $s);
}

function sec2hour($s, &$H, &$M, &$S) {
  $H = ($s / 3600);
  settype($H, "integer");
  $M = (($s % 3600) / 60 );
  settype($M, "integer");
  $S = ($s - ($H*3600) - ($M * 60));
  settype($S, "integer");
  if (strlen($H) == 1) $H = "0{$H}";
  if (strlen($M) == 1) $M = "0{$M}";
  if (strlen($S) == 1) $S = "0{$S}";
}
  
?>
