<?php
/**
 * Common util functions
 *
 * @author Anakeen 2002
 * @version $Id: Lib.Common.php,v 1.9 2003/08/18 15:46:42 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
/**
 */
// ---------------------------------------------------------------
// $Id: Lib.Common.php,v 1.9 2003/08/18 15:46:42 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Share/Lib.Common.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2000
// O*O  Anakeen Development Team
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
$LIB_COMMON = '$Id: Lib.Common.php,v 1.9 2003/08/18 15:46:42 eric Exp $';

// library of utilies functions

function print_r2($z) {
  print "<PRE>";
  print_r($z);
  print "</PRE>";
}

function AddLogMsg($msg) {
    global $action;
    if (isset($action->parent))
      $action->parent->AddLogMsg($msg);
}
function AddWarningMsg($msg) {
    global $action;
    if (isset($action->parent))
      $action->parent->AddWarningMsg($msg);
}

function getMailAddr($userid) {
    include_once("Class.MailAccount.php");

    $from="";
    $ma = new MailAccount("",$userid);
    if ($ma->isAffected()) {
      $dom = new Domain("",$ma->iddomain);
      $from = $ma->login."@".$dom->name;
    } else {
      $user = new User("",$userid);

      if ($user->isAffected() && ($user->isgroup=="Y") && ($user->iddomain > 1)) {
	$dom = new Domain("",$user->iddomain);
	$from = $user->login."@".$dom->name;
	
      }
    }
    return $from;
}


function GetParam($name, $def="") {
  global $action;
  if ($action)  return $action->getParam($name,$def);
}

function getLayoutFile($app, $layfile) {
  $socStyle = Getparam("CORE_SOCSTYLE");
  if ($socStyle != "") {
    $root = Getparam("CORE_PUBDIR");
    $file = $root."/$app/Layout/$socStyle/$layfile";

    if (file_exists($file))  return($file);
    
  }
  return $app."/Layout/".$layfile;
}

function microtime_diff($a,$b) {
    list($a_micro, $a_int)=explode(' ',$a);
     list($b_micro, $b_int)=explode(' ',$b);
     if ($a_int>$b_int) {
        return ($a_int-$b_int)+($a_micro-$b_micro);
     } elseif ($a_int==$b_int) {
        if ($a_micro>$b_micro) {
          return ($a_int-$b_int)+($a_micro-$b_micro);
        } elseif ($a_micro<$b_micro) {
           return ($b_int-$a_int)+($b_micro-$a_micro);
        } else {
          return 0;
        }
     } else { // $a_int<$b_int
        return ($b_int-$a_int)+($b_micro-$a_micro);
     }
}
?>