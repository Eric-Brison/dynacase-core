<?php
// ---------------------------------------------------------------
// $Id: gate_editurl.php,v 1.1 2003/04/07 12:33:04 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Core/gate_editurl.php,v $
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
function gate_editurl(&$action) {

  $url = $action->GetParam("GATE_URL");


  // url set
  $turl=explode(",",$url);
  $action->lay->set("urlG11",$turl[0]);
  $action->lay->set("urlG12",$turl[1]);
  $action->lay->set("urlG21",$turl[2]);
  $action->lay->set("urlG22",$turl[3]);
  $action->lay->set("urlG31",$turl[4]);
  $action->lay->set("urlG32",$turl[5]);

}