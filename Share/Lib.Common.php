<?
// ---------------------------------------------------------------
// $Id: Lib.Common.php,v 1.5 2003/05/13 08:52:38 eric Exp $
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
$LIB_COMMON = '$Id: Lib.Common.php,v 1.5 2003/05/13 08:52:38 eric Exp $';

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

function getMailAddr($userid) {
    include_once("Class.MailAccount.php");

    $from="";
    $ma = new MailAccount("",$userid);
    if ($ma->isAffected()) {
      $dom = new Domain("",$ma->iddomain);
      $from = $ma->login."@".$dom->name;
    }
    return $from;
}


function GetParam($name, $def="") {
  global $action;
  if ($action)  return $action->getParam($name,$def);
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