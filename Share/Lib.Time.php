<?php
/**
 * Set of usefull Time functions
 *
 * @author Anakeen 2000
 * @version $Id: Lib.Time.php,v 1.2 2003/08/18 15:46:42 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 */
/**
 */
// ---------------------------------------------------------------
// $Id: Lib.Time.php,v 1.2 2003/08/18 15:46:42 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Share/Lib.Time.php,v $
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
