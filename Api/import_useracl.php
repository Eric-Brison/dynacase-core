<?php
/**
 * import USER login and acl
 *
 * @param string $filename the file which contain new login or ACLs
 * @author Anakeen 2002
 * @version $Id: import_useracl.php,v 1.2 2003/08/18 15:46:41 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage WSH
 */
/**
 */
// ---------------------------------------------------------------
// $Id: import_useracl.php,v 1.2 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Api/import_useracl.php,v $
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

include_once("Lib.Http.php");
include_once("ACCESS/upload.php");

$filename = GetHttpVars("filename");
$content = file($filename);




$tnewacl=array();  
while (list($k,$v) = each($content)) {
    switch (substr($v, 0, 1)) {
    case "U":
      changeuser($action, substr($v,2), true);
      break;
    case "A":
      changeacl($action, substr($v,2), true);
      break;
    }
}

?>