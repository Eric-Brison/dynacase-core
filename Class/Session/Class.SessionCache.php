<?php
// ---------------------------------------------------------------------------
// Marc Claverie (marc.claverie@anakeen.com)- anakeen 2000 
// ---------------------------------------------------------------------------
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
// ---------------------------------------------------------------------------
// $Id: Class.SessionCache.php,v 1.1 2002/01/28 16:55:29 eric Exp $
// ---------------------------------------------------------------------------
$DROITS_CLASS_SESSIONVAR_PHP = '$Id: Class.SessionCache.php,v 1.1 2002/01/28 16:55:29 eric Exp $';

include_once('Class.DbObj.php');

Class SessionCache extends DbObj
{

var $fields = array (  "index", "lasttime");

var $id_fields = array ("index");

var $dbtable = "session_cache";

var $sqlcreate = "create table session_cache ( index varchar(100), 
			    lasttime	    int);";
 var $isCacheble= false;


 function SessionCache($dbaccess='', $id='',$res='',$dbid=0) {
   DbObj::DbObj($dbaccess, $id,$res,$dbid);
   if ((! $this->isAffected()) && ($id != '')) {
     $this->index = $id;
     
     $date = gettimeofday();
     $this->lasttime = $date['sec'];
     $this->Add();
     
   }
 }
 // modify with current date
 function setTime() {
      $date = gettimeofday();
      $this->lasttime = $date['sec'];
      $this->Modify();
 }



}
?>
